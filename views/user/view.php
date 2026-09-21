<?php

declare(strict_types=1);

/**
 * @var yii\web\View $this
 * @var app\models\User $user
 * @var app\models\UserCategory[] $userCategories
 * @var app\models\Feedback[] $feedbacks
 * @var int $completedTasks
 * @var int $failedTasks
 * @var float|null $rating
 * @var int|null $ratingPosition
 * @var int|null $age
 * @var string $status
 */

use yii\helpers\Html;
?>

<main class="main-content container">
    <div class="left-column">
        <h3 class="head-main">
            <?= Html::encode($user->name) ?>
        </h3>

        <div class="user-card">
            <div class="photo-rate">
                <img
                    class="card-photo"
                    src="<?= Html::encode(
                        $user->avatar_path !== null
                            ? Yii::getAlias('@web') . $user->avatar_path
                            : Yii::getAlias('@web/img/man-glasses.png')
                    ) ?>"
                    width="191"
                    height="190"
                    alt="Фото пользователя"
                >

                <?php if ($rating !== null): ?>
                    <div class="card-rate">
                        <div class="stars-rating big">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="<?= $i <= round($rating) ? 'fill-star' : '' ?>">&nbsp;</span>
                            <?php endfor; ?>
                        </div>

                        <span class="current-rate">
                            <?= Html::encode(number_format($rating, 2, '.', '')) ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <p class="user-description">
                <?= Html::encode($user->about) ?>
            </p>
        </div>

        <div class="specialization-bio">
            <div class="specialization">
                <p class="head-info">Специализации</p>

                <ul class="special-list">
                    <?php foreach ($userCategories as $userCategory): ?>
                        <li class="special-item">
                            <?= Html::a(
                                Html::encode($userCategory->category->name),
                                ['tasks/index'],
                                ['class' => 'link link--regular']
                            ) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="bio">
                <p class="head-info">Био</p>

                <p class="bio-info">
                    <?php if ($user->city !== null): ?>
                        <span class="town-info">
                            <?= Html::encode($user->city->name) ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($age !== null): ?>
                        <span class="age-info">
                            <?= $user->city !== null ? ', ' : '' ?>
                            <?= Html::encode($age) ?>
                            <?= $age === 1 ? 'год' : ($age < 5 ? 'года' : 'лет') ?>
                        </span>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <h4 class="head-regular">Отзывы заказчиков</h4>

        <?php foreach ($feedbacks as $feedback): ?>
            <div class="response-card">
                <img
                    class="customer-photo"
                    src="<?= Html::encode(
                        $feedback->author->avatar_path !== null
                            ? Yii::getAlias('@web') . $feedback->author->avatar_path
                            : Yii::getAlias('@web/img/man-glasses.png')
                    ) ?>"
                    width="120"
                    height="127"
                    alt="Фото заказчика"
                >

                <div class="feedback-wrapper">
                    <p class="feedback">
                        «<?= Html::encode($feedback->comment) ?>»
                    </p>

                    <p class="task">
                        Задание
                        «<?= Html::a(
                            Html::encode($feedback->task->title),
                            ['tasks/view', 'id' => $feedback->task->id],
                            ['class' => 'link link--small']
                        ) ?>»
                        выполнено
                    </p>
                </div>

                <div class="feedback-wrapper">
                    <div class="stars-rating small">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="<?= $i <= $feedback->evaluation ? 'fill-star' : '' ?>">&nbsp;</span>
                        <?php endfor; ?>
                    </div>

                    <p class="info-text">
                        <?= Html::encode(
                            Yii::$app->formatter->asRelativeTime(
                                $feedback->created_at
                            )
                        ) ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (!$feedbacks): ?>
            <p class="info-text">Отзывов пока нет.</p>
        <?php endif; ?>
    </div>

    <div class="right-column">
        <div class="right-card black">
            <h4 class="head-card">Статистика исполнителя</h4>

            <dl class="black-list">
                <dt>Всего заказов</dt>
                <dd>
                    <?= Html::encode($completedTasks) ?> выполнено,
                    <?= Html::encode($failedTasks) ?> провалено
                </dd>

                <dt>Место в рейтинге</dt>
                <dd>
                    <?= $ratingPosition !== null
                        ? Html::encode($ratingPosition) . ' место'
                        : '—' ?>
                </dd>

                <dt>Дата регистрации</dt>
                <dd>
                    <?= Html::encode(
                        Yii::$app->formatter->asDate(
                            $user->created_at,
                            'long'
                        )
                    ) ?>
                </dd>

                <dt>Статус</dt>
                <dd>
                    <?= Html::encode($status) ?>
                </dd>
            </dl>
        </div>

        <div class="right-card white">
            <h4 class="head-card">Контакты</h4>

            <ul class="enumeration-list">
                <?php if ($user->phone_number !== null): ?>
                    <li class="enumeration-item">
                        <?= Html::a(
                            Html::encode($user->phone_number),
                            'tel:' . $user->phone_number,
                            ['class' => 'link link--block link--phone']
                        ) ?>
                    </li>
                <?php endif; ?>

                <li class="enumeration-item">
                    <?= Html::a(
                        Html::encode($user->email),
                        'mailto:' . $user->email,
                        ['class' => 'link link--block link--email']
                    ) ?>
                </li>

                <?php if ($user->telegram !== null): ?>
                    <li class="enumeration-item">
                        <?= Html::a(
                            Html::encode($user->telegram),
                            'https://t.me/' . ltrim($user->telegram, '@'),
                            ['class' => 'link link--block link--tg']
                        ) ?>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</main>
