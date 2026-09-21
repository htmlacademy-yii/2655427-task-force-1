<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "user_category".
 *
 * @property int $user_id
 * @property int $category_id
 *
 * @property Category $category
 * @property User $user
 */
class UserCategory extends \yii\db\ActiveRecord
{
    /**
     * Returns the database table name.
     *
     * @return string Database table name.
     */
    public static function tableName(): string
    {
        return 'user_category';
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
                ['user_id', 'category_id'],
                'required',
            ],
            [
                ['user_id', 'category_id'],
                'integer',
            ],
            [
                ['user_id', 'category_id'],
                'unique',
                'targetAttribute' => [
                    'user_id',
                    'category_id',
                ],
            ],
            [
                ['category_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Category::class,
                'targetAttribute' => ['category_id' => 'id'],
            ],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id'],
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
            'user_id' => 'User ID',
            'category_id' => 'Category ID',
        ];
    }

    /**
     * Returns the category relation.
     *
     * @return ActiveQuery Category relation.
     */
    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(
            Category::class,
            ['id' => 'category_id']
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
}
