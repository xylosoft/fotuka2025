<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $customer_id
 * @property int $asset_id
 * @property int $label_id
 * @property int|null $confidence
 */
class AssetLabel extends ActiveRecord
{
    public static function tableName()
    {
        return 'asset_labels';
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    public function rules()
    {
        return [
            [['customer_id', 'asset_id', 'label_id'], 'required'],
            [['customer_id', 'asset_id', 'label_id'], 'integer'],
            [['confidence'], 'integer', 'min' => 0, 'max' => 100],
        ];
    }

    public function getLabel()
    {
        return $this->hasOne(Label::class, ['id' => 'label_id']);
    }

    public function getAssetLabels()
    {
        return $this->hasMany(\common\models\AssetLabel::class, ['asset_id' => 'id'])
            ->andOnCondition(['asset_labels.customer_id' => $this->customer_id]);
    }

    public function getLabels()
    {
        return $this->hasMany(\common\models\Label::class, ['id' => 'label_id'])
            ->via('assetLabels');
    }

    public function getCustomer() {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
}