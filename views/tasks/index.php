<?php

declare(strict_types=1);

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var app\models\Task[] $tasks */
/** @var app\models\Category[] $categories */
/** @var app\models\TaskFilter $filter */
/** @var yii\data\ActiveDataProvider $dataProvider */
?>

<main class="main-content container">
    <div class="left-column">
        <h3 class="head-main head-task">Новые задания</h3>

        <?php foreach ($tasks as $task): ?>

            <div class="task-card">
                <div class="header-task">
                    <a
                        href="<?= Url::to([
                            'tasks/view',
                            'id' => $task->id,
                        ]) ?>"
                        class="link link--block link--big"
                    >
                        <?= Html::encode($task->title) ?>
                    </a>

                    <p class="price price--task">
                        <?= Html::encode($task->budget) ?> ₽
                    </p>
                </div>

                <p class="info-text">
                    <?= Html::encode(
                        Yii::$app->formatter->asRelativeTime(
                            $task->created_at
                        )
                    ) ?>
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
                        href="<?= Url::to([
                            'tasks/view',
                            'id' => $task->id,
                        ]) ?>"
                        class="button button--black"
                    >
                        Смотреть Задание
                    </a>
                </div>
            </div>

        <?php endforeach; ?>

        <div class="pagination-wrapper">
            <?= LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'options' => [
                    'class' => 'pagination-list',
                ],
                'linkOptions' => [
                    'class' => 'link link--page',
                ],
                'pageCssClass' => 'pagination-item',
                'activePageCssClass' => 'pagination-item--active',
                'prevPageCssClass' => 'pagination-item mark',
                'nextPageCssClass' => 'pagination-item mark',
                'prevPageLabel' => '',
                'nextPageLabel' => '',
            ]) ?>
        </div>
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
