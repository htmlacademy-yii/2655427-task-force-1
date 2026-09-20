<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\AppAsset;

AppAsset::register($this);

/** @var yii\web\View $this */
/** @var string $content */

$this->title = 'Taskforce';

$isRegistrationPage = Yii::$app->controller->route === 'registration/index';
$route = Yii::$app->controller->route;
$isGuest = Yii::$app->user->isGuest;
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

        <a href="<?= Url::to(['site/index']) ?>" class="header-logo">
            <img
                class="logo-image"
                src="/img/logotype.png"
                width="227"
                height="60"
                alt="taskforce"
            >
        </a>

        <?php if (!$isRegistrationPage && !$isGuest): ?>

            <div class="nav-wrapper">

                <ul class="nav-list">

                    <li class="list-item <?= $route === 'tasks/index' ? 'list-item--active' : '' ?>">
                        <a
                            href="<?= Url::to(['tasks/index']) ?>"
                            class="link link--nav"
                        >
                            Новое
                        </a>
                    </li>

                    <li class="list-item">
                        <a
                            href="#"
                            class="link link--nav"
                        >
                            Мои задания
                        </a>
                    </li>

                    <li class="list-item <?= $route === 'tasks/create' ? 'list-item--active' : '' ?>">
                        <a
                            href="<?= Url::to(['tasks/create']) ?>"
                            class="link link--nav"
                        >
                            Создать задание
                        </a>
                    </li>

                    <li class="list-item">
                        <a
                            href="#"
                            class="link link--nav"
                        >
                            Настройки
                        </a>
                    </li>

                </ul>

            </div>

        <?php endif; ?>

    </nav>

    <?php if (!$isRegistrationPage && !$isGuest): ?>

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
                            <a href="#" class="link">
                                Настройки
                            </a>
                        </li>

                        <li class="menu-item">
                            <a href="#" class="link">
                                Связаться с нами
                            </a>
                        </li>

                        <li class="menu-item">

                            <form
                                action="<?= Url::to(['site/logout']) ?>"
                                method="post"
                            >
                                <?= Html::hiddenInput(
                                    Yii::$app->request->csrfParam,
                                    Yii::$app->request->getCsrfToken()
                                ) ?>

                                <?= Html::submitButton(
                                    'Выход из системы',
                                    ['class' => 'link']
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
