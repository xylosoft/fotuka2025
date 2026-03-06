<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class TemplateSection extends ActiveRecord
{
    const TYPE_HEADER_IMAGE = 'header_image';
    const TYPE_IMAGE_CAROUSEL = 'image_carousel';
    const TYPE_LOGO = 'logo';
    const TYPE_COMPANY_NAME = 'company_name';
    const TYPE_SINGLE_IMAGE = 'single_image';
    const TYPE_GALLERY = 'gallery';
    const TYPE_TEXT_BLOCK = 'text_block';

    public static function tableName()
    {
        return '{{%template_sections}}';
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
            [['template_id', 'customer_id', 'user_id', 'section_key', 'type'], 'required'],
            [['template_id', 'customer_id', 'user_id', 'row_no', 'width', 'height', 'sort_order', 'custom_field_id', 'image_count'], 'integer'],
            [['is_locked'], 'boolean'],
            [['text', 'settings_json'], 'string'],
            [['background_color', 'text_color'], 'string', 'max' => 20],
            [['section_key'], 'string', 'max' => 64],
            [['type'], 'string', 'max' => 50],
            [['label'], 'string', 'max' => 100],
        ];
    }

    public function getTemplate()
    {
        return $this->hasOne(Template::class, ['template_id' => 'template_id']);
    }

    public function getCustomField()
    {
        return $this->hasOne(TemplateCustomField::class, ['custom_field_id' => 'custom_field_id']);
    }

    public function getSettings()
    {
        $decoded = json_decode((string)$this->settings_json, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function requiresAssetSelection()
    {
        return in_array($this->type, [
            self::TYPE_HEADER_IMAGE,
            self::TYPE_IMAGE_CAROUSEL,
            self::TYPE_SINGLE_IMAGE,
        ], true);
    }
}