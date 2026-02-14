<?php

namespace common\ImageProcessing;

class SVGHandler extends BaseImageHandler {

    public function __construct($filename){
        $this->attributes[self::FILE_FORMAT] = self::FORMAT_SVG;
        $this->attributes[self::VALID_FORMATS] = array(self::FORMAT_JPEG, self::FORMAT_JPG, self::FORMAT_PNG, self::FORMAT_GIF, self::FORMAT_WEBP,
            self::FORMAT_AI, self::FORMAT_TIF, self::FORMAT_TIFF, self::FORMAT_PSD, self::FORMAT_TGA, self::FORMAT_BMP);
        parent::__construct($filename);

        // @todo Check if file extension matches file format name
    }
}