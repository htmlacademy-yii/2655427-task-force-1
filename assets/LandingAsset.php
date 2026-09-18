<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for the landing page.
 */
class LandingAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/normalize.css',
        'css/landing.css',
    ];

    public $js = [
        'js/landing.js',
    ];
}
