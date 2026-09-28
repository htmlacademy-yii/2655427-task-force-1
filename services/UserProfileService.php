<?php

declare(strict_types=1);

namespace app\services;

use app\models\Feedback;
use app\models\Task;
use app\models\User;
use app\models\UserCategory;
use TaskForce\Logic\Enums\TaskStatus;
use yii\web\NotFoundHttpException;

/**
 * Provides data for executor profiles.
 */
class UserProfileService
{
    /**
     * Returns all data required to display an executor profile.
     *
     * @param int $userId Executor user ID.
     *
     * @return array<string, mixed> Profile data.
     *
     * @throws NotFoundHttpException If the executor is not found.
     */
    public function getProfile(int $userId): array
    {
        $user = $this->findExecutor($userId);

        $feedbacks = Feedback::find()
            ->with(['author', 'task'])
            ->where(['executor_id' => $user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $rating = Feedback::find()
            ->where(['executor_id' => $user->id])
            ->average('evaluation');

        return [
            'user' => $user,
            'userCategories' => UserCategory::find()
                ->with('category')
                ->where(['user_id' => $user->id])
                ->all(),
            'feedbacks' => $feedbacks,
            'completedTasks' => count($feedbacks),
            'failedTasks' => (int) $user->failed_tasks_count,
            'rating' => $rating !== false ? (float) $rating : null,
            'ratingPosition' => $this->getRatingPosition(
                $user->id,
                $rating
            ),
            'age' => $this->getAge($user),
            'status' => $this->getStatus($user->id),
        ];
    }

    /**
     * Finds an executor by ID.
     *
     * @param int $userId User ID.
     *
     * @return User Executor model.
     *
     * @throws NotFoundHttpException If the executor is not found.
     */
    private function findExecutor(int $userId): User
    {
        $user = User::findOne($userId);

        if ($user === null || !$user->isUserRoleExecutor()) {
            throw new NotFoundHttpException(
                'Пользователь не найден.'
            );
        }

        return $user;
    }

    /**
     * Returns the executor's rating position.
     *
     * @param int $userId Executor user ID.
     * @param float|int|string|null $rating Executor rating.
     *
     * @return int|null Rating position or null if there is no rating.
     */
    private function getRatingPosition(
        int $userId,
        float|int|string|false|null $rating
    ): ?int {
        if ($rating === null || $rating === false) {
            return null;
        }

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
            if ((int) $executorRating['executor_id'] === $userId) {
                return $position + 1;
            }
        }

        return null;
    }

    /**
     * Calculates the executor's age.
     *
     * @param User $user Executor model.
     *
     * @return int|null Executor age or null if birthday is not set.
     */
    private function getAge(User $user): ?int
    {
        if ($user->birthday === null) {
            return null;
        }

        $birthday = new \DateTimeImmutable($user->birthday);
        $today = new \DateTimeImmutable();

        return $birthday->diff($today)->y;
    }

    /**
     * Returns the executor's availability status.
     *
     * @param int $userId Executor user ID.
     *
     * @return string Availability status.
     */
    private function getStatus(int $userId): string
    {
        $isBusy = Task::find()
            ->joinWith('status')
            ->where([
                'executor_id' => $userId,
                'status.name' => TaskStatus::Work->label(),
            ])
            ->exists();

        return $isBusy
            ? 'Занят'
            : 'Открыт для новых заказов';
    }
}
