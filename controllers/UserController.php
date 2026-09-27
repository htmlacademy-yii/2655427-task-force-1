<?php

declare(strict_types=1);

namespace app\controllers;

use app\services\UserProfileService;
use yii\web\Controller;

/**
 * Handles requests for users.
 */
class UserController extends Controller
{
    /**
     * Displays an executor profile by their ID.
     *
     * @param int $id User ID.
     *
     * @return string Rendered executor profile.
     */
    public function actionView(int $id): string
    {
        $service = new UserProfileService();

        return $this->render(
            'view',
            $service->getProfile($id)
        );
    }
}
