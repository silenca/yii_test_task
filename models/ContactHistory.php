<?php

namespace app\models;

use Yii;
use yii\helpers\Html;
use yii\db\Query;

/**
 * This is the model class for table "contact_history".
 *
 * @property integer $id
 * @property integer $contact_id
 * @property string $text
 * @property string $type
 * @property integer $contact_show_id
 * @property integer $contact_visit_id
 * @property integer $contact_contract_id
 */
class ContactHistory extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'contact_history';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['contact_id', 'datetime'], 'required'],
            [['contact_id'], 'integer'],
            [['text', 'type'], 'string'],
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
            'text' => 'Text',
            'datetime' => 'Date Time',
            'type' => 'Type',
        ];
    }

    public static function getByContactId($contact_id) {
        $query = new Query();
        $history = $query->select(['`ch`.*'])
                ->from(self::tableName() . ' ch')
                ->where(['ch.contact_id' => $contact_id])
                ->orderBy(['ch.id' => SORT_ASC])
                ->all();
        $history_array = [];
        foreach ($history as $i => $_history) {
            $history_array[$i]['text'] = $_history['text'];
            $history_array[$i]['datetime'] = date("d-m-Y G:i:s", strtotime($_history['datetime']));
            $history_array[$i]['type'] = $_history['type'];
        }
        return $history_array;
    }

    public function add($contact_id, $text, $type, $datetime = false) {
        $this->contact_id = $contact_id;
        $this->text = $text;
        $this->type = $type;
        if ($datetime) {
            $this->datetime = $datetime;
        } else {
            $this->datetime = date('Y-m-d H:i:s');
        }
        return $this->save();
    }

}