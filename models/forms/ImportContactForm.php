<?php

namespace app\models\forms;

use app\models\Contact;
use Yii;
use yii\base\Model;
use yii\validators\EmailValidator;
use app\components\Filter;

/**
 * ContactForm is the model behind the contact form.
 */
class ImportContactForm extends ContactForm
{

    public $isApi = false;

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
            'flat',
            'tags_str'
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
        $rules = parent::rules();
        $new_rule = [['first_phone', 'second_phone', 'third_phone', 'fourth_phone'], 'filter', 'filter' => function($value) {
            $value = preg_replace("/[^a-zA-Z0-9]/i","", $value);
            if (strlen($value) == 11 || preg_match('/^(8)/', $value)) {
                $value = preg_replace('/^.?/s', '7', $value);
            }
            return $value;
        }, 'skipOnEmpty' => true];
        array_splice( $rules, 2, 0, [$new_rule] );
        return $rules;
    }

    public function requiredForContact($attribute, $params)
    {
        if (empty($this->phones)) {
            $this->addCustomError($attribute, 'Необходимо заполнить телефон');
        }
    }

    public function formName()
    {
        return 'import_contact';
    }

    public function phoneArray($attribute, $params)
    {
        $phones = self::dataConvert($this->$attribute, 'phones');
        foreach ($phones as $phone_key => $phone_val) {
            if (!$this->isApi || ($this->isApi && $phone_key == 'first_phone')) {
                $this->checkPhone($phone_val, $attribute);
            }

            $this->$phone_key = $phone_val;
        }
    }

    public function emailArray($attribute, $params)
    {
        $emails = self::dataConvert($this->$attribute, 'emails');
        foreach ($emails as $email_key => $email_val) {
            $this->checkEmail($email_val, $attribute);
//            $fields = Contact::getEmailCols();
//            $this->isUnique($email_val, $attribute, $fields, function($attr, $value, $int_contact_id) {
//                $this->addError($attr, 'Ошибка: email (' . $value . ') уже существует в базе. ID контакта: ' . $int_contact_id);
//            });
            $this->$email_key = $email_val;
        }
    }

    public function checkPhone($phone, $attribute)
    {
//        if ($phone !== null) {
//            if (strlen($phone) != 11) {
//                $this->addError($attribute, 'Ошибка: номер (' . $phone . ') записан в ненадлежащем формате.');
//                return false;
//            }
//        }
//
//        $firstNumber = $phone[0];
//        if ($firstNumber !== null) {
//            if ($firstNumber == '8') {
//                $phone[0] = '7';
//            } else if ($firstNumber !== '7') {
//                $this->addError($attribute, 'Ошибка: номер (' . $phone . ') не принадлежит номерной ёмкости РФ.');
//                return false;
//            }
//        }
        if (!preg_match('/^\+?\d{10,}$/', $phone)) {
            $this->addCustomError($attribute, 'Телефон должен содержать только цифры (не менее 10 цифр)');
            return false;
        }
        $fields = Contact::getPhoneCols();
        $conflictId = $this->isUnique($phone, $attribute, $fields, function($attr, $value, $int_contact_id) use ($attribute) {
            //if (!$this->isApi || ($this->isApi && $phone_key == "first_phone")) {
            $this->addError($attr, 'Ошибка: телефон (' . $value . ') уже существует в базе. ID контакта: ' . $int_contact_id);
            //}
        });
        if ($conflictId !== true) {
            return false;
        }
        return true;
    }
}
