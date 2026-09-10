<?php

namespace app\controllers;

use app\models\Task;
use app\models\TaskFilter;
use app\models\Category;
use yii\web\Controller;

class TasksController extends Controller
{
    public function actionIndex()
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
                date('Y-m-d H:i:s', strtotime("-{$filter->period} hours"))
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
}
