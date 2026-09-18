<?php

namespace app\models;

use Yii;

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
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'file';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['task_id', 'file_path'], 'required'],
            [['task_id'], 'integer'],
            [['created_at'], 'safe'],
            [['file_path'], 'string', 'max' => 255],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::class, 'targetAttribute' => ['task_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
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
     * Gets query for [[Task]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTask(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }
}
