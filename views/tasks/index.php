<?php

use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

?>

<main class="main-content container">
    <div class="left-column">
        <h3 class="head-main head-task">Новые задания</h3>

        <?php foreach ($tasks as $task): ?>

            <div class="task-card">
                <div class="header-task">
                    <a href="#" class="link link--block link--big">
                        <?= $task->title ?>
                    </a>

                    <p class="price price--task">
                        <?= $task->budget ?> ₽
                    </p>
                </div>

                <p class="info-text">
                    <?= $task->created_at ?>
                </p>

                <p class="task-text">
                    <?= $task->description ?>
                </p>

                <div class="footer-task">
                    <p class="info-text town-text">
                        <?= $task->city->name ?>
                    </p>

                    <p class="info-text category-text">
                        <?= $task->category->name ?>
                    </p>

                    <a href="#" class="button button--black">
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

                <?= $form->field($filter, 'categories')->checkboxList(
                    ArrayHelper::map($categories, 'id', 'name')
                ) ?>

                <h4 class="head-card">Дополнительно</h4>

                <?= $form->field($filter, 'without_performer')
                    ->checkbox(['label' => 'Без исполнителя']) ?>

                <h4 class="head-card">Период</h4>

                <?= $form->field($filter, 'period')->dropDownList([
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
