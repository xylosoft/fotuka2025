<?php

namespace common\ImageProcessing;

class PSDHandler extends BaseImageHandler {

    public function __construct($filename){
        $this->attributes[self::FILE_FORMAT] = self::FORMAT_PSD;
        $this->attributes[self::VALID_FORMATS] = array(self::FORMAT_JPEG, self::FORMAT_JPG, self::FORMAT_PNG, self::FORMAT_GIF, self::FORMAT_WEBP,
            self::FORMAT_AI, self::FORMAT_TIF, self::FORMAT_TIFF, self::FORMAT_TGA, self::FORMAT_BMP);
        parent::__construct($filename);

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
}

// https://customerscanvas.com/help/designers-manual/adobe/photoshop/gallery.html