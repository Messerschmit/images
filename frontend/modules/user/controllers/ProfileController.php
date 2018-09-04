<?php

namespace frontend\modules\user\controllers;

use yii\web\Controller;
use frontend\models\User;
use yii\web\NotFoundHttpException;
use Yii;
use frontend\modules\user\models\forms\PictureForm;
use yii\web\UploadedFile;
use yii\web\Response;

class ProfileController extends Controller {
    
    public function actionView($nickname)
    {
        /* @var $currentUser User */
        $currentUser = Yii::$app->user->identity;
        
        $modelPicture = new PictureForm();
                
        return $this->render('view', [
            'user' => $this->findUserByNickname($nickname),
            'currentUser' => $currentUser,
            'modelPicture' => $modelPicture,
        ]);
    }
    
    private function findUserByNickname($nickname)
    {
        if ($user = User::find()->where(['nickname' => $nickname])->orWhere(['id' => $nickname])->one()){
            return $user;
        } 
        
        throw new NotFoundHttpException();
    }
    
    public function actionSubscribe($id)
    {
        if (Yii::$app->user->isGuest){
            return $this->redirect(['/user/default/login']);
        }
        
        /* @var $currentUser User */
        
        $currentUser = Yii::$app->user->identity;
        
        $user = $this->findUserById($id);
        
        $currentUser->followUser($user);
        
        return $this->redirect(['/user/profile/view', 'nickname' => $user->getNickname()]);
    }
    
    private function findUserById($id) {
        if ($user = User::findOne($id)){
            return $user;
        }
        
        throw new NotFoundHttpException();
    }
    
        public function actionUnsubscribe($id)
    {
        if (Yii::$app->user->isGuest){
            return $this->redirect(['/user/default/login']);
        }
        
        /* @var $currentUser User */
        
        $currentUser = Yii::$app->user->identity;
        
        $user = $this->findUserById($id);
        
        $currentUser->unfollowUser($user);
        
        return $this->redirect(['/user/profile/view', 'nickname' => $user->getNickname()]);
    }
    
    /*

     * Handle profile image upload via ajax request
     */
    public function actionUploadPicture()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $model = new PictureForm();
        $model->picture = UploadedFile::getInstance($model, 'picture');

        if ($model->validate()){
            
            $user = Yii::$app->user->identity;
            
            $user->picture = Yii::$app->storage->saveUploadedFile($model->picture);
            
            if ($user->save(false, ['picture'])){
                return [
                    'success' => true,
                    'pictureUri' => Yii::$app->storage->getFile($user->picture),
                ];
            }
        }
        return ['success' => false, 'errors' => $model->getErrors()];
    }
    
    /* 
     * Handle profile image delete via ajax request 
     */
    public function actionDeletePicture() {
        
        if (Yii::$app->user->isGuest){
            
            return $this->redirect(['/user/default/login']);
        }
        
        /* @var $currentUser User*/
        $currentUser = Yii::$app->user->identity;
        
        if ($currentUser->deletePicture()){
            Yii::$app->session->setFlash('success', 'Picture deleted');
        } else {
            Yii::$app->session->setFlash('danger', 'Error ocured'); 
        }
        
        return $this->redirect(['/user/profile/view', 'nickname' => $currentUser->getNickname()]);
    }
    
    
    
//    public function actionGenerate()
//    {
//        $faker = \Faker\Factory::create();
//        
//        for ($i = 0; $i < 1000; $i++){
//            $user = new User([
//                'username' => $faker->name,
//                'email' => $faker->email,
//                'about' => $faker->text(200),
//                'nickname' => $faker->regexify('[A-Za-z0-9_]{5,15}'),
//                'auth_key' => Yii::$app->security->generateRandomString(),
//                'password_hash' => Yii::$app->security->generateRandomString(),
//                'created_at' => $time = time(),
//                'updated_at' => $time,
//            ]);
//            
//            $user->save(false);
//        }
//    }
    
}
