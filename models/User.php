<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $user_role
 * @property int $failed_tasks_count
 * @property int $hide_contacts
 * @property int|null $vk_id
 * @property int|null $github_id
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
class User extends \yii\db\ActiveRecord implements IdentityInterface
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
            [['vk_id', 'github_id', 'password', 'avatar_path', 'phone_number', 'birthday', 'telegram'], 'default', 'value' => null],
            [['hide_contacts'], 'default', 'value' => 0],
            [['user_role', 'email', 'name', 'city_id'], 'required'],
            [['user_role'], 'string'],
            [['failed_tasks_count', 'hide_contacts', 'vk_id', 'github_id', 'city_id'], 'integer'],
            [['created_at', 'birthday'], 'safe'],
            [['email', 'name'], 'string', 'max' => 128],
            [['password', 'avatar_path'], 'string', 'max' => 255],
            [['phone_number'], 'string', 'max' => 11],
            [['telegram'], 'string', 'max' => 64],
            ['user_role', 'in', 'range' => array_keys(self::optsUserRole())],
            [['email'], 'unique'],
            [['github_id'], 'unique'],
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
            'github_id' => 'GitHub ID',
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
     * Finds an identity by the specified ID.
     *
     * @param int|string $id User ID.
     *
     * @return static|null User instance or null if the user was not found.
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * Finds a user by email address.
     *
     * @param string $email User email address.
     *
     * @return static|null User instance or null if the user was not found.
     */
    public static function findByEmail(string $email)
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * Returns the unique identifier of the user.
     *
     * @return int|string User ID.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the authentication key.
     *
     * @return string Authentication key.
     */
    public function getAuthKey()
    {
        return (string) $this->id;
    }

    /**
     * Validates the authentication key.
     *
     * @param string $authKey Authentication key.
     *
     * @return bool Whether the authentication key is valid.
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Finds an identity by the specified access token.
     *
     * @param string $token Access token.
     * @param string|null $type Token type.
     *
     * @return static|null User instance or null if the user was not found.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    /**
     * Gets query for [[Categories]].
     *
     * @return ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'category_id'])
            ->viaTable('user_category', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[City]].
     *
     * @return ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
    }

    /**
     * Gets query for [[Feedbacks]].
     *
     * @return ActiveQuery
     */
    public function getFeedbacks()
    {
        return $this->hasMany(Feedback::class, ['author_id' => 'id']);
    }

    /**
     * Gets query for [[Feedbacks0]].
     *
     * @return ActiveQuery
     */
    public function getFeedbacks0()
    {
        return $this->hasMany(Feedback::class, ['executor_id' => 'id']);
    }

    /**
     * Gets query for [[Responses]].
     *
     * @return ActiveQuery
     */
    public function getResponses()
    {
        return $this->hasMany(Response::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Tasks]].
     *
     * @return ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Task::class, ['author_id' => 'id']);
    }

    /**
     * Gets query for [[Tasks0]].
     *
     * @return ActiveQuery
     */
    public function getTasks0()
    {
        return $this->hasMany(Task::class, ['executor_id' => 'id']);
    }

    /**
     * Gets query for [[Tasks1]].
     *
     * @return ActiveQuery
     */
    public function getTasks1()
    {
        return $this->hasMany(Task::class, ['id' => 'task_id'])
            ->viaTable('response', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserCategories]].
     *
     * @return ActiveQuery
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
