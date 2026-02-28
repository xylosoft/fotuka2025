<?php

namespace common\ImageProcessing;

use Yii;

class PNGHandler extends BaseImageHandler {

    public function __construct($filename, $assetId){
        $this->attributes[self::FILE_FORMAT] = self::FORMAT_PNG;
        $this->attributes[self::VALID_FORMATS] = array(self::FORMAT_PNG, self::FORMAT_JPEG, self::FORMAT_JPG, self::FORMAT_GIF, self::FORMAT_WEBP,
            self::FORMAT_AI, self::FORMAT_TIF, self::FORMAT_TIFF, self::FORMAT_PSD, self::FORMAT_TGA, self::FORMAT_BMP);

        parent::__construct($filename, $assetId);

        // @todo Check if file extension matches file format name
    }
}