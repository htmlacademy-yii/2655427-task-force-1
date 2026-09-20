<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "task".
 *
 * @property int $id
 * @property int $status_id
 * @property int $category_id
 * @property string $created_at
 * @property string $title
 * @property string $description
 * @property int $author_id
 * @property int|null $executor_id
 * @property int|null $city_id
 * @property string|null $location
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int|null $budget
 * @property string|null $deadline
 *
 * @property User $author
 * @property Category $category
 * @property City $city
 * @property User $executor
 * @property Feedback $feedback
 * @property File[] $files
 * @property Response[] $responses
 * @property Status $status
 * @property User[] $users
 */
class Task extends \yii\db\ActiveRecord
{
    /**
     * Returns the database table name.
     *
     * @return string Database table name.
     */
    public static function tableName(): string
    {
        return 'task';
    }

    /**
     * Returns validation rules for task attributes.
     *
     * @return array<int, array<string, mixed>> Validation rules.
     */
    public function rules(): array
    {
        return [
            [
                [
                    'executor_id',
                    'city_id',
                    'location',
                    'latitude',
                    'longitude',
                    'budget',
                    'deadline',
                ],
                'default',
                'value' => null,
            ],

            [
                ['title', 'description', 'category_id'],
                'required',
                'message' => 'Поле обязательно для заполнения',
            ],

            [
                ['title', 'description'],
                'filter',
                'filter' => 'trim',
            ],

            [
                ['location'],
                'filter',
                'filter' => static function ($value) {
                    return is_string($value) ? trim($value) : $value;
                },
            ],

            [
                'title',
                'validateNonWhitespaceLength',
                'params' => ['minLength' => 10],
            ],

            [
                'description',
                'validateNonWhitespaceLength',
                'params' => ['minLength' => 30],
            ],

            [
                [
                    'status_id',
                    'category_id',
                    'author_id',
                    'executor_id',
                    'city_id',
                ],
                'integer',
            ],

            [
                ['budget'],
                'integer',
                'min' => 1,
            ],

            [
                ['created_at'],
                'safe',
            ],

            [
                ['title'],
                'string',
                'max' => 128,
            ],

            [
                ['description'],
                'string',
            ],

            [
                ['location'],
                'string',
                'max' => 255,
            ],

            [
                ['deadline'],
                'date',
                'format' => 'php:Y-m-d',
                'min' => date('Y-m-d'),
                'strictDateFormat' => true,
                'tooSmall' => 'Дата должна быть больше текущей',
            ],

            [
                ['latitude', 'longitude'],
                'number',
            ],

            [
                ['author_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['author_id' => 'id'],
            ],

            [
                ['category_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Category::class,
                'targetAttribute' => ['category_id' => 'id'],
            ],

            [
                ['city_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => City::class,
                'targetAttribute' => ['city_id' => 'id'],
            ],

            [
                ['executor_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['executor_id' => 'id'],
            ],

            [
                ['status_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Status::class,
                'targetAttribute' => ['status_id' => 'id'],
            ],
        ];
    }

    /**
     * Validates the number of non-whitespace characters in an attribute.
     *
     * @param string $attribute Attribute name.
     * @param array<string, mixed> $params Validation parameters.
     *
     * @return void
     */
    public function validateNonWhitespaceLength(
        string $attribute,
        array $params
    ): void {
        $value = preg_replace('/\s+/u', '', (string) $this->$attribute);

        if (mb_strlen($value) < $params['minLength']) {
            $this->addError(
                $attribute,
                'Поле должно содержать не менее '
                . $params['minLength']
                . ' символов без пробелов.'
            );
        }
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
            'status_id' => 'Status ID',
            'category_id' => 'Категория',
            'created_at' => 'Created At',
            'title' => 'Опишите суть работы',
            'description' => 'Подробности задания',
            'author_id' => 'Author ID',
            'executor_id' => 'Executor ID',
            'city_id' => 'Город',
            'location' => 'Локация',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'budget' => 'Бюджет',
            'deadline' => 'Срок исполнения',
        ];
    }

    /**
     * Returns the task author relation.
     *
     * @return ActiveQuery Author relation.
     */
    public function getAuthor(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    /**
     * Returns the task category relation.
     *
     * @return ActiveQuery Category relation.
     */
    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Returns the task city relation.
     *
     * @return ActiveQuery City relation.
     */
    public function getCity(): ActiveQuery
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
    }

    /**
     * Returns the task executor relation.
     *
     * @return ActiveQuery Executor relation.
     */
    public function getExecutor(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'executor_id']);
    }

    /**
     * Returns the task feedback relation.
     *
     * @return ActiveQuery Feedback relation.
     */
    public function getFeedback(): ActiveQuery
    {
        return $this->hasOne(Feedback::class, ['task_id' => 'id']);
    }

    /**
     * Returns the task files relation.
     *
     * @return ActiveQuery Files relation.
     */
    public function getFiles(): ActiveQuery
    {
        return $this->hasMany(File::class, ['task_id' => 'id']);
    }

    /**
     * Returns the task responses relation.
     *
     * @return ActiveQuery Responses relation.
     */
    public function getResponses(): ActiveQuery
    {
        return $this->hasMany(Response::class, ['task_id' => 'id']);
    }

    /**
     * Returns the task status relation.
     *
     * @return ActiveQuery Status relation.
     */
    public function getStatus(): ActiveQuery
    {
        return $this->hasOne(Status::class, ['id' => 'status_id']);
    }

    /**
     * Returns users who responded to the task.
     *
     * @return ActiveQuery Users relation.
     */
    public function getUsers(): ActiveQuery
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('response', ['task_id' => 'id']);
    }
}
