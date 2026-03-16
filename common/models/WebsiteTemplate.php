<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $customer_id
 * @property string $name
 * @property string $definition_json
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $deleted
 * @property int|null $deleted_by_user_id
 */
class WebsiteTemplate extends ActiveRecord
{
    public static function tableName()
    {
        return 'website_template';
    }

    public function rules()
    {
        return [
            [['user_id', 'name', 'definition_json'], 'required'],
            [['user_id', 'customer_id', 'created_at', 'updated_at', 'deleted', 'deleted_by_user_id'], 'integer'],
            [['definition_json'], 'string'],
            [['name'], 'string', 'max' => 200],
            ['definition_json', 'validateDefinitionJson'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Template Name',
        ];
    }

    public function validateDefinitionJson($attribute)
    {
        $decoded = json_decode((string) $this->$attribute, true);

        if (!is_array($decoded)) {
            $this->addError($attribute, 'Template definition JSON is invalid.');
            return;
        }

        if (!isset($decoded['page']) || !isset($decoded['components']) || !is_array($decoded['components'])) {
            $this->addError($attribute, 'Template definition JSON is missing required keys.');
        }
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $now = time();

        if ($insert) {
            $this->created_at = $now;
        }

        $this->updated_at = $now;

        return true;
    }

    public static function defaultDefinition()
    {
        return [
            'schema_version' => 2,
            'page' => [
                'canvas_width' => 1200,
                'canvas_min_height' => 1500,
                'background_color' => '#ffffff',
                'button_color' => '#2563eb',
            ],
            'publish_defaults' => [
                'is_password_protected' => false,
                'allow_download_all' => false,
            ],
            'components' => [],
        ];
    }

    public function getDefinitionArray()
    {
        $decoded = json_decode((string) $this->definition_json, true);

        if (!is_array($decoded)) {
            return static::defaultDefinition();
        }

        if (!isset($decoded['page']) || !is_array($decoded['page'])) {
            $decoded['page'] = static::defaultDefinition()['page'];
        }

        if (!isset($decoded['components']) || !is_array($decoded['components'])) {
            $decoded['components'] = [];
        }

        return $decoded;
    }

    public function setDefinitionArray(array $definition)
    {
        $this->definition_json = json_encode($definition, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function getActivePublications()
    {
        return $this->hasMany(WebsitePublication::class, ['template_id' => 'id'])
            ->andWhere([WebsitePublication::tableName() . '.deleted' => null]);
    }

    public function isInUse()
    {
        return (bool) $this->getActivePublications()->count();
    }

    public static function findActive($alias = null)
    {
        $query = static::find();

        if ($alias) {
            $query->alias($alias);
            return $query->andWhere([$alias . '.deleted' => null]);
        }

        return $query->andWhere([static::tableName() . '.deleted' => null]);
    }
}