<?php

declare(strict_types=1);

/**
 * Landing page layout.
 *
 * @var yii\web\View $this
 * @var string $content
 */

use Yii;

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php $this->head() ?>
</head>
<body class="landing">

<?php $this->beginBody() ?>

<?= $content ?>

<div class="overlay"></div>

<?php $this->endBody() ?>

</body>
</html>
<?php $this->endPage() ?>
