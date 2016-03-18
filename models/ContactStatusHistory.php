<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contact_status_history".
 *
 * @property integer $id
 * @property integer $contact_id
 * @property integer $manager_id
 * @property string $date_time
 * @property string $status
 */
class ContactStatusHistory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contact_status_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contact_id', 'manager_id', 'date_time'], 'required'],
            [['contact_id', 'manager_id'], 'integer'],
            [['date_time'], 'safe'],
            [['status'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'contact_id' => 'Contact ID',
            'manager_id' => 'Manager ID',
            'date_time' => 'Date Time',
            'status' => 'Status',
        ];
    }
    public function add($contact_id, $manager_id, $status) {
        $this->contact_id = $contact_id;
        $this->manager_id = $manager_id;
        $this->date_time = date('Y-m-d H:i:s');
        $this->status = $status;
        return $this->save();
    }
}
