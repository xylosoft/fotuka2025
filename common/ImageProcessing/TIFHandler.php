<?php

namespace common\ImageProcessing;

use Yii;

class TIFHandler extends BaseImageHandler {

    public function __construct($filename, $assetId){
        $this->attributes[self::FILE_FORMAT] = self::FORMAT_TIF;
        $this->attributes[self::VALID_FORMATS] = array(self::FORMAT_TIF, self::FORMAT_TIFF, self::FORMAT_JPEG, self::FORMAT_JPG);
        parent::__construct($filename, $assetId);

        // @todo Check if file extension matches file format name
    }

    /**
     * Executes all conversion commands to create desired destination file.
     * @return void
     */
    public function  convert(){
        $command = "PATH=/opt/local/bin:/usr/bin:/bin " . Yii::$app->params['IMAGEMAGICK_PATH'] . 'magick -quiet';

        // Source File
        $command .= " \"{$this->attributes[self::FILE_NAME]}[0]\"";

        // AUTO ORIENTATION
        $command .= " -auto-orient";

        $command .= " -strip";

        // Resize / Thumbnail options
        if ($this->width && $this->height){
            if ($this->thumbnail){
                $command .= " -resize {$this->width}x{$this->height}^ -gravity North -extent {$this->width}x{$this->height}";
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