<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "file".
 *
 * @property int $id
 * @property int $task_id
 * @property string $file_path
 * @property string $created_at
 *
 * @property Task $task
 */
class File extends \yii\db\ActiveRecord
{
    /**
     * Returns the database table name.
     *
     * @return string Database table name.
     */
    public static function tableName(): string
    {
        return 'file';
    }

    /**
     * Returns validation rules for the model.
     *
     * @return array<int, array<string, mixed>> Validation rules.
     */
    public function rules(): array
    {
        return [
            [
                ['task_id', 'file_path'],
                'required',
            ],
            [
                ['task_id'],
                'integer',
            ],
            [
                ['created_at'],
                'safe',
            ],
            [
                ['file_path'],
                'string',
                'max' => 255,
            ],
            [
                ['task_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Task::class,
                'targetAttribute' => ['task_id' => 'id'],
            ],
        ];
    }

    /**
     * Returns human-readable labels for model attributes.
     *
     * @return array<string, string> Attribute labels.
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'task_id' => 'Task ID',
            'file_path' => 'File Path',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Returns the task relation.
     *
     * @return ActiveQuery Task relation.
     */
    public function getTask(): ActiveQuery
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }
}
