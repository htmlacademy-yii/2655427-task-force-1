<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $user_role
 * @property int $failed_tasks_count
 * @property int $hide_contacts
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
 * @property string|null $about
 *
 * @property Category[] $categories
 * @property City $city
 * @property Feedback[] $feedbacks
 * @property Response[] $responses
 * @property Task[] $tasks
 * @property UserCategory[] $userCategories
 */
class User extends ActiveRecord implements IdentityInterface
{
    public const USER_ROLE_CUSTOMER = 'customer';

    public const USER_ROLE_EXECUTOR = 'executor';

    /**
     * Returns the database table name.
     *
     * @return string Database table name.
     */
    public static function tableName(): string
    {
        return 'user';
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
                ['about'],
                'string',
            ],

            [
                [
                    'github_id',
                    'password',
                    'avatar_path',
                    'phone_number',
                    'birthday',
                    'telegram',
                ],
                'default',
                'value' => null,
            ],

            [
                ['hide_contacts'],
                'default',
                'value' => 0,
            ],

            [
                ['user_role', 'email', 'name', 'city_id'],
                'required',
            ],

            [
                ['user_role'],
                'string',
            ],

            [
                [
                    'failed_tasks_count',
                    'hide_contacts',
                    'github_id',
                    'city_id',
                ],
                'integer',
            ],

            [
                ['created_at'],
                'safe',
            ],

            [
                ['email', 'name'],
                'string',
                'max' => 128,
            ],

            [
                ['password', 'avatar_path'],
                'string',
                'max' => 255,
            ],

            [
                ['phone_number'],
                'match',
                'pattern' => '/^\d{11}$/',
                'message' => 'Номер телефона должен содержать 11 цифр.',
            ],

            [
                ['telegram'],
                'string',
                'max' => 64,
            ],

            [
                ['birthday'],
                'date',
                'format' => 'php:d.m.Y',
                'strictDateFormat' => true,
            ],

            [
                ['user_role'],
                'in',
                'range' => array_keys(self::optsUserRole()),
            ],

            [
                ['email'],
                'email',
            ],

            [
                ['email'],
                'unique',
            ],

            [
                ['github_id'],
                'unique',
                'skipOnEmpty' => true,
            ],

            [
                ['city_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => City::class,
                'targetAttribute' => ['city_id' => 'id'],
            ],
        ];
    }

    /**
     * Returns attribute labels.
     *
     * @return array<string, string> Attribute labels.
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_role' => 'User Role',
            'failed_tasks_count' => 'Failed Tasks Count',
            'hide_contacts' => 'Hide Contacts',
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
            'about' => 'About',
        ];
    }

    /**
     * Finds a user by ID.
     *
     * @param int|string $id User ID.
     *
     * @return static|null User or null when not found.
     */
    public static function findIdentity($id): ?self
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * Finds a user by email.
     *
     * @param string $email User email.
     *
     * @return static|null User or null when not found.
     */
    public static function findByEmail(string $email): ?self
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * Returns the user ID.
     *
     * @return int User ID.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns the authentication key.
     *
     * @return string Authentication key.
     */
    public function getAuthKey(): string
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
    public function validateAuthKey($authKey): bool
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Finds a user by access token.
     *
     * @param string $token Access token.
     * @param string|null $type Authentication type.
     *
     * @return static|null User or null when not found.
     */
    public static function findIdentityByAccessToken(
        $token,
        $type = null
    ): ?self {
        return null;
    }

    /**
     * Returns user's categories.
     *
     * @return ActiveQuery Category relation.
     */
    public function getCategories(): ActiveQuery
    {
        return $this->hasMany(Category::class, ['id' => 'category_id'])
            ->viaTable('user_category', ['user_id' => 'id']);
    }

    /**
     * Returns user's city.
     *
     * @return ActiveQuery City relation.
     */
    public function getCity(): ActiveQuery
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
    }

    /**
     * Returns feedbacks authored by the user.
     *
     * @return ActiveQuery Feedback relation.
     */
    public function getFeedbacks(): ActiveQuery
    {
        return $this->hasMany(Feedback::class, ['author_id' => 'id']);
    }

    /**
     * Returns responses created by the user.
     *
     * @return ActiveQuery Response relation.
     */
    public function getResponses(): ActiveQuery
    {
        return $this->hasMany(Response::class, ['user_id' => 'id']);
    }

    /**
     * Returns tasks authored by the user.
     *
     * @return ActiveQuery Task relation.
     */
    public function getTasks(): ActiveQuery
    {
        return $this->hasMany(Task::class, ['author_id' => 'id']);
    }

    /**
     * Returns user's category relations.
     *
     * @return ActiveQuery UserCategory relation.
     */
    public function getUserCategories(): ActiveQuery
    {
        return $this->hasMany(UserCategory::class, ['user_id' => 'id']);
    }

    /**
     * Returns available user roles.
     *
     * @return array<string, string> User roles.
     */
    public static function optsUserRole(): array
    {
        return [
            self::USER_ROLE_CUSTOMER => 'customer',
            self::USER_ROLE_EXECUTOR => 'executor',
        ];
    }

    /**
     * Returns the display name of the user's role.
     *
     * @return string Display name of the role.
     */
    public function displayUserRole(): string
    {
        $roles = self::optsUserRole();

        return $roles[$this->user_role] ?? '';
    }

    /**
     * Checks whether the user is a customer.
     *
     * @return bool True when the user is a customer.
     */
    public function isUserRoleCustomer(): bool
    {
        return $this->user_role === self::USER_ROLE_CUSTOMER;
    }

    /**
     * Sets the user's role.
     *
     * @param string $role User role.
     *
     * @return void
     */
    public function setRole(string $role): void
    {
        $this->user_role = $role;
    }

    /**
     * Checks whether the user is an executor.
     *
     * @return bool True when the user is an executor.
     */
    public function isUserRoleExecutor(): bool
    {
        return $this->user_role === self::USER_ROLE_EXECUTOR;
    }
}
