<?php

namespace app\models;

use yii\base\Model;

/**
 * TaskFilter is the model for filtering tasks.
 */
class TaskFilter extends Model
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
