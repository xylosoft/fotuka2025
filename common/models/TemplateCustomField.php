<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class TemplateCustomField extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%template_custom_fields}}';
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
            [['template_id', 'customer_id', 'user_id', 'field_key', 'name', 'slug'], 'required'],
            [['template_id', 'customer_id', 'user_id', 'font_size', 'sort_order'], 'integer'],
            [['settings_json'], 'string'],
            [['name', 'slug'], 'string', 'max' => 100],
            [['text_color'], 'string', 'max' => 20],
            [['font_weight', 'font_style', 'text_align'], 'string', 'max' => 20],
        ];
    }

    public function getTemplate()
    {
        return $this->hasOne(Template::class, ['template_id' => 'template_id']);
    }

    public function getStyleArray()
    {
        return [
            'color' => $this->text_color ?: '#111827',
            'font-size' => $this->font_size ? $this->font_size . 'px' : '16px',
            'font-weight' => $this->font_weight ?: '400',
            'font-style' => $this->font_style ?: 'normal',
            'text-align' => $this->text_align ?: 'left',
        ];
    }
}