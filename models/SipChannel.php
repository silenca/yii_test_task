<?php

namespace app\models;

use app\components\Asterisk;
use Yii;

/**
 * This is the model class for table "sip_channel".
 *
 * @property integer $id
 * @property string $name
 * @property integer $is_active
 * @property integer $attraction_channel_id
 *
 * @property AttractionChannel $attractionChannel
 */
class SipChannel extends \yii\db\ActiveRecord
{
    const ACTIVE = 1;
    const INACTIVE = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sip_channel';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
            [['name'], 'required'],
            [['attraction_channel_id', 'is_active'], 'integer']
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
            'is_active' => 'Status',
        ];
    }

    public static $safe_fields = [
        'name',
        'is_active',
    ];

    public static function getColsForTableView()
    {
        $result = [
            'id' => ['label' => 'ID', 'have_search' => false, 'orderable' => true],
            'name' => ['label' => 'Название', 'have_search' => true, 'orderable' => true],
            'is_active' => ['label' => 'Активен', 'have_search' => false, 'orderable' => false],
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttractionChannel()
    {
        return $this->hasOne(AttractionChannel::className(), ['id' => 'attraction_channel_id']);
    }

    public static function syncWithConfig()
    {
        $sips = (new Asterisk())->getExternalSips();
        // Craete SIPs from config if it doesn't exist
        foreach($sips as $sip) {
            $channel = SipChannel::findOne(['name' => $sip]);
            if(!$channel) {
                $channel = new SipChannel();
                $channel->name = $sip;
                $channel->is_active = SipChannel::ACTIVE;
                $channel->save();
            }
        }
        // Mark as inactive if sip is not in config
        $channels = SipChannel::find()->where(['not in', 'name', $sips])->all();
        foreach($channels as $channel) {
            /**@var $channel SipChannel*/
            $channel->is_active = SipChannel::INACTIVE;
            $channel->save();
        }
    }
}
