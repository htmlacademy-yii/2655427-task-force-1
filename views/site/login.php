<?php

declare(strict_types=1);

/**
 * @var yii\web\View $this
 * @var app\models\LoginForm $model
 */

use yii\authclient\widgets\AuthChoice;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Вход на сайт';

$htmlIcon = <<<HTML
{label}<div class="input-group"><span class="input-group-text" aria-hidden="true">%s</span>{input}</div>{error}{hint}
HTML;

$labelOptions = ['class' => 'form-label fw-semibold small'];
?>

<div class="site-login d-flex align-items-center justify-content-center py-5">
    <div class="card border-0 overflow-hidden login-split-card">
        <div class="p-4 p-lg-5">
            <div class="text-center mb-4">
                <h1 class="h3 fw-bold mb-1"><?= Html::encode($this->title) ?></h1>
                <p class="text-body-secondary small">Введите данные для входа</p>
            </div>

            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

            <div class="mb-3">
                <?= $form->field($model, 'email', [
                    'options' => ['class' => 'mb-0'],
                    'template' => sprintf($htmlIcon, '&#128100;'),
                    'inputOptions' => [
                        'class' => 'form-control',
                        'placeholder' => 'Email',
                        'autofocus' => true,
                    ],
                ])->textInput()->label('Email', $labelOptions) ?>
            </div>

            <div class="mb-3">
                <?= $form->field($model, 'password', [
                    'options' => ['class' => 'mb-0'],
                    'template' => sprintf($htmlIcon, '&#128274;'),
                    'inputOptions' => [
                        'class' => 'form-control',
                        'placeholder' => 'Password',
                    ],
                ])->passwordInput()->label('Пароль', $labelOptions) ?>
            </div>

            <div class="d-grid">
                <?= Html::submitButton(
                    'Войти',
                    [
                        'class' => 'btn login-btn btn-lg rounded-3 text-white',
                        'name' => 'login-button',
                    ],
                ) ?>
            </div>

            <?php ActiveForm::end(); ?>

            <div class="text-center my-3">
                <span class="text-body-secondary small">или</span>
            </div>

            <div class="text-center">
                <?= AuthChoice::widget([
                    'baseAuthUrl' => ['site/auth'],
                ]) ?>
            </div>
        </div>
    </div>
</div>
