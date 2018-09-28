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
    var $sip_channel_id = [];
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
            ['sip_channel_id','validateSipChannels'],
            ['sip_channel_id','required','when'=>function($model){return $model->type == AttractionChannel::TYPE_SIP_CHANNEL;}, 'message' => 'Необходимо выбрать канал'],
            ['integration_type','in','range'=>AttractionChannel::INTEGRATIONS, 'message' => 'Неправильное значение типа']
        ];
    }

    public function validateSipChannels($attribute,$params)
    {
        if(!is_array($this->$attribute)) {
//            $this->addError($attribute,"Наверное значение");
            $this->$attribute = [$this->$attribute];
        }
        if($this->type == AttractionChannel::TYPE_SIP_CHANNEL && empty($this->$attribute))
            $this->addError($attribute,"Необходимо выбрать канал");
        $available = SipChannel::find()->select('id')->where(['is','attraction_channel_id',NULL])
            ->orWhere(['attraction_channel_id'=>$this->edited_id])->asArray()->all();
        $available = \array_column($available,'id');
        $ext = \array_intersect($available,$this->$attribute);
        $rez = \array_diff($ext, $this->$attribute);
        if(!empty($rez)) {
            $this->addError($attribute, "Наверное значение");
            return false;
        }
        return true;
    }

    public function getChannelAtributes()
    {
        $attributes = $this->attributes;
        unset($attributes['sip_channel_id']);
        return $attributes;
    }
}