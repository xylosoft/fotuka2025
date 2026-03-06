<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class PublishedPageCustomFieldValue extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%published_page_custom_field_values}}';
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
            [['published_page_id', 'custom_field_id'], 'required'],
            [['published_page_id', 'custom_field_id'], 'integer'],
            [['value'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }
}