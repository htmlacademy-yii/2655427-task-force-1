<?php

declare(strict_types=1);

/**
 * @var yii\web\View $this
 * @var app\models\LoginForm $model
 */

use yii\authclient\widgets\AuthChoice;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Вход на сайт';
$this->params['mainContentClass'] = 'container--registration';
?>

<div class="container container--registration">
    <div class="center-block">
        <div class="registration-form regular-form">

            <?php $form = ActiveForm::begin([
                'method' => 'post',
            ]); ?>

            <h3 class="head-main head-task">
                Вход на сайт
            </h3>

            <div class="half-wrapper">
                <?= $form->field($model, 'email')
                    ->input('email')
                    ->label('Email') ?>
            </div>

            <div class="half-wrapper">
                <?= $form->field($model, 'password')
                    ->passwordInput()
                    ->label('Пароль') ?>
            </div>

            <?= Html::submitButton(
                'Войти',
                ['class' => 'button button--blue']
            ) ?>

            <?php ActiveForm::end(); ?>

            <div >
                <p class="form-label">Или войти через:</p>
               <div>

                <?= AuthChoice::widget([
                    'baseAuthUrl' => ['site/auth'],
                ]) ?>

               </div>
            </div>

        </div>
    </div>
</div>
