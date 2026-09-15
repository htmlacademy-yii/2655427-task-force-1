<?php

use yii\helpers\Html;

/** @var app\models\User $user */
?>

<main class="main-content container">

    <div class="left-column">

        <div class="user-card">

            <h1 class="head-main">
                <?= Html::encode($user->name) ?>
            </h1>

            <?php if ($user->avatar_path): ?>

                <img
                    src="<?= Html::encode($user->avatar_path) ?>"
                    alt="<?= Html::encode($user->name) ?>"
                >

            <?php endif; ?>

            <?php if ($user->city): ?>

                <p class="info-text">
                    Город: <?= Html::encode($user->city->name) ?>
                </p>

            <?php endif; ?>

        </div>

    </div>

</main>
