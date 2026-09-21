<?php

declare(strict_types=1);

namespace app\models;

/**
 * TaskFilter is the model for filtering tasks.
 */
class TaskFilter extends \yii\base\Model
{
    /**
     * Selected task category IDs.
     *
     * @var array|null
     */
    public $categories;

    /**
     * Whether to show only tasks without a performer.
     *
     * @var bool|null
     */
    public $without_performer;

    /**
     * Period for filtering tasks in hours.
     *
     * @var int|null
     */
    public $period;
}
