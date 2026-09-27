<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\AccountSettingsForm;
use app\models\Category;
use app\services\ProfileService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Handles user profile settings.
 */
class ProfileController extends Controller
{
    /**
     * Configures access control for profile actions.
     *
     * @return array<string, mixed> Access control configuration.
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Displays and updates account settings.
     *
     * @return string|Response Rendered settings page or redirect response.
     */
    public function actionIndex(): string|Response
    {
        $user = Yii::$app->user->identity;

        $model = new AccountSettingsForm(
            $user,
            Yii::$app->security
        );

        $categories = Category::find()
            ->orderBy(['name' => SORT_ASC])
            ->all();

        $post = Yii::$app->request->post();

        if (isset($post['AccountSettingsForm']['avatar'])) {
            unset($post['AccountSettingsForm']['avatar']);
        }

        if (isset($post['AccountSettingsForm'])) {
            $post['AccountSettingsForm']['categories'] =
                $post['AccountSettingsForm']['categories'] ?? [];
        }

        if ($model->load($post)) {
            $model->avatar = UploadedFile::getInstance(
                $model,
                'avatar'
            );

            if ($model->validate()) {
                $service = new ProfileService();

                if ($service->update($user, $model)) {
                    return $this->redirect([
                        'profile/index',
                    ]);
                }
            }
        }

        return $this->render('index', [
            'model' => $model,
            'categories' => $categories,
        ]);
    }
}
