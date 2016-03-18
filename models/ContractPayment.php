<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contract_payment".
 *
 * @property integer $id
 * @property integer $contract_id
 * @property string $system_date
 * @property integer $manager_id
 * @property double $amount
 * @property string $comment
 *
 * @property ContactContract $contract
 * @property User $manager
 */
class ContractPayment extends \yii\db\ActiveRecord {
    
    public $history_text;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'contract_payment';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['contract_id', 'system_date', 'manager_id', 'amount'], 'required'],
            [['contract_id', 'manager_id'], 'integer'],
            [['system_date'], 'safe'],
            [['amount'], 'number'],
            [['comment'], 'string'],
            [['contract_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContactContract::className(), 'targetAttribute' => ['contract_id' => 'id']],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['manager_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'contract_id' => 'Contract ID',
            'system_date' => 'System Date',
            'manager_id' => 'Manager ID',
            'amount' => 'Amount',
            'comment' => 'Comment',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContract() {
        return $this->hasOne(ContactContract::className(), ['id' => 'contract_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager() {
        return $this->hasOne(User::className(), ['id' => 'manager_id']);
    }

    public function add($contact_id, $contract_id, $amount, $comment = null) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->contract_id = $contract_id;
            $this->amount = $amount;
            $this->comment = $comment;
            $this->system_date = date('Y-m-d H:i:s');
            $this->save();
            $action = new Action();
            $action_type = ActionType::find()->where(['name' => 'payment'])->one();
            $contract = ContactContract::find()->with('object')->where(['id' => $contract_id])->one();
            $object = $contract->object;
            $action->add($contact_id, $action_type->id, [$object->id]);
            $contact_history = new ContactHistory();
            $history_text = $this->buildHistory($object, $amount, $comment);
            $contact_history->add($contact_id, $history_text, $this->id, 'payment', $this->system_date);
            $this->setHistoryText($history_text);

            $transaction->commit();
            return true;
        } catch (Exception $ex) {
            $transaction->rollback();
            return false;
        }
    }
    
    private function buildHistory($object, $amount, $comment = null) {
        $history_text = "Платеж по объекту <a href='" . $object->link . "' target='_blank'>".$object->number."</a>";
        if ($comment) {
            $history_text.=" Комментарий: '".$comment."'";
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
