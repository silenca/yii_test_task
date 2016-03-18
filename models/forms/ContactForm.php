<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class ContactForm extends Model {

    var $first_name;
    var $second_name;
    var $first_phone;
    var $second_phone;
    var $first_email;
    var $second_email;

    /*
     * first_name
     * second_name
     * first_email
     * second_email
     * first_mobile
     * first_landline
     * second_mobile
     * second_landline
     * channel_attraction_id
     * language
     * status
     * distribution
     */

    public function rules() {
        return [
            [['first_name', 'second_name', 'first_phone', 'second_phone'], 'requiredForContact'],
            [['first_phone', 'second_phone'], 'phoneArray'],
            [['first_name', 'second_name'], 'string', 'length' => [4, 150],
                'tooShort' => '{attribute} должен содержать больше {min} символов',
                'tooLong' => '{attribute} должен содержать до {max} символов'
            ],
            [['first_email', 'second_email'], 'email', 'message' => '{attribute} введен не корректно'],
        ];
    }

    public function attributeLabels() {
        return [
            'first_name' => 'Имя 1',
            'second_name' => 'Имя 2',
            'first_phone' => 'Номер телефона',
            'second_phone' => 'Номер телефона',
            'first_email' => 'Email',
            'second_email' => 'Email',
            'channel_attraction_id' => 'Канал',
            'status' => 'Статус',
            'language' => 'Язык',
        ];
    }

    public function formName() {
        return '';
    }

    public function requiredForContact($attribute, $params) {
        if (empty($this->first_name) || empty($this->first_phone) && (empty($this->second_name) || empty($this->second_phone))) {
            $this->addError($attribute, 'Необходимо заполнить имя и телефон');
        }
    }

    public function phoneArray($attribute, $params) {
        if (!empty($attribute)) {
            $phones = explode(',', $this->$attribute);
            if (isset($phones[0])) {
                $phones[0] = trim($phones[0]);
                if (!is_numeric($phones[0]) || $phones[0] < 1 || strlen($phones[0]) < 7) {
                    $this->addError($attribute, 'Телефон заполнен некорректно');
                }
            }
            if (isset($phones[1])) {
                $phones[1] = trim($phones[1]);
                if (!is_numeric($phones[1]) || $phones[1] < 1 || strlen($phones[1]) < 7) {
                    $this->addError($attribute, 'Телефон заполнен некорректно');
                }
            }
        }
    }

}
