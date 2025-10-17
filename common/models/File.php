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
 * @property string|null $thumbnail
 * @property string|null $preview
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
    const THUMBNAIL_PENDING = 'pending';
    const THUMBNAIL_DONE = 'done';
    const THUMBNAIL_UNSUPPORTED = 'unsupported';
    const PREVIEW_PENDING = 'pending';
    const PREVIEW_DONE = 'done';
    const PREVIEW_UNSUPPORTED = 'unsupported';
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
            [['type', 'width', 'height', 'filename', 'extension', 'orientation', 'pages'], 'default', 'value' => null],
            [['preview'], 'default', 'value' => 'pending'],
            [['customer_id', 'user_id', 'filesize'], 'required'],
            [['customer_id', 'user_id', 'width', 'height', 'filesize', 'pages'], 'integer'],
            [['type', 'thumbnail', 'preview', 'orientation'], 'string'],
            [['filename'], 'string', 'max' => 255],
            [['extension'], 'string', 'max' => 10],
            ['type', 'in', 'range' => array_keys(self::optsType())],
            ['thumbnail', 'in', 'range' => array_keys(self::optsThumbnail())],
            ['preview', 'in', 'range' => array_keys(self::optsPreview())],
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
            'thumbnail' => 'Thumbnail',
            'preview' => 'Preview',
            'filename' => 'Filename',
            'extension' => 'Extension',
            'orientation' => 'Orientation',
            'filesize' => 'Filesize',
            'pages' => 'Pages',
        ];
    }


    /**
     * column type ENUM value labels
     * @return string[]
     */
    public static function optsType()
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
     * column thumbnail ENUM value labels
     * @return string[]
     */
    public static function optsThumbnail()
    {
        return [
            self::THUMBNAIL_PENDING => 'pending',
            self::THUMBNAIL_DONE => 'done',
            self::THUMBNAIL_UNSUPPORTED => 'unsupported',
        ];
    }

    /**
     * column preview ENUM value labels
     * @return string[]
     */
    public static function optsPreview()
    {
        return [
            self::PREVIEW_PENDING => 'pending',
            self::PREVIEW_DONE => 'done',
            self::PREVIEW_UNSUPPORTED => 'unsupported',
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

    /**
     * @return string
     */
    public function displayType()
    {
        return self::optsType()[$this->type];
    }

    /**
     * @return bool
     */
    public function isTypeImage()
    {
        return $this->type === self::TYPE_IMAGE;
    }

    public function setTypeToImage()
    {
        $this->type = self::TYPE_IMAGE;
    }

    /**
     * @return bool
     */
    public function isTypeVideo()
    {
        return $this->type === self::TYPE_VIDEO;
    }

    public function setTypeToVideo()
    {
        $this->type = self::TYPE_VIDEO;
    }

    /**
     * @return bool
     */
    public function isTypeAudio()
    {
        return $this->type === self::TYPE_AUDIO;
    }

    public function setTypeToAudio()
    {
        $this->type = self::TYPE_AUDIO;
    }

    /**
     * @return bool
     */
    public function isTypeDocument()
    {
        return $this->type === self::TYPE_DOCUMENT;
    }

    public function setTypeToDocument()
    {
        $this->type = self::TYPE_DOCUMENT;
    }

    /**
     * @return bool
     */
    public function isTypeSpreadsheet()
    {
        return $this->type === self::TYPE_SPREADSHEET;
    }

    public function setTypeToSpreadsheet()
    {
        $this->type = self::TYPE_SPREADSHEET;
    }

    /**
     * @return bool
     */
    public function isTypePresentation()
    {
        return $this->type === self::TYPE_PRESENTATION;
    }

    public function setTypeToPresentation()
    {
        $this->type = self::TYPE_PRESENTATION;
    }

    /**
     * @return bool
     */
    public function isTypeArchive()
    {
        return $this->type === self::TYPE_ARCHIVE;
    }

    public function setTypeToArchive()
    {
        $this->type = self::TYPE_ARCHIVE;
    }

    /**
     * @return bool
     */
    public function isTypeCode()
    {
        return $this->type === self::TYPE_CODE;
    }

    public function setTypeToCode()
    {
        $this->type = self::TYPE_CODE;
    }

    /**
     * @return bool
     */
    public function isTypeFont()
    {
        return $this->type === self::TYPE_FONT;
    }

    public function setTypeToFont()
    {
        $this->type = self::TYPE_FONT;
    }

    /**
     * @return bool
     */
    public function isType3d()
    {
        return $this->type === self::TYPE_3D;
    }

    public function setTypeTo3d()
    {
        $this->type = self::TYPE_3D;
    }

    /**
     * @return bool
     */
    public function isTypeOther()
    {
        return $this->type === self::TYPE_OTHER;
    }

    public function setTypeToOther()
    {
        $this->type = self::TYPE_OTHER;
    }

    /**
     * @return string
     */
    public function displayThumbnail()
    {
        return self::optsThumbnail()[$this->thumbnail];
    }

    /**
     * @return bool
     */
    public function isThumbnailPending()
    {
        return $this->thumbnail === self::THUMBNAIL_PENDING;
    }

    public function setThumbnailToPending()
    {
        $this->thumbnail = self::THUMBNAIL_PENDING;
    }

    /**
     * @return bool
     */
    public function isThumbnailDone()
    {
        return $this->thumbnail === self::THUMBNAIL_DONE;
    }

    public function setThumbnailToDone()
    {
        $this->thumbnail = self::THUMBNAIL_DONE;
    }

    /**
     * @return bool
     */
    public function isThumbnailUnsupported()
    {
        return $this->thumbnail === self::THUMBNAIL_UNSUPPORTED;
    }

    public function setThumbnailToUnsupported()
    {
        $this->thumbnail = self::THUMBNAIL_UNSUPPORTED;
    }

    /**
     * @return string
     */
    public function displayPreview()
    {
        return self::optsPreview()[$this->preview];
    }

    /**
     * @return bool
     */
    public function isPreviewPending()
    {
        return $this->preview === self::PREVIEW_PENDING;
    }

    public function setPreviewToPending()
    {
        $this->preview = self::PREVIEW_PENDING;
    }

    /**
     * @return bool
     */
    public function isPreviewDone()
    {
        return $this->preview === self::PREVIEW_DONE;
    }

    public function setPreviewToDone()
    {
        $this->preview = self::PREVIEW_DONE;
    }

    /**
     * @return bool
     */
    public function isPreviewUnsupported()
    {
        return $this->preview === self::PREVIEW_UNSUPPORTED;
    }

    public function setPreviewToUnsupported()
    {
        $this->preview = self::PREVIEW_UNSUPPORTED;
    }

    /**
     * @return string
     */
    public function displayOrientation()
    {
        return self::optsOrientation()[$this->orientation];
    }

    /**
     * @return bool
     */
    public function isOrientationHorizontal()
    {
        return $this->orientation === self::ORIENTATION_HORIZONTAL;
    }

    public function setOrientationToHorizontal()
    {
        $this->orientation = self::ORIENTATION_HORIZONTAL;
    }

    /**
     * @return bool
     */
    public function isOrientationVertical()
    {
        return $this->orientation === self::ORIENTATION_VERTICAL;
    }

    public function setOrientationToVertical()
    {
        $this->orientation = self::ORIENTATION_VERTICAL;
    }
}
