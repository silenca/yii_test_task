<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contact_visit".
 *
 * @property integer $id
 * @property integer $contact_id
 * @property string $system_date
 * @property string $schedule_date
 */
class ContactVisit extends \yii\db\ActiveRecord {

    public $history_text;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'contact_visit';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['contact_id'], 'required'],
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
            'system_date' => 'System Date',
            'schedule_date' => 'Schedule Date',
        ];
    }

    public function add($contact_id, $schedule_date) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->contact_id = $contact_id;
            $this->system_date = date('Y-m-d G:i:s', time());

            $action = new Action();
            $action_type = ActionType::find()->where(['name' => 'visit'])->one();
            $action->add($contact_id, $action_type->id, [], $schedule_date);

            if (strlen($schedule_date) > 0) {
                $this->schedule_date = date('Y-m-d G:i:s', strtotime($schedule_date));
                $action->addManagerNotification($action->id, $this->system_date, 'visit', $this->manager_id, $this->contact_id);
            }
            $this->save();

            $contact_history = new ContactHistory();
            $history_text = $this->buildHistory($schedule_date);
            $contact_history->add($contact_id, $history_text, $this->id, 'visit', $this->system_date);
            $this->setHistoryText($history_text);
            $transaction->commit();
            return true;
        } catch (\Exception $ex) {
            $transaction->rollback();
            return false;
        }
    }

    private function buildHistory($schedule_date) {
        $history_text = "Визит клиента";
        if (strlen($schedule_date) > 0) {
            $history_text.=" на " . date('d-m-Y G:i:s', strtotime($schedule_date));
        } else {
            $history_text.=" сейчас";
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
