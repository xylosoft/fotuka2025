<?php

namespace common\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class PublishedPage extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%published_pages}}';
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
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by_user_id',
                'updatedByAttribute' => 'updated_by_user_id',
            ],
        ];
    }

    public function rules()
    {
        return [
            [['template_id', 'folder_id', 'customer_id', 'user_id', 'uri'], 'required'],
            [['template_id', 'folder_id', 'customer_id', 'user_id', 'deleted_by_user_id', 'created_by_user_id', 'updated_by_user_id'], 'integer'],
            [['password_required', 'allow_downloads'], 'boolean'],
            [['published_at', 'deleted', 'created_at', 'updated_at'], 'safe'],
            [['uri'], 'match', 'pattern' => '/^[A-Za-z0-9\-_]+$/', 'message' => 'URI can contain only letters, numbers, dashes and underscores.'],
            [['uri'], 'unique'],
            [['uri'], 'string', 'max' => 150],
            [['page_title'], 'string', 'max' => 255],
            [['page_password'], 'string', 'max' => 255],
        ];
    }

    public function getTemplate()
    {
        return $this->hasOne(Template::class, ['template_id' => 'template_id']);
    }

    public function getCustomFieldValues()
    {
        return $this->hasMany(PublishedPageCustomFieldValue::class, ['published_page_id' => 'published_page_id']);
    }

    public function getSectionAssets()
    {
        return $this->hasMany(PublishedPageSectionAsset::class, ['published_page_id' => 'published_page_id'])
            ->orderBy(['section_id' => SORT_ASC, 'slot_no' => SORT_ASC, 'id' => SORT_ASC]);
    }
}