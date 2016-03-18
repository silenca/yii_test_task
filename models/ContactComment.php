<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contact_comment".
 *
 * @property integer $id
 * @property integer $contact_id
 * @property string $comment
 * @property string $date_time
 */
class ContactComment extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'contact_comment';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['contact_id', 'datetime'], 'required'],
            [['contact_id'], 'integer'],
            [['comment'], 'string'],
            [['datetime'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'contact_id' => 'Contact ID',
            'comment' => 'Comment',
            'datetime' => 'Date Time',
        ];
    }

    public function add($contact_id, $comment) {
        $this->contact_id = $contact_id;
        $this->comment = $comment;
        $this->datetime = date('Y-m-d H:i:s');
        return $this->save();
    }

}
