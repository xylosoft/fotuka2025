<?php

namespace common\ImageProcessing;

use Yii;
use common\models\Asset;
use yii\db\Exception;

class DOCHandler extends BaseImageHandler {

    private $internalHandler = null;

    public function __construct($filename, $assetId){
        $this->attributes[self::FILE_NAME] = $filename;
        $this->attributes[self::ASSET_ID] = $assetId;
        $this->attributes[self::FILE_FORMAT] = self::FORMAT_DOCX;
        $this->attributes[self::VALID_FORMATS] = array(self::FORMAT_DOCX, self::FORMAT_DOC, self::FORMAT_DOCX, self::FORMAT_JPEG, self::FORMAT_JPG);
        $this->attributes[self::FILE_SIZE] = filesize($filename);

        $this->destinationFile = $filename;
        $this->destinationFile = str_replace('.docx', '.jpg', $this->destinationFile);
        $this->destinationFile = str_replace('.doc', '.jpg', $this->destinationFile);

        $handlerName = 'common\\ImageProcessing\\JPGHandler';

        // Pre-process file before sending it to JPGHandler
        $command = "HOME=/tmp TMPDIR=/tmp PATH=/opt/local/bin:/usr/bin:/bin " . Yii::$app->params['SOFFICE_PATH'] .
            'soffice --headless --nologo --nolockcheck --norestore --nodefault --safe-mode';

        $command .= ' --convert-to jpg';

        $command .= " --outdir " . $dir = dirname($this->destinationFile);

        $command .= ' ' . $this->attributes[self::FILE_NAME];


        $start = microtime(true);
        $output = null;
        $result_code = null;
        exec($command, $output, $result_code);
        $end = microtime(true);
        echo "Process took: " . ($end - $start) . " seconds.\n";

        if (!file_exists($this->destinationFile)){
            // TODO: Delete temp files
            throw new Exception("Invalid File Format.");
        }else{
            $this->internalHandler = new $handlerName($this->destinationFile, $this->attributes[self::ASSET_ID]);
            $this->setDestinationFormat(BaseImageHandler::FORMAT_JPG);
            $this->internalHandler->setDestinationFormat(BaseImageHandler::FORMAT_JPG);
        }
        parent::__construct($filename, $assetId, true);
    }

    /**
     * Executes all conversion commands to create desired destination file.
     * @return $this|void
     * @throws \Exception
     */
    public function convert(){
        $this->internalHandler->convert();
        return $this;
    }

    public function createThumbnail($width, $height){
        $this->internalHandler->createThumbnail($width, $height);
        return $this;
    }


    public function createPreview($width, $height){
        $this->internalHandler->createPreview($width, $height);
        return $this;
    }

    public function resize($width, $height){
        $this->internalHandler->resize($width, $height);
        return $this;
    }

    public function saveThumbnail($asset){
        $this->internalHandler->saveThumbnail($asset);
        return $this;
    }

    public function cleanup($asset){
        //$this->internalHandler->cleanup($asset);
        //@unlink($this->destinationFile);
        return $this;
    }

    public function getFileInfo(){
        $this->internalHandler->getFileInfo();
        return $this;
    }
}