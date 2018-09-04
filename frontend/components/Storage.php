<?php

namespace frontend\components;

use yii\base\Component;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use Yii;


/*

 * File storage component
 *  */
class Storage extends Component implements StorageInterface
{
    private $fileName;
    /*

     * Save given UploadedFile instance to disk
     * @param UploadedFile $file
     * @return string|null
     */
    public function saveUploadedFile(UploadedFile $file) {
        
        $path = $this->preparePath($file);
        
        if ($path && $file->saveAs($path)){
            return $this->fileName;
        }
        
    }
    
    
    /*

     * Delete file from storage
     * @var string $filename
     * @return bool
     *      */
    public function deleteUploadedFile(string $filename) {
        
        $file = $this->getStoragePath().$filename;
        
        if (file_exists($file)){
            return unlink($file);
        }
        
        return true;
    }


    /*

     * Prepare path to save uploaded file
     * @param UploadedFile $file
     * @return string|null;
     */
    
    protected function preparePath(UploadedFile $file) {
        
        $this->fileName = $this->getFileName($file);
        // 0a/c0/s3dx4ms2exffffddd.jpg
        
        $path = $this->getStoragePath().$this->fileName;
        // /var/www/project/frontend/web/uploads/0a/c0/s3dx4ms2exffffddd.jpg
        
        $path = FileHelper::normalizePath($path);
        
        if(FileHelper::createDirectory(dirname($path))){
            return $path;
        }
    }
    
    /*

     * @param UploadedFile $file
     * @return string
     */
    
    protected function getFilename(UploadedFile $file){
        
        // $file->tempName - /tmp/qio3ce
        
        $hash = sha1_file($file->tempName); // 0ac0s3dx4ms2exffffddd
        
        $name = substr_replace($hash, '/', 2, 0); // 0a/c0s3dx4ms2exffffddd
        $name = substr_replace($name, '/', 5, 0); // 0a/c0/s3dx4ms2exffffddd
        
        return $name. '.' .$file->extension; // 0a/c0/s3dx4ms2exffffddd.jpg
    }
    
    /*

     * @return string
     */
    protected function getStoragePath(){
        return Yii::getAlias(Yii::$app->params['storagePath']);
    }

    /*

     * @param string $filename
     * @return string;
     *      */
    public function getFile(string $filename) {
        
        return Yii::$app->params['storageUri'].$filename;
    }
}
