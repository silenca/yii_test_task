<?php
/**
 * AttractionChannelForm.php
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */

namespace app\models\forms;


use app\models\AttractionChannel;
use app\models\SipChannel;
use yii\base\Model;

class AttractionChannelForm extends Model
{
    var $name;
    var $is_active;
    var $type;
    var $sip_channel_id;
    var $integration_type;
    var $edited_id;

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

    public function rules()
    {
        return [
            [['name','type'], 'required', 'message' => 'Необходимо заполнить {attribute}'],
            [['is_active'],'default','value'=>0],
            ['type','in','range'=>\array_keys(AttractionChannel::TYPE_LABELS), 'message' => 'Неправильное значение типа'],
            [['sip_channel_id'], 'exist', 'skipOnError' => true, 'targetClass' => SipChannel::className(), 'targetAttribute' => ['sip_channel_id' => 'id'],'message' => 'Неправильное значение SIP-канала'],
            ['integration_type','in','range'=>AttractionChannel::INTEGRATIONS]
        ];
    }
}