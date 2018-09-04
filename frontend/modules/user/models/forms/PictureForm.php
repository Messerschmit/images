<?php

namespace frontend\modules\user\models\forms;

use Yii;
use yii\base\Model;
use Intervention\Image\ImageManager;
    
class PictureForm extends Model {
    
    public $picture;
    
    public function rules() {
        return [
            [['picture'], 'file',
                'extensions' => ['jpg'],
                'checkExtensionByMimeType' => 'true',
                'maxSize' => $this->getMaxSize(),
            ]
        ];
    }
    
    public function __construct() {
        
        $this->on(self::EVENT_AFTER_VALIDATE, [$this, 'resizePicture']);
    }
    
    /* Resize uploaded picture if need */
    public function resizePicture() {
        
        if ($this->picture->error){
            
               /* В объекте UploadedFile есть свойство error. Если в нем "1", значит
+            * произошла ошибка и работать с изображением не нужно, прерываем
+            * выполнение метода */
            
            return ;
        }
        
        $width = Yii::$app->params['profilePicture']['maxWidth'];
        $height = Yii::$app->params['profilePicture']['maxHeight'];
        
        $manager = new ImageManager(['driver' => 'imagick']);
        
        $image = $manager->make($this->picture->tempName);
        
        $image->resize($width, $height, function ($constrait) {
            
            $constrait->aspectRatio();
            
            $constrait->upsize();
            
        })->save();
    }
    
    public function save()
    {
        return 1;
    }
    
    /*

     * Return max size file from app params
     * @return integer
     */
    protected function getMaxSize(){
        return Yii::$app->params['maxFileSize'];
    }
}
