<?php

namespace app\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "contact_history".
 *
 * @property integer $id
 * @property integer $contact_id
 * @property string $text
 * @property string $type
 * @property integer $contact_show_id
 * @property integer $contact_visit_id
 * @property integer $contact_contract_id
 */
class ContactHistory extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'contact_history';
    }

    public function getContactVisit() {
        return $this->hasOne(ContactVisit::className(), ['id' => 'contact_visit_id'])
                        ->from(['cv' => ContactVisit::tableName()]);
    }

    public function getContactShow() {
        return $this->hasOne(ContactShow::className(), ['id' => 'contact_show_id'])
                        ->from(['cs' => ContactVisit::tableName()]);
    }

    public function getContactContract() {
        return $this->hasOne(ContactContract::className(), ['id' => 'contact_contract_id'])
                        ->from(['cc' => ContactVisit::tableName()]);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['contact_id', 'datetime'], 'required'],
            [['contact_id', 'contact_show_id', 'contact_visit_id', 'contact_contract_id'], 'integer'],
            [['text', 'type'], 'string'],
            [['datetime'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'contact_id' => 'Contact ID',
            'text' => 'Text',
            'datetime' => 'Date Time',
            'type' => 'Type',
        ];
    }

    public static function getByContactId($contact_id) {
        $query = new Query();
        $history = $query->select(['`ch`.*', '`cv`.`schedule_date` as "visit_schedule_date"', '`cs`.`schedule_date` as "show_schedule_date"'])
                ->from(self::tableName() . ' ch')
                ->join('LEFT JOIN', ContactVisit::tableName() . ' `cv`', '`ch`.`contact_visit_id` = `cv`.`id`')
                ->join('LEFT JOIN', ContactShow::tableName() . ' `cs`', '`ch`.`contact_show_id` = `cs`.`id`')
                ->where(['ch.contact_id' => $contact_id])
                ->orderBy(['ch.id' => SORT_ASC])
                ->all();
        $history_array = [];
        foreach ($history as $i => $_history) {
            $history_array[$i]['text'] = $_history['text'];
            $history_array[$i]['datetime'] = date("d-m-Y G:i:s", strtotime($_history['datetime']));
            switch ($_history['type']) {
                case "show":
                    $history_array[$i]['schedule_date'] = $_history['show_schedule_date'];
                    $history_array[$i]['contact_action_id'] = $_history['contact_show_id'];
                    break;
                case "visit":
                    $history_array[$i]['schedule_date'] = $_history['visit_schedule_date'];
                    $history_array[$i]['contact_action_id'] = $_history['contact_visit_id'];
                    break;
                case "contract":
                    $history_array[$i]['contact_action_id'] = $_history['contact_contract_id'];
                    break;
            }
            $history_array[$i]['type'] = $_history['type'];
        }
        return $history_array;
    }

    public function add($contact_id, $text, $contact_action_id, $type, $datetime = false) {
        $this->contact_id = $contact_id;
        $this->text = $text;
        $this->type = $type;
        switch ($type) {
            case "show":
                $this->contact_show_id = $contact_action_id;
                break;
            case "visit":
                $this->contact_visit_id = $contact_action_id;
                break;
            case "contract":
                $this->contact_contract_id = $contact_action_id;
                break;
        }
        if ($datetime) {
            $this->datetime = $datetime;
        } else {
            $this->datetime = date('Y-m-d H:i:s');
        }
        return $this->save();
    }

}