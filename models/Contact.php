<?php

namespace app\models;

use Yii;
use app\components\UtilHelper;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "contact".
 *
 * @property integer $id
 * @property integer $int_id
 * @property string $name
 * @property string $surname
 * @property string $middle_name
 * @property string $first_phone
 * @property string $second_phone
 * @property string $third_phone
 * @property string $fourth_phone
 * @property string $first_email
 * @property string $second_email
 * @property string $country
 * @property string $region
 * @property string $area
 * @property string $city
 * @property string $street
 * @property string $house
 * @property string $flat
 * @property string $status
 * @property integer $manager_id
 * @property integer $is_deleted
 */
class Contact extends \yii\db\ActiveRecord {

    public static $safe_fields = [
        'int_id',
        'name',
        'surname',
        'middle_name',
        'first_phone',
        'second_phone',
        'third_phone',
        'fourth_phone',
        'first_email',
        'second_email',
        'country',
        'region',
        'area',
        'city',
        'street',
        'house',
        'flat',
        'status',
        'manager_id',
        'is_deleted',
        'remove_tags'
    ];

    public $is_called;
    public $remove_tags;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%contact}}';
    }

    public function getManager() {
        return $this->hasOne(User::className(), ['id' => 'manager_id']);
    }

    public function beforeValidate() {
        if ($this->isNewRecord) {
            $this->int_id = UtilHelper::getRandomNumbers(7);
        }
        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['int_id'], 'required'],
            [['int_id', 'manager_id'], 'integer'],
            [['first_phone','second_phone','third_phone','fourth_phone','first_email','second_email','country','region','area','city','street','house','flat','status'], 'string', 'max' => 255],
            [['name', 'surname', 'middle_name'], 'string', 'max' => 150],
            [['first_email', 'second_email'], 'string', 'max' => 255],
        ];
    }

    public static function getTableColumns() {
        return [
            1 => 'int_id',
            2 => 'surname',
            3 => 'name',
            4 => 'middle_name',
            5 => '',
            6 => 'first_phone',
            7 => 'first_email',
            8 => 't.name',
        ];
    }

    public static function getAllSafeCols() {
        return [
            'id',
            'int_id',
            'name',
            'surname',
            'middle_name',
            'first_phone',
            'second_phone',
            'third_phone',
            'fourth_phone',
            'first_email',
            'second_email',
            'country',
            'region',
            'area',
            'city',
            'street',
            'house',
            'flat'
        ];
    }

    public static function getColsForTableView() {
        $result =  [
            'id' => ['label' => 'ID', 'have_search' => false, 'orderable' => true],
            'int_id' => ['label' => '№', 'have_search' => false, 'orderable' => false],
            'surname' => ['label' => 'Фамилия', 'have_search' => true, 'orderable' => true],
            'name' => ['label' => 'Имя', 'have_search' => true, 'orderable' => true],
            'middle_name' => ['label' => 'Отчество', 'have_search' => true, 'orderable' => true],
            'link_with' => ['label' => 'Связать', 'have_search' => false, 'orderable' => false],
            'phones' => ['label' => 'Телефоны', 'have_search' => true, 'orderable' => false, 'db_cols' => ['first_phone','second_phone','third_phone','fourth_phone']],
            'emails' => ['label' => 'Email', 'have_search' => true, 'orderable' => false, 'db_cols' => ['first_email','second_email']],
            'tags' => ['label' => 'Теги', 'have_search' => true, 'orderable' => false],
            'country' => ['label' => 'Страна', 'have_search' => true, 'orderable' => true],
            'region' => ['label' => 'Регион', 'have_search' => true, 'orderable' => true],
            'area' => ['label' => 'Район', 'have_search' => true, 'orderable' => true],
            'city' => ['label' => 'Город', 'have_search' => true, 'orderable' => true],
            'street' => ['label' => 'Улица', 'have_search' => true, 'orderable' => true],
            'house' => ['label' => 'Дом', 'have_search' => true, 'orderable' => true],
            'flat' => ['label' => 'Квартира', 'have_search' => true, 'orderable' => true],
            'delete_button' => ['label' => 'Удалить', 'have_search' => false, 'orderable' => false]
        ];
        if (!Yii::$app->user->can('delete_contact')) {
            unset($result['delete_button']);
        }
        return $result;
    }

    public static function getFIOCols() {
        return [
            'surname',
            'name',
            'middle_name'
        ];
    }

    public function getFIOValues() {
        return [
            'surname' => $this->surname,
            'name' => $this->name,
            'middle_name' => $this->middle_name,
        ];
    }

    public static function getPhoneCols() {
        return [
            'first_phone',
            'second_phone',
            'third_phone',
            'fourth_phone'
        ];
    }

    public function getPhoneValues() {
        return [
            'first_phone' => $this->first_phone,
            'second_phone' => $this->second_phone,
            'third_phone' => $this->third_phone,
            'fourth_phone' => $this->fourth_phone
        ];
    }

//    public function getPhoneColsWithVal() {
//        $phones = [];
//        isset($this->first_phone) ? $phones['first_phone'] = $this->first_phone : null;
//        isset($this->second_phone) ? $phones['second_phone'] = $this->second_phone : null;
//        isset($this->third_phone) ? $phones['third_phone'] = $this->third_phone : null;
//        isset($this->fourth_phone) ? $phones['fourth_phone'] = $this->fourth_phone : null;
//        return $phones;
//    }

    public static function getEmailCols() {
        return [
            'first_email',
            'second_email',
        ];
    }

    public function getEmailValues() {
        return [
            'first_email' => $this->first_email,
            'second_email' => $this->second_email
        ];
    }

//    public function getEmailColsWithVal() {
//        $emails = [];
//        isset($this->first_email) ? $emails['first_email'] = $this->first_email : null;
//        isset($this->second_email) ? $emails['second_email'] = $this->second_email : null;
//        return $emails;
//    }

    public static function getLocationCols() {
        return [
            'country',
            'region',
            'area',
            'city',
            'street',
            'house',
            'flat'
        ];
    }

    public function mergeTogether($contact)
    {
        foreach ($this->attributes as $prop_key => $prop_val) {
            if (preg_match('/surname|name|middle_name/', $prop_key)) {
                if (is_null($prop_val) || $prop_val == '') {
                    $this->$prop_key = $contact->$prop_key;
                }
            }
        }

        $phone_cols = self::getPhoneCols();
        $email_cols = self::getEmailCols();
        $location_cols = self::getLocationCols();

        $contact_phones = self::getPropValues($contact, $phone_cols);
        $contact_emails = self::getPropValues($contact, $email_cols);
        $contact_location = self::getPropValues($contact, $location_cols);

        $self_phones = self::getPropValues($this, $phone_cols);
        $self_emails = self::getPropValues($this, $email_cols);

        if (count($contact_phones['exists']) > count($self_phones['empty'])) {
            $this->addError('prop_count_miss', 'Ошибка: телефоны переполнены');
            return false;
        } elseif (count($contact_emails['exists']) > count($self_emails['empty'])) {
            $this->addError('prop_count_miss', 'Ошибка: email-ы переполнены');
            return false;
        }

        $i = 0;
        foreach ($self_phones['empty'] as $phone_key => $phone_val) {
            $this->$phone_key = isset($contact_phones['exists'][$i]) ? $contact_phones['exists'][$i] : null;
            $i++;
        }
        $i = 0;
        foreach ($self_emails['empty'] as $email_key => $email_val) {
            $this->$email_key = isset($contact_emails['exists'][$i]) ? $contact_emails['exists'][$i] : null;
            $i++;
        }

        if (count($contact_location['exists']) > 0) {
            $this->setNull($location_cols);

            foreach ($location_cols as $col) {
                $this->$col = $contact->$col;
            }
        }

        return true;
    }

    public static function getPropValues($contact, $cols)
    {
        $values = ['exists' => [], 'empty' => []];
        $cols_str = implode('|', $cols);
        $pattern = '/'.$cols_str.'/';
        foreach ($contact->attributes as $prop_key => $prop_val) {
            if (preg_match($pattern, $prop_key)) {
                if (is_null($prop_val) || $prop_val == '') {
                    $values['empty'][$prop_key] = $prop_val;
                } else {
                    $values['exists'][] = $prop_val;
                }
            }
        }
        return $values;
    }

    public function setNull($cols)
    {
        foreach ($cols as $col) {
            $this->$col = null;
        }
    }

    public static function getContactByPhone($phone) {
        return self::find()->where(['is_deleted' => '0'])
                        ->andWhere(['or', ['first_phone' => $phone], ['second_phone' => $phone], ['third_phone' => $phone], ['fourth_phone' => $phone]])
                        ->one();
    }

    public function getManagerId() {
        $manager = $this->manager;
        if ($manager && $manager->role == User::ROLE_MANAGER) {
            return $manager->id;
        }
        return null;
    }

    public static function getManagerById($id) {
        $contact = self::find()
                ->with('manager')
                ->where(['id' => $id])
                ->one();
        return $contact->manager;
    }

    public static function getById($id) {
        return self::find()->where(['id' => $id])->one();
    }

    public static function deleteById($id) {
        $contact = self::find()->with('tags')->where(['id' => $id])->one();
        if ($contact) {
            // Удаление временных записей связанных с контаком
            Contact::removeContInPool($id);

            // Удаление тегов связанных с контактом
            //$contact->unlink('tags', $contact->tags[0]);

            $contact->is_deleted = 1;
            $contact->save();
            return true;
        }
        return false;
    }

    public function edit($related) {
        $transaction = Yii::$app->db->beginTransaction();
//        $call = new Call();
        try {
            $this->save();
            if (isset($related['tags'])) {
                $this->tags = $related['tags'];
            }
            if ($this->isNewRecord) {
                $contact_history = new ContactHistory();
                $contact_history->add($this->id, 'создан контакт', 'new_contact');
                $contact_history->save();
                $contactStatusHistory = new ContactStatusHistory();
                $contactStatusHistory->add($this->id, $this->manager_id, 'lead');
                $contactStatusHistory->save();
//                $call->setContactIdByPhone($this->new_phone, $this->id);
            }
            $transaction->commit();
            return true;
        } catch (\Exception $ex) {
            $transaction->rollback();
            return false;
        }
    }

    public function getActions() {
        return $this->hasMany(Action::className(), ['contact_id' => 'id']);
    }

    public function getComments() {
        return $this->hasMany(ContactComment::className(), ['contact_id' => 'id']);
    }

    public function getCalls() {
        return $this->hasMany(Call::className(), ['contact_id' => 'id']);
    }



    public function getTags() {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('contact_tag', ['contact_id' => 'id']);
    }

//    public function setTags($new_tags) {
//        foreach ($new_tags as $new_tag) {
//            $new_tag->save();
//            $this->link('tags', $new_tag);
//        }
//    }

    public function setTags($new_tags) {
        if ($this->remove_tags == true) {
            $this->unlinkAll('tags');
        }
        foreach ($new_tags as $new_tag) {
            $new_tag->save();
            if (!ContactTag::find()->where(['contact_id' => $this->id, 'tag_id' => $new_tag->id])->exists()) {
                $this->link('tags', $new_tag);
            }
        }
    }

    public static function getByTag($tag_id, $user_role, $filters, $for_export) {
        $query = new Query();
        $select = [
            '`contact`.*',
            '`user`.`firstname` as `operator_name`',
            '`call`.`status` as `call_status`',
            '`call`.`type` as `call_type`',
            '`call`.`comment` as `call_comment`',
            '`call`.`attitude_level` as `call_attitude_level`'
        ];
        $query
            ->join('LEFT JOIN', '`call`', '`contact`.`id` = `call`.`contact_id` AND `call`.`id` in (SELECT MAX(`call`.`id`) FROM `call` WHERE `contact`.`id` = `call`.`contact_id` AND `call`.`tag_id`='.$tag_id.')')
            //->join('LEFT JOIN', '(SELECT * FROM `call` WHERE `call`.`tag_id`='.$tag_id.' GROUP BY `id` ORDER BY `id` DESC) as `call`', '`contact`.`id` = `call`.`contact_id`')
            ->join('LEFT JOIN', 'call_manager', '`call`.`id` = `call_manager`.`call_id`')
            ->join('LEFT JOIN', '`user`', '`user`.`id` = `call_manager`.`manager_id`')
            ->join('LEFT JOIN', '`contact_tag`', '`contact`.`id` = `contact_tag`.`contact_id`')
            //LEFT JOIN `contact_history` ON `contact_history`.`contact_id` = `contact`.`id`
            //->join('LEFT JOIN', '`tag`', '`tag`.`id` = `contact_tag`.`tag_id`')
            ->where(['`contact_tag`.`tag_id`' => $tag_id])
            ->andWhere(['`contact`.is_deleted' => 0])
            ->from('`contact`')
            //->groupBy('`contact`.`id`')
            ->orderBy(['`call`.id' => SORT_DESC]);
            //->distinct();
        if ($for_export) {
            $select = array_merge($select, [
                '`contact_history`.`text` as `history_text`',
                '`contact_history`.`datetime` as `history_datetime`',
            ]);
            $query->join('LEFT JOIN', '`contact_history`', '`contact`.`id` = `contact_history`.`contact_id`');
        }
        $query->select($select);

        //$dump = $query->createCommand()->rawSql;

        foreach ($filters as $name => $value) {
            switch ($name) {
                case 'manager_id':
                    if ($user_role != User::ROLE_OPERATOR) {
                        $query->andWhere(['`call_manager`.`manager_id`' => $value]);
                    }
                    break;
                case 'status':
                    $query->andWhere(['`call`.`status`' => $value]);
                    break;
                case 'comment':
                    $query->andWhere("`call`.`comment` LIKE '%$value%'");
                    break;
                case 'attitude_level':
                    $query->andWhere(['`call`.`attitude_level`' => $value]);
                    break;
            }
        }

        if ($user_role == User::ROLE_OPERATOR) {
            $notCalledContactQuery = clone $query;
            $query->andWhere(['`call_manager`.`manager_id`' => Yii::$app->user->identity->id]);
            $notCalledContactQuery->andWhere(['is','`call`.`status`', null]);
            $contactsIdInPool = Contact::getContactsIdInPool($tag_id,  Yii::$app->user->identity->id);
            $notCalledContactQuery->andWhere(['NOT IN', '`contact`.`id`', $contactsIdInPool]);
            $notCalledContactQuery->limit(1);
            //$dump = $notCalledContactQuery->createCommand()->rawSql;
            $notCalledContact = $notCalledContactQuery->all();
            if (count($notCalledContact)) {
                Contact::addContInPool($notCalledContact[0]['id'],Yii::$app->user->identity->id, $tag_id, null);
                //file_put_contents('/var/log/pool.log', 'Pool :'.$notCalledContact[0]['id'] . ' : ' . Yii::$app->user->identity->id .' : ' . $tag_id . PHP_EOL, FILE_APPEND);
            }

            //$dump = $query->createCommand()->rawSql;
            $contacts = $query->all();
            $contacts = array_merge($contacts, $notCalledContact);

        } else {
            $contacts = $query->all();
        }

        return $contacts;
    }

    public static function getCountByTag($tag_id) {
        $query = new Query();
        $query
            ->select(['COUNT(DISTINCT `contact`.`id`) as `count`', '`call`.`status` as `status`'])
            //->join('LEFT JOIN', '`call`', '`contact`.`id` = `call`.`contact_id` AND `call`.`tag_id`='.$tag_id)
            ->join('LEFT JOIN', '(SELECT * FROM `call` WHERE `call`.`tag_id`='.$tag_id.' GROUP BY `call`.`contact_id` ORDER BY `id` DESC) as `call`', '`contact`.`id` = `call`.`contact_id`')
            //->join('LEFT JOIN', 'call_manager', '`call`.`id` = `call_manager`.`call_id`')
            //->join('LEFT JOIN', '`user`', '`user`.`id` = `call_manager`.`manager_id`')
            ->join('LEFT JOIN', '`contact_tag`', '`contact`.`id` = `contact_tag`.`contact_id`')
            //->join('LEFT JOIN', '`tag`', '`tag`.`id` = `contact_tag`.`tag_id`')
            ->where(['`contact_tag`.`tag_id`' => $tag_id])
            ->andWhere(['`contact`.is_deleted' => 0])
            ->from('`contact`')
            ->groupBy('`call`.`status`')
            ->orderBy(['`call`.id' => SORT_DESC]);
        //$dump = $query->createCommand()->rawSql;
        $counts = $query->all();
        $all_count = 0;
        $called_count = 0;
        foreach ($counts as $count) {
            if ($count['status'] != null) {
                $called_count+= $count['count'];
            }
            $all_count += $count['count'];
        }
        return [
            'all' => $all_count,
            'called' => $called_count
        ];
    }
    public static function getContactsIdInPool($tag_id, $operator_id) {
        $query = new Query();
        $query->select('contact_id')->from('temp_contacts_pool')
            ->where(['tag_id' => $tag_id])
            ->andWhere(['<>', 'manager_id', $operator_id]);
        $contacts = $query->all();
        $contactsIds = [];
        foreach ($contacts as $contact) {
            $contactsIds[] = $contact['contact_id'];
        }
        return $contactsIds;
    }


    public static function addContInPool($contact_id, $manager_id, $tag_id, $order_token)
    {
        $cont_pool = TempContactsPool::findOne(['contact_id' => $contact_id, 'manager_id' => $manager_id, 'tag_id' => $tag_id]);
        if ($cont_pool) {
            $cont_pool->delete();
        }
        $cont_pool = new TempContactsPool(['contact_id' => $contact_id, 'manager_id' => $manager_id, 'tag_id' => $tag_id, 'order_token' => $order_token]);
        return $cont_pool->save();
    }

    public static function removeContInPool($contact_id)
    {
        $cont_pool = TempContactsPool::findOne(['contact_id' => $contact_id]);
        if ($cont_pool) {
            return $cont_pool->delete();
        }
        return false;
    }
}
