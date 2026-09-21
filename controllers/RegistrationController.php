<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\City;
use app\models\RegistrationForm;
use app\models\User;
use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * Handles user registration.
 */
class RegistrationController extends Controller
{
    /**
     * Displays the registration form and creates a new user.
     *
     * @return string|Response Registration form or redirect response.
     */
    public function actionIndex(): string|Response
    {
        $model = new RegistrationForm();
        $cities = City::find()->all();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $user = new User();
            $user->name = $model->name;
            $user->email = $model->email;
            $user->city_id = (int) $model->city_id;
            $user->password = password_hash(
                $model->password,
                PASSWORD_DEFAULT
            );
            $user->setRole(
                $model->is_executor
                    ? User::USER_ROLE_EXECUTOR
                    : User::USER_ROLE_CUSTOMER
            );

            if ($user->save()) {
                Yii::$app->user->login($user);

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
