<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class CallForm extends Model {

    //const SCENARIO_CALLSTART = 'call_start';
    const SCENARIO_CALLSTART = 'call_start';
    const SCENARIO_CALLEND = 'call_end';

    var $uniqueid;
    var $callerid;
    var $answered;
    var $datetime;
    var $totaltime;
    var $answeredtime;
    var $status;
    var $record_file;

    public function rules() {
        return [
            [['callerid', 'answered', 'uniqueid'], 'required', 'on' => self::SCENARIO_CALLSTART],
            //[['callerid', 'answered'], 'string', 'length' => [3, 10], 'on' => self::SCENARIO_CALLSTART],
            [['callerid', 'answered'], 'integer', 'on' => self::SCENARIO_CALLSTART],
            
            
            [['uniqueid'], 'string', 'length' => [6, 30]],
            [['uniqueid', 'datetime', 'status','answered'], 'required', 'on' => self::SCENARIO_CALLEND],
            [['callerid', 'answered'], 'string', 'on' => self::SCENARIO_CALLEND],
            [['totaltime', 'answeredtime'], 'integer', 'on' => self::SCENARIO_CALLEND],
            //[['callerid'], 'checkNumbers', 'on' => self::SCENARIO_CALLEND],
        ];
    }

    public function attributeLabels() {
        return [
            'callerid' => 'Номер звонящего',
            'answered' => 'Набранный номер',
            'uniqueid' => 'Уникальный ID',
            'datetime' => 'Время звонка',
            'totaltime' => 'Общее время звонка',
            'answeredtime' => 'Время разговора',
            'status' => 'Статус разговора',
            'record_file' => 'Файл записи',
        ];
    }

    public function formName() {
        return '';
    }

    public function checkNumbers($attribute, $params) {
        if ($this->status === "ANSWERED") {
            if (strlen($this->callerid) !== 3 && strlen($this->answered) !== 3) {
                $this->addError($attribute, 'Phones mistyped');
            }
        }
    }

}
