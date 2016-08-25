<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\helpers\BaseUrl;
use app\models\FailExportCall;

/**
 * This is the model class for table "call".
 *
 * @property integer $id
 * @property string $date_time
 * @property string $type
 * @property integer $contact_id
 * @property integer $phone_number
 * @property string $record
 * @property string $unical_id
 * @property string $manager_int_id
 */
class Call extends \yii\db\ActiveRecord {

    //const CALL_NEW = 'new';
    const CALL_INCOMING = 'incoming';
    const CALL_OUTGOING = 'outgoing';
    const CALL_STATUS_MISSED = 'missed';
    const CALL_STATUS_FAILURE = 'failure';
    const CALL_STATUS_ANSWERED = 'answered';
    const CALL_STATUS_NEW = 'new';

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%call}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['date_time', 'type', 'unique_id'], 'required'],
            [['date_time', 'attitude_level', 'call_order_token', 'tag_id', 'sended_crm'], 'safe'],
            [['type', 'status', 'unique_id', 'call_order_token'], 'string'],
            [['contact_id', 'phone_number', 'total_time', 'answered_time', 'attitude_level', 'tag_id'], 'integer'],
            [['record'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'date_time' => 'Date Time',
            'type' => 'Type',
            'contact_id' => 'Contact ID',
            'phone_number' => 'Phone Number',
            'record' => 'Record',
        ];
    }

    public static function getTableColumns() {
        return [
            'id' => '`c`.`id`',
            'date' => 'DATE_FORMAT(`c`.`date_time`, "%Y-%m-%d")',
            'time' => 'DATE_FORMAT(`c`.`date_time`, "%H-%i-%S")',
            'type' => '`c`.`type`',
            'manager' => '`u`.`firstname`',
            'contact_id' => '`c`.`contact_id`',
            'phone_number' => '`c`.`phone_number`',
            'contact' => '`ct`.`name`',
            'record' => '`c`.`record`',
            'status' => '`c`.`status`',
            //'missed_call_id' => '`mc`.`id`',
        ];
    }

    public static function getCallStatuses()
    {
        return [
            ['name' => self::CALL_STATUS_ANSWERED.'_'.self::CALL_STATUS_MISSED.'|'.self::CALL_INCOMING, 'label' => 'Исходящий'],
            ['name' => self::CALL_STATUS_ANSWERED.'|'.self::CALL_OUTGOING, 'label' => 'Входящий'],
            ['name' => self::CALL_STATUS_MISSED.'|'.self::CALL_OUTGOING, 'label' => 'Пропущенный'],
            ['name' => self::CALL_STATUS_FAILURE.'|'.self::CALL_INCOMING.'_'.self::CALL_OUTGOING, 'label' => 'Сбой'],
        ];
    }

    public static function getAttitudeLevels()
    {
        return [
            ['name' => 1, 'label' => '--'],
            ['name' => 2, 'label' => '-'],
            ['name' => 3, 'label' => '+-'],
            ['name' => 4, 'label' => '+'],
            ['name' => 5, 'label' => '++'],
        ];
    }

    public static function getAttitubeLevelLabel($level) {
        switch ($level) {
            case '1':
                $label = '-2';
                break;
            case '2':
                $label = '-1';
                break;
            case '3':
                $label = '0';
                break;
            case '4':
                $label = '1';
                break;
            case '5':
                $label = '2';
                break;
            default:
                $label = '';
                break;
        }
        return $label;
    }

    public static function buildSelectQuery() {
        $columns = self::getTableColumns();
        $select = [];
        foreach ($columns as $alias => $column) {
            $select[] = $column . " as " . $alias;
        }
        return $select;
    }

    public function incoming($unique_id, $contact_id, $phone_number, $call_order_token, $tag_id) {
        $this->unique_id = $unique_id;
        $this->date_time = date('Y-m-d H:i:s');
        $this->type = Call::CALL_INCOMING;
        $this->phone_number = $phone_number;
        $this->contact_id = $contact_id;
        $this->status = Call::CALL_STATUS_NEW;
        $this->call_order_token = $call_order_token;
        if (!$contact_id) {
            $this->sended_crm = 1;
        }
        //$this->tag_id = $tag_id;
        return $this->save();
    }

    public function outgoing($unique_id, $contact_id, $phone_number) {
        $this->unique_id = $unique_id;
        $this->date_time = date('Y-m-d H:i:s');
        $this->type = Call::CALL_OUTGOING;
        $this->phone_number = $phone_number;
        $this->contact_id = $contact_id;
        $this->status = Call::CALL_STATUS_NEW;
        if (!$contact_id) {
            $this->sended_crm = 1;
        }
        return $this->save();
    }

    public static function getByUniquelId($unique_id) {
        return self::find()->where(['unique_id' => $unique_id])->one();
    }

    /*
     *  $status NO ANSWER | FAILED | BUSY | ANSWERED | UNKNOWN | CONGESTION
     */

    public function callEnd($date_time, $total_time, $answered_time, $record_file, $status, $managers_id, $tag_id) {
        $this->date_time = date('Y-m-d H:i:s', strtotime($date_time));
        if ($status == 'ANSWERED') {
            $this->total_time = $total_time;
            $this->answered_time = $answered_time;
            $this->record = $record_file;
            $this->status = Call::CALL_STATUS_ANSWERED;
        } else if ($status == 'NO ANSWER') {
            $this->status = Call::CALL_STATUS_MISSED;
        } else {
            $this->status = Call::CALL_STATUS_FAILURE;
        }
        $this->tag_id = $tag_id;
        $managers = $this->setManagersForCall($managers_id, $status);
        TempContactsPool::clearForManagers($managers);
        if ($this->save()) {
            if ($this->attitude_level !== null) {
                $this->sendToCRM($managers[0]);
            }
            return true;
        }
        return false;
    }

    public function sendToCRM($manager) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch,CURLOPT_HTTPHEADER,array('X-Amz-Meta-Crm-Api-Token: 6e5b4d74875ea09f3f888601c7825211'));

        $crm_host = Yii::$app->params['crm_host'];
        $url = $crm_host."/api/v1/callcenter/calls";
        $calls['PhoneNumber'] = $this->phone_number;
        $calls['Type'] = $this->type;
        $calls['DateTime'] = strtotime($this->date_time);
        $calls['Status'] = $this->status;
        $calls['TotalTime'] = $this->total_time;
        $calls['Comment'] = $this->comment;
        $calls['Emotion'] = Call::getAttitubeLevelLabel($this->attitude_level);
        $calls['InternalNo'] = $manager->int_id;
        $calls['AudioLink'] = Yii::$app->params['call_crm_host'].$this->record;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $calls);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($calls));
        //curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($calls));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec ($ch);

        //TODO temp
        $response_log_data = $response;
        if ($response == false) {
            $response_log_data = curl_error($ch);
        }
        curl_close ($ch);
        $request_data = urldecode(http_build_query($calls));
        $log_data = date("j-m-Y G:i:s", time()). "\r\n" . "Request: " .$request_data . "\r\n\r\n";
        file_put_contents(Yii::getAlias('@runtime_log_folder') . '/api_export_call.log', $log_data, FILE_APPEND);
        file_put_contents(Yii::getAlias('@runtime_log_folder') . '/api_export_call.log', "Response: ". $response_log_data."\r\n", FILE_APPEND);
        file_put_contents(Yii::getAlias('@runtime_log_folder') . '/api_export_call.log', "=============================================\r\n\r\n", FILE_APPEND);

        try {
            if (!is_array($response)) {
                $response = (array)json_decode($response);
            }
            if (!isset($response['Status']) || $response['Status'] == 0) {
                FailExportCall::add($this->id);
                return false;
            } else {
                $this->sended_crm = 1;
                $this->save();
            }
        } catch(Exception $e) {
            FailExportCall::add($this->id);
            return false;
        }

        return true;
    }


    public function setManagersForCall($managers_id, $status) {
        $contact_manager = null;
        $managers_id_array = array_map('trim', explode(',', $managers_id));
        $managers = User::find()->where(['int_id' => $managers_id_array])->all();
        foreach ($managers as $manager) {
            if ($status === "NO ANSWER") {
                $missed_call = new MissedCall();
                $missed_call->add($this->id, $manager->id);
                $manager_notification = new ManagerNotification();
                $manager_notification->add($this->date_time, 'call_missed', $manager->id, $this->phone_number, $this->contact_id);
            }            
            $call_manager = new CallManager();
            $call_manager->call_id = $this->id;
            $call_manager->manager_id = $manager->id;
            $call_manager->save();
        }
        if ($this->contact_id) {
            $contact_manager = Contact::getManagerById($this->contact_id);
            if ($contact_manager) {
                if ($status !== "NO ANSWER") {
                    if (array_search($contact_manager->int_id, $managers_id_array) === false) {
                        $manager_notification = new ManagerNotification();
                        $manager_notification->add($this->date_time, 'call_missed', $contact_manager->id, $this->phone_number, $this->contact_id);
                    }
                }
            }
        }
        return $managers;
    }

    public static function getCallAttitudeLabel($attitude)
    {
        $res = '';
        $mediana = 3;
        if ($attitude === $mediana) {
            $res = '+-';
        } else if ($attitude > $mediana) {
            for ($i = $mediana; $i < $attitude; $i++) {
                $res .= '+';
            }
        } else if ($attitude < $mediana) {
            for ($i = $mediana; $i > $attitude; $i--) {
                $res .= '-';
            }
        }
        return $res;
    }

    public static function getCallStatusLabel($type, $status)
    {
        $res = '';
        switch ($status) {
            case "answered":
                switch ($type) {
                    case "incoming":
                        $res = "Исходящий";
                        break;
                    case "outgoing":
                        $res = "Входящий";
                        break;
                }
                break;
            case "missed":
                switch ($type) {
                    case "incoming":
                        $res = "Исходящий - пропущенный";
                        break;
                    case "outgoing":
                        $res = "Пропущенный";
                        break;
                }
                break;
            case "failure":
                switch ($type) {
                    case "incoming":
                        $res = "Исходящий - сбой";
                        break;
                    case "outgoing":
                        $res = "Входящий - сбой";
                        break;
                }
                break;
        }
        return $res;
    }
    
    public function setContactIdByPhone($phone, $contact_id) {
        $this->updateAll(['contact_id' => $contact_id], ['phone_number' => $phone]);
    }

    public function getTag() {
        return $this->hasOne(Tag::className(), ['id' => 'tag_id']);
    }

    public function getMissedCall() {
        return $this->hasOne(MissedCall::className(), ['call_id' => 'id']);
    }

    public function getContact() {
        return $this->hasOne(Contact::className(), ['id' => 'contact_id']);
    }

    public function getCallManagers() {
        return $this->hasMany(CallManager::className(), ['call_id' => 'id']);
    }

    public function getManager() {
        return $this->hasMany(User::className(), ['id' => 'manager_id'])->viaTable('call_manager', ['call_id' => 'id']);
    }
}
