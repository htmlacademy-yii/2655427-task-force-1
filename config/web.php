<?php

declare(strict_types=1);

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$localConfig = file_exists(__DIR__ . '/web-local.php')
    ? require __DIR__ . '/web-local.php'
    : [];

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'container' => [
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                'class' => \yii\symfonymailer\Mailer::class,
                'useFileTransport' => true,
                'viewPath' => '@app/mail',
            ],
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'authClientCollection' => [
            'class' => yii\authclient\Collection::class,
            'clients' => [
                'github' => [
                    'class' => yii\authclient\clients\GitHub::class,
                    'clientId' => 'Ov23liev9dj8XvoUZKnd',
                    'clientSecret' => $localConfig['githubClientSecret'] ?? '',
                ],
            ],
        ],
        'request' => [
            'cookieValidationKey' => 'URiySkQ38cKplw1A0O-pjVxdEsmTNZ7d',
        ],
        'authManager' => [
            'class' => \yii\rbac\DbManager::class,
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => \yii\mail\MailerInterface::class,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
    ],
    'params' => array_merge(
        $params,
        [
            'yandexGeocoderApiKey' => $localConfig['yandexGeocoderApiKey'] ?? '',
        ]
    ),
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
    ];
}

return $config;
