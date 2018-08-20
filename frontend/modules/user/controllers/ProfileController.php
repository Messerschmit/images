<?php

namespace frontend\modules\user\controllers;

use yii\web\Controller;
use frontend\models\User;
use yii\web\NotFoundHttpException;

class ProfileController extends Controller {
    
    public function actionView($id)
    {
        return $this->render('view', [
            'user' => $this->findUserById($id),
        ]);
    }
    
    private function findUserById($id)
    {
        if ($user = User::find()->where(['id' => $id])->one()){
            return $user;
        } 
        
        throw new NotFoundHttpException();
    }
    
}
