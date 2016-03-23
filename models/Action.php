<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "action".
 *
 * @property integer $id
 * @property string $system_date
 * @property integer $contact_id
 * @property integer $manager_id
 * @property string $schedule_date
 * @property integer $action_type_id
 */
class Action extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'action';
    }

    public function getContact() {
        return $this->hasOne(Contact::className(), ['id' => 'contact_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['system_date', 'contact_id', 'manager_id', 'action_type_id'], 'required'],
            [['system_date'], 'safe'],
            ['schedule_date', 'date', 'format' => 'yyyy-M-d H:m:s'],
            [['contact_id', 'manager_id', 'action_type_id'], 'integer'],
        ];
    }
    
    public static function getTableColumns() {
        return [
            '`a`.`id`',
            '`a`.`system_date`',
            '`at`.`name` as "type"',
            '`c`.`name`',
            '`c`.`surname`',
            '`c`.`middle_name`',
            //'`oa`.`link` as "object_link"',
            '`a`.`schedule_date`',
            '`u`.`firstname` as "manager_name"',
            '`cc`.`comment` as contact_comment',
            '`a`.`contact_id`',
            '`a`.`viewed`'
        ];
    }

    public function add($contact_id, $type_id, $objects_id, $schedule_date = null) {
        $this->system_date = date('Y-m-d H:i:s');
        $this->action_type_id = $type_id;
        $this->manager_id = Yii::$app->user->identity->id;
        $this->contact_id = $contact_id;
        if ($schedule_date) {
            $this->schedule_date = date('Y-m-d G:i:s', strtotime($schedule_date));
        }
        if ($this->save()) {
            foreach ($objects_id as $object_id) {
                $action_object = new ActionObject();
                $action_object->action_id = $this->id;
                $action_object->object_id = $object_id;
                $action_object->save();
            }
            return true;
        }
        return false;        
    }

    public function addManagerNotification($action_id, $date, $type, $manager_id, $contact_id = null, $phone = null) {
        if ($phone !== null) {
            if ($contact = Contact::getContactByPhone($phone)) {
                $contact_id = $contact->id;
            }
        }

        $manager_notification = new ManagerNotification();
        if ($manager_notification->add($date, $type, $manager_id, $phone, $contact_id)) {
            $manager_notification_action = new ManagerNotificationAction();
            $manager_notification_action->action_id = $action_id;
            $manager_notification_action->manager_notification_id = $manager_notification->id;
            $manager_notification_action->save();
        }
    }

}
