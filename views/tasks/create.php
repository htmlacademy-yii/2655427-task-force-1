<?php

use app\models\City;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Task $task */
/** @var app\models\Category[] $categories */

$this->title = 'Публикация нового задания';

$cities = City::find()->all();
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

    <div class="form-group">
        <label class="control-label" for="location">
            Локация
        </label>

        <?= Html::dropDownList(
            'location',
            null,
            ArrayHelper::map($cities, 'id', 'name'),
            [
                'id' => 'location',
                'class' => 'form-control',
                'prompt' => 'Выберите город',
            ]
        ) ?>
    </div>

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
