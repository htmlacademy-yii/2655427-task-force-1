<?php

namespace app\controllers;

use app\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class UserController extends Controller
{
    public function actionView($id)
    {
        $user = User::findOne($id);

        if ($user === null) {
            throw new NotFoundHttpException('Пользователь не найден.');
        }

        return $this->render('view', [
            'user' => $user,
        ]);
    }
}
