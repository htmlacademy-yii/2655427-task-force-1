<?php

declare(strict_types=1);

namespace TaskForce\Logic\Enums;

use TaskForce\Logic\Exceptions\TaskException;

/**
 * Defines all possible actions that can be performed on a task.
 */
enum TaskAction: string
{
    case Respond = 'respond';
    case Cancel = 'cancel';
    case Start = 'start';
    case Finish = 'finish';
    case Refuse = 'refuse';

    /**
     * Returns the task statuses in which this action is available.
     *
     * @return array List of allowed task statuses.
     */
    public function availableInStatuses(): array
    {
        return match($this) {
            self::Respond => [TaskStatus::New],
            self::Cancel => [TaskStatus::New],
            self::Start => [TaskStatus::New],
            self::Finish => [TaskStatus::Work],
            self::Refuse => [TaskStatus::Work],
        };
    }

    /**
     * Returns the status the task transitions to after performing this action.
     *
     * @return TaskStatus The resulting task status.
     *
     * @throws TaskException If the action does not change the task status.
     */
    public function resultingStatus(): TaskStatus
    {
        return match ($this) {
            self::Cancel => TaskStatus::Cancel,
            self::Start => TaskStatus::Work,
            self::Finish => TaskStatus::Done,
            self::Refuse => TaskStatus::Failed,
            self::Respond => throw new TaskException('Действие Respond не изменяет статус задания.'),
        };
    }

    /**
     * Checks whether the current user has permission to perform the action.
     *
     * @param int $customerId ID of the task customer.
     * @param int|null $executorId ID of the task executor.
     * @param int $currentUserId ID of the current user.
     * @param TaskStatus $currentStatus Current task status.
     *
     * @return bool Whether the action is available to the current user.
     */
    public function checkRights(
        int $customerId,
        ?int $executorId,
        int $currentUserId,
        TaskStatus $currentStatus
    ): bool {
        return match ($this) {
            self::Respond => $customerId !== $currentUserId && $executorId === null,
            self::Cancel => $customerId === $currentUserId,
            self::Start => $customerId === $currentUserId && $executorId === null,
            self::Finish => $customerId === $currentUserId && $executorId !== null,
            self::Refuse => $executorId === $currentUserId,
        };
    }

    /**
     * Returns the display name of the action.
     *
     * @return string Action name in Russian.
     */
    public function label(): string
    {
        return match ($this) {
            self::Respond => 'Откликнуться',
            self::Cancel => 'Отменить',
            self::Start => 'Принять',
            self::Finish => 'Завершить',
            self::Refuse => 'Отказаться',
        };
    }
}
