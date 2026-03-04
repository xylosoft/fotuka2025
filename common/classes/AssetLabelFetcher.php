<?php

namespace common\classes;

use Aws\Rekognition\RekognitionClient;
use Yii;
use yii\base\BaseObject;
use yii\db\Transaction;
use yii\db\Query;
use common\models\Asset;
use common\models\Label;

class AssetLabelFetcher extends BaseObject
{
private RekognitionClient $client;

public int $maxLabels = 30;
public int $minConfidence = 70;

    public function init()
    {
        parent::init();

        // Parameters (as you specified)
        $this->maxLabels = (int)Yii::$app->params['rekognition']['maxLabels'];
        $this->minConfidence = (int)Yii::$app->params['rekognition']['minConfidence'];

        // Rekognition client (your requested approach, corrected to valid AWS SDK syntax)
        $this->client = new RekognitionClient([
            'region' => Yii::$app->params['AWS_REGION'],
            'version' => 'latest',
            'credentials' => [
                'key' => Yii::$app->params['AWS_ACCESS_KEY_ID'],
                'secret' => Yii::$app->params['AWS_SECRET_ACCESS_KEY'],
            ],
        ]);
    }

    /**
     * Fetch labels for an asset (no DB writes).
     *
     * Returns:
     * [
     *   ['name' => 'dog', 'confidence' => 97],
     *   ['name' => 'animal', 'confidence' => 97],
     *   ...
     * ]
     */
    public function fetchLabelsForAsset(Asset $asset): array
    {
        $env = YII_ENV_DEV ? 'dev' : 'prod';
        $bucket = Yii::$app->params['AWS_BUCKET'];
        $customerId = $asset->customer_id;
        $key = "{$env}/preview/{$customerId}/{$asset->id}";
        echo "Rekognition ASSET KEY: $key\n";

        $result = $this->client->detectLabels([
            'Image' => [
                'S3Object' => [
                    'Bucket' => $bucket,
                    'Name' => $key,
                ],
            ],
            'MaxLabels' => $this->maxLabels,
            'MinConfidence' => $this->minConfidence,
        ]);

        $labels = $result['Labels'] ?? [];

        // Flatten + dedupe: keep max confidence per label
        $map = []; // name => confidenceInt

        foreach ($labels as $label) {
            $name = $this->norm($label['Name'] ?? null);
            if ($name === null) {
                continue;
            }

            $conf = $this->toConfidenceInt($label['Confidence'] ?? null);
            $this->keepMax($map, $name, $conf);

            $parents = $label['Parents'] ?? [];
            foreach ($parents as $p) {
                $pName = $this->norm($p['Name'] ?? null);
                if ($pName === null) {
                    continue;
                }
                $this->keepMax($map, $pName, $conf);
            }
        }

        $out = [];
        foreach ($map as $name => $conf) {
            $out[] = ['name' => $name, 'confidence' => $conf];
        }

        usort($out, function ($a, $b) {
            $c = ($b['confidence'] <=> $a['confidence']);
            return $c !== 0 ? $c : strcmp($a['name'], $b['name']);
        });

        return $out;
    }

    /**
     * Fetch labels and persist them to:
     * - labels
     * - asset_labels (customer scoped)
     *
     * This is typically what you want in your thumbnail/preview pipeline.
     */
    public function fetchAndSaveLabelsForAsset(Asset $asset): array
    {
        if (empty($asset->customer_id)) {
            throw new \RuntimeException("Asset {$asset->id} is missing customer_id.");
        }

        // If this asset already has labels, skip this process to avoid overwriting
        $existingCount = (int)$asset->getAssetLabels()->count();
        if ($existingCount > 0) {
            return [];
        }

        $labels = $this->fetchLabelsForAsset($asset);
        if (empty($labels)) {
            return [];
        }

        $db = Yii::$app->db;
        $tx = $db->beginTransaction(Transaction::SERIALIZABLE);

        try {
            $customerId = (int)$asset->customer_id;
            $assetId = (int)$asset->id;

            // 1) Ensure label rows exist
            $names = array_values(array_unique(array_map(fn($x) => $x['name'], $labels)));
            $rows = array_map(fn($n) => [$n], $names);

            $sql = $db->createCommand()
                ->batchInsert(Label::tableName(), ['name'], $rows)
                ->getRawSql();

            // MySQL upsert pattern (fast)
            $sql .= " ON DUPLICATE KEY UPDATE name = VALUES(name)";
            $db->createCommand($sql)->execute();

            // 2) Fetch ids for those names
            $labelRows = (new Query())
                ->from(Label::tableName())
                ->select(['id', 'name'])
                ->where(['name' => $names])
                ->all($db);

            $nameToId = [];
            foreach ($labelRows as $r) {
                $nameToId[$r['name']] = (int)$r['id'];
            }

            // 3) Replace existing rows for this asset + customer
            $db->createCommand()
                ->delete('asset_labels', [
                    'customer_id' => $customerId,
                    'asset_id' => $assetId,
                ])
                ->execute();

            // 4) Insert join rows
            $joinRows = [];
            foreach ($labels as $l) {
                $labelId = $nameToId[$l['name']] ?? null;
                if (!$labelId) {
                    continue;
                }
                $joinRows[] = [$customerId, $assetId, (int)$labelId, (int)$l['confidence']];
            }

            if (!empty($joinRows)) {
                $db->createCommand()
                    ->batchInsert('asset_labels', ['customer_id', 'asset_id', 'label_id', 'confidence'], $joinRows)
                    ->execute();
            }

            $tx->commit();
            return $labels;

        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    private function norm(?string $s): ?string
    {
        if ($s === null) return null;
        $s = trim($s);
        if ($s === '') return null;
        return mb_strtolower($s);
    }

    private function toConfidenceInt($confidence): int
    {
        if ($confidence === null) return 0;
        $c = (int)round((float)$confidence);
        if ($c < 0) $c = 0;
        if ($c > 100) $c = 100;
        return $c;
    }

    private function keepMax(array &$map, string $name, int $conf): void
    {
        if (!isset($map[$name]) || $conf > $map[$name]) {
            $map[$name] = $conf;
        }
    }
}