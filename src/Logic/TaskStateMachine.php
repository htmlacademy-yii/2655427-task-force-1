<?php

declare(strict_types=1);

namespace TaskForce\Logic;

use TaskForce\Logic\Enums\TaskStatus;
use TaskForce\Logic\Enums\TaskAction;
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
     * @return array List of available actions.
     */
    public function getAvailableActions(TaskStatus $status): array
    {
        return array_filter(TaskAction::cases(), fn(TaskAction $action) => in_array($status, $action->availableInStatuses(), true));
    }

    /**
     * Returns the list of actions available to the current user.
     *
     * Checks the user's permissions for each action
     * available in the current task status.
     *
     * @param TaskStatus $status Current task status.
     * @param int $customerId Customer ID.
     * @param int|null $currentUserId Current user ID.
     * @param int|null $executorId Task executor ID.
     *
     * @return array List of allowed actions.
     */
    public function getAllowedActions(TaskStatus $status, int $customerId, ?int $currentUserId, ?int $executorId): array
    {
        $cases = $this->getAvailableActions($status);
        return array_filter($cases, fn(TaskAction $action) => $action->checkRights($customerId, $executorId, $currentUserId, $status));
    }

    /**
     * Transitions the task to a new status.
     *
     * Checks that the specified action is allowed for the current status
     * and returns the new task status.
     *
     * @param TaskStatus $current Current task status.
     * @param TaskAction $action Action to perform.
     *
     * @return TaskStatus New task status.
     *
     * @throws TaskException If the action is not available for the current status.
     */
    public function transition(TaskStatus $current, TaskAction $action): TaskStatus
    {
        if (!in_array($current, $action->availableInStatuses(), true)) {
            throw new TaskException("Действие {$action->label()} недоступно для статуса {$current->label()}.");
        }

        return $action->resultingStatus();
    }
}
