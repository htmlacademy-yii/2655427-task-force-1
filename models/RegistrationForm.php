<?php

namespace app\models;

use yii\base\Model;

class RegistrationForm extends Model
{
    public $name;
    public $email;
    public $city_id;
    public $password;
    public $password_repeat;
    public $is_executor;

    public function rules()
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

    public function attributeLabels()
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
