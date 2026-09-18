<?php

namespace app\models;

use Yii;

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
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'task';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['executor_id', 'city_id', 'latitude', 'longitude', 'budget', 'deadline'], 'default', 'value' => null],
            [['status_id', 'category_id', 'title', 'description', 'author_id'], 'required'],
            [['status_id', 'category_id', 'author_id', 'executor_id', 'city_id', 'budget'], 'integer'],
            [['created_at', 'deadline'], 'safe'],
            [['description'], 'string'],
            [['latitude', 'longitude'], 'number'],
            [['title'], 'string', 'max' => 128],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['author_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['city_id'], 'exist', 'skipOnError' => true, 'targetClass' => City::class, 'targetAttribute' => ['city_id' => 'id']],
            [['executor_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['executor_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['status_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'status_id' => 'Status ID',
            'category_id' => 'Category ID',
            'created_at' => 'Created At',
            'title' => 'Title',
            'description' => 'Description',
            'author_id' => 'Author ID',
            'executor_id' => 'Executor ID',
            'city_id' => 'City ID',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'budget' => 'Budget',
            'deadline' => 'Deadline',
        ];
    }

    /**
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[City]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCity(): \yii\db\ActiveQuery
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
    }

    /**
     * Gets query for [[Executor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExecutor(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'executor_id']);
    }

    /**
     * Gets query for [[Feedback]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFeedback(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Feedback::class, ['task_id' => 'id']);
    }

    /**
     * Gets query for [[Files]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFiles(): \yii\db\ActiveQuery
    {
        return $this->hasMany(File::class, ['task_id' => 'id']);
    }

    /**
     * Gets query for [[Responses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResponses(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Response::class, ['task_id' => 'id']);
    }

    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Status::class, ['id' => 'status_id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers(): \yii\db\ActiveQuery
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('response', ['task_id' => 'id']);
    }
}
