<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "files".
 *
 * @property int $id
 * @property int $customer_id
 * @property int $user_id
 * @property string|null $type
 * @property int|null $width
 * @property int|null $height
 * @property string|null $filename
 * @property string|null $extension
 * @property string|null $orientation
 * @property int $filesize
 * @property int|null $pages
 */
class File extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_AUDIO = 'audio';
    const TYPE_DOCUMENT = 'document';
    const TYPE_SPREADSHEET = 'spreadsheet';
    const TYPE_PRESENTATION = 'presentation';
    const TYPE_ARCHIVE = 'archive';
    const TYPE_CODE = 'code';
    const TYPE_FONT = 'font';
    const TYPE_3D = '3d';
    const TYPE_OTHER = 'other';
    const ORIENTATION_HORIZONTAL = 'horizontal';
    const ORIENTATION_VERTICAL = 'vertical';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'width', 'height', 'filename', 'extension', 'orientation', 'pages', 'tmp_location'], 'default', 'value' => null],
            [['customer_id', 'user_id', 'filesize', 'filename'], 'required'],
            [['customer_id', 'user_id', 'width', 'height', 'filesize', 'pages'], 'integer'],
            [['type', 'orientation', 'tmp_location'], 'string'],
            [['filename', 'tmp_location'], 'string', 'max' => 255],
            [['extension'], 'string', 'max' => 10],
            ['type', 'in', 'range' => array_keys(self::fileType())],
            ['orientation', 'in', 'range' => array_keys(self::optsOrientation())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'user_id' => 'User ID',
            'type' => 'Type',
            'width' => 'Width',
            'height' => 'Height',
            'filename' => 'Filename',
            'extension' => 'Extension',
            'orientation' => 'Orientation',
            'filesize' => 'Filesize',
            'pages' => 'Pages',
            'tmp_location' => 'Temporary Location',
        ];
    }


    /**
     * column type ENUM value labels
     * @return string[]
     */
    public static function fileType()
    {
        return [
            self::TYPE_IMAGE => 'image',
            self::TYPE_VIDEO => 'video',
            self::TYPE_AUDIO => 'audio',
            self::TYPE_DOCUMENT => 'document',
            self::TYPE_SPREADSHEET => 'spreadsheet',
            self::TYPE_PRESENTATION => 'presentation',
            self::TYPE_ARCHIVE => 'archive',
            self::TYPE_CODE => 'code',
            self::TYPE_FONT => 'font',
            self::TYPE_3D => '3d',
            self::TYPE_OTHER => 'other',
        ];
    }

    /**
     * column orientation ENUM value labels
     * @return string[]
     */
    public static function optsOrientation()
    {
        return [
            self::ORIENTATION_HORIZONTAL => 'horizontal',
            self::ORIENTATION_VERTICAL => 'vertical',
        ];
    }
}
