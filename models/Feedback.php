<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "feedback".
 *
 * @property int $id
 * @property string $created_at
 * @property int $author_id
 * @property int $executor_id
 * @property int $task_id
 * @property int $evaluation
 * @property string $comment
 *
 * @property User $author
 * @property User $executor
 * @property Task $task
 */
class Feedback extends \yii\db\ActiveRecord
{
    /**
     * Returns the database table name.
     *
     * @return string Database table name.
     */
    public static function tableName(): string
    {
        return 'feedback';
    }

    /**
     * Returns validation rules for feedback attributes.
     *
     * @return array<int, array<string, mixed>> Validation rules.
     */
    public function rules(): array
    {
        return [
            [
                ['created_at'],
                'safe',
            ],

            [
                ['author_id', 'executor_id', 'task_id', 'evaluation', 'comment'],
                'required',
            ],

            [
                ['author_id', 'executor_id', 'task_id', 'evaluation'],
                'integer',
            ],

            [
                ['evaluation'],
                'integer',
                'min' => 1,
                'max' => 5,
            ],

            [
                ['comment'],
                'string',
            ],

            [
                ['task_id'],
                'unique',
            ],

            [
                ['author_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => [
                    'author_id' => 'id',
                ],
            ],

            [
                ['task_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Task::class,
                'targetAttribute' => [
                    'task_id' => 'id',
                ],
            ],

            [
                ['executor_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => [
                    'executor_id' => 'id',
                ],
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
            'created_at' => 'Created At',
            'author_id' => 'Author ID',
            'executor_id' => 'Executor ID',
            'task_id' => 'Task ID',
            'evaluation' => 'Evaluation',
            'comment' => 'Comment',
        ];
    }

    /**
     * Returns the feedback author relation.
     *
     * @return ActiveQuery Author relation.
     */
    public function getAuthor(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    /**
     * Returns the feedback executor relation.
     *
     * @return ActiveQuery Executor relation.
     */
    public function getExecutor(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'executor_id']);
    }

    /**
     * Returns the feedback task relation.
     *
     * @return ActiveQuery Task relation.
     */
    public function getTask(): ActiveQuery
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }
}
