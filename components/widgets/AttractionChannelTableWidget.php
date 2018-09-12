<?php
/**
 * AttractionChannelTableWidget.php
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */

namespace app\components\widgets;


use app\models\AttractionChannel;
use yii\base\Widget;

class AttractionChannelTableWidget extends Widget
{
    /**
     * @var $attraction_channels AttractionChannel[]
     */
    public $attraction_channels;


    public function run()
    {
        $data = [];
        foreach ($this->attraction_channels as $k => $attraction_channel) {
            $data[$k][] = $attraction_channel->id;
            $data[$k][] = $attraction_channel->name;
            $data[$k][] = $attraction_channel->is_active;
            $data[$k][] = $attraction_channel->type;
//            $data[$k][] = (isset($attraction_channel->sip_channel_id))?$attraction_channel->sipChannel->host:'';
//            $data[$k][] = $attraction_channel->integration_type;
            $data[$k][] = '<div class="col-md-offset-3 remove"><i class="fa fa-remove"></i></div>';
        }
        return $data;
    }
}