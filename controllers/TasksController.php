<?php

namespace app\controllers;

use app\models\Category;
use app\models\Feedback;
use app\models\File;
use app\models\Response;
use app\models\Status;
use app\models\Task;
use app\models\TaskFilter;
use Yii;
use TaskForce\Logic\Enums\TaskAction;
use TaskForce\Logic\Enums\TaskStatus;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response as WebResponse;
use yii\web\UploadedFile;

class TasksController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => [
                    'create',
                    'respond',
                    'accept-response',
                    'reject-response',
                    'finish',
                    'refuse',
                    'cancel',
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
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
                        $fileName = uniqid('', true)
                            . '.'
                            . $uploadedFile->extension;

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

        $response = new Response();

        return $this->render('view', [
            'task' => $task,
            'response' => $response,
        ]);
    }

    public function actionRespond($id)
    {
        $task = $this->findTask($id);
        $userId = (int) Yii::$app->user->id;

        $this->checkTaskAction(
            $task,
            TaskAction::Respond,
            $userId
        );

        $response = new Response();
        $response->task_id = $task->id;
        $response->user_id = $userId;

        if ($response->load(Yii::$app->request->post())) {
            $response->task_id = $task->id;
            $response->user_id = $userId;
            $response->setStatusToNew();

            if ($response->save()) {
                return $this->redirect([
                    'view',
                    'id' => $task->id,
                ]);
            }
        }

        return $this->render('view', [
            'task' => $task,
            'response' => $response,
        ]);
    }

    public function actionAcceptResponse($id): WebResponse
    {
        $response = $this->findResponse($id);
        $task = $response->task;
        $userId = (int) Yii::$app->user->id;

        $this->checkTaskAction(
            $task,
            TaskAction::Start,
            $userId
        );

        if (!$response->isStatusNew()) {
            throw new ForbiddenHttpException(
                'Этот отклик уже нельзя принять.'
            );
        }

        $workStatus = $this->findStatus(TaskStatus::Work);

        $task->executor_id = $response->user_id;
        $task->status_id = $workStatus->id;

        $response->setStatusToAccepted();

        if ($response->save(false) && $task->save(false)) {
            return $this->redirect([
                'view',
                'id' => $task->id,
            ]);
        }

        throw new ForbiddenHttpException(
            'Не удалось принять отклик.'
        );
    }

    public function actionRejectResponse($id): WebResponse
    {
        $response = $this->findResponse($id);
        $task = $response->task;
        $userId = (int) Yii::$app->user->id;

        if ($task->author_id !== $userId) {
            throw new ForbiddenHttpException(
                'Только заказчик может отказаться от отклика.'
            );
        }

        if ($task->status->name !== TaskStatus::New->label()) {
            throw new ForbiddenHttpException(
                'Отклики нельзя изменять после начала работы.'
            );
        }

        if (!$response->isStatusNew()) {
            throw new ForbiddenHttpException(
                'Этот отклик уже обработан.'
            );
        }

        $response->setStatusToRejected();
        $response->save(false);

        return $this->redirect([
            'view',
            'id' => $task->id,
        ]);
    }

    public function actionFinish($id)
    {
        $task = $this->findTask($id);
        $userId = (int) Yii::$app->user->id;

        $this->checkTaskAction(
            $task,
            TaskAction::Finish,
            $userId
        );

        $feedback = new Feedback();

        $feedback->task_id = $task->id;
        $feedback->author_id = $userId;
        $feedback->executor_id = $task->executor_id;

        if ($feedback->load(Yii::$app->request->post())) {
            $feedback->task_id = $task->id;
            $feedback->author_id = $userId;
            $feedback->executor_id = $task->executor_id;

            if ($feedback->validate()) {
                $doneStatus = $this->findStatus(TaskStatus::Done);

                $task->status_id = $doneStatus->id;

                if ($feedback->save(false) && $task->save(false)) {
                    return $this->redirect([
                        'view',
                        'id' => $task->id,
                    ]);
                }
            }
        }

        return $this->render('view', [
            'task' => $task,
            'response' => new Response(),
            'feedback' => $feedback,
        ]);
    }

    public function actionRefuse($id): WebResponse
    {
        $task = $this->findTask($id);
        $userId = (int) Yii::$app->user->id;

        $this->checkTaskAction(
            $task,
            TaskAction::Refuse,
            $userId
        );

        $failedStatus = $this->findStatus(TaskStatus::Failed);

        $task->status_id = $failedStatus->id;

        if ($task->save(false)) {
            return $this->redirect([
                'view',
                'id' => $task->id,
            ]);
        }

        throw new ForbiddenHttpException(
            'Не удалось отказаться от задания.'
        );
    }

    public function actionCancel($id): WebResponse
    {
        $task = $this->findTask($id);
        $userId = (int) Yii::$app->user->id;

        $this->checkTaskAction(
            $task,
            TaskAction::Cancel,
            $userId
        );

        $cancelStatus = $this->findStatus(TaskStatus::Cancel);

        $task->status_id = $cancelStatus->id;

        if ($task->save(false)) {
            return $this->redirect([
                'view',
                'id' => $task->id,
            ]);
        }

        throw new ForbiddenHttpException(
            'Не удалось отменить задание.'
        );
    }

    private function findTask($id): Task
    {
        $task = Task::findOne($id);

        if ($task === null) {
            throw new NotFoundHttpException(
                'Задание не найдено.'
            );
        }

        return $task;
    }

    private function findResponse($id): Response
    {
        $response = Response::findOne($id);

        if ($response === null) {
            throw new NotFoundHttpException(
                'Отклик не найден.'
            );
        }

        return $response;
    }

    private function findStatus(TaskStatus $status): Status
    {
        $model = Status::findOne([
            'name' => $status->label(),
        ]);

        if ($model === null) {
            throw new NotFoundHttpException(
                'Статус задания не найден.'
            );
        }

        return $model;
    }

    private function checkTaskAction(
        Task $task,
        TaskAction $action,
        int $userId
    ): void {
        $currentStatus = $this->getTaskStatus($task);

        if (!in_array(
            $currentStatus,
            $action->availableInStatuses(),
            true
        )) {
            throw new ForbiddenHttpException(
                'Это действие недоступно для текущего статуса задания.'
            );
        }

        if (!$action->checkRights(
            (int) $task->author_id,
            $task->executor_id !== null
                ? (int) $task->executor_id
                : null,
            $userId,
            $currentStatus
        )) {
            throw new ForbiddenHttpException(
                'У вас нет прав для выполнения этого действия.'
            );
        }
    }

    private function getTaskStatus(Task $task): TaskStatus
    {
        return match ($task->status->name) {
            'Новое' => TaskStatus::New,
            'Отменено' => TaskStatus::Cancel,
            'В работе' => TaskStatus::Work,
            'Выполнено' => TaskStatus::Done,
            'Провалено' => TaskStatus::Failed,
            default => throw new ForbiddenHttpException(
                'Неизвестный статус задания.'
            ),
        };
    }
}