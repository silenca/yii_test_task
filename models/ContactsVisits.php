<?php

namespace app\models;

/**
 * This is the model class for table "contacts_visits".
 *
 * @property integer $id
 * @property string $create_date
 * @property string $edit_date
 * @property string $visit_date
 * @property integer $contact_id
 * @property integer $department_id
 * @property string $medium_oid
 * @property integer $status
 * @property integer $manager_id
 * @property integer $sync_status
 * @property Departments $department
 * @property string $cabinet_oid
 * @property string $doctor_oid
 * @property string $cabinet_name
 * @property string $doctor_name
 * @property integer $time
 * @property string $comment
 */
class ContactsVisits extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 1;//В ожидании
    const STATUS_TAKE_PLACE  = 2;//Состоялся

    const STATUS_PENDING_MEDIUM = "В ожидании";
    const STATUS_TAKE_PLACE_MEDIUM = "Состоялся";

    const SYNC_STATUS_NEW = 1;
    const SYNC_STATUS_WAITING = 2;
    const SYNC_STATUS_SYNCED = 3;
    const SYNC_STATUS_ERROR = 4;

    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contacts_visits';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contact_id','department_id','status', 'manager_id', 'sync_status', 'time'], 'integer'],
            [['medium_oid', 'cabinet_oid', 'cabinet_name', 'doctor_oid', 'doctor_name', 'comment'], 'string'],
            [['create_date', 'edit_date', 'visit_date'], 'datetime', 'format' => 'php:'.self::DATE_FORMAT],
        ];
    }

    public function getDepartment()
    {
        return $this->hasOne(Departments::className(), ['id' => 'department_id']);
    }

    public function getContact()
    {
        return $this->hasOne(Contact::className(), ['id' => 'contact_id']);
    }

    public function getManager()
    {
        return $this->hasOne(User::className(), ['id' => 'manager_id']);
    }

    /**
     * @return ContactsVisits[]
     */
    public static function fetchToSync()
    {
        return self::find()->where([
            'sync_status' => self::SYNC_STATUS_NEW,
        ])->all();
    }

    /**
     * @return ContactsVisits[]
     */
    public static function fetchToUpdateLead()
    {
        return self::find()->where([
            'sync_status' => self::SYNC_STATUS_WAITING,
        ])->all();
    }
}