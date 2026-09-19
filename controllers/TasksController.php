<?php

namespace app\controllers;

use app\models\Category;
use app\models\File;
use app\models\Status;
use app\models\Task;
use app\models\TaskFilter;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class TasksController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['create'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => static function () {
                            return Yii::$app->user->identity->user_role
                                === User::USER_ROLE_CUSTOMER;
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $filter = new TaskFilter();
        $categories = Category::find()->all();

        $filter->load(Yii::$app->request->get());

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
                ),
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

    public function actionCreate()
    {
        $task = new Task();
        $categories = Category::find()->all();

        if ($task->load(Yii::$app->request->post())) {
            $task->author_id = Yii::$app->user->id;

            $status = Status::findOne(['name' => 'Новое']);

            if ($status !== null) {
                $task->status_id = $status->id;
            }

            if ($task->validate()) {
                $files = UploadedFile::getInstancesByName('files');

                if ($task->save()) {
                    $uploadDirectory = Yii::getAlias('@webroot/uploads/tasks');

                    if (!is_dir($uploadDirectory)) {
                        mkdir($uploadDirectory, 0777, true);
                    }

                    foreach ($files as $uploadedFile) {
                        $fileName = uniqid('', true) . '.' . $uploadedFile->extension;
                        $filePath = $uploadDirectory . '/' . $fileName;

                        if ($uploadedFile->saveAs($filePath)) {
                            $file = new File();
                            $file->task_id = $task->id;
                            $file->file_path = '/uploads/tasks/' . $fileName;
                            $file->save();
                        }
                    }

                    return $this->redirect([
                        'view',
                        'id' => $task->id,
                    ]);
                }
            }
        }

        return $this->render('create', [
            'task' => $task,
            'categories' => $categories,
        ]);
    }

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
