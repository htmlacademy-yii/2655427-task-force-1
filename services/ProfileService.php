<?php

declare(strict_types=1);

namespace app\services;

use app\models\AccountSettingsForm;
use app\models\User;
use app\models\UserCategory;
use Yii;
use yii\web\UploadedFile;

/**
 * Handles user profile updates.
 */
class ProfileService
{
    /**
     * Updates user profile data.
     *
     * @param User $user User model.
     * @param AccountSettingsForm $model Settings form.
     *
     * @return bool Whether the profile was saved successfully.
     */
    public function update(
        User $user,
        AccountSettingsForm $model
    ): bool {
        $this->updateUserData($user, $model);

        if ($model->shouldChangePassword()) {
            $user->password = Yii::$app->security
                ->generatePasswordHash(
                    $model->new_password
                );
        }

        if (!$this->saveAvatar($user, $model)) {
            return false;
        }

        if ($model->hasErrors() || !$user->save()) {
            if ($user->hasErrors()) {
                $model->addErrors($user->getErrors());
            }

            return false;
        }

        $this->updateCategories($user, $model);

        return true;
    }

    /**
     * Updates basic user profile data.
     *
     * @param User $user User model.
     * @param AccountSettingsForm $model Settings form.
     *
     * @return void
     */
    private function updateUserData(
        User $user,
        AccountSettingsForm $model
    ): void {
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
    }

    /**
     * Saves the user's avatar.
     *
     * @param User $user User model.
     * @param AccountSettingsForm $model Settings form.
     *
     * @return bool Whether the avatar was saved successfully.
     */
    private function saveAvatar(
        User $user,
        AccountSettingsForm $model
    ): bool {
        if ($model->avatar === null) {
            return true;
        }

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

            return false;
        }

        $user->avatar_path = '/uploads/avatars/'
            . $fileName;

        return true;
    }

    /**
     * Updates user's specializations.
     *
     * @param User $user User model.
     * @param AccountSettingsForm $model Settings form.
     *
     * @return void
     */
    private function updateCategories(
        User $user,
        AccountSettingsForm $model
    ): void {
        UserCategory::deleteAll([
            'user_id' => $user->id,
        ]);

        foreach ($model->categories as $categoryId) {
            $userCategory = new UserCategory();
            $userCategory->user_id = $user->id;
            $userCategory->category_id = (int) $categoryId;
            $userCategory->save();
        }
    }
}
