<?php

namespace app\models;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $user_role
 * @property int $failed_tasks_count
 * @property int $hide_contacts
 * @property int|null $vk_id
 * @property string $created_at
 * @property string $email
 * @property string $name
 * @property string|null $password
 * @property int $city_id
 * @property string|null $avatar_path
 * @property string|null $phone_number
 * @property string|null $birthday
 * @property string|null $telegram
 *
 * @property Category[] $categories
 * @property City $city
 * @property Feedback[] $feedbacks
 * @property Feedback[] $feedbacks0
 * @property Response[] $responses
 * @property Task[] $tasks
 * @property Task[] $tasks0
 * @property Task[] $tasks1
 * @property UserCategory[] $userCategories
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * Customer role.
     */
    const USER_ROLE_CUSTOMER = 'customer';

    /**
     * Executor role.
     */
    const USER_ROLE_EXECUTOR = 'executor';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vk_id', 'password', 'avatar_path', 'phone_number', 'birthday', 'telegram'], 'default', 'value' => null],
            [['hide_contacts'], 'default', 'value' => 0],
            [['user_role', 'email', 'name', 'city_id'], 'required'],
            [['user_role'], 'string'],
            [['failed_tasks_count', 'hide_contacts', 'vk_id', 'city_id'], 'integer'],
            [['created_at', 'birthday'], 'safe'],
            [['email', 'name'], 'string', 'max' => 128],
            [['password', 'avatar_path'], 'string', 'max' => 255],
            [['phone_number'], 'string', 'max' => 11],
            [['telegram'], 'string', 'max' => 64],
            ['user_role', 'in', 'range' => array_keys(self::optsUserRole())],
            [['email'], 'unique'],
            [['vk_id'], 'unique'],
            [['city_id'], 'exist', 'skipOnError' => true, 'targetClass' => City::class, 'targetAttribute' => ['city_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_role' => 'User Role',
            'failed_tasks_count' => 'Failed Tasks Count',
            'hide_contacts' => 'Hide Contacts',
            'vk_id' => 'Vk ID',
            'created_at' => 'Created At',
            'email' => 'Email',
            'name' => 'Name',
            'password' => 'Password',
            'city_id' => 'City ID',
            'avatar_path' => 'Avatar Path',
            'phone_number' => 'Phone Number',
            'birthday' => 'Birthday',
            'telegram' => 'Telegram',
        ];
    }

    /**
     * Gets query for [[Categories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'category_id'])
            ->viaTable('user_category', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[City]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
    }

    /**
     * Gets query for [[Feedbacks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFeedbacks()
    {
        return $this->hasMany(Feedback::class, ['author_id' => 'id']);
    }

    /**
     * Gets query for [[Feedbacks0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFeedbacks0()
    {
        return $this->hasMany(Feedback::class, ['executor_id' => 'id']);
    }

    /**
     * Gets query for [[Responses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResponses()
    {
        return $this->hasMany(Response::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Tasks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Task::class, ['author_id' => 'id']);
    }

    /**
     * Gets query for [[Tasks0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTasks0()
    {
        return $this->hasMany(Task::class, ['executor_id' => 'id']);
    }

    /**
     * Gets query for [[Tasks1]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTasks1()
    {
        return $this->hasMany(Task::class, ['id' => 'task_id'])
            ->viaTable('response', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserCategories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserCategories()
    {
        return $this->hasMany(UserCategory::class, ['user_id' => 'id']);
    }

    /**
     * Returns available user roles and their labels.
     *
     * @return string[]
     */
    public static function optsUserRole()
    {
        return [
            self::USER_ROLE_CUSTOMER => 'customer',
            self::USER_ROLE_EXECUTOR => 'executor',
        ];
    }

    /**
     * Returns the current user role label.
     *
     * @return string
     */
    public function displayUserRole()
    {
        return self::optsUserRole()[$this->user_role];
    }

    /**
     * Checks whether the user has the customer role.
     *
     * @return bool
     */
    public function isUserRoleCustomer()
    {
        return $this->user_role === self::USER_ROLE_CUSTOMER;
    }

    /**
     * Sets the user role.
     *
     * @param string $role User role.
     */
    public function setRole(string $role)
    {
        $this->user_role = $role;
    }

    /**
     * Checks whether the user has the executor role.
     *
     * @return bool
     */
    public function isUserRoleExecutor()
    {
        return $this->user_role === self::USER_ROLE_EXECUTOR;
    }
}
