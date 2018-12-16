<?php

namespace app\models;


use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * This is the model class for table "cdr".
 *
 * @property integer $id
 * @property string $accountcode
 * @property string $src
 * @property string $dst
 * @property string $dcontext
 * @property string $clid
 * @property string $channel
 * @property string $dstchannel
 * @porperty string $lastapp
 * @property string $lastdata
 * @property string $start
 * @property string $answer
 * @property string $end
 * @property integer $duration
 * @property integer $billsec
 * @property string $disposition
 * @property string $amaflags
 * @property string $userfield
 * @property string $uniqueid
 * @property string $linkedid
 * @property string $peeraccount
 * @property integer $sequence
 * @property string $record
 *
 */
class Cdr extends ActiveRecord
{
    const CALL_INCOMING = 'incoming';
    const CALL_OUTGOING = 'outgoing';
    const CALL_STATUS_MISSED = 'missed';
    const CALL_STATUS_FAILURE = 'failure';
    const CALL_STATUS_ANSWERED = 'answered';
    const CALL_STATUS_NEW = 'new';

    public $manager;

    public function rules()
    {
        return [
            [['date', 'duration', 'type', 'manager_id', 'phone'], 'required'],
            [['date', 'duration', 'type', 'phone'], 'string'],
            [['record'], 'string', 'max' => 250]
        ];
    }

    public function attributeLabels()
    {
        return [
            'date' => 'Call date',
            'time' => 'Call time',
            'type' => 'Call type',
            'manager' => 'Manager involved',
            'contact' => 'Contact object by phone',
            'record' => 'Record file',
//            'uniqueid'  =>  'Exists unique id of call'
        ];
    }

    public static function tableName()
    {
        return '{{cdr}}';
    }

    public function getManager()
    {
        $int_id = $this->dcontext === 'in' ? substr($this->dstchannel, 4, 3) : $this->src;
        return User::find(['int_id' => $int_id])->one();
    }

    public function getType()
    {
        return $this->dcontext === 'in' ? 'Входящий' : 'Исходящий';
    }

    public function getUnique()
    {
        return $this->uniqueid;
    }

    public static function getCallStatuses()
    {
        return [
            ['name' => self::CALL_STATUS_ANSWERED . '_' . self::CALL_STATUS_MISSED . '|' . self::CALL_INCOMING, 'label' => 'Исходящий'],
            ['name' => self::CALL_STATUS_ANSWERED . '|' . self::CALL_OUTGOING, 'label' => 'Входящий'],
            ['name' => self::CALL_STATUS_MISSED . '|' . self::CALL_OUTGOING, 'label' => 'Пропущенный'],
            ['name' => self::CALL_STATUS_FAILURE . '|' . self::CALL_INCOMING . '_' . self::CALL_OUTGOING, 'label' => 'Сбой'],
        ];
    }
    public function getContact(){
        if($this->dcontext === 'in'){
            return Contact::getContactByPhone(substr(stristr($this->clid,'<'),1,-1)) ? Contact::getContactByPhone(substr(stristr($this->clid,'<'),1,-1)) : substr(stristr($this->clid,'<'),1,-1);
        }else{
            return Contact::getContactByPhone($this->dst) ? Contact::getContactByPhone($this->dst) : $this->dst;
        }
    }
}