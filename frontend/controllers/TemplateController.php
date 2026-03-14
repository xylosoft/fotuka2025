<?php

namespace frontend\controllers;

use common\models\WebsitePublication;
use common\models\WebsiteTemplate;
use Yii;
use yii\data\Pagination;
use yii\db\Query;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


class TemplateController extends Controller
{
    public function actionTemplates()
    {
        $this->layout = 'folder';
        $templateQuery = WebsiteTemplate::findActive()->orderBy(['updated_at' => SORT_DESC]);
        $templateQuery = $this->applyOwnershipScope($templateQuery, WebsiteTemplate::tableName());

        $templatePagination = new Pagination([
            'totalCount' => $templateQuery->count(),
            'pageSize' => 10,
            'pageParam' => 'templatePage',
        ]);

        $templates = $templateQuery
            ->offset($templatePagination->offset)
            ->limit($templatePagination->limit)
            ->all();

        $publicationQuery = WebsitePublication::findActive('publication')
            ->joinWith(['template template'])
            ->orderBy(['publication.updated_at' => SORT_DESC]);
        //echo $publicationQuery->createCommand()->rawSql;die;

        $publicationQuery = $this->applyOwnershipScope($publicationQuery, 'publication');

        $publicationPagination = new Pagination([
            'totalCount' => $publicationQuery->count(),
            'pageSize' => 10,
            'pageParam' => 'publicationPage',
        ]);

        $publications = $publicationQuery
            ->offset($publicationPagination->offset)
            ->limit($publicationPagination->limit)
            ->all();

        $folderNames = [];
        foreach ($publications as $publication) {
            $folderNames[$publication->id] = $this->resolveFolderNameFromId($publication->folder_id);
        }

        return $this->render('templates', [
            'templates' => $templates,
            'templatePagination' => $templatePagination,
            'publications' => $publications,
            'publicationPagination' => $publicationPagination,
            'folderNames' => $folderNames,
        ]);
    }

    public function actionTemplateEditor($id = null)
    {
        $this->layout = 'folder';

        if ($id) {
            $model = $this->findTemplate($id);
        } else {
            $model = new WebsiteTemplate();
            $model->name = '';
            $model->setDefinitionArray(WebsiteTemplate::defaultDefinition());
        }

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->name = trim((string) $model->name);
                if ($model->name === '') {
                    $model->addError('name', 'Please enter a name for this Template');
                    Yii::$app->session->setFlash('error', 'Please fix the highlighted problems and save again.');
                    return $this->render('editor', [
                        'model' => $model,
                        'definition' => $model->getDefinitionArray(),
                    ]);
                }

                if ($model->isNewRecord) {
                    $model->user_id = $this->currentUserId();
                    $model->customer_id = $this->currentCustomerId();
                }

                $model->name = trim((string)$model->name);
                // TODO: Check if this is still needed. I don't think so.
                if ($model->name === '') {
                    $model->name = 'Untitled Template';
                }

                if (empty($model->definition_json)) {
                    $model->setDefinitionArray(WebsiteTemplate::defaultDefinition());
                }

                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Template saved successfully.');
                    return $this->redirect(['templates']    );
                }

                Yii::$app->session->setFlash('error', 'Please fix the highlighted problems and save again.');
            } else {
                Yii::$app->session->setFlash('error', 'No template data was received.');
            }
        }

        return $this->render('editor', [
            'model' => $model,
            'definition' => $model->getDefinitionArray(),
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findTemplate($id);

        if ($model->isInUse()) {
            Yii::$app->session->setFlash('error', 'This template cannot be deleted because it is currently in use by a published folder.');
            return $this->redirect(['templates']);
        }

        $model->deleted = time();
        $model->deleted_by_user_id = $this->currentUserId();
        $model->save(false, ['deleted', 'deleted_by_user_id']);

        Yii::$app->session->setFlash('success', 'Template deleted.');
        return $this->redirect(['templates']);
    }

    public function actionPublish($id, $template_id = null)
    {
        $this->layout = 'folder';
        $folder = $this->fetchFolder($id);

        if (!$folder) {
            throw new NotFoundHttpException('Folder not found.');
        }

        $publication = null;
        $template = null;
        $definition = null;

        $publication = WebsitePublication::findActive()
            ->andWhere(['folder_id' => (int) $id])
            ->one();

        if ($publication) {
            $template = $publication->template;
            $definition = $template
                ? $template->getDefinitionArray()
                : $publication->getSnapshotArray(); // legacy fallback only
        } elseif ($template_id) {
            $template = $this->findTemplate($template_id);
            $definition = $template->getDefinitionArray();
        }

        if (Yii::$app->request->isPost) {
            $wpPost = Yii::$app->request->post('WebsitePublication', []);

            if (!$publication) {
                $publication = new WebsitePublication();
                $publication->user_id = $this->currentUserId();
                $publication->customer_id = $this->currentCustomerId();
                $publication->folder_id = (int) $id;
            }

            $selectedTemplateId = (int) (
                $wpPost['template_id']
                ?? $template_id
                ?? $publication->template_id
                ?? 0
            );

            if ($selectedTemplateId <= 0) {
                Yii::$app->session->setFlash('error', 'Please select a template before publishing.');
                return $this->refresh();
            }

            $template = $this->findTemplate($selectedTemplateId);
            $definition = $template->getDefinitionArray();

            $publishDefaults = $definition['publish_defaults'] ?? [
                    'is_password_protected' => false,
                    'allow_download_all' => false,
                ];

            if ($publication->isNewRecord) {
                $publication->is_password_protected = !empty($publishDefaults['is_password_protected']) ? 1 : 0;
                $publication->allow_download_all = !empty($publishDefaults['allow_download_all']) ? 1 : 0;
            }

            $uri = trim((string) ($wpPost['uri'] ?? ''));
            $pageTitle = trim((string) ($wpPost['page_title'] ?? $this->resolveFolderName($folder)));
            $isProtected = !empty($wpPost['is_password_protected']) ? 1 : 0;
            $allowDownloadAll = !empty($wpPost['allow_download_all']) ? 1 : 0;

            $decodedValues = $this->normalizePublicationValuesForDefinition(
                $wpPost['values_json'] ?? '{}',
                $definition
            );

            $valuesJson = Json::encode($decodedValues, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $publication->folder_id = (int) $id;
            $publication->template_id = $template->id;
            $publication->page_title = $pageTitle;
            $publication->uri = $uri ?: $this->defaultFolderSlug($folder);
            $publication->is_password_protected = $isProtected;
            $publication->allow_download_all = $allowDownloadAll;
            $publication->plain_password = (string) ($wpPost['plain_password'] ?? '');
            $publication->template_snapshot_json = Json::encode($definition, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $publication->values_json = $valuesJson;

            try {
                if (!$publication->save()) {
                    Yii::$app->session->setFlash('error', 'Please review the publishing form and try again.');
                } else {
                    Yii::$app->session->setFlash('success', 'Folder published successfully.');
                    return $this->redirect(['templates']);
                }
            } catch (\Throwable $e) {
                error_log($e->getMessage());
                error_log($e->getTraceAsString());
                Yii::$app->session->setFlash('error', 'An unexpected error occurred while publishing.');
            }
        }

        $templates = WebsiteTemplate::findActive()->orderBy(['name' => SORT_ASC])->all();
        $assets = $this->fetchAssets($id);

        // TODO: Review
        $customerId = (int) Yii::$app->user->identity->customer_id;
        $assets = $this->buildPublishPickerAssets($assets, $customerId);

        return $this->render('publish', [
            'folder' => $folder,
            'folderName' => $this->resolveFolderName($folder),
            'folderDefaultSlug' => $this->defaultFolderSlug($folder),
            'publication' => $publication ?: new WebsitePublication(),
            'template' => $template,
            'definition' => $definition,
            'templates' => $templates,
            'assets' => $assets,
        ]);
    }

    public function actionPublicationDelete($id)
    {
        $publication = $this->findPublication($id);
        $publication->deleted = time();
        $publication->deleted_by_user_id = $this->currentUserId();
        $publication->save(false, ['deleted', 'deleted_by_user_id']);

        Yii::$app->session->setFlash('success', 'Published page removed. It will no longer be available publicly.');
        return $this->redirect(['templates']);
    }

    public function actionPage($uri)
    {
        $this->layout = false;
        $publication = WebsitePublication::findActive()
            ->where(['uri' => $uri])
            ->one();

        if (!$publication) {
            throw new NotFoundHttpException('Published page not found.');
        }

        if ((int) $publication->is_password_protected === 1 && !$this->hasPageAccess($publication)) {
            return $this->redirect(['page-password', 'uri' => $uri]);
        }

        $definition = $publication->template
            ? $publication->template->getDefinitionArray()
            : $publication->getSnapshotArray();

        $values = $this->normalizePublicationValuesForDefinition(
            $publication->getValuesArray(),
            $definition
        );

        return $this->render('page', [
            'publication' => $publication,
            'definition' => $definition,
            'values' => $values,
            'folderName' => $this->resolveFolderNameFromId($publication->folder_id),
        ]);
    }

    public function actionPagePassword($uri)
    {
        $this->layout = false;
        $publication = WebsitePublication::findActive()
            ->where(['uri' => $uri])
            ->one();

        if (!$publication) {
            throw new NotFoundHttpException('Published page not found.');
        }

        if ((int) $publication->is_password_protected !== 1) {
            return $this->redirect(['page', 'uri' => $uri]);
        }

        if (Yii::$app->request->isPost) {
            $password = (string) Yii::$app->request->post('page_password', '');

            if ($publication->validatePassword($password)) {
                Yii::$app->session->set($this->pageAccessSessionKey($publication), true);
                return $this->redirect(['page', 'uri' => $uri]);
            }

            Yii::$app->session->setFlash('error', 'Invalid password.');
        }

        return $this->render('password', [
            'publication' => $publication,
        ]);
    }

    protected function applyOwnershipScope($query, $alias = null)
    {
        $columnPrefix = $alias ? $alias . '.' : '';
        $customerId = $this->currentCustomerId();
        $userId = $this->currentUserId();

        if ($customerId) {
            $query->andWhere([$columnPrefix . 'customer_id' => $customerId]);
        } elseif ($userId) {
            $query->andWhere([$columnPrefix . 'user_id' => $userId]);
        }

        return $query;
    }

    protected function findTemplate($id)
    {
        $query = WebsiteTemplate::findActive()->andWhere(['id' => (int) $id]);
        $query = $this->applyOwnershipScope($query, WebsiteTemplate::tableName());
        $model = $query->one();

        if (!$model) {
            throw new NotFoundHttpException('Template not found.');
        }

        return $model;
    }

    protected function findPublication($id)
    {
        $query = WebsitePublication::findActive()->andWhere(['id' => (int) $id]);
        $query = $this->applyOwnershipScope($query, WebsitePublication::tableName());
        $model = $query->one();

        if (!$model) {
            throw new NotFoundHttpException('Publication not found.');
        }

        return $model;
    }

    protected function fetchFolder($folderId)
    {
        if ((int) $folderId <= 0) {
            return null;
        }

        return (new Query())
            ->from('folders')
            ->where(['id' => (int) $folderId])
            ->one();
    }

    protected function fetchAssets($folderId)
    {
        if ((int) $folderId <= 0) {
            return [];
        }

        $customerId = $this->currentCustomerId();
        $userId = $this->currentUserId();

        $query = (new Query())
            ->select([
                'asset_id' => 'a.id',
                'status' => 'a.status',
                'title' => 'a.title',
                'description' => 'a.description',
                'thumbnail_url' => 'a.thumbnail_url',
                'preview_url' => 'a.preview_url',
                'type' => 'f.type',
                'width' => 'f.width',
                'height' => 'f.height',
                'filename' => 'f.filename',
                'extension' => 'f.extension',
                'orientation' => 'f.orientation',
            ])
            ->from(['a' => 'assets'])
            ->innerJoin(['f' => 'files'], 'f.id = a.file_id')
            ->where([
                'a.folder_id' => (int) $folderId,
                'a.deleted' => null,
                'a.status' => 'active',
                'f.type' => 'image',
            ])
            ->orderBy(['a.id' => SORT_DESC]);

        if ($customerId) {
            $query->andWhere([
                'a.customer_id' => $customerId,
                'f.customer_id' => $customerId,
            ]);
        } elseif ($userId) {
            $query->andWhere([
                'a.user_id' => $userId,
                'f.user_id' => $userId,
            ]);
        }

        return $query->all();
    }

    protected function resolveFolderNameFromId($folderId)
    {
        $folder = $this->fetchFolder($folderId);
        return $this->resolveFolderName($folder);
    }

    protected function resolveFolderName($folder)
    {
        if (!$folder || !is_array($folder)) {
            return 'Folder';
        }

        if (!empty($folder['name'])) {
            return (string) $folder['name'];
        }

        return 'Folder #' . ($folder['id'] ?? '');
    }

    protected function defaultFolderSlug($folder)
    {
        return Inflector::slug($this->resolveFolderName($folder), '-');
    }

    protected function currentUserId()
    {
        return (int) (Yii::$app->user->id ?? 0);
    }

    protected function currentCustomerId()
    {
        $identity = Yii::$app->user->identity ?? null;
        return (int) ($identity->customer_id ?? 0);
    }

    protected function pageAccessSessionKey(WebsitePublication $publication)
    {
        return 'published_page_access_' . $publication->id;
    }

    protected function hasPageAccess(WebsitePublication $publication)
    {
        return (bool) Yii::$app->session->get($this->pageAccessSessionKey($publication), false);
    }

    protected function normalizePublicationValuesForDefinition($rawValues, array $definition): array
    {
        if (is_array($rawValues)) {
            $decoded = $rawValues;
        } else {
            $raw = trim((string) $rawValues);

            if ($raw === '' || $raw === '[]') {
                $raw = '{}';
            }

            try {
                $decoded = Json::decode($raw, true);
            } catch (\Throwable $e) {
                error_log('Invalid values_json: ' . $raw);
                error_log('values_json decode error: ' . $e->getMessage());
                $decoded = [];
            }
        }

        if (!is_array($decoded)) {
            $decoded = [];
        }

        if (isset($decoded['components']) && is_array($decoded['components'])) {
            return [
                'components' => $decoded['components'],
            ];
        }

        $components = [];
        $definitionComponents = $definition['components'] ?? [];

        foreach ($definitionComponents as $component) {
            $componentId = (string) ($component['id'] ?? '');
            $fieldName = (string) ($component['field_name'] ?? '');
            $type = (string) ($component['type'] ?? '');

            if ($componentId === '' || $fieldName === '') {
                continue;
            }

            if ($type === 'dynamic_text' && !empty($decoded['dynamic_text'][$fieldName])) {
                $components[$componentId] = [
                    'type' => 'dynamic_text',
                    'html' => (string) ($decoded['dynamic_text'][$fieldName]['html'] ?? ($component['default_html'] ?? '<p></p>')),
                ];
                continue;
            }

            if ($type === 'image' && !empty($decoded['image'][$fieldName])) {
                $components[$componentId] = [
                    'type' => 'image',
                    'asset' => $decoded['image'][$fieldName],
                ];
                continue;
            }

            if ($type === 'carousel' && !empty($decoded['carousel'][$fieldName])) {
                $items = $decoded['carousel'][$fieldName]['items'] ?? [];
                $components[$componentId] = [
                    'type' => 'carousel',
                    'items' => is_array($items) ? array_values(array_filter($items)) : [],
                ];
                continue;
            }

            if ($type === 'gallery' && !empty($decoded['gallery'][$fieldName])) {
                $items = $decoded['gallery'][$fieldName]['items'] ?? [];
                $components[$componentId] = [
                    'type' => 'gallery',
                    'auto_folder_gallery' => !empty($decoded['gallery'][$fieldName]['auto_folder_gallery']) ? 1 : 0,
                    'items' => is_array($items) ? array_values(array_filter($items)) : [],
                ];
            }
        }

        return [
            'components' => $components,
        ];
    }

    private function buildPublishPickerAssets(array $assets, int $customerId): array
    {
        $assetIds = [];

        foreach ($assets as $asset) {
            $assetId = (int) ($asset['asset_id'] ?? $asset['id'] ?? 0);
            if ($assetId > 0) {
                $assetIds[] = $assetId;
            }
        }

        $assetIds = array_values(array_unique($assetIds));

        $labelsByAsset = [];

        if ($assetIds) {
            $labelRows = (new Query())
                ->select([
                    'asset_id' => 'al.asset_id',
                    'label_name' => 'l.name',
                ])
                ->from(['al' => 'asset_labels'])
                ->innerJoin(['l' => 'labels'], 'l.id = al.label_id')
                ->where([
                    'al.customer_id' => $customerId,
                    'al.asset_id' => $assetIds,
                ])
                ->orderBy(['l.name' => SORT_ASC])
                ->all();

            foreach ($labelRows as $row) {
                $assetId = (int) ($row['asset_id'] ?? 0);
                $labelName = trim((string) ($row['label_name'] ?? ''));

                if ($assetId > 0 && $labelName !== '') {
                    $labelsByAsset[$assetId][] = $labelName;
                }
            }
        }

        $result = [];

        foreach ($assets as $asset) {
            $assetId = (int) ($asset['asset_id'] ?? $asset['id'] ?? 0);
            if ($assetId <= 0) {
                continue;
            }

            $status = strtolower(trim((string) ($asset['status'] ?? 'active')));
            if ($status !== 'active') {
                continue;
            }

            $fileCategory = strtolower(trim((string) (
                $asset['type']
                ?? $asset['file_category']
                ?? $asset['file_kind']
                ?? $asset['file_type_group']
                ?? ''
            )));

            $mimeType = strtolower(trim((string) ($asset['file_type'] ?? $asset['mime_type'] ?? '')));

            $isImage = (
                $fileCategory === 'image'
                || strpos($mimeType, 'image/') === 0
            );

            if (!$isImage) {
                continue;
            }

            $width = (int) ($asset['width'] ?? 0);
            $height = (int) ($asset['height'] ?? 0);

            $result[] = [
                'asset_id' => $assetId,
                'title' => (string) ($asset['title'] ?? ''),
                'description' => (string) ($asset['description'] ?? ''),
                'filename' => (string) ($asset['filename'] ?? ''),
                'extension' => strtolower((string) (
                    $asset['extension']
                    ?? pathinfo((string) ($asset['filename'] ?? ''), PATHINFO_EXTENSION)
                )),
                'orientation' => strtolower((string) ($asset['orientation'] ?? '')),
                'width' => $width,
                'height' => $height,
                'thumbnail_url' => (string) ($asset['thumbnail_url'] ?? ''),
                'preview_url' => (string) ($asset['preview_url'] ?? ''),
                'labels' => array_values(array_unique($labelsByAsset[$assetId] ?? [])),
            ];
        }

        return $result;
    }
}