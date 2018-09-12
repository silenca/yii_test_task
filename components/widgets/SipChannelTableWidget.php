<?php
/**
 * SipChannelTableWidget.php
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */

namespace app\components\widgets;


use app\models\SipChannel;
use yii\base\Widget;

class SipChannelTableWidget extends Widget
{
    /**
     * @var $sip_channels SipChannel[]
     */
    public $sip_channels;


    public function run()
    {
        $data = [];
        foreach ($this->sip_channels as $k => $sip_channel) {
            $data[$k][] = $sip_channel->id;
            $data[$k][] = $sip_channel->phone_number;
            $data[$k][] = $sip_channel->host;
            $data[$k][] = $sip_channel->port;
            $data[$k][] = $sip_channel->login;
            $data[$k][] = $sip_channel->password;
            $data[$k][] = '<div class="col-md-offset-3 remove"><i class="fa fa-remove"></i></div>';
        }
        return $data;
    }
}