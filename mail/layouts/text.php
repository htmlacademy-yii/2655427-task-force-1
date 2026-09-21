<?php

declare(strict_types=1);

/**
 * @var yii\web\View $this View component instance.
 * @var yii\mail\BaseMessage $message Message being composed.
 * @var string $content Main view render result.
 */

$this->beginPage();
$this->beginBody();
echo $content;
$this->endBody();
$this->endPage();
