<?php

declare(strict_types=1);

namespace app\services;

use app\models\Category;
use app\models\Task;
use app\models\TaskFilter;
use TaskForce\Logic\Enums\TaskStatus;
use yii\data\ActiveDataProvider;

/**
 * Provides task lists and filters.
 */
class TaskListService
{
    /**
     * Returns data for the task list page.
     *
     * @param TaskFilter $filter Task list filter.
     *
     * @return array<string, mixed> Task list data.
     */
    public function getTasks(TaskFilter $filter): array
    {
        $query = Task::find()
            ->joinWith('status')
            ->where([
                'status.name' => TaskStatus::New->label(),
            ]);

        if (!empty($filter->categories)) {
            $query->andWhere([
                'category_id' => $filter->categories,
            ]);
        }

        if ($filter->without_performer) {
            $query->andWhere([
                'executor_id' => null,
            ]);
        }

        if ($filter->period) {
            $query->andWhere([
                '>=',
                'created_at',
                date(
                    'Y-m-d H:i:s',
                    strtotime("-{$filter->period} hours")
                ),
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy([
                'created_at' => SORT_DESC,
            ]),
            'pagination' => [
                'pageSize' => 5,
            ],
        ]);

        return [
            'tasks' => $dataProvider->getModels(),
            'filter' => $filter,
            'categories' => Category::find()->all(),
            'dataProvider' => $dataProvider,
        ];
    }
}
