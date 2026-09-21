<?php

declare(strict_types=1);

/**
 * @var yii\web\View $this
 * @var app\models\Task[] $newTasks
 * @var app\models\Task[] $inProgressTasks
 * @var app\models\Task[] $closedTasks
 * @var app\models\Task[] $overdueTasks
 * @var bool $isExecutor
 */

use yii\helpers\Html;

$this->title = 'Мои задания';

$section = Yii::$app->request->get('section', $isExecutor ? 'work' : 'new');

$sections = $isExecutor
    ? [
        'work' => [
            'title' => 'В процессе',
            'tasks' => $inProgressTasks,
        ],
        'overdue' => [
            'title' => 'Просрочено',
            'tasks' => $overdueTasks,
        ],
        'closed' => [
            'title' => 'Закрытые',
            'tasks' => $closedTasks,
        ],
    ]
    : [
        'new' => [
            'title' => 'Новые задания',
            'tasks' => $newTasks,
        ],
        'work' => [
            'title' => 'Задания в процессе',
            'tasks' => $inProgressTasks,
        ],
        'closed' => [
            'title' => 'Закрытые задания',
            'tasks' => $closedTasks,
        ],
    ];

$currentSection = $sections[$section] ?? reset($sections);
?>

<main class="main-content container">
    <div class="left-menu">
        <h3 class="head-main head-task">Мои задания</h3>

        <ul class="side-menu-list">
            <?php foreach ($sections as $key => $item): ?>
                <li class="side-menu-item <?= $section === $key ? 'side-menu-item--active' : '' ?>">
                    <?= Html::a(
                        Html::encode($item['title']),
                        ['tasks/my-tasks', 'section' => $key],
                        ['class' => 'link link--nav']
                    ) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="left-column left-column--task">
        <h3 class="head-main head-regular">
            <?= Html::encode($currentSection['title']) ?>
        </h3>

        <?php foreach ($currentSection['tasks'] as $task): ?>
            <div class="task-card">
                <div class="header-task">
                    <?= Html::a(
                        Html::encode($task->title),
                        ['tasks/view', 'id' => $task->id],
                        ['class' => 'link link--block link--big']
                    ) ?>

                    <?php if ($task->budget !== null): ?>
                        <p class="price price--task">
                            <?= Html::encode($task->budget) ?> ₽
                        </p>
                    <?php endif; ?>
                </div>

                <p class="info-text">
                    <?= Html::encode(
                        Yii::$app->formatter->asRelativeTime($task->created_at)
                    ) ?>
                </p>

                <p class="task-text">
                    <?= Html::encode($task->description) ?>
                </p>

                <div class="footer-task">
                    <?php if ($task->city !== null): ?>
                        <p class="info-text town-text">
                            <?= Html::encode($task->city->name) ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($task->category !== null): ?>
                        <p class="info-text category-text">
                            <?= Html::encode($task->category->name) ?>
                        </p>
                    <?php endif; ?>

                    <?= Html::a(
                        'Смотреть задание',
                        ['tasks/view', 'id' => $task->id],
                        ['class' => 'button button--black']
                    ) ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($currentSection['tasks'])): ?>
            <p class="info-text">Заданий в этом разделе нет.</p>
        <?php endif; ?>
    </div>
</main>
