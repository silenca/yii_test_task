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
class JivositeManagerNotification extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jivosite_manager_notification';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['jivosite_id', 'manager_notification_id'], 'required'],
            [['jivosite_id', 'manager_notification_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'jivosite_id' => 'Jivosite ID',
            'manager_notification_id' => 'Manager_notification ID',
        ];
    }
}
