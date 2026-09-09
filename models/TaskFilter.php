<?php

namespace app\models;

use yii\base\Model;

class TaskFilter extends Model
{
    public $categories;
    public $without_performer;
    public $period;
}
