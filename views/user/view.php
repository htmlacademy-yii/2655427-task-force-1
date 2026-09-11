<?php

/** @var app\models\User $user */
?>

<main class="main-content container">

    <div class="left-column">

        <div class="user-card">

            <h1 class="head-main">
                <?= $user->name ?>
            </h1>

            <?php if ($user->avatar_path): ?>

                <img
                    src="<?= $user->avatar_path ?>"
                    alt="<?= $user->name ?>"
                >

            <?php endif; ?>

            <?php if ($user->city): ?>

                <p class="info-text">
                    Город: <?= $user->city->name ?>
                </p>

            <?php endif; ?>

        </div>

    </div>

</main>
