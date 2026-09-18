<?php

namespace app\controllers;

use app\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Handles requests for users.
 */
class UserController extends Controller
{
    /**
     * Displays a user by their ID.
     *
     * @param int $id User ID.
     *
     * @return string
     *
     * @throws NotFoundHttpException If the user is not found.
     */
    public function actionView($id): string
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
