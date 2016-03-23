<?php

namespace app\assets;

use yii\web\AssetBundle;


class ImportAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [];
    public $js = [
        'media/js/contexts/import.js',
    ];
    public $depends = [];
}
