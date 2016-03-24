<?php

namespace app\models\forms;

use app\models\Contact;
use Yii;
use yii\base\Model;
use yii\validators\EmailValidator;

/**
 * ContactForm is the model behind the contact form.
 */
class ContactForm extends Model
{

    var $name;
    var $surname;
    var $middle_name;
    var $phones;
    var $emails;

    public static function getAllCols() {
        return [
            'surname',
            'name',
            'phones',
            'middle_name',
            'emails',
            'country',
            'region',
            'area',
            'city',
            'street',
            'house',
            'flat'
        ];
    }

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

    public function rules()
    {
        return [
            [['name', 'surname', 'phones'], 'requiredForContact'],
            [['phones'], 'phoneArray'],
            [['emails'], 'emailArray'],
            [['name', 'surname', 'middle_name'], 'string', 'length' => [4, 150],
                'tooShort' => '{attribute} должен содержать больше {min} символов',
                'tooLong' => '{attribute} должен содержать до {max} символов'
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'middle_name' => 'Отчество',
            'phones' => 'Номер телефона',
            'emails' => 'Email'
        ];
    }

    public function formName()
    {
        return 'contact';
    }

    public function requiredForContact($attribute, $params)
    {
        if (empty($this->name) || empty($this->surname) || empty($this->phones)) {
            $this->addError($attribute, 'Необходимо заполнить ФИО и телефон');
        }
    }

    public static function dataConvert($data, $type, $action) {
        if ($action == 'explode') {
            $res_data = [];
            $data = array_map('trim', explode(',', $data));
            if ($type == 'phones') {
                $data = array_map(function($el) {
                    return preg_replace("/[^a-zA-Z0-9]/i","", $el);
                }, $data);
                $data_cols = Contact::getPhoneCols();
            } else {
                $data_cols = Contact::getEmailCols();
            }
            $count = 0;
            foreach ($data_cols as $col) {
                if (isset($data[$count])) {
                    $res_data[$col] = $data[$count];
                } else {
                    $res_data[$col] = null;
                }
                $count++;
            }
        } elseif ($action == 'implode') {
            $res_data = '';
            $res_data = implode(', ', $data);
        }
        return $res_data;
    }

    public function phoneArray($attribute, $params)
    {
        $phones = self::dataConvert($this->$attribute, 'phones', 'explode');
        foreach ($phones as $phone) {
            $this->checkPhone($phone, $attribute);
        }
    }

    public function checkPhone($phone, $attribute)
    {
        if ($phone !== null) {
            if (!preg_match('/^\d*$/', $phone)) {
                if ($this->getFirstError($attribute) == null) {
                    $this->addError($attribute, 'Телефон не должен содержать буквенные символы');
                }
            } elseif (strlen($phone) == 10) {
                $this->addError($attribute, 'Код страны не введен. Код России: 7');
            } elseif (strlen($phone) < 10 || strlen($phone) > 15) {
                $this->addError($attribute, 'Телефон заполнен некорректно');
            }
//            elseif (!preg_match('/^(8|7|\+7)/', $phone)) {
//                if ($this->getFirstError($attribute) == null) {
//                    $this->addError($attribute, 'Код страны введен не верно');
//                }
//            } elseif (!preg_match('/^(8|7|\+7)((\d{10})|(\s\(\d{3}\)\s\d{3}\s\d{2}\s\d{2}))/', $phone)) {
//                if ($this->getFirstError($attribute) == null) {
//                    $this->addError($attribute, 'Телефон заполнен некорректно');
//                }
//            }
        }
    }

    public function checkEmail($email, $attribute)
    {
        if ($email !== null) {
            $email_validator = new EmailValidator();
            if (!$email_validator->validate($email)) {
                if ($this->getFirstError($attribute) == null) {
                    $this->addError($attribute, 'Email введен не верно');
                }
            }
        }

    }

    public function emailArray($attribute, $params)
    {
        $emails = self::dataConvert($this->$attribute, 'emails', 'explode');
        foreach ($emails as $email) {
            $this->checkEmail($email, $attribute);
        }
    }

}
