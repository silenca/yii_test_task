<?php

namespace app\models;

use Yii;
use app\components\UtilHelper;

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
    private $new_phone;

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
            [['name','surname','middle_name','first_phone','second_phone','third_phone','fourth_phone','first_email','second_email','country','region','area','city','street','house','flat','status'], 'string'],
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
        if ($this->new_phone) {
            $contact = self::find()
                    ->andWhere(['is_deleted' => false])
                    ->andWhere(
                            [
                                'or',
                                ['first_phone' => $this->new_phone],
                                ['second_phone' => $this->new_phone],
                                ['third_phone' => $this->new_phone],
                                ['fourth_phone' => $this->new_phone]
                            ]
                    )
                    ->one();
            if ($contact) {
                return true;
            }
        }
        return false;
    }

    public function buildData($data) {
        if (isset($data['first_phone'])) {
            if (!empty($data['first_phone'])) {
                $phones = array_map('trim', explode(',', $data['first_phone']));
                if (isset($phones[0])) {
                    $phone = $phones[0];
                    if (strlen($phone) > 10) {
                        $phone = substr($phone, strlen($phone) - 10);
                    }
                    if ($this->first_mobile !== $phone) {
                        $this->first_mobile = $phone;
                        $this->new_phone = $phone;
                    }
                }
                if (isset($phones[1])) {
                    $phone = $phones[1];
                    if (strlen($phone) > 10) {
                        $phone = substr($phones[1], strlen($phones[1]) - 10);
                    }
                    if ($this->first_landline !== $phone) {
                        $this->first_landline = $phone;
                        $this->new_phone = $phone;
                    }
                }
            } else {
                $this->first_mobile = null;
                $this->first_landline = null;
            }
            unset($data['first_phone']);
        }
        if (isset($data['second_phone'])) {
            if (!empty($data['second_phone'])) {
                $phones = array_map('trim', explode(',', $data['second_phone']));
                if (isset($phones[0])) {
                    $phone = $phones[0];
                    if (strlen($phone) > 10) {
                        $phone = substr($phone, strlen($phone) - 10);
                    }
                    if ($this->second_mobile !== $phone) {
                        $this->second_mobile = $phone;
                        $this->new_phone = $phone;
                    }
                }
                if (isset($phones[1])) {
                    $phone = $phones[1];
                    if (strlen($phone) > 10) {
                        $phone = substr($phones[1], strlen($phones[1]) - 10);
                    }
                    if ($this->second_landline !== $phone) {
                        $this->second_landline = $phone;
                        $this->new_phone = $phone;
                    }
                }
            } else {
                $this->second_mobile = null;
                $this->second_landline = null;
            }
            unset($data['second_phone']);
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
                $contact_history->add($this->id, 'создан контакт', '', 'new_contact');
                $contact_history->save();
                $contactStatusHistory = new ContactStatusHistory();
                $contactStatusHistory->add($this->id, $this->manager_id, 'lead');
                $contactStatusHistory->save();

                $call->setContactIdByPhone($this->new_phone, $this->id);
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
