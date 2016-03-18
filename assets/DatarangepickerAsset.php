<?php
/**
 * Created by PhpStorm.
 * User: phobos
 * Date: 11/10/15
 * Time: 1:07 PM
 */

namespace app\assets;

use yii\web\AssetBundle;



class DatarangepickerAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'media/plugins/bootstrap-datarangepicker/css/daterangepicker.css',
    ];
    public $js = [
        'media/plugins/bootstrap-datarangepicker/js/daterangepicker.js',
    ];
    public $depends = [
        'app\assets\AppAsset',
        'app\assets\MomentJsAsset',
    ];
}

//