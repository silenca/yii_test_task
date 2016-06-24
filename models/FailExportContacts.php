<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "fail_export_contacts".
 *
 * @property integer $id
 * @property integer $contact_id
 * @property string $date_time
 */
class FailExportContacts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fail_export_contacts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contact_id', 'date_time'], 'required'],
            [['contact_id'], 'integer'],
            [['date_time'], 'safe'],
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
            'date_time' => 'Date Time',
        ];
    }

    public static function add($contact_id) {
        if (!FailExportContacts::find()->where(['contact_id' => $contact_id])->exists()) {
            $fail_contact = new FailExportContacts();
            $fail_contact->contact_id = $contact_id;
            $fail_contact->date_time = date('Y-m-d H:i:s');
            return $fail_contact->save();
        }
        return true;
    }

    public static function remove($contact_id) {
        return self::deleteAll(['contact_id' => $contact_id]);
    }
}