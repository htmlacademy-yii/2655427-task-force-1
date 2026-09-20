<?php

namespace app\models;

class Response extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 'new';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    public static function tableName(): string
    {
        return 'response';
    }

    public function rules(): array
    {
        return [
            [
                ['comment'],
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
                ['price'],
                'required',
                'message' => 'Укажите стоимость работы.',
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

    public function getTask(): \yii\db\ActiveQuery
    {
        return $this->hasOne(
            Task::class,
            ['id' => 'task_id']
        );
    }

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(
            User::class,
            ['id' => 'user_id']
        );
    }

    public static function optsStatus(): array
    {
        return [
            self::STATUS_NEW => 'new',
            self::STATUS_ACCEPTED => 'accepted',
            self::STATUS_REJECTED => 'rejected',
        ];
    }

    public function displayStatus(): string
    {
        return self::optsStatus()[$this->status];
    }

    public function isStatusNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function setStatusToNew(): void
    {
        $this->status = self::STATUS_NEW;
    }

    public function isStatusAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function setStatusToAccepted(): void
    {
        $this->status = self::STATUS_ACCEPTED;
    }

    public function isStatusRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function setStatusToRejected(): void
    {
        $this->status = self::STATUS_REJECTED;
    }
}
