<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use yii\validators\EmailValidator;
use yii\validators\NumberValidator;

/**
 * ContactForm is the model behind the contact form.
 */
class ContactForm extends Model {

    var $name;
    var $surname;
    var $middle_name;
    var $phones;
    var $emails;

    /*'name',
    'surname',
    'middle_name',
    'first_phone',
    'second_phone',
    'third_phone',
    'fourth_phone',
    'first_email',
    'second_email',
    'country',
    'region',
    'area',
    'city',
    'street',
    'house',
    'flat',
    'status'
    */

    public function rules() {
        return [
            [['name', 'surname', 'phones'], 'requiredForContact'],
            [['phones'], 'phoneArray'],
            [['emails'], 'emailArray'],
            [['name', 'surname', 'middle_name'], 'string', 'length' => [4, 150],
                'tooShort' => '{attribute} должен содержать больше {min} символов',
                'tooLong' => '{attribute} должен содержать до {max} символов'
            ],
//            [['first_email', 'second_email'], 'email', 'message' => '{attribute} введен не корректно'],
        ];
    }

    public function attributeLabels() {
        return [
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'middle_name' => 'Отчество',
            'first_phone' => 'Номер телефона',
            'second_phone' => 'Номер телефона',
            'third_phone' => 'Номер телефона',
            'fourth_phone' => 'Номер телефона',
            'first_email' => 'Email',
            'second_email' => 'Email',
        ];
    }

    public function formName() {
        return 'contact';
    }

    public function requiredForContact($attribute, $params) {
        $this->addError($attribute, 'Необходимо заполнить имя и телефон');
        if (empty($this->name) || empty($this->surname) || empty($this->phones)) {
            $this->addError($attribute, 'Необходимо заполнить имя и телефон');
        }
    }

    public function phoneArray($attribute, $params) {
        if (!empty($attribute)) {
            $validator = new NumberValidator();
            $phones = array_map('trim', explode(',', $this->$attribute));
//            $phones = explode(',', $this->$attribute);
            foreach ($phones as $phone) {
                $phone = trim($phone);
                if (!$validator->validate($phone, $error)) {
                    $this->addError($attribute, 'Телефон заполнен некорректно');
                }
            }
        }


        if (!empty($attribute)) {
            $phones = explode(',', $this->$attribute);

            foreach ($phones as $phone) {
                $phone = trim($phone);

//                if (!$this->checkPhone($phone)) {
//                    if ($this->getFirstError($attribute) == null) {
//                        $this->addError($attribute, 'Телефон заполнен некорректно');
//                    }
//                }
//                if (!is_numeric($phone) || strlen($phone) < 10) {
//                    if ($this->getFirstError($attribute) == null) {
//                        $this->addError($attribute, 'Телефон заполнен некорректно');
//                    }
//                }
            }
//            if (isset($phones[0])) {
//                $phones[0] = trim($phones[0]);
//                if (!is_numeric($phones[0]) || $phones[0] < 1 || strlen($phones[0]) < 7) {
//                    $this->addError($attribute, 'Телефон заполнен некорректно');
//                }
//            }
        }
    }

    public function checkPhone($phone) {
        return preg_match("/^\+?([87](?!95[4-79]|99[^2457]|907|94[^0]|336)([348]\d|9[0-689]|7[027])\d{8}|[1246]\d{9,13}|68\d{7}|5[1-46-9]\d{8,12}|55[1-9]\d{9}|500[56]\d{4}|5016\d{6}|5068\d{7}|502[45]\d{7}|5037\d{7}|50[457]\d{8}|50855\d{4}|509[34]\d{7}|376\d{6}|855\d{8}|856\d{10}|85[0-4789]\d{8,10}|8[68]\d{10,11}|8[14]\d{10}|82\d{9,10}|852\d{8}|90\d{10}|96(0[79]|17[01]|13)\d{6}|96[23]\d{9}|964\d{10}|96(5[69]|89)\d{7}|96(65|77)\d{8}|92[023]\d{9}|91[1879]\d{9}|9[34]7\d{8}|959\d{7}|989\d{9}|97\d{8,12}|99[^456]\d{7,11}|994\d{9}|9955\d{8}|996[57]\d{8}|380[34569]\d{8}|381\d{9}|385\d{8,9}|375[234]\d{8}|372\d{7,8}|37[0-4]\d{8}|37[6-9]\d{7,11}|30[69]\d{9}|34[67]\d{8}|3[12359]\d{8,12}|36\d{9}|38[1679]\d{8}|382\d{8,9})$/", preg_replace("/[^0-9]/i","", $phone));
    }

    public function emailArray($attribute, $params) {
        $valids = $this->activeValidators;
        if (!empty($attribute)) {
            $emails = explode(',', $this->$attribute);
            foreach ($emails as $email) {
                $email = trim($email);
//                if ($this->checkPhone($phone)) {
//                    if ($this->getFirstError($attribute) == null) {
//                        $this->addError($attribute, 'Телефон заполнен некорректно');
//                    }
//                }
//                if (!is_numeric($phone) || strlen($phone) < 10) {
//                    if ($this->getFirstError($attribute) == null) {
//                        $this->addError($attribute, 'Телефон заполнен некорректно');
//                    }
//                }
            }
//            if (isset($phones[0])) {
//                $phones[0] = trim($phones[0]);
//                if (!is_numeric($phones[0]) || $phones[0] < 1 || strlen($phones[0]) < 7) {
//                    $this->addError($attribute, 'Телефон заполнен некорректно');
//                }
//            }
        }
    }

}
