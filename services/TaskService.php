<?php

declare(strict_types=1);

namespace app\services;

use app\models\File;
use app\models\Status;
use app\models\Task;
use TaskForce\Logic\Enums\TaskStatus;
use Yii;
use yii\web\UploadedFile;

/**
 * Handles task creation and related operations.
 */
class TaskService
{
    /**
     * TaskService constructor.
     *
     * @param Geocoder $geocoder Geocoding service.
     */
    public function __construct(
        private readonly Geocoder $geocoder
    ) {
    }

    /**
     * Creates a task with its files and coordinates.
     *
     * @param Task $task Task model.
     * @param UploadedFile[] $files Uploaded task files.
     *
     * @return bool Whether the task was created successfully.
     */
    public function create(Task $task, array $files = []): bool
    {
        $task->author_id = (int) Yii::$app->user->id;

        $status = Status::findOne([
            'name' => TaskStatus::New->label(),
        ]);

        if ($status === null) {
            $task->addError(
                'status_id',
                'Статус задания не найден.'
            );

            return false;
        }

        $task->status_id = $status->id;

        $this->setCoordinates($task);

        if (!$task->validate()) {
            return false;
        }

        if (!$task->save()) {
            return false;
        }

        $this->saveFiles($task, $files);

        return true;
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

        $coordinates = $this->geocoder->getCoordinates(
            $task->location
        );

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
     * Saves uploaded task files.
     *
     * @param Task $task Task model.
     * @param UploadedFile[] $files Uploaded files.
     *
     * @return void
     */
    private function saveFiles(Task $task, array $files): void
    {
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

            $filePath = $uploadDirectory
                . DIRECTORY_SEPARATOR
                . $fileName;

            if (!$uploadedFile->saveAs($filePath)) {
                continue;
            }

            $file = new File();
            $file->task_id = $task->id;
            $file->file_path = '/uploads/tasks/' . $fileName;
            $file->save();
        }
    }
}
