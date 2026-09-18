<?php

namespace app\models;

use yii\base\Model;

/**
 * RegistrationForm is the model behind the user registration form.
 */
class RegistrationForm extends Model
{
    /**
     * User's name.
     *
     * @var string
     */
    public $name;

    /**
     * User's email address.
     *
     * @var string
     */
    public $email;

    /**
     * User's city ID.
     *
     * @var int
     */
    public $city_id;

    /**
     * User's password.
     *
     * @var string
     */
    public $password;

    /**
     * Password confirmation.
     *
     * @var string
     */
    public $password_repeat;

    /**
     * Whether the user wants to respond to tasks.
     *
     * @var bool
     */
    public $is_executor;

    /**
     * Returns the validation rules.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['name', 'email', 'city_id', 'password', 'password_repeat'], 'required', 'message' => 'Заполните это поле.'],
            ['name', 'string', 'max' => 128],

            ['email', 'email'],
            ['email', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'email'],

            ['city_id', 'integer'],
            ['city_id', 'exist', 'targetClass' => City::class, 'targetAttribute' => 'id'],

            ['password', 'string', 'min' => 6, 'max' => 255],

            ['password_repeat', 'compare', 'compareAttribute' => 'password'],

            ['is_executor', 'boolean'],
        ];
    }

    /**
     * Returns customized attribute labels.
     *
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'name' => 'Ваше имя',
            'email' => 'Email',
            'city_id' => 'Город',
            'password' => 'Пароль',
            'password_repeat' => 'Повтор пароля',
            'is_executor' => 'Я собираюсь откликаться на заказы',
        ];
    }
}
