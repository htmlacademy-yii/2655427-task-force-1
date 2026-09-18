<?php

namespace app\models;

use Yii;

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
    /**
     * ENUM field values.
     */
    const STATUS_NEW = 'new';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'response';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['price', 'comment'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'new'],
            [['created_at'], 'safe'],
            [['user_id', 'task_id'], 'required'],
            [['user_id', 'task_id', 'price'], 'integer'],
            [['comment', 'status'], 'string'],
            ['status', 'in', 'range' => array_keys(self::optsStatus())],
            [['user_id', 'task_id'], 'unique', 'targetAttribute' => ['user_id', 'task_id']],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::class, 'targetAttribute' => ['task_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'user_id' => 'User ID',
            'task_id' => 'Task ID',
            'price' => 'Price',
            'comment' => 'Comment',
            'status' => 'Status',
        ];
    }

    /**
     * Gets query for [[Task]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTask(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Returns available status values and their labels.
     *
     * @return string[]
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
     * Returns the current status label.
     *
     * @return string
     */
    public function displayStatus(): string
    {
        return self::optsStatus()[$this->status];
    }

    /**
     * Checks whether the response has a new status.
     *
     * @return bool
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
     * @return bool
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
     * @return bool
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
