<?php
/**
 * AttractionChannelForm.php
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */

namespace app\models\forms;


use yii\base\Model;

class AttractionChannelForm extends Model
{
    var $name;
    var $is_active;
    var $type;
    var $sip_channel_id;
    var $integration_type;

    public function attributeLabels()
    {
        return [
            'name' => 'Наименование',
            'is_active' => 'Активный',
            'type' => 'Тип',
            'sip_channel_id' => 'SIP Канал',
            'integration_type' => 'Интеграция',
        ];
    }
}