<?php
/*
 * Yii2 Autocomplete Helper
 * https://github.com/iiifx-production/yii2-autocomplete-helper
 *
 * Vitaliy IIIFX Khomenko (c) 2016
 */

class Yii extends \yii\BaseYii
{
    /**
     * @var BaseApplication|WebApplication|ConsoleApplication
     */
    public static $app;
}

/**
 * @property yii\rbac\PhpManager $authManager
 * @property yii\web\DbSession $session
 * @property yii\caching\FileCache $cache
 * @property yii\swiftmailer\Mailer $mailer
 * @property yii\db\Connection $db
 */
abstract class BaseApplication extends \yii\base\Application {}

/**
 * @property yii\rbac\PhpManager $authManager
 * @property yii\web\DbSession $session
 * @property yii\caching\FileCache $cache
 * @property yii\swiftmailer\Mailer $mailer
 * @property yii\db\Connection $db
 */
class WebApplication extends \yii\web\Application {}

/**
 * @property yii\rbac\PhpManager $authManager
 * @property yii\web\DbSession $session
 * @property yii\caching\FileCache $cache
 * @property yii\swiftmailer\Mailer $mailer
 * @property yii\db\Connection $db
 */
class ConsoleApplication extends \yii\console\Application {}
