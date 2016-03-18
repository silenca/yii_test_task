<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class PaymentForm extends Model {

    public $id;
    public $contract;
    public $amount;
    public $comment;

    public function rules() {
        return [
            [['id', 'contract', 'amount'], 'required'],
            [['id', 'contract'], 'integer'],
            [['amount'], 'float'],
            [['comment'], 'string'],
        ];
    }

    public function formName() {
        return '';
    }

    public function float($attribute, $params) {
        if (!is_numeric($this->amount)) {
            $this->addError($attribute, 'Сумма введена не корректно');
        }
    }

    public function attributeLabels() {
        return [
            'id' => 'ID контакта',
            'contract' => 'ID договора',
            'amount' => 'Сумма платежа',
            'comment' => 'Комментарий',
        ];
    }

}
