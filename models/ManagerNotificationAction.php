<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "jivosite_manager_notification".
 *
 * @property integer $id
 * @property integer $jivosite_id
 * @property integer $manager_notification_id
 */
class ManagerNotificationAction extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'manager_notification_action';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['action_id', 'manager_notification_id'], 'required'],
            [['action_id', 'manager_notification_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'action_id' => 'Action ID',
            'manager_notification_id' => 'Manager notification ID',
        ];
    }
}