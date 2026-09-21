<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\AccountSettingsForm;
use app\models\Category;
use app\models\UserCategory;
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
                $user->name = $model->name;
                $user->email = $model->email;
                $user->birthday = $model->birthday !== ''
                    ? \DateTimeImmutable::createFromFormat(
                        'd.m.Y',
                        $model->birthday
                    )->format('Y-m-d')
                    : null;
                $user->phone_number = $model->phone_number !== ''
                    ? $model->phone_number
                    : null;
                $user->telegram = $model->telegram !== ''
                    ? $model->telegram
                    : null;
                $user->hide_contacts = $model->hide_contacts ? 1 : 0;

                if ($model->shouldChangePassword()) {
                    $user->password = Yii::$app->security
                        ->generatePasswordHash(
                            $model->new_password
                        );
                }

                if ($model->avatar !== null) {
                    $uploadPath = Yii::getAlias(
                        '@webroot/uploads/avatars'
                    );

                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0775, true);
                    }

                    $fileName = Yii::$app->security
                        ->generateRandomString(32)
                        . '.'
                        . $model->avatar->extension;

                    $filePath = $uploadPath
                        . DIRECTORY_SEPARATOR
                        . $fileName;

                    if (!$model->avatar->saveAs($filePath)) {
                        $model->addError(
                            'avatar',
                            'Не удалось сохранить аватар.'
                        );
                    } else {
                        $user->avatar_path = '/uploads/avatars/'
                            . $fileName;
                    }
                }

                if (!$model->hasErrors() && $user->save()) {
                    UserCategory::deleteAll([
                        'user_id' => $user->id,
                    ]);

                    foreach ($model->categories as $categoryId) {
                        $userCategory = new UserCategory();
                        $userCategory->user_id = $user->id;
                        $userCategory->category_id = (int) $categoryId;
                        $userCategory->save();
                    }

                    return $this->redirect(['profile/index']);
                }

                if ($user->hasErrors()) {
                    $model->addErrors($user->getErrors());
                }
            }
        }

        return $this->render('index', [
            'model' => $model,
            'categories' => $categories,
        ]);
    }
}