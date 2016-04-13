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
class AppAsset extends AssetBundle {

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        '/media/plugins/pace/pace-theme-flash.css',
        '/media/plugins/boostrapv3/css/bootstrap.min.css',
        '/media/plugins/font-awesome/css/font-awesome.css',
        '/media/plugins/jquery-scrollbar/jquery.scrollbar.css',
        '/media/plugins/bootstrap-select2/select2.css',
        '/media/plugins/bootstrap-select/css/bootstrap-select.min.css',
        '/media/plugins/switchery/css/switchery.min.css',
        '/media/css/pages-icons.css',
        '/media/css/pages.css',
        '/media/css/main.css',
    ];
    public $js = [
        '/media/plugins/pace/pace.min.js',
        '/media/plugins/jquery/jquery-1.11.1.min.js',
        '/media/plugins/modernizr.custom.js',
        '/media/plugins/jquery-ui/jquery-ui.min.js',
        '/media/plugins/boostrapv3/js/bootstrap.min.js',
        '/media/plugins/jquery/jquery-easy.js',
        '/media/plugins/jquery-unveil/jquery.unveil.min.js',
        '/media/plugins/jquery-bez/jquery.bez.min.js',
        '/media/plugins/jquery-ios-list/jquery.ioslist.min.js',
        '/media/plugins/jquery-actual/jquery.actual.min.js',
        '/media/plugins/jquery-scrollbar/jquery.scrollbar.min.js',
        '/media/plugins/bootstrap-select2/select2.js',
        '/media/plugins/bootstrap-select/js/bootstrap-select.js',
        '/media/plugins/classie/classie.js',
        '/media/plugins/switchery/js/switchery.min.js',
        '/media/plugins/jquery-validation/js/jquery.validate.min.js',
        '/media/plugins/jquery-maskedinput/jquery.maskedinput.min.js',
        '/media/js/pages.js',
        '/media/js/sidebar.custom.js',
        '/media/js/scripts.js',
    ];
    public $depends = [

    ];

}
