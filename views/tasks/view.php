<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\models\Task $task */
?>

<main class="main-content container">

    <div class="left-column">

        <div class="task-card">

            <div class="header-task">

                <div>
                    <h1 class="head-main">
                        <?= Html::encode($task->title) ?>
                    </h1>

                    <p class="info-text">
                        <a href="<?= Url::to([
                            'tasks/index',
                            'TaskFilter[categories][]' => $task->category_id
                        ]) ?>">
                            <?= Html::encode($task->category->name) ?>
                        </a>

                        ·

                        <?= Html::encode($task->created_at) ?>
                    </p>
                </div>

                <p class="price price--task">
                    <?= Html::encode($task->budget) ?> ₽
                </p>

            </div>

            <p class="task-text">
                <?= Html::encode($task->description) ?>
            </p>

        </div>

    </div>

    <div class="right-column">

        <div class="right-card">

            <h3 class="head-card">
                Заказчик
            </h3>

            <p class="info-text">
                <a href="<?= Url::to([
                    'user/view',
                    'id' => $task->author_id
                ]) ?>">
                    <?= Html::encode($task->author->name) ?>
                </a>
            </p>

        </div>

    </div>

</main>
