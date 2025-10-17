<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "folders".
 *
 * @property int $id
 * @property int $parent_id
 * @property string $created
 * @property int $customer_id
 * @property int $user_id
 * @property string $name
 * @property int $folder_size
 * @property int $asset_count
 * @property string|null $status
 * @property int $thumbnail_id
 */
class Folder extends \yii\db\ActiveRecord
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
        return 'folders';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'default', 'value' => null],
            [['thumbnail_id'], 'default', 'value' => 0],
            [['parent_id', 'customer_id', 'user_id', 'name'], 'required'],
            [['parent_id', 'customer_id', 'user_id', 'folder_size', 'asset_count', 'thumbnail_id'], 'integer'],
            [['created'], 'safe'],
            [['status'], 'string'],
            [['name'], 'string', 'max' => 50],
            ['status', 'in', 'range' => array_keys(self::optsStatus())],
            [['parent_id', 'name'], 'unique', 'targetAttribute' => ['parent_id', 'name']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'created' => 'Created',
            'customer_id' => 'Customer ID',
            'user_id' => 'User ID',
            'name' => 'Name',
            'folder_size' => 'Folder Size',
            'asset_count' => 'Asset Count',
            'status' => 'Status',
            'thumbnail_id' => 'Thumbnail ID',
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
