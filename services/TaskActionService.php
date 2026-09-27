<?php

declare(strict_types=1);

namespace app\services;

use app\models\Feedback;
use app\models\Response;
use app\models\Status;
use app\models\Task;
use TaskForce\Logic\Enums\TaskAction;
use TaskForce\Logic\Enums\TaskStatus;
use TaskForce\Logic\Exceptions\TaskException;
use TaskForce\Logic\TaskStateMachine;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Handles task actions and status changes.
 */
class TaskActionService
{
    /**
     * Checks whether a user can perform an action on a task.
     *
     * @param Task $task Task model.
     * @param TaskAction $action Requested action.
     * @param int $userId Current user ID.
     *
     * @return void
     *
     * @throws ForbiddenHttpException If the action is not allowed.
     */
    public function checkAction(
        Task $task,
        TaskAction $action,
        int $userId
    ): void {
        if ($task->status === null) {
            throw new ForbiddenHttpException(
                'Статус задания не найден.'
            );
        }

        $stateMachine = new TaskStateMachine();

        try {
            $status = $stateMachine->getStatusByName(
                $task->status->name
            );
        } catch (TaskException $exception) {
            throw new ForbiddenHttpException(
                'Неизвестный статус задания.',
                0,
                $exception
            );
        }

        if (!$stateMachine->canPerformAction(
            $status,
            $action,
            (int) $task->author_id,
            $userId,
            $task->executor_id !== null
                ? (int) $task->executor_id
                : null
        )) {
            throw new ForbiddenHttpException(
                'У вас нет прав для выполнения этого действия.'
            );
        }
    }

    /**
     * Accepts a response to a task.
     *
     * @param Response $response Response model.
     *
     * @return Task Updated task.
     *
     * @throws NotFoundHttpException If the task does not exist.
     * @throws ForbiddenHttpException If the response cannot be accepted.
     */
    public function acceptResponse(Response $response): Task
    {
        $task = $response->task;

        if ($task === null) {
            throw new NotFoundHttpException(
                'Задание не найдено.'
            );
        }

        if (!$response->isStatusNew()) {
            throw new ForbiddenHttpException(
                'Этот отклик уже нельзя принять.'
            );
        }

        $workStatus = $this->findStatus(TaskStatus::Work);

        $task->executor_id = $response->user_id;
        $task->status_id = $workStatus->id;

        $response->setStatusToAccepted();

        if (!$response->save(false) || !$task->save(false)) {
            throw new ForbiddenHttpException(
                'Не удалось принять отклик.'
            );
        }

        return $task;
    }

    /**
     * Rejects a response to a task.
     *
     * @param Response $response Response model.
     *
     * @return Task Related task.
     *
     * @throws NotFoundHttpException If the task does not exist.
     * @throws ForbiddenHttpException If the response cannot be rejected.
     */
    public function rejectResponse(Response $response): Task
    {
        $task = $response->task;

        if ($task === null) {
            throw new NotFoundHttpException(
                'Задание не найдено.'
            );
        }

        if (!$response->isStatusNew()) {
            throw new ForbiddenHttpException(
                'Этот отклик уже обработан.'
            );
        }

        $response->setStatusToRejected();

        if (!$response->save(false)) {
            throw new ForbiddenHttpException(
                'Не удалось отказаться от отклика.'
            );
        }

        return $task;
    }

    /**
     * Finishes a task and saves feedback.
     *
     * @param Task $task Task model.
     * @param Feedback $feedback Feedback model.
     *
     * @return void
     *
     * @throws ForbiddenHttpException If the task cannot be finished.
     */
    public function finish(
        Task $task,
        Feedback $feedback
    ): void {
        $doneStatus = $this->findStatus(TaskStatus::Done);

        $task->status_id = $doneStatus->id;

        if (!$feedback->save(false) || !$task->save(false)) {
            throw new ForbiddenHttpException(
                'Не удалось завершить задание.'
            );
        }
    }

    /**
     * Marks a task as failed.
     *
     * @param Task $task Task model.
     *
     * @return void
     *
     * @throws ForbiddenHttpException If the task cannot be refused.
     */
    public function refuse(Task $task): void
    {
        $failedStatus = $this->findStatus(TaskStatus::Failed);

        $task->status_id = $failedStatus->id;

        if (!$task->save(false)) {
            throw new ForbiddenHttpException(
                'Не удалось отказаться от задания.'
            );
        }
    }

    /**
     * Cancels a task.
     *
     * @param Task $task Task model.
     *
     * @return void
     *
     * @throws ForbiddenHttpException If the task cannot be cancelled.
     */
    public function cancel(Task $task): void
    {
        $cancelStatus = $this->findStatus(TaskStatus::Cancel);

        $task->status_id = $cancelStatus->id;

        if (!$task->save(false)) {
            throw new ForbiddenHttpException(
                'Не удалось отменить задание.'
            );
        }
    }

    /**
     * Checks whether a response can be rejected.
     *
     * @param Response $response Response model.
     * @param int $userId Current user ID.
     *
     * @return void
     *
     * @throws ForbiddenHttpException If the response cannot be rejected.
     * @throws NotFoundHttpException If the task does not exist.
     */
    public function checkResponseRejection(
        Response $response,
        int $userId
    ): void {
        $task = $response->task;

        if ($task === null) {
            throw new NotFoundHttpException(
                'Задание не найдено.'
            );
        }

        if (
            $task->author_id !== $userId
            || $task->status === null
            || $task->status->name !== TaskStatus::New->label()
        ) {
            throw new ForbiddenHttpException(
                'Только заказчик может отказаться от отклика.'
            );
        }
    }

    /**
     * Creates a response for a task.
     *
     * @param Response $response Response model.
     * @param Task $task Task model.
     * @param int $userId Current user ID.
     *
     * @return bool Whether the response was saved successfully.
     */
    public function createResponse(
        Response $response,
        Task $task,
        int $userId
    ): bool {
        $response->task_id = $task->id;
        $response->user_id = $userId;
        $response->setStatusToNew();

        return $response->save();
    }

    /**
     * Finds a task status by enum value.
     *
     * @param TaskStatus $status Task status.
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
}
