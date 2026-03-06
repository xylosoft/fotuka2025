<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class PublishedPageSectionAsset extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%published_page_section_assets}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['published_page_id', 'section_id', 'asset_id', 'slot_no'], 'required'],
            [['published_page_id', 'section_id', 'asset_id', 'slot_no'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function getAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'asset_id']);
    }
}