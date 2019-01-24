<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

if (file_exists(__DIR__ . '/params-local.php')) {
    $params = require(__DIR__ . '/params-local.php');
} else {
    $params = require(__DIR__ . '/params.php');
}

$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'Europe/Kiev',
    'controllerNamespace' => 'app\commands',
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
            'defaultRoles' => ['manager', 'supervisor', 'fin_dir','admin'],
            'itemFile' => 'rbac/items.php',
            'assignmentFile' => 'rbac/assignments.php',
            'ruleFile' => 'rbac/rules.php'
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\EmailTarget',
                    'levels' => ['error'],
                    'categories' => ['yii\db\*'],
                    'message' => [
                        'from' => ['log@example.com'],
                        'to' => ['admin@example.com', 'developer@example.com'],
                        'subject' => 'Database errors at example.com',
                    ],
                ],
            ],
        ],
        'db' => $db,
        'medium' => require(__DIR__ . '/medium.php'),
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
