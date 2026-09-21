<?php

declare(strict_types=1);

namespace app\models;

use DateTimeImmutable;
use yii\base\Model;
use yii\base\Security;
use yii\web\UploadedFile;

/**
 * Form model for updating account settings.
 */
class AccountSettingsForm extends Model
{
    /**
     * User's name.
     *
     * @var string
     */
    public string $name = '';

    /**
     * User's email address.
     *
     * @var string
     */
    public string $email = '';

    /**
     * User's birthday.
     *
     * @var string
     */
    public string $birthday = '';

    /**
     * User's phone number.
     *
     * @var string
     */
    public string $phone_number = '';

    /**
     * User's Telegram username.
     *
     * @var string
     */
    public string $telegram = '';

    /**
     * User's current password.
     *
     * @var string
     */
    public string $old_password = '';

    /**
     * User's new password.
     *
     * @var string
     */
    public string $new_password = '';

    /**
     * Repeated new password.
     *
     * @var string
     */
    public string $repeat_password = '';

    /**
     * Whether the user wants to hide contacts.
     *
     * @var bool
     */
    public bool $hide_contacts = false;

    /**
     * Uploaded avatar file.
     *
     * @var UploadedFile|null
     */
    public UploadedFile|null $avatar = null;

    /**
     * Selected category IDs.
     *
     * @var array<int>
     */
    public array $categories = [];

    /**
     * User associated with the form.
     *
     * @var User
     */
    private User $user;

    /**
     * AccountSettingsForm constructor.
     *
     * @param User $user User being edited.
     * @param Security $security Security component.
     * @param array<string, mixed> $config Model configuration.
     */
    public function __construct(
        User $user,
        private readonly Security $security,
        array $config = []
    ) {
        $this->user = $user;

        $this->name = $user->name;
        $this->email = $user->email;

        if ($user->birthday !== null) {
            $birthday = DateTimeImmutable::createFromFormat(
                'Y-m-d',
                $user->birthday
            );

            if ($birthday !== false) {
                $this->birthday = $birthday->format('d.m.Y');
            }
        }

        $this->phone_number = $user->phone_number ?? '';
        $this->telegram = $user->telegram ?? '';
        $this->hide_contacts = (bool) $user->hide_contacts;
        $this->categories = UserCategory::find()
            ->select('category_id')
            ->where(['user_id' => $user->id])
            ->column();

        parent::__construct($config);
    }

    /**
     * Returns validation rules for account settings.
     *
     * @return array<int, array<string, mixed>> Validation rules.
     */
    public function rules(): array
    {
        return [
            [
                'avatar',
                'file',
                'extensions' => ['png', 'jpg', 'jpeg', 'webp'],
                'checkExtensionByMimeType' => true,
                'maxSize' => 5 * 1024 * 1024,
                'skipOnEmpty' => true,
            ],
            [
                ['name', 'email'],
                'required',
                'message' => 'Поле обязательно для заполнения.',
            ],
            [
                ['name', 'email', 'phone_number', 'telegram'],
                'trim',
            ],
            [
                ['name'],
                'string',
                'max' => 128,
            ],
            [
                ['email'],
                'email',
            ],
            [
                ['email'],
                'validateEmail',
            ],
            [
                ['birthday'],
                'date',
                'format' => 'php:d.m.Y',
                'strictDateFormat' => true,
            ],
            [
                ['phone_number'],
                'match',
                'pattern' => '/^\d{11}$/',
                'message' => 'Номер телефона должен содержать 11 цифр.',
                'skipOnEmpty' => true,
            ],
            [
                ['telegram'],
                'string',
                'max' => 64,
            ],
            [
                ['old_password', 'new_password', 'repeat_password'],
                'string',
            ],
            [
                ['old_password'],
                'validateOldPassword',
            ],
            [
                ['new_password'],
                'required',
                'when' => static function (
                    AccountSettingsForm $model
                ): bool {
                    return $model->old_password !== '';
                },
                'whenClient' => "function (attribute, value) {
                    return $('#accountsettingsform-old_password').val() !== '';
                }",
                'message' => 'Введите новый пароль.',
            ],
            [
                ['repeat_password'],
                'required',
                'when' => static function (
                    AccountSettingsForm $model
                ): bool {
                    return $model->old_password !== '';
                },
                'whenClient' => "function (attribute, value) {
                    return $('#accountsettingsform-old_password').val() !== '';
                }",
                'message' => 'Повторите новый пароль.',
            ],
            [
                ['repeat_password'],
                'compare',
                'compareAttribute' => 'new_password',
                'when' => static function (
                    AccountSettingsForm $model
                ): bool {
                    return $model->new_password !== '';
                },
                'message' => 'Пароли должны совпадать.',
            ],
            [
                ['hide_contacts'],
                'boolean',
            ],
            [
                ['categories'],
                'each',
                'rule' => [
                    'integer',
                ],
            ],
        ];
    }

    /**
     * Validates the current email address.
     *
     * @param string $attribute Attribute name.
     *
     * @return void
     */
    public function validateEmail(string $attribute): void
    {
        $user = User::find()
            ->where(['email' => $this->$attribute])
            ->andWhere(['<>', 'id', $this->user->id])
            ->one();

        if ($user !== null) {
            $this->addError(
                $attribute,
                'Пользователь с таким email уже существует.'
            );
        }
    }

    /**
     * Validates the current password.
     *
     * @param string $attribute Attribute name.
     *
     * @return void
     */
    public function validateOldPassword(string $attribute): void
    {
        if ($this->old_password === '') {
            return;
        }

        if (
            $this->user->password === null
            || !$this->security->validatePassword(
                $this->old_password,
                $this->user->password
            )
        ) {
            $this->addError(
                $attribute,
                'Неверный текущий пароль.'
            );
        }
    }

    /**
     * Returns the user associated with the form.
     *
     * @return User User model.
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Returns whether the password should be changed.
     *
     * @return bool True if a new password was entered.
     */
    public function shouldChangePassword(): bool
    {
        return $this->old_password !== '';
    }
}
