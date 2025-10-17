<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "assets".
 *
 * @property int $id
 * @property string $created
 * @property string $updated_at
 * @property int $customer_id
 * @property int $user_id
 * @property int $folder_id
 * @property int $file_id
 * @property string|null $status
 * @property string|null $title
 * @property string|null $description
 */
class Asset extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DELETED = 'deleted';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'assets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'description'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'active'],
            [['created', 'updated_at'], 'safe'],
            [['customer_id', 'user_id', 'folder_id', 'file_id'], 'required'],
            [['customer_id', 'user_id', 'folder_id', 'file_id'], 'integer'],
            [['status'], 'string'],
            [['title'], 'string', 'max' => 100],
            [['description'], 'string', 'max' => 255],
            ['status', 'in', 'range' => array_keys(self::optsStatus())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created' => 'Created',
            'updated_at' => 'Updated At',
            'customer_id' => 'Customer ID',
            'user_id' => 'User ID',
            'folder_id' => 'Folder ID',
            'file_id' => 'File ID',
            'status' => 'Status',
            'title' => 'Title',
            'description' => 'Description',
        ];
    }


    /**
     * column status ENUM value labels
     * @return string[]
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_ACTIVE => 'active',
            self::STATUS_INACTIVE => 'inactive',
            self::STATUS_DELETED => 'deleted',
        ];
    }

    /**
     * @return string
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->status];
    }

    /**
     * @return bool
     */
    public function isStatusActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function setStatusToActive()
    {
        $this->status = self::STATUS_ACTIVE;
    }

    /**
     * @return bool
     */
    public function isStatusInactive()
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    public function setStatusToInactive()
    {
        $this->status = self::STATUS_INACTIVE;
    }

    /**
     * @return bool
     */
    public function isStatusDeleted()
    {
        return $this->status === self::STATUS_DELETED;
    }

    public function setStatusToDeleted()
    {
        $this->status = self::STATUS_DELETED;
    }
}
