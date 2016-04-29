<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contact_call".
 *
 * @property integer $id
 * @property integer $contact_id
 * @property string $system_date
 * @property integer $manager_id
 *
 * @property Contact $contact
 * @property User $manager
 */
class ContactRingRound extends \yii\db\ActiveRecord {
    
    public $history_text;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'contact_ring_round';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['contact_id', 'system_date', 'manager_id'], 'required'],
            [['contact_id', 'manager_id'], 'integer'],
            [['system_date'], 'safe'],
            [['contact_id'], 'exist', 'skipOnError' => true, 'targetClass' => Contact::className(), 'targetAttribute' => ['contact_id' => 'id']],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['manager_id' => 'id']],
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
            'manager_id' => 'Manager ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContact() {
        return $this->hasOne(Contact::className(), ['id' => 'contact_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager() {
        return $this->hasOne(User::className(), ['id' => 'manager_id']);
    }

    public function add($contact_id, $action_comment_text, $call_order_token, $attitude_level) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->contact_id = $contact_id;
            $this->system_date = date('Y-m-d G:i:s', time());
            $action = new Action();
            $action_type = ActionType::find()->where(['name' => 'ring_round'])->one();
            $action->add($contact_id, $action_type->id, []);
            if (!is_null($action_comment_text)) {
                $action_comment = new ActionComment(['comment' => $action_comment_text]);
                $action_comment->save();
                $action->link('comment', $action_comment);
            }
            if (!is_null($call_order_token)) {
                $call = Call::findOne(['call_order_token' => $call_order_token]);
                if ($call) {
                    $call->attitude_level = $attitude_level;
                    $call->save();
                }
            }

            $this->save();

            $contact_history = new ContactHistory();
            $history_text = $this->buildHistory();
            $contact_history->add($contact_id, $history_text, 'ring_round', $this->system_date);
            $this->setHistoryText($history_text);
            $transaction->commit();
            return true;
        } catch (\Exception $ex) {
            $transaction->rollBack();
            return false;
        }
    }

    private function buildHistory() {
        $history_text = "Прозвон контакта";
        return $history_text;
    }

    public function setHistoryText($history_text) {
        $this->history_text = $history_text;
    }

    public function getHistoryText() {
        return $this->history_text;
    }

}
