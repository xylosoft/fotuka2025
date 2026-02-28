<?php

namespace common\ImageProcessing;

use Yii;

class EPSHandler extends BaseImageHandler {

    public function __construct($filename, $assetId){
        $this->attributes[self::FILE_FORMAT] = self::FORMAT_EPS;
        $this->attributes[self::VALID_FORMATS] = array(self::FORMAT_EPS, self::FORMAT_JPEG, self::FORMAT_JPG);
        parent::__construct($filename, $assetId);

        // @todo Check if file extension matches file format name
    }

    /**
     * Executes all conversion commands to create desired destination file.
     * @return void
     */
    public function  convert(){
        $command = "PATH=/opt/local/bin:/usr/bin:/bin " . Yii::$app->params['IMAGEMAGICK_PATH'] . 'magick';

        // Source File
        $command .= " -density 150 \"{$this->attributes[self::FILE_NAME]}[0]\"";

        // Ensures consistent web color
        $command .= " -colorspace sRGB";

        // Resize / Thumbnail options
        if ($this->width && $this->height){
            if ($this->thumbnail){
                $command .= " -thumbnail {$this->width}x{$this->height}^ -extent {$this->width}x{$this->height} -gravity North";
            }else{
                $command .= " -resize \" -resize {$this->width}x{$this->height}";
            }
        }

        // File Quality
        if ($this->quality){
            $command .= " -quality {$this->quality}";
        }

        // Destination File
        if (!$this->destinationFile){
            $this->destinationFile = tempnam('/tmp', $this->attributes[self::ASSET_ID]);
        }
        $command .= " {$this->destinationFormat}:{$this->destinationFile}";

        echo "COMMAND: $command\n";

        $start = microtime(true);
        $output = null;
        $result_code = null;
        echo "FINAL COMMAND: $command\n";
        exec($command, $output, $result_code);
        echo "Result Code: $result_code\n";
        echo print_r($output,1) . "\n";
        $end = microtime(true);
        echo "Process took: " . ($end - $start) . " seconds.\n";
        return $this;
        // @todo throw error if return code != 0
    }
}