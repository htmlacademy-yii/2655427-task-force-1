<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var app\models\RegistrationForm $model */
/** @var app\models\City[] $cities */
?>

<div class="container container--registration">
    <div class="center-block">
        <div class="registration-form regular-form">

            <?php $form = ActiveForm::begin([
                'method' => 'post',
            ]); ?>

            <h3 class="head-main head-task">
                Регистрация нового пользователя
            </h3>

            <?= $form->field($model, 'name')
                ->textInput()
                ->label('Ваше имя') ?>

            <div class="half-wrapper">

                <?= $form->field($model, 'email')
                    ->input('email')
                    ->label('Email') ?>

                <?= $form->field($model, 'city_id')
                    ->dropDownList(
                        \yii\helpers\ArrayHelper::map($cities, 'id', 'name'),
                        ['prompt' => 'Выберите город']
                    )
                    ->label('Город') ?>

            </div>

            <div class="half-wrapper">

                <?= $form->field($model, 'password')
                    ->passwordInput()
                    ->label('Пароль') ?>

            </div>

            <div class="half-wrapper">

                <?= $form->field($model, 'password_repeat')
                    ->passwordInput()
                    ->label('Повтор пароля') ?>

            </div>

            <?= $form->field($model, 'is_executor')
                ->checkbox([
                    'label' => 'я собираюсь откликаться на заказы',
                ]) ?>

            <?= Html::submitButton(
                'Создать аккаунт',
                ['class' => 'button button--blue']
            ) ?>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
