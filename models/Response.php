<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "response".
 *
 * @property int $id
 * @property string $created_at
 * @property int $user_id
 * @property int $task_id
 * @property int|null $price
 * @property string|null $comment
 * @property string $status
 *
 * @property Task $task
 * @property User $user
 */
class Response extends \yii\db\ActiveRecord
{
    public const STATUS_NEW = 'new';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Returns the database table name.
     *
     * @return string Database table name.
     */
    public static function tableName(): string
    {
        return 'response';
    }

    /**
     * Returns validation rules for response attributes.
     *
     * @return array<int, array<string, mixed>> Validation rules.
     */
    public function rules(): array
    {
        return [
            [
                ['comment', 'price'],
                'default',
                'value' => null,
            ],

            [
                ['status'],
                'default',
                'value' => self::STATUS_NEW,
            ],

            [
                ['created_at'],
                'safe',
            ],

            [
                ['user_id', 'task_id'],
                'required',
            ],

            [
                ['user_id', 'task_id'],
                'integer',
            ],

            [
                ['price'],
                'integer',
                'min' => 1,
                'tooSmall' => 'Стоимость должна быть больше 0.',
            ],

            [
                ['comment', 'status'],
                'string',
            ],

            [
                ['status'],
                'in',
                'range' => array_keys(self::optsStatus()),
            ],

            [
                ['user_id', 'task_id'],
                'unique',
                'targetAttribute' => [
                    'user_id',
                    'task_id',
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
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => [
                    'user_id' => 'id',
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
            'created_at' => 'Дата создания',
            'user_id' => 'Пользователь',
            'task_id' => 'Задание',
            'price' => 'Стоимость',
            'comment' => 'Комментарий',
            'status' => 'Статус',
        ];
    }

    /**
     * Returns the task relation.
     *
     * @return ActiveQuery Task relation.
     */
    public function getTask(): ActiveQuery
    {
        return $this->hasOne(
            Task::class,
            ['id' => 'task_id']
        );
    }

    /**
     * Returns the user relation.
     *
     * @return ActiveQuery User relation.
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(
            User::class,
            ['id' => 'user_id']
        );
    }

    /**
     * Returns available response statuses.
     *
     * @return array<string, string> Response statuses.
     */
    public static function optsStatus(): array
    {
        return [
            self::STATUS_NEW => 'new',
            self::STATUS_ACCEPTED => 'accepted',
            self::STATUS_REJECTED => 'rejected',
        ];
    }

    /**
     * Returns the display name of the response status.
     *
     * @return string Response status.
     */
    public function displayStatus(): string
    {
        return self::optsStatus()[$this->status] ?? $this->status;
    }

    /**
     * Checks whether the response has a new status.
     *
     * @return bool True when the response is new.
     */
    public function isStatusNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    /**
     * Sets the response status to new.
     *
     * @return void
     */
    public function setStatusToNew(): void
    {
        $this->status = self::STATUS_NEW;
    }

    /**
     * Checks whether the response has an accepted status.
     *
     * @return bool True when the response is accepted.
     */
    public function isStatusAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Sets the response status to accepted.
     *
     * @return void
     */
    public function setStatusToAccepted(): void
    {
        $this->status = self::STATUS_ACCEPTED;
    }

    /**
     * Checks whether the response has a rejected status.
     *
     * @return bool True when the response is rejected.
     */
    public function isStatusRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Sets the response status to rejected.
     *
     * @return void
     */
    public function setStatusToRejected(): void
    {
        $this->status = self::STATUS_REJECTED;
    }
}
