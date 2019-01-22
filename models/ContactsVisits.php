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
 * @property Departments $department
 */
class ContactsVisits extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 1;//В ожидании
    const STATUS_TAKE_PLACE  = 2;//Состоялся

    const STATUS_PENDING_MEDIUM = "В ожидании";
    const STATUS_TAKE_PLACE_MEDIUM = "Состоялся";

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
            [['contact_id','department_id','status', 'manager_id'], 'integer'],
            [['medium_oid'], 'string'],
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
}