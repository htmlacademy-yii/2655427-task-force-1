<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var app\models\Task[] $tasks */
/** @var app\models\Category[] $categories */
/** @var app\models\TaskFilter $filter */

?>

<main class="main-content container">
    <div class="left-column">
        <h3 class="head-main head-task">Новые задания</h3>

        <?php foreach ($tasks as $task): ?>

            <div class="task-card">
                <div class="header-task">
                    <a
                        href="<?= Url::to(['tasks/view', 'id' => $task->id]) ?>"
                        class="link link--block link--big"
                    >
                        <?= Html::encode($task->title) ?>
                    </a>

                    <p class="price price--task">
                        <?= Html::encode($task->budget) ?> ₽
                    </p>
                </div>

                <p class="info-text">
                    <?= Html::encode($task->created_at) ?>
                </p>

                <p class="task-text">
                    <?= Html::encode($task->description) ?>
                </p>

                <div class="footer-task">
                    <p class="info-text town-text">
                        <?php if ($task->city !== null): ?>
                            <?= Html::encode($task->city->name) ?>
                        <?php endif; ?>
                    </p>

                    <p class="info-text category-text">
                        <?php if ($task->category !== null): ?>
                            <?= Html::encode($task->category->name) ?>
                        <?php endif; ?>
                    </p>

                    <a
                        href="<?= Url::to(['tasks/view', 'id' => $task->id]) ?>"
                        class="button button--black"
                    >
                        Смотреть Задание
                    </a>
                </div>
            </div>

        <?php endforeach; ?>
    </div>

    <div class="pagination-wrapper">
        <ul class="pagination-list">
            <li class="pagination-item mark">
                <a href="#" class="link link--page"></a>
            </li>

            <li class="pagination-item">
                <a href="#" class="link link--page">1</a>
            </li>

            <li class="pagination-item pagination-item--active">
                <a href="#" class="link link--page">2</a>
            </li>

            <li class="pagination-item">
                <a href="#" class="link link--page">3</a>
            </li>

            <li class="pagination-item mark">
                <a href="#" class="link link--page"></a>
            </li>
        </ul>
    </div>

    <div class="right-column">
        <div class="right-card black">
            <div class="search-form">

                <?php $form = ActiveForm::begin([
                    'method' => 'get',
                ]); ?>

                <h4 class="head-card">Категории</h4>

                <?= $form->field($filter, 'categories')
                    ->label(false)
                    ->dropDownList(
                        ArrayHelper::map($categories, 'id', 'name'),
                        [
                            'prompt' => 'Выберите категорию',
                        ]
                    ) ?>

                <h4 class="head-card">Дополнительно</h4>

                <?= $form->field($filter, 'without_performer')
                    ->label(false)
                    ->checkbox([
                        'label' => 'Без исполнителя',
                    ]) ?>

                <h4 class="head-card">Период</h4>

                <?= $form->field($filter, 'period')
                    ->label(false)
                    ->dropDownList([
                        1 => '1 час',
                        12 => '12 часов',
                        24 => '24 часа',
                    ], [
                        'prompt' => 'Выберите период',
                    ]) ?>

                <input
                    type="submit"
                    class="button button--blue"
                    value="Искать"
                >

                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</main>
