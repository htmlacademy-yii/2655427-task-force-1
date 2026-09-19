<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $content */

$this->title = 'Taskforce';

$isRegistrationPage = Yii::$app->controller->route === 'registration/index';
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title><?= Html::encode($this->title) ?></title>

    <?php $this->registerCsrfMetaTags() ?>
    <?php $this->head() ?>

    <link rel="stylesheet" href="<?= Url::to('@web/css/style.css') ?>">
</head>

<body>

<?php $this->beginBody() ?>

<header class="page-header">
    <nav class="main-nav">
        <a href="#" class="header-logo">
            <img
                class="logo-image"
                src="/img/logotype.png"
                width="227"
                height="60"
                alt="taskforce"
            >
        </a>

        <?php if (!$isRegistrationPage): ?>

            <div class="nav-wrapper">
                <ul class="nav-list">
                    <li class="list-item list-item--active">
                        <a class="link link--nav">Новое</a>
                    </li>

                    <li class="list-item">
                        <a href="#" class="link link--nav">Мои задания</a>
                    </li>

                    <li class="list-item">
                        <a href="#" class="link link--nav">Создать задание</a>
                    </li>

                    <li class="list-item">
                        <a href="#" class="link link--nav">Настройки</a>
                    </li>
                </ul>
            </div>

        <?php endif; ?>
    </nav>

    <?php if (!$isRegistrationPage): ?>

        <div class="user-block">
            <a href="#">
                <img
                    class="user-photo"
                    src="/img/man-glasses.png"
                    width="55"
                    height="55"
                    alt="Аватар"
                >
            </a>

            <div class="user-menu">
                <p class="user-name">
                    <?= Html::encode(Yii::$app->user->identity->name) ?>
                </p>

                <div class="popup-head">
                    <ul class="popup-menu">
                        <li class="menu-item">
                            <a href="#" class="link">Настройки</a>
                        </li>

                        <li class="menu-item">
                            <a href="#" class="link">Связаться с нами</a>
                        </li>

                        <li class="menu-item">
                            <form
                                action="<?= Url::to(['site/logout']) ?>"
                                method="post"
                            >
                                <?= Html::submitButton(
                                    'Выход из системы',
                                    [
                                        'class' => 'link',
                                    ],
                                ) ?>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    <?php endif; ?>
</header>

<main class="main-content container">

    <?= $content ?>

</main>

<?php $this->endBody() ?>

</body>
</html>

<?php $this->endPage() ?>
