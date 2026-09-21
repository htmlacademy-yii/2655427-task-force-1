<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property string $name
 *
 * @property Task[] $tasks
 * @property UserCategory[] $userCategories
 * @property User[] $users
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * Returns the database table name.
     *
     * @return string Database table name.
     */
    public static function tableName(): string
    {
        return 'category';
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
     * Returns tasks belonging to the category.
     *
     * @return ActiveQuery Tasks relation.
     */
    public function getTasks(): ActiveQuery
    {
        return $this->hasMany(Task::class, ['category_id' => 'id']);
    }

    /**
     * Returns user-category relations.
     *
     * @return ActiveQuery UserCategory relation.
     */
    public function getUserCategories(): ActiveQuery
    {
        return $this->hasMany(
            UserCategory::class,
            ['category_id' => 'id']
        );
    }

    /**
     * Returns users associated with the category.
     *
     * @return ActiveQuery Users relation.
     */
    public function getUsers(): ActiveQuery
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('user_category', ['category_id' => 'id']);
    }
}
