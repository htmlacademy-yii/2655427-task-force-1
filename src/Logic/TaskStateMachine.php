<?php

declare(strict_types=1);

namespace TaskForce\Logic;

use TaskForce\Logic\Enums\TaskAction;
use TaskForce\Logic\Enums\TaskStatus;
use TaskForce\Logic\Exceptions\TaskException;

/**
 * Manages task states.
 *
 * Determines:
 * - actions available for the current status;
 * - actions allowed for a specific user;
 * - task transitions between statuses.
 */
class TaskStateMachine
{
    /**
     * Returns the list of actions available for the specified status.
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
     * Returns the list of actions available to the current user.
     *
     * @param TaskStatus $status Current task status.
     * @param int $customerId Customer ID.
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
     * Transitions the task to a new status.
     *
     * @param TaskStatus $current Current task status.
     * @param TaskAction $action Action to perform.
     *
     * @return TaskStatus New task status.
     *
     * @throws TaskException If the action is not available.
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
