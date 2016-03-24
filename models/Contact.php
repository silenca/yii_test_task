<?php

namespace app\models;

use Yii;
use app\components\UtilHelper;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "contact".
 *
 * @property integer $id
 * @property integer $int_id
 * @property string $name
 * @property string $surname
 * @property string $middle_name
 * @property string $first_phone
 * @property string $second_phone
 * @property string $third_phone
 * @property string $fourth_phone
 * @property string $first_email
 * @property string $second_email
 * @property string $country
 * @property string $region
 * @property string $area
 * @property string $city
 * @property string $street
 * @property string $house
 * @property string $flat
 * @property string $status
 * @property integer $manager_id
 * @property integer $is_deleted
 */
class Contact extends \yii\db\ActiveRecord {

    public static $safe_fields = [
        'int_id',
        'name',
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
        'status',
        'manager_id',
        'is_deleted'
    ];
    private $new_phones = [];
    private $new_emails = [];

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'contact';
    }

    public function getManager() {
        return $this->hasOne(User::className(), ['id' => 'manager_id'])->andWhere([
                    'role' => User::ROLE_MANAGER
        ]);
    }

    public function beforeValidate() {
        if ($this->isNewRecord) {
            $this->int_id = UtilHelper::getRandomNumbers(7);
        }
        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['int_id'], 'required'],
            [['int_id', 'manager_id'], 'integer'],
            [['first_phone','second_phone','third_phone','fourth_phone','first_email','second_email','country','region','area','city','street','house','flat','status'], 'string', 'max' => 255],
            [['name', 'surname', 'middle_name'], 'string', 'max' => 150],
            [['first_email', 'second_email'], 'string', 'max' => 255],
        ];
    }

    public static function getTableColumns() {
        return [
            1 => 'int_id',
            2 => 'surname',
            3 => 'name',
            4 => 'middle_name',
            5 => '',
            6 => 'first_phone',
            7 => 'first_email',
            8 => 't.name',
        ];
    }

    public static function getAllCols() {
        return [
            'name',
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
            'flat'
        ];
    }

    public static function getFIOCols() {
        return [
            'surname',
            'name',
            'middle_name'
        ];
    }

    public static function getPhoneCols() {
        return [
            'first_phone',
            'second_phone',
            'third_phone',
            'fourth_phone'
        ];
    }

    public function getPhoneColsWithVal() {
        $phones = [];
        isset($this->first_phone) ? $phones['first_phone'] = $this->first_phone : null;
        isset($this->second_phone) ? $phones['second_phone'] = $this->second_phone : null;
        isset($this->third_phone) ? $phones['third_phone'] = $this->third_phone : null;
        isset($this->fourth_phone) ? $phones['fourth_phone'] = $this->fourth_phone : null;
        return $phones;
    }

    public static function getEmailCols() {
        return [
            'first_email',
            'second_email',
        ];
    }

    public function getEmailColsWithVal() {
        $emails = [];
        isset($this->first_email) ? $emails['first_email'] = $this->first_email : null;
        isset($this->second_email) ? $emails['second_email'] = $this->second_email : null;
        return $emails;
    }

    public static function getContactByPhone($phone) {
        return self::find()
                        ->where(['first_phone' => $phone])
                        ->orWhere(['second_phone' => $phone])
                        ->orWhere(['third_phone' => $phone])
                        ->orWhere(['fourth_phone' => $phone])
                        ->one();
    }

    public function getManagerId() {
        $manager = $this->manager;
        if ($manager && $manager->role == User::ROLE_MANAGER) {
            return $manager->id;
        }
        return null;
    }

    public static function getManagerById($id) {
        $contact = self::find()
                ->with('manager')
                ->where(['id' => $id])
                ->one();
        return $contact->manager;
    }

    public static function getById($id) {
        return self::find()->where(['id' => $id])->one();
    }

    public static function deleteById($id) {
        $contact = self::find()->where(['id' => $id])->one();
        if ($contact) {
            $contact->is_deleted = 1;
            $contact->save();
            return true;
        }
        return false;
    }

    //works only on active contacts (skips records marked as deleted)
    public function isPhoneNumberExists() {
        if (count($this->new_phones) > 0) {
            foreach ($this->new_phones as $new_phone) {
                $contact = self::find()
                    ->andWhere(['is_deleted' => false])
                    ->andWhere(
                        [
                            'or',
                            ['first_phone' => $new_phone],
                            ['second_phone' => $new_phone],
                            ['third_phone' => $new_phone],
                            ['fourth_phone' => $new_phone]
                        ]
                    )
                    ->one();
                if ($contact) {
                    $this->addError('new_phones', $new_phone.', такой телефон уже существует в базе');
                    return true;
                }
            }
        }
        return false;
    }

    public function isEmailExists() {
        if (count($this->new_emails) > 0) {
            foreach ($this->new_emails as $new_email) {
                $contact = self::find()
                    ->andWhere(['is_deleted' => false])
                    ->andWhere(
                        [
                            'or',
                            ['first_email' => $new_email],
                            ['second_email' => $new_email],
                        ]
                    )
                    ->one();
                if ($contact) {
                    $this->addError('new_emails', $new_email.', такой Email уже существует в базе');
                    return true;
                }
            }
        }
        return false;
    }

    public function buildData($data) {
        if (isset($data['phones'])) {
            $db_phones = $this->getPhoneColsWithVal();
            if (!empty($data['phones'])) {
                $phones = forms\ContactForm::dataConvert($data['phones'], 'phones', 'explode');
                foreach ($phones as $phone_key => $phone_val) {
                    if ($phone_val !== null) {
//                        if (!ArrayHelper::isIn($phone_val, $db_phones)) {
//                            $this->new_phones[] = $phone_val;
//                        }
                        if (!in_array($phone_val, $db_phones)) {
                            $this->new_phones[] = $phone_val;
                        }
                    }
                    $this->$phone_key = $phone_val;
                }
            } else {
                foreach ($db_phones as $phone_key => $phone_val) {
                    $this->$phone_key = null;
                }
            }
            unset($data['phones']);
        }
        if (isset($data['emails'])) {
            $db_emails = $this->getEmailColsWithVal();
            if (!empty($data['emails'])) {
                $emails = forms\ContactForm::dataConvert($data['emails'], 'emails', 'explode');
                foreach ($emails as $email_key => $email_val) {
                    if ($email_val !== null) {
//                        if (!ArrayHelper::isIn($email_val, $db_emails)) {
//                            $this->new_emails[] = $email_val;
//                        }
                        if (!in_array($email_val, $db_emails)) {
                            $this->new_emails[] = $email_val;
                        }
                    }
                    $this->$email_key = $email_val;
                }
            } else {
                foreach ($db_emails as $email_key => $email_val) {
                    $this->$email_key = null;
                }
            }
            unset($data['emails']);
        }
        foreach ($data as $name => $value) {
            $this->$name = $value;
        }
    }

    public function edit() {
        $is_new_record = $this->isNewRecord;
        $transaction = Yii::$app->db->beginTransaction();
        $call = new Call();
        try {
            $this->save();
            if ($is_new_record) {
                $contact_history = new ContactHistory();
                $contact_history->add($this->id, 'создан контакт', 'new_contact');
                $contact_history->save();
                $contactStatusHistory = new ContactStatusHistory();
                $contactStatusHistory->add($this->id, $this->manager_id, 'lead');
                $contactStatusHistory->save();

//                $call->setContactIdByPhone($this->new_phone, $this->id);
            }
            $transaction->commit();
            return true;
        } catch (\Exception $ex) {
            $transaction->rollback();
            return false;
        }
    }

    public function getTags() {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('contact_tag', ['contact_id' => 'id']);
    }

}
