<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CallAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [];
    public $js = [
//        'media/js/contexts/call.js',
        'media/js/global_contact_form.js',
        'media/js/SIPml-api.js',
        'media/js/crmcaller.js'

    ];
    public $depends = [
        'app\assets\DatapickerAsset'
    ];
}

//