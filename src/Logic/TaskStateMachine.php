<?php

declare(strict_types=1);

namespace TaskForce\Logic;

use TaskForce\Logic\Enums\TaskAction;
use TaskForce\Logic\Enums\TaskStatus;
use TaskForce\Logic\Exceptions\TaskException;

/**
 * Manages task states and available actions.
 */
class TaskStateMachine
{
    /**
     * Returns all actions available for the given task status.
     *
     * @param TaskStatus $status Current task status.
     *
     * @return array<int, TaskAction> Available actions.
     */
    public function getAvailableActions(TaskStatus $status): array
    {
        return array_filter(
            TaskAction::cases(),
            static function (TaskAction $action) use ($status): bool {
                return in_array(
                    $status,
                    $action->availableInStatuses(),
                    true
                );
            }
        );
    }

    /**
     * Returns actions allowed for the current user.
     *
     * @param TaskStatus $status Current task status.
     * @param int $customerId Task customer ID.
     * @param int $currentUserId Current user ID.
     * @param int|null $executorId Task executor ID.
     *
     * @return array<int, TaskAction> Allowed actions.
     */
    public function getAllowedActions(
        TaskStatus $status,
        int $customerId,
        int $currentUserId,
        ?int $executorId
    ): array {
        $actions = $this->getAvailableActions($status);

        return array_filter(
            $actions,
            static function (TaskAction $action) use (
                $customerId,
                $executorId,
                $currentUserId
            ): bool {
                return $action->checkRights(
                    $customerId,
                    $executorId,
                    $currentUserId
                );
            }
        );
    }

    /**
     * Checks whether a user can perform an action.
     *
     * @param TaskStatus $status Current task status.
     * @param TaskAction $action Requested action.
     * @param int $customerId Task customer ID.
     * @param int $currentUserId Current user ID.
     * @param int|null $executorId Task executor ID.
     *
     * @return bool Whether the action is allowed.
     */
    public function canPerformAction(
        TaskStatus $status,
        TaskAction $action,
        int $customerId,
        int $currentUserId,
        ?int $executorId
    ): bool {
        return in_array(
            $action,
            $this->getAllowedActions(
                $status,
                $customerId,
                $currentUserId,
                $executorId
            ),
            true
        );
    }

    /**
     * Converts a database status name to a task status enum.
     *
     * @param string $name Database status name.
     *
     * @return TaskStatus Task status enum.
     *
     * @throws TaskException If the status is unknown.
     */
    public function getStatusByName(string $name): TaskStatus
    {
        return match ($name) {
            'Новое' => TaskStatus::New,
            'Отменено' => TaskStatus::Cancel,
            'В работе' => TaskStatus::Work,
            'Выполнено' => TaskStatus::Done,
            'Провалено' => TaskStatus::Failed,
            default => throw new TaskException(
                "Неизвестный статус задания: {$name}."
            ),
        };
    }

    /**
     * Returns the resulting status for an action.
     *
     * @param TaskStatus $current Current task status.
     * @param TaskAction $action Requested action.
     *
     * @return TaskStatus Resulting task status.
     *
     * @throws TaskException If the action is unavailable.
     */
    public function transition(
        TaskStatus $current,
        TaskAction $action
    ): TaskStatus {
        if (
            !in_array(
                $current,
                $action->availableInStatuses(),
                true
            )
        ) {
            throw new TaskException(
                "Действие {$action->label()} недоступно "
                . "для статуса {$current->label()}."
            );
        }

        return $action->resultingStatus();
    }
}
