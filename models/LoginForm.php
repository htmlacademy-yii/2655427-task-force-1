<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\Security;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 */
class LoginForm extends Model
{
    /**
     * User's email address.
     *
     * @var string
     */
    public string $email = '';

    /**
     * User's password.
     *
     * @var string
     */
    public string $password = '';

    /**
     * Cached user model.
     *
     * @var User|null
     */
    private User|null $_user = null;

    /**
     * Whether the user has already been loaded.
     *
     * @var bool
     */
    private bool $_userLoaded = false;

    /**
     * LoginForm constructor.
     *
     * @param Security $security Security component.
     * @param array $config Model configuration.
     */
    public function __construct(
        private readonly Security $security,
        $config = []
    ) {
        parent::__construct($config);
    }

    /**
     * Returns the validation rules.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'email'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     *
     * @param string $attribute The attribute currently being validated.
     * @param array|null $params Additional parameters given in the validation rule.
     *
     * @return void
     */
    public function validatePassword(string $attribute, array|null $params): void
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->password || !$this->security->validatePassword($this->password, $user->password)) {
                $this->addError($attribute, 'Incorrect email or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided email and password.
     *
     * @return bool Whether the user is logged in successfully.
     */
    public function login(): bool
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser());
        }

        return false;
    }

    /**
     * Finds a user by email address.
     *
     * @return User|null
     */
    public function getUser(): User|null
    {
        if (!$this->_userLoaded) {
            $this->_user = User::findByEmail($this->email);
            $this->_userLoaded = true;
        }

        return $this->_user;
    }
}
