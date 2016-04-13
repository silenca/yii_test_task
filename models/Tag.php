<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tag".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 */
class Tag extends \yii\db\ActiveRecord {

    public static $safe_fields = [
        'name',
        'description',
        'script'
    ];

    /*
    'id',
    'name',
    'description',
    'script',
    'as_task',
    'start_date',
    'end_date',
    */

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'tag';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name'], 'required'],
            [['name', 'description', 'script'], 'string'],
            [['as_task'], 'integer'],
            [['start_date', 'end_date', 'as_task'], 'safe'],
            [['start_date', 'end_date'], 'date', 'format' => 'yyyy-M-d H:m:s'],
        ];
    }

    public static function getTableColumns() {
        return [
            'name',
            'description',
            'script',
            'as_task',
            'script',
            'start_date',
            'end_date',
        ];
    }

    public function getUsers() {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('user_tag', ['tag_id' => 'id']);
    }

    public function getContacts() {
        return $this->hasMany(Contact::className(), ['id' => 'contact_id'])->viaTable('contact_tag', ['tag_id' => 'id']);
    }

    public static function getById($id) {
        return self::find()->where(['id' => $id])->one();
    }

    public static function getByName($name) {
        return self::find()->where(['name' => $name])->one();
    }

    public function getCalls() {
        return $this->hasMany(Call::className(), ['tag_id' => 'id']);
    }

    public static function getContactsForTagTableView() {
        return [
            'id' => ['label' => 'ID', 'have_search' => false, 'orderable' => true],
            'int_id' => ['label' => '№', 'have_search' => false, 'orderable' => false],
            'fio' => ['label' => 'ФИО', 'have_search' => true, 'db_cols' => ['surname', 'name', 'middle_name']],
            'phones' => ['label' => 'Телефоны', 'have_search' => true, 'orderable' => false, 'db_cols' => ['first_phone','second_phone','third_phone','fourth_phone']],
            'emails' => ['label' => 'Email', 'have_search' => true, 'orderable' => false, 'db_cols' => ['first_email','second_email']],
            'tags' => ['label' => 'Теги', 'have_search' => true, 'orderable' => false],
            'country' => ['label' => 'Страна', 'have_search' => true, 'orderable' => true],
            'region' => ['label' => 'Регион', 'have_search' => true, 'orderable' => true],
            'city' => ['label' => 'Город', 'have_search' => true, 'orderable' => true],
            'contacts' => ['label' => 'Добавить', 'have_search' => false, 'orderable' => false]
        ];
    }

    public function edit($related) {
        $transaction = Yii::$app->db->beginTransaction();
        $is_new = $this->isNewRecord;
        try {
            if ($is_new) {
                $this->start_date = date('Y-m-d G:i:s', strtotime($this->start_date));
                $this->end_date = date('Y-m-d G:i:s');
            }
            $this->save();
            $this->setRelation('users', $related['user_ids']);
            $this->setRelation('contacts', $related['contact_ids']);
            if ($this->hasErrors()) {
                $transaction->rollback();
                return false;
            }
            $transaction->commit();
            return true;
        } catch (\Exception $ex) {
            $transaction->rollback();
            return false;
        }
    }

    public function setRelation($relation, $models = [])
    {
        $this->unlinkAll($relation);
        if ($models) {
            $this->$relation = $models;
        }
    }

    public function setUsers($user_ids) {
        foreach ($user_ids as $user_id) {
            $user_model = null;
            $user_model = User::findOne(['id' => $user_id]);
            if ($user_model) {
                $this->link('users', $user_model);
            }
        }
    }
    public function setContacts($contact_ids) {
        foreach ($contact_ids as $contact_id) {
            $contact_model = null;
            $contact_model = Contact::findOne(['id' => $contact_id]);
            if ($contact_model) {
                $this->link('contacts', $contact_model);
            }
        }
    }
}