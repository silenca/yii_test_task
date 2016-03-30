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
        $rules = parent::rules();
        $rules[] = [['first_phone', 'second_phone', 'third_phone', 'fourth_phone'], 'filter', 'filter' => function($value) {
            $value = preg_replace("/[^a-zA-Z0-9]/i","", $value);
            if (strlen($value) == 11 || preg_match('/^(8)/', $value)) {
                $value = preg_replace('/^.?/s', '7', $value);
            }
            return $value;
        }, 'skipOnEmpty' => true];
        return $rules;
    }

    public function formName()
    {
        return 'import_contact';
    }

    public function phoneArray($attribute, $params)
    {
        $phones = self::dataConvert($this->$attribute, 'phones', 'explode');
        foreach ($phones as $phone_key => $phone_val) {
            $this->checkPhone($phone_val, $attribute);
            $fields = Contact::getPhoneCols();
            $this->isUnique($phone_val, $attribute, $fields, function($attr, $value, $int_contact_id) {
                $this->addError($attr, $value.', уже существует в базе. ID контакта: '.$int_contact_id);
            });
            $this->$phone_key = $phone_val;
        }
    }

    public function emailArray($attribute, $params)
    {
        $emails = self::dataConvert($this->$attribute, 'emails', 'explode');
        foreach ($emails as $email_key => $email_val) {
            $this->checkEmail($email_val, $attribute);
            $fields = Contact::getEmailCols();
            $this->isUnique($email_val, $attribute, $fields, function($attr, $value, $int_contact_id) {
                $this->addError($attr, $value.', уже существует в базе. ID контакта: '.$int_contact_id);
            });
            $this->$email_key = $email_val;
        }
    }

    public function checkPhone($phone, $attribute)
    {
        if ($phone !== null) {
            if (!preg_match('/^(7|8)\d{10}$/', $phone)) {
                if ($this->getFirstError($attribute) == null) {
                    $this->addError($attribute, 'ошибка - номер не из России');
                }
            }
        }
    }
}
