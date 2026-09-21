<?php

declare(strict_types=1);

namespace TaskForce\Logic\Enums;

/**
 * Defines all possible task statuses.
 */
enum TaskStatus: string
{
    case New = 'new';
    case Cancel = 'cancel';
    case Work = 'work';
    case Done = 'done';
    case Failed = 'failed';

    /**
     * Returns the display name of the status.
     *
     * @return string Status name in Russian.
     */
    public function label(): string
    {
        return match ($this) {
            self::New => 'Новое',
            self::Cancel => 'Отменено',
            self::Work => 'В работе',
            self::Done => 'Выполнено',
            self::Failed => 'Провалено',
        };
    }
}
