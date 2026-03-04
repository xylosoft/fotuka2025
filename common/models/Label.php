<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 */
class Label extends ActiveRecord
{
    public static function tableName()
    {
        return 'labels';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 80],
            [['name'], 'unique'],
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        if ($this->name !== null) {
            $this->name = mb_strtolower(trim($this->name));
        }

        return true;
    }
}