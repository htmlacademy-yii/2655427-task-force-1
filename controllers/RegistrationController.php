<?php

namespace app\controllers;

use app\models\City;
use app\models\RegistrationForm;
use app\models\Role;
use app\models\User;
use yii\web\Controller;

/**
 * Handles user registration.
 */
class RegistrationController extends Controller
{
    /**
     * Displays the registration form and creates a new user.
     *
     * @return string|\yii\web\Response
     */
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
            $user->setRole(
                $model->is_executor ? Role::Executor : Role::Customer
            );

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
