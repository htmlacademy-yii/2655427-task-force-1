<?php

namespace app\controllers;

use app\models\City;
use app\models\RegistrationForm;
use app\models\User;
use yii\web\Controller;

class RegistrationController extends Controller
{
    public function actionIndex()
    {
        $model = new RegistrationForm();
        $cities = City::find()->all();

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            $user = new User();

            $user->name = $model->name;
            $user->email = $model->email;
            $user->city_id = $model->city_id;
            $user->password = password_hash($model->password, PASSWORD_DEFAULT);

            if ($model->is_executor) {
                $user->setUserRoleToExecutor();
            } else {
                $user->setUserRoleToCustomer();
            }

            if ($user->save()) {
                return $this->redirect(['tasks/index']);
            }

            $model->addErrors($user->getErrors());
        }

        return $this->render('index', [
            'model' => $model,
            'cities' => $cities,
        ]);
    }
}
