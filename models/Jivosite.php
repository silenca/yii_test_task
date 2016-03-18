<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "jivosite".
 *
 * @property integer $id
 * @property string $system_date
 * @property string $type
 * @property string $name
 * @property string $phone
 * @property string $email
 * @property string $message
 */
class Jivosite extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'jivosite';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['type'], 'required'],
            [['type', 'name', 'phone', 'email', 'message'], 'string'],
        ];
    }

    public function getManagerNotifications() {
        return $this->hasMany(ManagerNotification::className(), ['id' => 'manager_notification_id'])->viaTable('jivosite_manager_notification', ['jivosite_id' => 'id']);
    }

    public function add($system_date, $type, $name = null, $phone = null, $email = null, $message = null) {
        $this->system_date = $system_date;
        $this->type = $type;
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
        $this->message = $message;
        return $this->save();
    }

    public function addManagerNotification($jivosite_id, $jivosite_phone = null) {
        $contact_id = null;
        if ($jivosite_phone !== null) {
            if ($contact = Contact::getContactByPhone($jivosite_phone)) {
                $contact_id = $contact->id;
            }
        }

        $manager_ids = User::getManagersData('id');
        foreach ($manager_ids as $manager_id) {
            $manager_notification = new ManagerNotification();
            if ($manager_notification->add(date('Y-m-d G:i:s', time()), 'jivosite', $manager_id, $jivosite_phone, $contact_id)) {
                $jivosite_manager_notification = new JivositeManagerNotification();
                $jivosite_manager_notification->jivosite_id = $jivosite_id;
                $jivosite_manager_notification->manager_notification_id = $manager_notification->id;
                $jivosite_manager_notification->save();
            }
        }
    }

}
