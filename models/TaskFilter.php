<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;

/**
 * TaskFilter is the model for filtering tasks.
 */
class TaskFilter extends Model
{
    /**
     * Selected task category IDs.
     */
    public ?array $categories = null;

    /**
     * Whether to show only tasks without a performer.
     */
    public ?bool $without_performer = null;

    /**
     * Period for filtering tasks in hours.
     */
    public ?int $period = null;
}
