<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Category;
use app\models\City;
use app\models\Feedback;
use app\models\Response;
use app\models\Task;
use app\models\TaskFilter;
use app\services\Geocoder;
use app\services\MyTasksService;
use app\services\TaskActionService;
use app\services\TaskService;
use app\services\TaskListService;
use Yii;
use TaskForce\Logic\Enums\TaskAction;
use yii\filters\AccessControl;
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
                    'my-tasks',
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
        $filter->load(Yii::$app->request->get());

        $service = new TaskListService();

        return $this->render(
            'index',
            $service->getTasks($filter)
        );
    }

    /**
     * Displays the current user's tasks.
     *
     * @return string Rendered my tasks page.
     */
    public function actionMyTasks(): string
    {
        $service = new MyTasksService();

        $data = $service->getTasks(
            (int) Yii::$app->user->id
        );

        return $this->render('my-tasks', $data);
    }

    /**
     * Creates a new task.
     *
     * @return string|WebResponse Rendered form or redirect response.
     */
    public function actionCreate(): string|WebResponse
    {
        $task = new Task();

        if ($task->load(Yii::$app->request->post())) {
            $files = UploadedFile::getInstancesByName('files');

            $service = new TaskService(
                new Geocoder()
            );

            if ($service->create($task, $files)) {
                return $this->redirect([
                    'view',
                    'id' => $task->id,
                ]);
            }
        }

        return $this->render('create', [
            'task' => $task,
            'categories' => Category::find()->all(),
            'cities' => City::find()->all(),
        ]);
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

        $service = new TaskActionService();

        $service->checkAction(
            $task,
            TaskAction::Respond,
            $userId
        );

        $response = new Response();
        $response->task_id = $task->id;
        $response->user_id = $userId;

        if ($response->load(Yii::$app->request->post())) {
            if ($service->createResponse(
                $response,
                $task,
                $userId
            )) {
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

        $service = new TaskActionService();

        $service->checkAction(
            $task,
            TaskAction::Start,
            $userId
        );

        $service->acceptResponse($response);

        return $this->redirect([
            'view',
            'id' => $task->id,
        ]);
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

        $service = new TaskActionService();

        $service->checkResponseRejection(
            $response,
            $userId
        );

        $service->rejectResponse($response);

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

        $service = new TaskActionService();

        $service->checkAction(
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
                $service->finish($task, $feedback);

                return $this->redirect([
                    'view',
                    'id' => $task->id,
                ]);
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

        $service = new TaskActionService();

        $service->checkAction(
            $task,
            TaskAction::Refuse,
            $userId
        );

        $service->refuse($task);

        return $this->redirect([
            'view',
            'id' => $task->id,
        ]);
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

        $service = new TaskActionService();

        $service->checkAction(
            $task,
            TaskAction::Cancel,
            $userId
        );

        $service->cancel($task);

        return $this->redirect([
            'view',
            'id' => $task->id,
        ]);
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
}
