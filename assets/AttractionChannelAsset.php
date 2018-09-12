<?php
/**
 * AttractionChannelAsset.php
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */

namespace app\assets;


use yii\web\AssetBundle;

class AttractionChannelAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [];
    public $js = [
        'media/js/contexts/attraction_channel.js',
        'media/js/attraction_channel_form.js',
    ];
    public $depends = [
        'app\assets\DatapickerAsset'
    ];
}