<?php

declare(strict_types=1);

namespace app\services;

use app\models\Task;
use app\models\User;
use TaskForce\Logic\Enums\TaskStatus;
use Yii;

/**
 * Provides data for the current user's tasks page.
 */
class MyTasksService
{
    /**
     * Returns tasks grouped for the current user.
     *
     * @param int $userId Current user ID.
     *
     * @return array<string, mixed> Tasks grouped by status.
     */
    public function getTasks(int $userId): array
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        if ($user->isUserRoleCustomer()) {
            return $this->getCustomerTasks($userId);
        }

        return $this->getExecutorTasks($userId);
    }

    /**
     * Returns tasks for a customer.
     *
     * @param int $userId Customer ID.
     *
     * @return array<string, mixed> Customer's tasks grouped by status.
     */
    private function getCustomerTasks(int $userId): array
    {
        $newTasks = Task::find()
            ->joinWith('status')
            ->where([
                'author_id' => $userId,
                'status.name' => TaskStatus::New->label(),
                'executor_id' => null,
            ])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $inProgressTasks = Task::find()
            ->joinWith('status')
            ->where([
                'author_id' => $userId,
                'status.name' => TaskStatus::Work->label(),
            ])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $closedTasks = Task::find()
            ->joinWith('status')
            ->where([
                'author_id' => $userId,
                'status.name' => [
                    TaskStatus::Cancel->label(),
                    TaskStatus::Done->label(),
                    TaskStatus::Failed->label(),
                ],
            ])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return [
            'newTasks' => $newTasks,
            'inProgressTasks' => $inProgressTasks,
            'closedTasks' => $closedTasks,
            'overdueTasks' => [],
            'isExecutor' => false,
        ];
    }

    /**
     * Returns tasks for an executor.
     *
     * @param int $userId Executor ID.
     *
     * @return array<string, mixed> Executor's tasks grouped by status.
     */
    private function getExecutorTasks(int $userId): array
    {
        $inProgressTasks = Task::find()
            ->joinWith('status')
            ->innerJoin(
                'response',
                'response.task_id = task.id'
            )
            ->where([
                'response.user_id' => $userId,
                'status.name' => TaskStatus::Work->label(),
            ])
            ->andWhere([
                'or',
                ['deadline' => null],
                ['>=', 'deadline', date('Y-m-d')],
            ])
            ->distinct()
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $overdueTasks = Task::find()
            ->joinWith('status')
            ->innerJoin(
                'response',
                'response.task_id = task.id'
            )
            ->where([
                'response.user_id' => $userId,
                'status.name' => TaskStatus::Work->label(),
            ])
            ->andWhere([
                '<',
                'deadline',
                date('Y-m-d'),
            ])
            ->distinct()
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $closedTasks = Task::find()
            ->joinWith('status')
            ->innerJoin(
                'response',
                'response.task_id = task.id'
            )
            ->where([
                'response.user_id' => $userId,
                'status.name' => [
                    TaskStatus::Done->label(),
                    TaskStatus::Failed->label(),
                ],
            ])
            ->distinct()
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return [
            'newTasks' => [],
            'inProgressTasks' => $inProgressTasks,
            'overdueTasks' => $overdueTasks,
            'closedTasks' => $closedTasks,
            'isExecutor' => true,
        ];
    }
}
