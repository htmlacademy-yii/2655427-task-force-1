<?php

declare(strict_types=1);

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var app\models\Task $task
 * @var app\models\Category[] $categories
 * @var app\models\City[] $cities
 */

$this->title = 'Публикация нового задания';
?>

<div class="add-task-form regular-form">

    <h3 class="head-main">Публикация нового задания</h3>

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form',
            'enctype' => 'multipart/form-data',
        ],
    ]); ?>

    <?= $form->field($task, 'title', [
        'options' => ['class' => 'form-group'],
    ])->textInput([
        'id' => 'essence-work',
    ]) ?>

    <?= $form->field($task, 'description', [
        'options' => ['class' => 'form-group'],
    ])->textarea([
        'id' => 'username',
    ]) ?>

    <?= $form->field($task, 'category_id', [
        'options' => ['class' => 'form-group'],
    ])->dropDownList(
        ArrayHelper::map($categories, 'id', 'name'),
        [
            'id' => 'town-user',
            'prompt' => 'Выберите категорию',
        ]
    ) ?>

    <?= $form->field($task, 'city_id', [
        'options' => ['class' => 'form-group'],
    ])->dropDownList(
        ArrayHelper::map($cities, 'id', 'name'),
        [
            'id' => 'city',
            'prompt' => 'Выберите город',
        ]
    ) ?>

    <?= $form->field($task, 'location', [
        'options' => ['class' => 'form-group'],
    ])->textInput([
        'id' => 'location',
        'placeholder' => 'Например, ул. Тверская, 10',
    ]) ?>

    <div class="half-wrapper">

        <?= $form->field($task, 'budget', [
            'options' => ['class' => 'form-group'],
        ])->textInput([
            'class' => 'budget-icon',
            'id' => 'budget',
            'type' => 'number',
        ]) ?>

        <?= $form->field($task, 'deadline', [
            'options' => ['class' => 'form-group'],
        ])->input('date', [
            'id' => 'period-execution',
        ]) ?>

    </div>

    <div class="form-group">
        <label class="control-label">Файлы</label>
        <input type="file" name="files[]" multiple>
    </div>

    <?= Html::submitButton('Опубликовать', [
        'class' => 'button button--blue',
    ]) ?>

    <?php ActiveForm::end(); ?>

</div>
