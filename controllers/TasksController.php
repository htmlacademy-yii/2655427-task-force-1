<?php

namespace app\controllers;

use app\models\Category;
use app\models\Task;
use app\models\TaskFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Handles requests for tasks.
 */
class TasksController extends Controller
{
    /**
     * Displays the task list.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $filter = new TaskFilter();
        $categories = Category::find()->all();

        $filter->load(\Yii::$app->request->get());

        $query = Task::find()
            ->joinWith('status')
            ->where(['status.name' => 'Новое']);

        if (!empty($filter->categories)) {
            $query->andWhere(['category_id' => $filter->categories]);
        }

        if ($filter->without_performer) {
            $query->andWhere(['executor_id' => null]);
        }

        if ($filter->period) {
            $query->andWhere([
                '>=',
                'created_at',
                date(
                    'Y-m-d H:i:s',
                    strtotime("-{$filter->period} hours")
                )
            ]);
        }

        $tasks = $query
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('index', [
            'tasks' => $tasks,
            'filter' => $filter,
            'categories' => $categories,
        ]);
    }

    /**
     * Displays a task by its ID.
     *
     * @param int $id Task ID.
     *
     * @return string
     *
     * @throws NotFoundHttpException If the task is not found.
     */
    public function actionView($id): string
    {
        $task = Task::findOne($id);

        if ($task === null) {
            throw new NotFoundHttpException('Задание не найдено.');
        }

        return $this->render('view', [
            'task' => $task,
        ]);
    }
}
