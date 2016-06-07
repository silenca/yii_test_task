<?php

namespace app\assets;

use yii\web\AssetBundle;

class TagAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [];
    public $js = [
        //'media/js/contexts/tag.js',
        'media/js/contexts/new_tag.js?v=1',
        'media/js/contact_form.js',
        'media/js/contexts/import.js',
    ];
    public $depends = [
        'app\assets\DatapickerAsset'
    ];
}

