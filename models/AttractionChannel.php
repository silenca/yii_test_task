<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "attraction_channel".
 *
 * @property integer $id
 * @property string $name
 * @property integer $is_active
 * @property integer $type
 * @property integer $sip_channel_id
 * @property string $integration_type
 *
 * @property SipChannel $sipChannel
 */
class AttractionChannel extends \yii\db\ActiveRecord
{

    const TYPE_OFFLINE = 0;
    const TYPE_SIP_CHANNEL = 1;
    const TYPE_INTEGRATION = 2;

    const TYPE_LABELS = [
        self::TYPE_OFFLINE => 'Оффлайн',
        self::TYPE_SIP_CHANNEL => 'SIP-канал',
        self::TYPE_INTEGRATION => 'Интеграция',
    ];

    const INTEGRATIONS = [
        'Google',
        'Facebook',
        'Instagram',
        'Web порталы',
        'Свой сайт'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'attraction_channel';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_active', 'type', 'sip_channel_id'], 'integer'],
            [['name', 'integration_type'], 'string', 'max' => 255],
            [['sip_channel_id'], 'exist', 'skipOnError' => true, 'targetClass' => SipChannel::className(), 'targetAttribute' => ['sip_channel_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'is_active' => 'Is Active',
            'type' => 'Type',
            'sip_channel_id' => 'Sip Channel ID',
            'integration_type' => 'Integration Type',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSipChannel()
    {
        return $this->hasOne(SipChannel::className(), ['id' => 'sip_channel_id']);
    }

    public static function getColsForTableView()
    {
        $result = [
            'id' => ['label' => 'ID', 'have_search' => false, 'orderable' => true],
            'name' => ['label' => 'Наименование', 'have_search' => true, 'orderable' => true],
            'is_active' => ['label' => 'Активен', 'have_search' => false, 'orderable' => false],
            'type' => ['label' => 'Тип', 'have_search' => false, 'orderable' => false],
//            'sip_channel' => ['label' => 'Логин', 'have_search' => true, 'orderable' => true],
//            'password' => ['label' => 'Пароль', 'have_search' => false, 'orderable' => false],
            'delete_button' => ['label' => 'Удалить', 'have_search' => false]
        ];
        return $result;
    }

    public static function deleteById($id) {
        $channel = self::find()->where(['id' =>(int) $id])->one();
        if ($channel) {
            return $channel->delete();
        }
        return false;
    }
}
