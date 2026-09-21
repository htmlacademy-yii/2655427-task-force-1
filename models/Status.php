<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "status".
 *
 * @property int $id
 * @property string $name
 *
 * @property Task[] $tasks
 */
class Status extends \yii\db\ActiveRecord
{
    /**
     * Returns the database table name.
     *
     * @return string Database table name.
     */
    public static function tableName(): string
    {
        return 'status';
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
                ['name'],
                'required',
            ],
            [
                ['name'],
                'string',
                'max' => 64,
            ],
            [
                ['name'],
                'unique',
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
            'name' => 'Name',
        ];
    }

    /**
     * Returns tasks belonging to the status.
     *
     * @return ActiveQuery Tasks relation.
     */
    public function getTasks(): ActiveQuery
    {
        return $this->hasMany(Task::class, ['status_id' => 'id']);
    }
}
