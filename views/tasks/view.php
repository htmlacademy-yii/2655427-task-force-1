<?php

use app\models\Feedback;
use app\models\Response;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Task $task */
/** @var app\models\Response $response */
/** @var app\models\Feedback|null $feedback */

$this->title = $task->title;

$currentUserId = Yii::$app->user->id;
$isGuest = Yii::$app->user->isGuest;

$isAuthor = !$isGuest
    && (int) $task->author_id === (int) $currentUserId;

$isExecutor = !$isGuest
    && $task->executor_id !== null
    && (int) $task->executor_id === (int) $currentUserId;

$statusName = $task->status->name;

$isNew = $statusName === 'Новое';
$isWork = $statusName === 'В работе';

$alreadyResponded = false;

if (!$isGuest) {
    $alreadyResponded = Response::find()
        ->where([
            'task_id' => $task->id,
            'user_id' => $currentUserId,
        ])
        ->exists();
}

$canRespond = !$isGuest
    && !$isAuthor
    && Yii::$app->user->identity->user_role === \app\models\User::USER_ROLE_EXECUTOR
    && $isNew
    && $task->executor_id === null
    && !$alreadyResponded;

$canCancel = $isAuthor && $isNew;
$canFinish = $isAuthor && $isWork;
$canRefuse = $isExecutor && $isWork;

$feedback = $feedback ?? new Feedback();

$responseModalClass = $response->hasErrors()
    ? 'pop-up--open'
    : 'pop-up--close';

$overlayClass = $response->hasErrors()
    ? 'db'
    : '';

/*
 * Заказчик видит все отклики.
 * Обычный пользователь видит только собственный отклик.
 */
$responses = $task->responses;

if (!$isAuthor && !$isGuest) {
    $responses = array_filter(
        $responses,
        static function (Response $item) use ($currentUserId): bool {
            return (int) $item->user_id === (int) $currentUserId;
        }
    );
}
?>

<div class="left-column">

    <div class="head-wrapper">

        <h3 class="head-main">
            <?= Html::encode($task->title) ?>
        </h3>

        <?php if ($task->budget !== null): ?>
            <p class="price price--big">
                <?= Html::encode($task->budget) ?> ₽
            </p>
        <?php endif; ?>

    </div>

    <p class="task-description">
        <?= nl2br(Html::encode($task->description)) ?>
    </p>

    <?php if ($canRespond): ?>

        <a
            href="#"
            class="button button--blue action-btn"
            data-action="act_response"
        >
            Откликнуться на задание
        </a>

    <?php endif; ?>

    <?php if ($canRefuse): ?>

        <a
            href="#"
            class="button button--orange action-btn"
            data-action="refusal"
        >
            Отказаться от задания
        </a>

    <?php endif; ?>

    <?php if ($canFinish): ?>

        <a
            href="#"
            class="button button--pink action-btn"
            data-action="completion"
        >
            Завершить задание
        </a>

    <?php endif; ?>

    <?php if ($canCancel): ?>

        <?= Html::beginForm(
            [
                'tasks/cancel',
                'id' => $task->id,
            ],
            'post'
        ) ?>

        <?= Html::submitButton(
            'Отменить задание',
            [
                'class' => 'button button--orange',
            ]
        ) ?>

        <?= Html::endForm() ?>

    <?php endif; ?>

    <?php if ($task->city): ?>

        <div class="task-map">

            <div
                class="map"
                style="height: 346px; width: 725px;"
            ></div>

            <p class="map-address town">
                <?= Html::encode($task->city->name) ?>
            </p>

        </div>

    <?php endif; ?>

    <?php if (!empty($responses)): ?>

        <h4 class="head-regular">
            Отклики на задание
        </h4>

        <?php foreach ($responses as $item): ?>

            <div class="response-card">

                <img
                    class="customer-photo"
                    src="/img/man-glasses.png"
                    width="146"
                    height="156"
                    alt="Фото пользователя"
                >

                <div class="feedback-wrapper">

                    <a
                        href="#"
                        class="link link--block link--big"
                    >
                        <?= Html::encode(
                            $item->user->name ?? 'Пользователь'
                        ) ?>
                    </a>

                    <div class="response-wrapper">

                        <div class="stars-rating small">
                            <span class="fill-star">&nbsp;</span>
                            <span class="fill-star">&nbsp;</span>
                            <span class="fill-star">&nbsp;</span>
                            <span class="fill-star">&nbsp;</span>
                            <span>&nbsp;</span>
                        </div>

                        <p class="reviews">
                            0 отзывов
                        </p>

                    </div>

                    <?php if ($item->comment): ?>

                        <p class="response-message">
                            <?= nl2br(
                                Html::encode($item->comment)
                            ) ?>
                        </p>

                    <?php endif; ?>

                    <?php if ($item->isStatusRejected()): ?>

                        <p class="info-text">
                            Отклик отклонён
                        </p>

                    <?php elseif ($item->isStatusAccepted()): ?>

                        <p class="info-text">
                            Отклик принят
                        </p>

                    <?php endif; ?>

                </div>

                <div class="feedback-wrapper">

                    <p class="info-text">

                        <?php if ($item->created_at): ?>

                            <?= Yii::$app->formatter->asRelativeTime(
                                $item->created_at
                            ) ?>

                        <?php endif; ?>

                    </p>

                    <p class="price price--small">
                        <?= Html::encode($item->price) ?> ₽
                    </p>

                </div>

                <?php if (
                    $isAuthor
                    && $isNew
                    && $item->isStatusNew()
                ): ?>

                    <div class="button-popup">

                        <?= Html::beginForm(
                            [
                                'tasks/accept-response',
                                'id' => $item->id,
                            ],
                            'post'
                        ) ?>

                        <?= Html::submitButton(
                            'Принять',
                            [
                                'class' => 'button button--blue button--small',
                            ]
                        ) ?>

                        <?= Html::endForm() ?>

                        <?= Html::beginForm(
                            [
                                'tasks/reject-response',
                                'id' => $item->id,
                            ],
                            'post'
                        ) ?>

                        <?= Html::submitButton(
                            'Отказать',
                            [
                                'class' => 'button button--orange button--small',
                            ]
                        ) ?>

                        <?= Html::endForm() ?>

                    </div>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

<div class="right-column">

    <div class="right-card black info-card">

        <h4 class="head-card">
            Информация о задании
        </h4>

        <dl class="black-list">

            <dt>Категория</dt>
            <dd>
                <?= Html::encode($task->category->name) ?>
            </dd>

            <dt>Дата публикации</dt>
            <dd>
                <?= Yii::$app->formatter->asRelativeTime(
                    $task->created_at
                ) ?>
            </dd>

            <?php if ($task->deadline): ?>

                <dt>Срок выполнения</dt>
                <dd>
                    <?= Yii::$app->formatter->asDate(
                        $task->deadline,
                        'php:d.m.Y'
                    ) ?>
                </dd>

            <?php endif; ?>

            <dt>Статус</dt>

            <dd>
                <?= Html::encode($statusName) ?>
            </dd>

        </dl>

    </div>

    <?php if (!empty($task->files)): ?>

        <div class="right-card white file-card">

            <h4 class="head-card">
                Файлы задания
            </h4>

            <ul class="enumeration-list">

                <?php foreach ($task->files as $file): ?>

                    <li class="enumeration-item">

                        <a
                            href="<?= Html::encode($file->file_path) ?>"
                            class="link link--block link--clip"
                        >
                            <?= Html::encode(
                                basename($file->file_path)
                            ) ?>
                        </a>

                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>

</div>

<?php if ($canRefuse): ?>

    <section class="pop-up pop-up--refusal pop-up--close">

        <div class="pop-up--wrapper">

            <h4>
                Отказ от задания
            </h4>

            <p class="pop-up-text">
                <b>Внимание!</b><br>
                Вы собираетесь отказаться от выполнения этого задания.<br>
                Это действие плохо скажется на вашем рейтинге
                и увеличит счетчик проваленных заданий.
            </p>

            <?= Html::beginForm(
                [
                    'tasks/refuse',
                    'id' => $task->id,
                ],
                'post'
            ) ?>

            <?= Html::submitButton(
                'Отказаться',
                [
                    'class' => 'button button--pop-up button--orange',
                ]
            ) ?>

            <?= Html::endForm() ?>

            <div class="button-container">

                <button
                    class="button--close"
                    type="button"
                >
                    Закрыть окно
                </button>

            </div>

        </div>

    </section>

<?php endif; ?>

<?php if ($canFinish): ?>

    <section class="pop-up pop-up--completion pop-up--close">

        <div class="pop-up--wrapper">

            <h4>
                Завершение задания
            </h4>

            <p class="pop-up-text">
                Вы собираетесь отметить это задание как выполненное.
                Пожалуйста, оставьте отзыв об исполнителе
                и отметьте отдельно, если возникли проблемы.
            </p>

            <div class="completion-form pop-up--form regular-form">

                <?= Html::beginForm(
                    [
                        'tasks/finish',
                        'id' => $task->id,
                    ],
                    'post'
                ) ?>

                <div class="form-group">

                    <label
                        class="control-label"
                        for="completion-comment"
                    >
                        Ваш комментарий
                    </label>

                    <?= Html::textarea(
                        'Feedback[comment]',
                        $feedback->comment,
                        [
                            'id' => 'completion-comment',
                        ]
                    ) ?>

                    <?php if ($feedback->hasErrors('comment')): ?>

                        <div class="help-block">
                            <?= Html::encode(
                                implode(
                                    ' ',
                                    $feedback->getErrors('comment')
                                )
                            ) ?>
                        </div>

                    <?php endif; ?>

                </div>

                <p class="completion-head control-label">
                    Оценка работы
                </p>

                <?= Html::dropDownList(
                    'Feedback[evaluation]',
                    $feedback->evaluation,
                    [
                        1 => '1',
                        2 => '2',
                        3 => '3',
                        4 => '4',
                        5 => '5',
                    ],
                    [
                        'class' => 'form-control',
                    ]
                ) ?>

                <?php if ($feedback->hasErrors('evaluation')): ?>

                    <div class="help-block">
                        <?= Html::encode(
                            implode(
                                ' ',
                                $feedback->getErrors('evaluation')
                            )
                        ) ?>
                    </div>

                <?php endif; ?>

                <input
                    type="submit"
                    class="button button--pop-up button--blue"
                    value="Завершить"
                >

                <?= Html::endForm() ?>

            </div>

            <div class="button-container">

                <button
                    class="button--close"
                    type="button"
                >
                    Закрыть окно
                </button>

            </div>

        </div>

    </section>

<?php endif; ?>

<?php if ($canRespond): ?>

    <section class="pop-up pop-up--act_response <?= $responseModalClass ?>">

        <div class="pop-up--wrapper">

            <h4>
                Добавление отклика к заданию
            </h4>

            <p class="pop-up-text">
                Вы собираетесь оставить свой отклик к этому заданию.
                Пожалуйста, укажите стоимость работы
                и добавьте комментарий, если необходимо.
            </p>

            <div class="addition-form pop-up--form regular-form">

                <?= Html::beginForm(
                    [
                        'tasks/respond',
                        'id' => $task->id,
                    ],
                    'post'
                ) ?>

                <div class="form-group">

                    <label
                        class="control-label"
                        for="addition-comment"
                    >
                        Ваш комментарий
                    </label>

                    <?= Html::textarea(
                        'Response[comment]',
                        $response->comment,
                        [
                            'id' => 'addition-comment',
                        ]
                    ) ?>

                    <?php if ($response->hasErrors('comment')): ?>

                        <div class="help-block">
                            <?= Html::encode(
                                implode(
                                    ' ',
                                    $response->getErrors('comment')
                                )
                            ) ?>
                        </div>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="control-label"
                        for="addition-price"
                    >
                        Стоимость
                    </label>

                    <?= Html::input(
                        'number',
                        'Response[price]',
                        $response->price,
                        [
                            'id' => 'addition-price',
                            'min' => 1,
                        ]
                    ) ?>

                    <?php if ($response->hasErrors('price')): ?>

                        <div class="help-block">
                            <?= Html::encode(
                                implode(
                                    ' ',
                                    $response->getErrors('price')
                                )
                            ) ?>
                        </div>

                    <?php endif; ?>

                </div>

                <input
                    type="submit"
                    class="button button--pop-up button--blue"
                    value="Откликнуться"
                >

                <?= Html::endForm() ?>

            </div>

            <div class="button-container">

                <button
                    class="button--close"
                    type="button"
                >
                    Закрыть окно
                </button>

            </div>

        </div>

    </section>

<?php endif; ?>

<div class="overlay <?= $overlayClass ?>"></div>
