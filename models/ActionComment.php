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
class ActionComment extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'action_comment';
    }

    public function __construct(array $config)
    {
        $this->datetime = date('Y-m-d H:i:s');
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['datetime'], 'required'],
            [['action_id'], 'integer'],
            [['comment'], 'string'],
//            [['comment'], 'required', 'message' => '{attribute} не может быть пустым'],
            [['datetime'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'action_id' => 'Action ID',
            'comment' => 'Комментарий',
            'datetime' => 'Date Time',
        ];
    }

    public function add($action_id, $comment) {
        $this->action_id = $action_id;
        $this->comment = $comment;
        $this->datetime = date('Y-m-d H:i:s');
        return $this->save();
    }

}
