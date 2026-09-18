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
     * User's username.
     *
     * @var string
     */
    public string $username = '';

    /**
     * User's password.
     *
     * @var string
     */
    public string $password = '';

    /**
     * Whether to remember the user.
     *
     * @var bool
     */
    public bool $rememberMe = true;

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
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute The attribute currently being validated.
     * @param array|null $params The additional name-value pairs given in the rule.
     *
     * @return void
     */
    public function validatePassword(string $attribute, array|null $params): void
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$this->security->validatePassword($this->password, $user->passwordHash)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool Whether the user is logged in successfully.
     */
    public function login(): bool
    {
        if ($this->validate()) {
            return Yii::$app->user->login(
                $this->getUser(),
                $this->rememberMe ? 3600 * 24 * 30 : 0
            );
        }

        return false;
    }

    /**
     * Finds user by [[username]].
     *
     * @return User|null
     */
    public function getUser(): User|null
    {
        if (!$this->_userLoaded) {
            $this->_user = User::findByUsername($this->username);
            $this->_userLoaded = true;
        }

        return $this->_user;
    }
}
