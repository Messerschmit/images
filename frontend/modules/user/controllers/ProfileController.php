<?php

namespace frontend\modules\user\controllers;

use yii\web\Controller;
use frontend\models\User;
use yii\web\NotFoundHttpException;

class ProfileController extends Controller {
    
    public function actionView($nickname)
    {
        return $this->render('view', [
            'user' => $this->findUserByNickname($nickname),
        ]);
    }
    
    private function findUserByNickname($nickname)
    {
        if ($user = User::find()->where(['nickname' => $nickname])->orWhere(['id' => $nickname])->one()){
            return $user;
        } 
        
        throw new NotFoundHttpException();
    }
    
}
