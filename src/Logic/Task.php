<?php

declare(strict_types=1);

namespace TaskForce\Logic;

use TaskForce\Logic\Enums\TaskAction;
use TaskForce\Logic\Enums\TaskStatus;

/**
 * Represents a task.
 *
 * Stores information about the customer, executor, and current task status.
 * Uses the TaskStateMachine object to determine available actions and change
 * the task status.
 */
class Task
{
    private int $customerId;
    private ?int $executorId;
    private TaskStatus $status;

    /**
     * Creates a task object.
     *
     * @param int $customerId ID of the task customer.
     * @param int|null $executorId ID of the task executor, or null if no executor is assigned yet.
     * @param TaskStatus $status Current task status.
     */
    public function __construct(int $customerId, ?int $executorId = null, TaskStatus $status)
    {
        $this->customerId = $customerId;
        $this->executorId = $executorId;
        $this->status = $status;
    }

    /**
     * Returns the actions available to the current user.
     *
     * Delegates the check for available actions to the TaskStateMachine object.
     *
     * @param TaskStateMachine $machine Task state machine object.
     * @param int $currentUserId ID of the current user.
     *
     * @return array List of available actions.
     */
    public function getAvailableActions(TaskStateMachine $machine, int $currentUserId): array
    {
        return $machine->getAllowedActions(
            $this->status,
            $this->customerId,
            $currentUserId,
            $this->executorId
        );
    }

    /**
     * Applies an action to the task and changes its status.
     *
     * The new status is determined by the TaskStateMachine object.
     *
     * @param TaskStateMachine $machine Task state machine object.
     * @param TaskAction $action Action to perform.
     */
    public function apply(TaskStateMachine $machine, TaskAction $action): void
    {
        $this->status = $machine->transition($this->status, $action);
    }
}
