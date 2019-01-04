<?php

namespace app\models;

/**
 * This is the model class for table "contact_visit_log".
 *
 * @property integer $id
 * @property string $date
 * @property string $date_visit
 * @property integer $contact_id
 * @property string $medium_oid
 * @property string $request
 * @property string $response
 */
class ContactVisitLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contact_visit_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contact_id'], 'integer'],
            [['medium_oid','request','response'], 'string'],
        ];
    }
}
