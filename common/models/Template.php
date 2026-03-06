<?php

namespace common\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class Template extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%templates}}';
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
            [['customer_id', 'user_id', 'name'], 'required'],
            [['customer_id', 'user_id', 'deleted_by_user_id', 'created_by_user_id', 'updated_by_user_id'], 'integer'],
            [['allow_downloads', 'password_enabled'], 'boolean'],
            [['theme_json'], 'string'],
            [['deleted', 'created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['name'], 'unique', 'targetAttribute' => ['customer_id', 'name'], 'message' => 'A template with this name already exists for this customer.'],
        ];
    }

    public function getSections()
    {
        return $this->hasMany(TemplateSection::class, ['template_id' => 'template_id'])
            ->orderBy(['is_locked' => SORT_DESC, 'sort_order' => SORT_ASC, 'section_id' => SORT_ASC]);
    }

    public function getCustomFields()
    {
        return $this->hasMany(TemplateCustomField::class, ['template_id' => 'template_id'])
            ->orderBy(['sort_order' => SORT_ASC, 'custom_field_id' => SORT_ASC]);
    }

    public function getPublishedPages()
    {
        return $this->hasMany(PublishedPage::class, ['template_id' => 'template_id']);
    }

    public function getThemeSettings()
    {
        $defaults = [
            'page_background_color' => '#ffffff',
            'page_text_color' => '#111827',
            'accent_color' => '#2563eb',
            'button_color' => '#2563eb',
            'button_text_color' => '#ffffff',
            'section_background_color' => '#ffffff',
        ];

        $decoded = json_decode((string)$this->theme_json, true);
        return is_array($decoded) ? array_merge($defaults, $decoded) : $defaults;
    }
}