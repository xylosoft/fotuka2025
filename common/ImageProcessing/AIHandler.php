<?php

namespace common\ImageProcessing;

use Yii;

class AIHandler extends BaseImageHandler {

    public function __construct($filename, $assetId){
        $this->attributes[self::FILE_FORMAT] = self::FORMAT_AI;
        $this->attributes[self::VALID_FORMATS] = array(self::FORMAT_AI, self::FORMAT_JPEG, self::FORMAT_JPG);
            parent::__construct($filename, $assetId);

        // @todo Check if file extension matches file format name
    }
}