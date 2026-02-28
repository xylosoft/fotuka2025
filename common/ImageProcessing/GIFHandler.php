<?php

namespace common\ImageProcessing;

use Yii;

class GIFHandler extends BaseImageHandler {

    public function __construct($filename, $assetId){
        $this->attributes[self::FILE_FORMAT] = self::FORMAT_GIF;
        $this->attributes[self::VALID_FORMATS] = $validFormats = array(self::FORMAT_GIF, self::FORMAT_PNG, self::FORMAT_JPEG, self::FORMAT_JPG, self::FORMAT_WEBP,
            self::FORMAT_AI, self::FORMAT_TIF, self::FORMAT_TIFF, self::FORMAT_TGA, self::FORMAT_BMP);
        parent::__construct($filename, $assetId);

        // @todo Check if file extension matches file format name
    }

    public function convert(){
        $tmp = $this->attributes[self::FILE_NAME];

        // By default, it only exports first frame
        if (!$this->requestedFrame){
            $this->requestedFrame = 0;
        }

        // If we want to export single frame. Otherwise, it exports all.
        if (is_numeric($this->requestedFrame)){
            $this->attributes[self::FILE_NAME] .= '[' . $this->requestedFrame . ']';
        }

        parent::convert();
        $this->attributes[self::FILE_NAME] = $tmp;
    }

    public function __set($property, $value) {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }

}

// For some GIF files, the -coalesce option might be neededß
// For Transparent images: -transparent-color

// @todo Converting to TGA produces new image with different tone