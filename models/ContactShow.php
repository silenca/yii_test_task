<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "contact_show".
 *
 * @property integer $id
 * @property integer $contact_id
 * @property integer $object_id
 * @property string $system_date
 * @property string $schedule_date
 */
class ContactShow extends ActiveRecord {

    public $history_text;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'contact_show';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['contact_id', 'system_date'], 'required'],
            [['contact_id', 'manager_id'], 'integer'],
            ['schedule_date', 'date', 'format' => 'yyyy-M-d H:m:s'],
            [['system_date'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'contact_id' => 'Contact ID',
            'object_id' => 'Object ID',
            'system_date' => 'System Date',
            'schedule_date' => 'Schedule Date',
        ];
    }

    public function add($contact_id, $objects_id, $schedule_date = null) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->contact_id = $contact_id;
            $this->system_date = date('Y-m-d G:i:s', time());

            $action = new Action();
            $action_type = ActionType::find()->where(['name' => 'show'])->one();
            $action->add($contact_id, $action_type->id, $objects_id, $schedule_date);

            if (strlen($schedule_date) > 0) {
                $this->schedule_date = date('Y-m-d G:i:s', strtotime($schedule_date));
            }
            $action->addManagerNotification($action->id, $this->system_date, 'show', $this->manager_id, $this->contact_id);
            $this->save();

            foreach ($objects_id as $object_id) {
                $contact_show_object = new ContactShowObject();
                $contact_show_object->contact_show_id = $this->id;
                $contact_show_object->object_id = $object_id;
                $contact_show_object->save();
            }

            $object_apartments = ObjectApartment::find()->where(['in', 'id', $objects_id])->all();
            $history_text = $this->buildHistory($object_apartments, $schedule_date);
            $contact_history = new ContactHistory();
            $contact_history->add($contact_id, $history_text, $this->id, 'show', $this->system_date);
            $this->setHistoryText($history_text);
            $transaction->commit();
            return true;
        } catch (\Exception $ex) {
            $transaction->rollback();
            return false;
        }
    }

    private function buildHistory($objects, $schedule_date) {
        $history_text = "Запланирован показ";
        foreach ($objects as $object) {
            $history_text.= " <a href='" . $object->link . "' target='_blank'>Объекта</a>,";
        }
        $history_text = rtrim($history_text, ",");
        if ($schedule_date) {
            $history_text.=" на " . date('d-m-Y G:i:s', strtotime($schedule_date));
        }
        return $history_text;
    }

    public function setHistoryText($history_text) {
        $this->history_text = $history_text;
    }

    public function getHistoryText() {
        return $this->history_text;
    }

}
