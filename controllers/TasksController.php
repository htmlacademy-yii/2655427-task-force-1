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
use yii\httpclient\Client;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response as WebResponse;
use yii\web\UploadedFile;

/**
 * Handles task-related actions.
 */
class TasksController extends Controller
{
    /**
     * Configures access control for controller actions.
     *
     * @return array<string, mixed> Access control configuration.
     */
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

    /**
     * Displays the list of available tasks.
     *
     * @return string Rendered task list page.
     */
    public function actionIndex(): string
    {
        $filter = new TaskFilter();
        $categories = Category::find()->all();

        $filter->load(Yii::$app->request->get());

        $query = Task::find()
            ->joinWith('status')
            ->where(['status.name' => TaskStatus::New->label()]);

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

    /**
     * Creates a new task.
     *
     * @return string|WebResponse Rendered form or redirect response.
     */
    public function actionCreate(): string|WebResponse
    {
        $task = new Task();
        $categories = Category::find()->all();

        if ($task->load(Yii::$app->request->post())) {
            $task->author_id = Yii::$app->user->id;

            $status = Status::findOne([
                'name' => TaskStatus::New->label(),
            ]);

            if ($status !== null) {
                $task->status_id = $status->id;
            }

            $this->setCoordinates($task);

            if ($task->validate()) {
                $files = UploadedFile::getInstancesByName('files');

                if ($task->save()) {
                    $uploadDirectory = Yii::getAlias(
                        '@webroot/uploads/tasks'
                    );

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

    /**
     * Sets task coordinates based on its location.
     *
     * @param Task $task Task model.
     *
     * @return void
     */
    private function setCoordinates(Task $task): void
    {
        if (empty($task->location)) {
            $task->city_id = null;
            $task->latitude = null;
            $task->longitude = null;

            return;
        }

        $coordinates = $this->getCoordinates($task->location);

        if ($coordinates === null) {
            $task->addError(
                'location',
                'Не удалось определить координаты указанного места.'
            );
            return;
        }

        $task->longitude = $coordinates['longitude'];
        $task->latitude = $coordinates['latitude'];
    }

    /**
     * Gets coordinates for a location using the Yandex geocoder.
     *
     * @param string $location Location to geocode.
     *
     * @return array{longitude: float, latitude: float}|null Coordinates
     * or null if the location could not be found.
     */
    private function getCoordinates(string $location): ?array
    {
        $client = new Client();

        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl('https://geocode-maps.yandex.ru/v1/')
            ->setData([
                'apikey' => 'e75e2509-62f6-4ab7-852e-1e8ec6cd9d92',
                'geocode' => $location,
                'lang' => 'ru_RU',
                'format' => 'json',
            ])
            ->send();

        if (!$response->isOk) {
            return null;
        }

        $data = $response->data;

        $pos = $data[
            'response'
        ][
            'GeoObjectCollection'
        ][
            'featureMember'
        ][0][
            'GeoObject'
        ][
            'Point'
        ][
            'pos'
        ] ?? null;

        if ($pos === null) {
            return null;
        }

        $coordinates = preg_split(
            '/\s+/',
            trim($pos)
        );

        if (count($coordinates) < 2) {
            return null;
        }

        return [
            'longitude' => (float) $coordinates[0],
            'latitude' => (float) $coordinates[1],
        ];
    }

    /**
     * Displays a task.
     *
     * @param int $id Task ID.
     *
     * @return string Rendered task page.
     *
     * @throws NotFoundHttpException If the task does not exist.
     */
    public function actionView(int $id): string
    {
        $task = Task::findOne($id);

        if ($task === null) {
            throw new NotFoundHttpException('Задание не найдено.');
        }

        $response = new Response();

        return $this->render('view', [
            'task' => $task,
            'response' => $response,
            'feedback' => new Feedback(),
        ]);
    }

    /**
     * Creates a response to a task.
     *
     * @param int $id Task ID.
     *
     * @return string|WebResponse Rendered task page or redirect response.
     *
     * @throws NotFoundHttpException If the task does not exist.
     * @throws ForbiddenHttpException If the action is not allowed.
     */
    public function actionRespond(int $id): string|WebResponse
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

    /**
     * Accepts a response to a task.
     *
     * @param int $id Response ID.
     *
     * @return WebResponse Redirect response.
     *
     * @throws NotFoundHttpException If the response or status does not exist.
     * @throws ForbiddenHttpException If the action is not allowed.
     */
    public function actionAcceptResponse(int $id): WebResponse
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

    /**
     * Rejects a response to a task.
     *
     * @param int $id Response ID.
     *
     * @return WebResponse Redirect response.
     *
     * @throws NotFoundHttpException If the response does not exist.
     * @throws ForbiddenHttpException If the action is not allowed.
     */
    public function actionRejectResponse(int $id): WebResponse
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

    /**
     * Finishes a task and creates feedback.
     *
     * @param int $id Task ID.
     *
     * @return string|WebResponse Rendered task page or redirect response.
     *
     * @throws NotFoundHttpException If the task or status does not exist.
     * @throws ForbiddenHttpException If the action is not allowed.
     */
    public function actionFinish(int $id): string|WebResponse
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

    /**
     * Marks a task as failed.
     *
     * @param int $id Task ID.
     *
     * @return WebResponse Redirect response.
     *
     * @throws NotFoundHttpException If the task or status does not exist.
     * @throws ForbiddenHttpException If the action is not allowed.
     */
    public function actionRefuse(int $id): WebResponse
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

    /**
     * Cancels a task.
     *
     * @param int $id Task ID.
     *
     * @return WebResponse Redirect response.
     *
     * @throws NotFoundHttpException If the task or status does not exist.
     * @throws ForbiddenHttpException If the action is not allowed.
     */
    public function actionCancel(int $id): WebResponse
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

    /**
     * Finds a task by its ID.
     *
     * @param int $id Task ID.
     *
     * @return Task Task model.
     *
     * @throws NotFoundHttpException If the task does not exist.
     */
    private function findTask(int $id): Task
    {
        $task = Task::findOne($id);

        if ($task === null) {
            throw new NotFoundHttpException(
                'Задание не найдено.'
            );
        }

        return $task;
    }

    /**
     * Finds a response by its ID.
     *
     * @param int $id Response ID.
     *
     * @return Response Response model.
     *
     * @throws NotFoundHttpException If the response does not exist.
     */
    private function findResponse(int $id): Response
    {
        $response = Response::findOne($id);

        if ($response === null) {
            throw new NotFoundHttpException(
                'Отклик не найден.'
            );
        }

        return $response;
    }

    /**
     * Finds a task status by its enum value.
     *
     * @param TaskStatus $status Task status enum.
     *
     * @return Status Status model.
     *
     * @throws NotFoundHttpException If the status does not exist.
     */
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

    /**
     * Checks whether a user can perform an action on a task.
     *
     * @param Task $task Task model.
     * @param TaskAction $action Task action.
     * @param int $userId Current user ID.
     *
     * @return void
     *
     * @throws ForbiddenHttpException If the action is not allowed.
     */
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

    /**
     * Converts a task status name to the corresponding enum value.
     *
     * @param Task $task Task model.
     *
     * @return TaskStatus Task status enum.
     *
     * @throws ForbiddenHttpException If the task has an unknown status.
     */
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
