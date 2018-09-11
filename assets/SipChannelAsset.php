<?php
/**
 * SipChannelAsset.php
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */

namespace app\assets;


use yii\web\AssetBundle;

class SipChannelAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [];
    public $js = [
        'media/js/contexts/sip_channel.js',
        'media/js/sip_channel_form.js',
    ];
    public $depends = [
        'app\assets\DatapickerAsset'
    ];
}