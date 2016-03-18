<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "manager_notification".
 *
 * @property integer $id
 * @property string $system_date
 * @property integer $manager_int_id
 * @property string $type
 * @property integer $contact_id
 * @property integer $phone_number
 */
class ManagerNotification extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'manager_notification';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['system_date'], 'safe'],
            [['manager_id'], 'required'],
            [['manager_id', 'contact_id', 'phone_number','viewed'], 'integer'],
            [['type'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'system_date' => 'System Date',
            'manager_int_id' => 'Manager ID',
            'type' => 'Type',
            'contact_id' => 'Contact ID',
            'phone_number' => 'Phone Number',
        ];
    }
    
    public static function getTableColumns() {
        return [
            '`mn`.`id`',
            '`mn`.`system_date`',
            '`mn`.`type`',
            '`mn`.`contact_id`',
            '`mn`.`phone_number`',
            '`c`.`first_name`',
            '`c`.`second_name`',
            '`js`.`message` AS jivosite_message',
            '`cc`.`comment`',
            '`mn`.`viewed`',
            '`ac`.`schedule_date` AS action_schedule_date',
        ];
    }

    public function getActions() {
        return $this->hasMany(Action::className(), ['id' => 'action_id'])->viaTable('manager_notification_action', ['manager_notification_id' => 'id']);
    }

    public function add($system_date, $type, $manager_id, $phone_number = null, $contact_id = null) {
        $this->system_date = $system_date;
        $this->type = $type;
        $this->manager_id = $manager_id;
        $this->phone_number = $phone_number;
        $this->contact_id = $contact_id;
        return $this->save();
    }

}
