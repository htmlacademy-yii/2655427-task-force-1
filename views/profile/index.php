<?php

declare(strict_types=1);

/**
 * @var yii\web\View $this
 * @var app\models\AccountSettingsForm $model
 * @var app\models\Category[] $categories
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Настройки';
?>

<div class="main-content main-content--left container">
    <div class="left-menu left-menu--edit">
        <h3 class="head-main head-task">Настройки</h3>

        <ul class="side-menu-list">
            <li class="side-menu-item side-menu-item--active">
                <a
                    href="#profile"
                    class="link link--nav"
                    id="profile-tab"
                >
                    Мой профиль
                </a>
            </li>

            <li class="side-menu-item">
                <a
                    href="#security"
                    class="link link--nav"
                    id="security-tab"
                >
                    Безопасность
                </a>
            </li>
        </ul>
    </div>

    <div class="my-profile-form">
        <?php $form = ActiveForm::begin([
            'id' => 'account-settings-form',
            'options' => [
                'enctype' => 'multipart/form-data',
            ],
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => [
                    'class' => 'control-label',
                ],
                'errorOptions' => [
                    'class' => 'help-block',
                ],
            ],
        ]) ?>

        <div id="profile">
            <h3 class="head-main head-regular">Мой профиль</h3>

            <div class="photo-editing">
                <div>
                    <p class="form-label">Аватар</p>

                    <img
                        class="avatar-preview"
                        id="avatar-preview"
                        src="<?= Html::encode(
                            $model->getUser()->avatar_path !== null
                                ? Yii::getAlias('@web')
                                    . $model->getUser()->avatar_path
                                : Yii::getAlias('@web/img/man-glasses.png')
                        ) ?>"
                        width="83"
                        height="83"
                        alt="Аватар"
                    >
                </div>

                <?= Html::a(
                    'Сменить аватар',
                    '#',
                    [
                        'class' => 'button button--black',
                        'onclick' => 'document.getElementById("avatar-input").click(); return false;',
                    ]
                ) ?>

                <?= Html::activeFileInput($model, 'avatar', [
                    'id' => 'avatar-input',
                    'hidden' => true,
                ]) ?>
            </div>

            <?= $form->field($model, 'name', [
                'options' => [
                    'class' => 'form-group',
                ],
            ])->textInput([
                'id' => 'profile-name',
            ])->label('Ваше имя') ?>

            <div class="half-wrapper">
                <?= $form->field($model, 'email', [
                    'options' => [
                        'class' => 'form-group',
                    ],
                ])->input('email', [
                    'id' => 'profile-email',
                ])->label('Email') ?>

                <?= $form->field($model, 'birthday', [
                    'options' => [
                        'class' => 'form-group',
                    ],
                ])->textInput([
                    'id' => 'profile-date',
                    'placeholder' => 'дд.мм.гггг',
                ])->label('День рождения') ?>
            </div>

            <div class="half-wrapper">
                <?= $form->field($model, 'phone_number', [
                    'options' => [
                        'class' => 'form-group',
                    ],
                ])->input('tel', [
                    'id' => 'profile-phone',
                ])->label('Номер телефона') ?>

                <?= $form->field($model, 'telegram', [
                    'options' => [
                        'class' => 'form-group',
                    ],
                ])->textInput([
                    'id' => 'profile-tg',
                ])->label('Telegram') ?>
            </div>

            <div class="form-group">
                <p class="form-label">Выбор специализаций</p>

                <div class="checkbox-profile">
                    <?php foreach ($categories as $category): ?>
                        <label
                            class="control-label"
                            for="category-<?= (int) $category->id ?>"
                        >
                            <?= Html::checkbox(
                                'AccountSettingsForm[categories][]',
                                in_array(
                                    (int) $category->id,
                                    array_map('intval', $model->categories),
                                    true
                                ),
                                [
                                    'value' => $category->id,
                                    'id' => 'category-' . $category->id,
                                ]
                            ) ?>

                            <?= Html::encode($category->name) ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <?= Html::error(
                    $model,
                    'categories',
                    [
                        'class' => 'help-block',
                    ]
                ) ?>
            </div>

            <div class="form-group">
                <p class="form-label">Контакты</p>

                <?= $form->field($model, 'hide_contacts', [
                    'template' => "{input}{label}\n{error}",
                    'options' => [
                        'class' => '',
                    ],
                ])->checkbox([
                    'label' => 'Показывать мои контакты только заказчику',
                    'uncheck' => 0,
                ]) ?>
            </div>
        </div>

        <div id="security" style="display: none;">
            <h3 class="head-main head-regular">Безопасность</h3>

            <?= $form->field($model, 'old_password', [
                'options' => [
                    'class' => 'form-group',
                ],
            ])->passwordInput([
                'autocomplete' => 'current-password',
            ])->label('Текущий пароль') ?>

            <?= $form->field($model, 'new_password', [
                'options' => [
                    'class' => 'form-group',
                ],
            ])->passwordInput([
                'autocomplete' => 'new-password',
            ])->label('Новый пароль') ?>

            <?= $form->field($model, 'repeat_password', [
                'options' => [
                    'class' => 'form-group',
                ],
            ])->passwordInput([
                'autocomplete' => 'new-password',
            ])->label('Повторите новый пароль') ?>
        </div>

        <?= Html::submitButton(
            'Сохранить',
            [
                'class' => 'button button--blue',
            ]
        ) ?>

        <?php ActiveForm::end() ?>
    </div>
</div>

<script src="<?= Yii::getAlias('@web/js/main.js') ?>"></script>

<script>
    const profileTab = document.getElementById('profile-tab');
    const securityTab = document.getElementById('security-tab');
    const profile = document.getElementById('profile');
    const security = document.getElementById('security');

    profileTab.addEventListener('click', function (event) {
        event.preventDefault();

        profile.style.display = '';
        security.style.display = 'none';

        profileTab.parentElement.classList.add('side-menu-item--active');
        securityTab.parentElement.classList.remove('side-menu-item--active');
    });

    securityTab.addEventListener('click', function (event) {
        event.preventDefault();

        profile.style.display = 'none';
        security.style.display = '';

        profileTab.parentElement.classList.remove('side-menu-item--active');
        securityTab.parentElement.classList.add('side-menu-item--active');
    });

    const avatarInput = document.getElementById('avatar-input');
    const avatarPreview = document.getElementById('avatar-preview');

    avatarInput.addEventListener('change', function () {
        const file = this.files[0];

        if (!file) {
            return;
        }

        const reader = new FileReader();

        reader.addEventListener('load', function () {
            avatarPreview.src = reader.result;
        });

        reader.readAsDataURL(file);
    });
</script>
