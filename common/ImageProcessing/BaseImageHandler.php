<?php

namespace common\ImageProcessing;

use Yii;
use common\models\Asset;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class BaseImageHandler{
    // Destinations
    public const DESTINATION_FOTUKA = 1;
    public const DESTINATION_FILE = 2;
    public const DESTINATION_AWS= 2;
    public const DESTINATION_HTTP = 2;

    // Mirroring
    public const MIRRORING_HORIZONTAL = 1;
    public const MIRRORING_VERTICAL = 2;

    // Supported File Formats
    public const FORMAT_JPEG = "JPEG";
    public const FORMAT_JPG = "JPG";
    public const FORMAT_PNG = "PNG";
    public const FORMAT_GIF = "GIF";
    public const FORMAT_WEBP = "WEBP";
    public const FORMAT_AI = "AI";
    public const FORMAT_TIFF = "TIFF";
    public const FORMAT_TIF = "TIF";
    public const FORMAT_PSD = "PSD";
    public const FORMAT_TGA = "TGA";
    public const FORMAT_BMP = "BMP";
    public const FORMAT_EPS = "EPS";
    public const FORMAT_SVG = "SVG";
    public const FORMAT_PDF = "PDF";

    // FILE ATTRIBUTES
    public const FILE_SIZE = 0;
    public const FILE_NAME = 1;
    public const FILE_FORMAT = 2;
    public const FILE_WIDTH = 3;
    public const FILE_HEIGHT = 4;
    public const FILE_DEPTH = 5;
    public const FILE_COLORSPACE = 6;
    public const FILE_FILETYPE = 7;
    public const NUMBER_OF_FRAMES = 8; // GIF
    public const VALID_FORMATS = 9; // GIF

    // FILE TYPES
    public const FILETYPE_IMAGE = 1;
    public const FILETYPE_VIDEO = 2;
    public const FILETYPE_DOCUMENT = 3;
    public const FILETYPE_AUDIO = 4;
    public const FILETYPE_OTHER = 5;

    // ASSET ATTRIBUTES
    public const ASSET_ID = "ASSET_ID";

    //original Image Properties
    protected $attributes = array();

    // Manipulated Attributes
    protected $width = null;
    protected $height = null;
    protected $fileSize = null;
    protected $dpi = null;
    protected $rotation = null;

    protected $quality = 75;
    protected $mirroring = null;
    protected $mirroringDirection = null;
    protected $crop = null;
    protected $grayscale = null;
    protected $rotate = null;
    protected $rotationAngle = null;
    protected $extractMetadata = null;
    protected $destinationType = null;
    protected $destinationFormat = null;
    protected $destinationFile = null;
    // @todo Each Handler needs to override this and final file should contain this extension
    protected $destinationExtension = null;
    protected $thumbnail = null;
    protected $requestedFrame = null;

    // Metadata
    private $metadata = array();
    private $fileInfo = array();
    private $mimeTypes = array();


    /**
     * Constructor
     * @param $filename
     * @throws \Exception
     */
    public function __construct($filename, $assetId){
        $this->attributes[self::FILE_NAME] = $filename;
        $this->attributes[self::ASSET_ID] = $assetId;
        $this->mimeTypes = [self::FORMAT_JPEG => "image/jpeg",
                            self::FORMAT_JPG => "image/jpeg",
                            self::FORMAT_PNG => "image/png",
                            self::FORMAT_GIF => "image/gif",
                            self::FORMAT_WEBP => "image/webp",
                            self::FORMAT_AI => "application/postscript",
                            self::FORMAT_TIFF => "image/tiff",
                            self::FORMAT_TIF => "image/tiff",
                            self::FORMAT_PSD => "image/vnd.adobe.photoshop",
                            self::FORMAT_TGA => "image/x-tga",
                            self::FORMAT_BMP => "image/bmp",
                            self::FORMAT_EPS => "application/postscript",
                            self::FORMAT_SVG => "image/svg+xml",
                            self::FORMAT_PDF => "application/pdf"];

        if (!file_exists($filename)){
            throw new \Exception("Upload Failed. Please try again.");
        }

        $this->attributes[self::FILE_SIZE] = filesize($filename);
        $this->getFileInfo();
    }

    /**
     * Executes all conversion commands to create desired destination file.
     * @return void
     */
    public function  convert(){
        $command = "PATH=/opt/local/bin:/usr/bin:/bin " . Yii::$app->params['IMAGEMAGICK_PATH'] . 'magick';

        // Source File
        $command .= " \"{$this->attributes[self::FILE_NAME]}\"";

        // Resize / Thumbnail options
        if ($this->width && $this->height){
            if ($this->thumbnail){
                $command .= " -thumbnail ";
            }else{
                $command .= " -resize ";
            }
            $command .= "{$this->width}x{$this->height}\\!";
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
        echo "FINAL COMMAND (Base): $command\n";
        exec($command, $output, $result_code);
        echo "Result Code: $result_code\n";
        echo print_r($output,1) . "\n";
        $end = microtime(true);
        echo "Process took: " . ($end - $start) . " seconds.\n";
        return $this;
        // @todo throw error if return code != 0
    }

    public function createThumbnail($width, $height){
        echo "Base - CreateThumbnail...\n";
        $this->thumbnail = true;
        return $this->resize($width, $height)->convert();
    }
    
    /**
     * Calculates new Width/Height dimensions as requested.
     * @param $width
     * @param $height
     * @return void
     */
    public function resize($width, $height){
        echo "Base - Resizing to $width x $height\n";
        // If new dimensions are the same as the existing ones, no need to resize.
        if ($width == $this->attributes[self::FILE_WIDTH] && $height == $this->attributes[self::FILE_HEIGHT]){
            echo "Base: - Can't determine width and height\n";
            return $this;
        }

        if ($width && !$height){
            $this->width = $width;
            $ratio = $width/$this->attributes[self::FILE_WIDTH];
            $this->height = intval($this->attributes[self::FILE_HEIGHT] * $ratio);
        }else if ($height && !$width){
            $this->height = $height;
            $ratio = $height/$this->attributes[self::FILE_HEIGHT];
            $this->width = intval($this->attributes[self::FILE_WIDTH] * $ratio);
        }else{
            $this->width = $width;
            $this->height = $height;
        }
        return $this;
    }

    protected function getFileInfo(){
        $output = null;
        $result_code = null;

        $command = "PATH=/opt/local/bin:/usr/bin:/bin " . Yii::$app->params['IMAGEMAGICK_PATH'] . "identify \"{$this->attributes[self::FILE_NAME]}\"";
        echo "BaseImageController: Command: $command\n";
        exec($command, $output, $result_code);
        echo "Output: " . print_r($output) . "\n";


        // Count number of frames for GIF Files
        if ($this->attributes[self::FILE_FORMAT] == self::FORMAT_GIF){
            $this->attributes[self::NUMBER_OF_FRAMES] = count($output);
        }

        if ($output && is_array($output)){
            $output = $output[0];
        }
        //echo "Command: $command\n";
        //echo "Result Code: $result_code\n";
        //echo "Output: $output\n";
        $properties = explode(' ', $output);
        //echo print_r($properties,1) . "\n";

        // File Format
        $this->attributes[self::FILE_FORMAT] = isset($properties[1])?$properties[1]:"Unknown";

        // Resolution
        if (isset($properties[2]) && str_contains($properties[2], 'x')){
            $res = explode('x', $properties[2]);
            $this->attributes[self::FILE_WIDTH] = $res[0];
            $this->attributes[self::FILE_HEIGHT] = $res[1];
        }else{
            $this->attributes[self::FILE_WIDTH] = -1;
            $this->attributes[self::FILE_HEIGHT] = -1;
        }

        // File Depth
        if (isset($properties[4])){
            $this->attributes[self::FILE_DEPTH] = $properties[4];
        }else{
            $this->attributes[self::FILE_DEPTH] = -1;
        }

        // ColorSpace
        if (isset($properties[5])){
            $this->attributes[self::FILE_COLORSPACE] = $properties[5];
        }else{
            $this->attributes[self::FILE_COLORSPACE] = -1;
        }

        // File Type
        $this->getFileType();

        //echo "File Attributes: \n" . print_r($this->attributes,1 ) . "\n";
    }

    protected function getFileType(){
        // Imagemagick supported file formats: https://imagemagick.org/script/formats.php
        $validImageFormats = array(self::FORMAT_JPEG, self::FORMAT_JPG, self::FORMAT_PNG, self::FORMAT_GIF, self::FORMAT_WEBP, self::FORMAT_AI,
                                   self::FORMAT_TIFF, self::FORMAT_TIF, self::FORMAT_PSD, self::FORMAT_TGA);

        $validVideoFormats = array();
        $validDocumentFormats = array();
        $validAudioFormats = array();

        if (in_array($this->attributes[self::FILE_FORMAT], $validImageFormats)){
            $this->attributes[self::FILE_FILETYPE] = self::FILETYPE_IMAGE;
        }else if (in_array($this->attributes[self::FILE_FORMAT], $validVideoFormats)){
            $this->attributes[self::FILE_FILETYPE] = self::FILETYPE_VIDEO;
        }else if (in_array($this->attributes[self::FILE_FORMAT], $validDocumentFormats)){
            $this->attributes[self::FILE_FILETYPE] = self::FILETYPE_DOCUMENT;
        }else if (in_array($this->attributes[self::FILE_FORMAT], $validAudioFormats)){
            $this->attributes[self::FILE_FILETYPE] = self::FILETYPE_AUDIO;
        }else{
            $this->attributes[self::FILE_FILETYPE] = self::FILETYPE_OTHER;
        }
    }

    public function setDestinationFormat($fileFormat){
        $this->destinationFormat = $fileFormat;
        echo"******* SETTING DESTINATION FORMAT TO: " . $this->destinationFormat . "\n";

        // Check to see if it's a supported destination format.
        //echo "File Format: " . $fileFormat . "\n";
        //echo "Allowed Formats: " . print_r($this->attributes[self::VALID_FORMATS],1) . "\n";
        if (!$this->supportsDestinationFormat()){
            throw new \Exception("File Format conversion not supported.");
        }
        return $this;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    // Takes care of getters and setters for all defined properties
    public function __call($method, $params = null){
        $prefix = substr($method, 0, 3);
        $key = strtolower(substr($method, 3));

        if($prefix == 'set' && count($params) == 1) {
            if (property_exists($this, $key)) {
                $this->$key = $params[0];
                return $this;
            }
        }
        else if($prefix == 'get') {
            if (property_exists($this, $key)) {
                return $this->$key;
            }
        }
        else {
            if (!property_exists($this, $key)){
                throw new \Exception("Call to undefined method $method on " . get_class($this));
            }
        }

        if (!isset($this->functions[$method])){
            throw new \Exception("Call to undefined method $method on " . get_class($this));
        }

        return call_user_func_array($this->functions[$method], $params);
    }

    public function supportsDestinationFormat(){
        return in_array($this->destinationFormat, $this->attributes[self::VALID_FORMATS]);
    }

    public static function fetchHandler($asset){
        if (!$asset){
            return null;
        }

        $file = $asset->file;
        echo "BaseHandler: Downloading File\n";
        $filename = self::downloadFile($asset, $file);
        echo "Filename: $filename\n";

        $extension = $file->extension;
        if ($extension == 'jpeg'){
            $extension = 'jpg';
        }

        $handlerName = 'common\\ImageProcessing\\' . strtoupper($extension) . "Handler";
        echo "HandlerName: $handlerName\n";
        try{
            $handler = new $handlerName($filename, $asset->id);
            return $handler;
        }catch (\Throwable $e){
            echo $e->getMessage()."\n";
            echo $e->getTraceAsString()."\n";
            return null;
        }
    }

    public static function downloadFile($asset, $file){

        // If file has been recently uploaded, use tmp file to avoid unnecessary downloads
        if ($file->tmp_location && file_exists($file->tmp_location)){
            return $file->tmp_location;
        }

        // If not found (thumbnail re-generation), then download file for processing.
        $filename = tempnam('/tmp', $asset->id);
        $env = YII_ENV_DEV ? 'dev' : 'prod';

        // AWS config
        $s3 = new S3Client([
            'region' => Yii::$app->params['AWS_REGION'],
            'version' => 'latest',
            'credentials' => [
                'key'    => Yii::$app->params['AWS_ACCESS_KEY_ID'],
                'secret' => Yii::$app->params['AWS_SECRET_ACCESS_KEY'],
            ],
        ]);

        $key = "{$env}/original/{$asset->customer_id}/{$asset->id}";
        try {
            $result = $s3->getObject([
                'Bucket' => Yii::$app->params['AWS_BUCKET'],
                'Key'    => $key,
                'SaveAs' => $filename, // streams directly to disk
            ]);

            echo "Downloaded to: $filename\n";
        } catch (AwsException $e) {
            echo "S3 error: " . $e->getAwsErrorMessage() . "\n";
            return null;
        }
        return $filename;
    }

    public function saveThumbnail($asset){
        $env = YII_ENV_DEV ? 'dev' : 'prod';
        $this->destinationFormat = SELF::FORMAT_JPG;

        try {

            $s3 = new S3Client([
                'region' => Yii::$app->params['AWS_REGION'],
                'version' => 'latest',
                'credentials' => [
                    'key' => Yii::$app->params['AWS_ACCESS_KEY_ID'],
                    'secret' => Yii::$app->params['AWS_SECRET_ACCESS_KEY'],
                ],
            ]);

            $key = "{$env}/thumbnail/{$asset->customer_id}/{$asset->id}";
            //echo "KEY: $key\n";

            // Thumbnails will always be
            //$finfo = finfo_open(FILEINFO_MIME_TYPE);
            //$mimeType = finfo_file($finfo, $this->attributes[self::FILE_NAME]);
            //finfo_close($finfo);


            // Set Mimetype as defined by the destination format.
            echo"******* DESTINATION FORMAT IS: " . $this->destinationFormat . "\n";
            $mimeType = $this->mimeTypes[$this->destinationFormat];

            echo "Mime Type: " . $mimeType . "\n";

            $result = $s3->putObject([
                'Bucket' => Yii::$app->params['AWS_BUCKET'],
                'Key' => $key,
                'SourceFile' => $this->destinationFile,
                'ACL' => 'private', // or 'public-read' if you want instant CloudFront access
                'CacheControl' => 'max-age=31536000',
                'ContentType' => $mimeType,
            ]);
            echo "S3 upload result: " . ($result ? "OK" : "FAILED") . "\n";

            // Delete temporaty files
            @unlink($asset->file->tmp_location);
            @unlink($this->destinationFile);

            // Update asset status
            $asset->thumbnail_state = Asset::THUMBNAIL_READY;
            $asset->thumbnail_url = Yii::$app->params['CLOUDFRONT_URL'] . '/' . $env . '/thumbnail/' . $asset->customer_id . "/" . $asset->id;
            $asset->save();

            $asset->save();
            $asset->file->tmp_location = null;
            $asset->file->save();
        }catch (\Throwable $e) {
            echo "Error Uploading to S3: " . $e->getMessage() . "\n";
            echo $e->getTraceAsString() . "\n";
        }
    }

    public function cleanup($asset){
        @unlink($asset->file->tmp_location);
    }

}
/* Manipulation tools:
    pdfimages -list document.pdf
    pdftopng
    pdftoppm
    dcraw
    gs
    PDF2TXT_EXE
    jhead
    ncconvert
*/
