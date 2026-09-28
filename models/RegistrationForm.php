<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;

/**
 * Registration form model.
 */
class RegistrationForm extends Model
{
    /**
     * User's name.
     */
    public ?string $name = null;

    /**
     * User's email address.
     */
    public ?string $email = null;

    /**
     * User's city ID.
     */
    public int|string|null $city_id = null;

    /**
     * User's password.
     */
    public ?string $password = null;

    /**
     * Password confirmation.
     */
    public ?string $password_repeat = null;

    /**
     * Whether the user wants to respond to tasks.
     */
    public ?bool $is_executor = null;

    /**
     * Returns validation rules.
     *
     * @return array<int, array<string, mixed>> Validation rules.
     */
    public function rules(): array
    {
        return [
            [
                [
                    'name',
                    'email',
                    'city_id',
                    'password',
                    'password_repeat',
                ],
                'required',
                'message' => 'Заполните это поле.',
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
                'unique',
                'targetClass' => User::class,
                'targetAttribute' => 'email',
            ],

            [
                ['city_id'],
                'integer',
            ],

            [
                ['city_id'],
                'exist',
                'targetClass' => City::class,
                'targetAttribute' => ['city_id' => 'id'],
            ],

            [
                ['password'],
                'string',
                'min' => 6,
                'max' => 255,
            ],

            [
                ['password_repeat'],
                'compare',
                'compareAttribute' => 'password',
            ],

            [
                ['is_executor'],
                'boolean',
            ],
        ];
    }

    /**
     * Returns customized attribute labels.
     *
     * @return array<string, string> Attribute labels.
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
