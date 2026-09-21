<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Feedback;
use app\models\Task;
use app\models\User;
use app\models\UserCategory;
use TaskForce\Logic\Enums\TaskStatus;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

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
     *
     * @throws NotFoundHttpException If the executor is not found.
     */
    public function actionView(int $id): string
    {
        $user = User::findOne($id);

        if ($user === null || !$user->isUserRoleExecutor()) {
            throw new NotFoundHttpException('Пользователь не найден.');
        }

        $userCategories = UserCategory::find()
            ->with('category')
            ->where(['user_id' => $user->id])
            ->all();

        $feedbacks = Feedback::find()
            ->with(['author', 'task'])
            ->where(['executor_id' => $user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $completedTasks = Feedback::find()
            ->where(['executor_id' => $user->id])
            ->count();

        $failedTasks = (int) $user->failed_tasks_count;

        $rating = Feedback::find()
            ->where(['executor_id' => $user->id])
            ->average('evaluation');

        $ratingPosition = null;

        if ($rating !== false) {
            $ratings = Feedback::find()
                ->select([
                    'executor_id',
                    'rating' => 'AVG([[evaluation]])',
                ])
                ->groupBy(['executor_id'])
                ->orderBy(['rating' => SORT_DESC])
                ->asArray()
                ->all();

            foreach ($ratings as $position => $executorRating) {
                if (
                    (int) $executorRating['executor_id']
                    === (int) $user->id
                ) {
                    $ratingPosition = $position + 1;
                    break;
                }
            }
        }

        $age = null;

        if ($user->birthday !== null) {
            $birthday = new \DateTimeImmutable($user->birthday);
            $today = new \DateTimeImmutable();

            $age = $birthday->diff($today)->y;
        }

        $status = Task::find()
            ->joinWith('status')
            ->where([
                'executor_id' => $user->id,
                'status.name' => TaskStatus::Work->label(),
            ])
            ->exists()
            ? 'Занят'
            : 'Открыт для новых заказов';

        return $this->render('view', [
            'user' => $user,
            'userCategories' => $userCategories,
            'feedbacks' => $feedbacks,
            'completedTasks' => $completedTasks,
            'failedTasks' => $failedTasks,
            'rating' => $rating !== false ? (float) $rating : null,
            'ratingPosition' => $ratingPosition,
            'age' => $age,
            'status' => $status,
        ]);
    }
}
