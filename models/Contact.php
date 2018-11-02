<?php

namespace app\models;

use app\components\UtilHelper;
use Yii;
use HttpRequest;
//use yii\httpclient\Request;
use yii\httpclient\Client;

use yii\httpclient\XmlParser;
use yii\base\Exception;
use yii\db\Query;

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
class Contact extends \yii\db\ActiveRecord
{

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
        'remove_tags',
        'attraction_channel_id',
        'notification_service_id',
        'is_broadcast',
        'language_id',
        'medium_oid'
    ];

    public $is_called;
    public $remove_tags;
    const IS_BROADCAST_TRUE = 1;
    const IS_BROADCAST_FALSE = 0;

    const LEAD = 1;
    const CONTACT = 2;
    const MEDIUM_API_URL = 'http://91.225.122.210:8080/api/H:1D13C88C20AA6C6/D:WORK/D:1D13C9303C946F9/C:1D45F18F27C737D/';
    const MEDIUM_API_OBJECT = 'O:'; //FOR SINGLE OBJECT
    const MEDIUM_API_ITEM = 'I:'; //FOR LISTINGS

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contact}}';
    }

    public function getManager()
    {
        return $this->hasOne(User::className(), ['id' => 'manager_id']);
    }

    public function beforeValidate()
    {
        if ($this->isNewRecord) {
            $this->int_id = UtilHelper::getRandomNumbers(7);
        }
        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['int_id'], 'required'],
            [['int_id', 'manager_id'], 'integer'],
            [['first_phone', 'second_phone', 'third_phone', 'fourth_phone', 'first_email', 'second_email', 'country', 'region', 'area', 'city', 'street', 'house', 'flat', 'status'], 'string', 'max' => 255],
            [['int_id', 'manager_id', 'notification_service_id', 'language_id', 'attraction_channel_id'], 'integer'],
            [['first_phone', 'medium_oid', 'second_phone', 'third_phone', 'fourth_phone', 'first_email', 'second_email', 'birthday', 'country', 'city'], 'string', 'max' => 255],
            [['name', 'surname', 'middle_name'], 'string', 'max' => 150],
            [['first_email', 'second_email'], 'string', 'max' => 255],
            [['sended_crm'], 'safe'],
        ];
    }

    public static function getTableColumns()
    {
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

    public static function getAllSafeCols()
    {
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
            'flat',
            'is_broadcast',
            'language_id',
            'attraction_channel_id',
            'notification_service_id',
            'status',
            'manager_id',
            'medium_oid'
        ];
    }

    public static function getColsForTableView()
    {
        $result = [
            'id' => ['label' => 'ID', 'have_search' => false, 'orderable' => true],
            'int_id' => ['label' => '№', 'have_search' => false, 'orderable' => false],
            'surname' => ['label' => 'Фамилия', 'have_search' => true, 'orderable' => true],
            'name' => ['label' => 'Имя', 'have_search' => true, 'orderable' => true],
            'middle_name' => ['label' => 'Отчество', 'have_search' => true, 'orderable' => true],
            'link_with' => ['label' => 'Связать', 'have_search' => false, 'orderable' => false],
            'phones' => ['label' => 'Телефоны', 'have_search' => true, 'orderable' => false, 'db_cols' => ['first_phone', 'second_phone', 'third_phone', 'fourth_phone']],
            'emails' => ['label' => 'Email', 'have_search' => true, 'orderable' => false, 'db_cols' => ['first_email', 'second_email']],
            'tags' => ['label' => 'Теги', 'have_search' => true, 'orderable' => false],
            'country' => ['label' => 'Страна', 'have_search' => true, 'orderable' => true],
            'region' => ['label' => 'Регион', 'have_search' => true, 'orderable' => true],
            'area' => ['label' => 'Район', 'have_search' => true, 'orderable' => true],
            'city' => ['label' => 'Город', 'have_search' => true, 'orderable' => true],
            'street' => ['label' => 'Улица', 'have_search' => true, 'orderable' => true],
            'house' => ['label' => 'Дом', 'have_search' => true, 'orderable' => true],
            'flat' => ['label' => 'Квартира', 'have_search' => true, 'orderable' => true],
            'birthday'=> ['label' => 'Дата рождения', 'have_search' => true, 'orderable' => true],
            'city' => ['label' => 'Город проживания', 'have_search' => true, 'orderable' => true],
            'medium_oid' => ['label' => 'Идентификатор Medium', 'have_search' => true, 'orderable' => true],
            'attraction_channel_id' => ['label' => 'Канал привлечения', 'have_search' => true, 'orderable' => true],
            'is_broadcast' => ['label' => 'Рассылка', 'have_search' => true, 'orderable' => true],
            'notification_service_id' => ['label' => 'Способ оповещения', 'have_search' => true, 'orderable' => true],
            'language_id' => ['label' => 'Язык', 'have_search' => true, 'orderable' => true],
            'status' => ['label' => 'Статус', 'have_search' => true, 'orderable' => true],
            'manager_id' => ['label' => 'Ответственный', 'have_search' => true, 'orderable' => true],
            'delete_button' => ['label' => 'Удалить', 'have_search' => false, 'orderable' => false],
        ];
        if (!Yii::$app->user->can('delete_contact')) {
            unset($result['delete_button']);
        }
        return $result;
    }

    public static function getFIOCols()
    {
        return [
            'surname',
            'name',
            'middle_name',
        ];
    }

    public function getFIOValues()
    {
        return [
            'surname' => $this->surname,
            'name' => $this->name,
            'middle_name' => $this->middle_name,
        ];
    }

    public static function getPhoneCols()
    {
        return [
            'first_phone',
            'second_phone',
            'third_phone',
            'fourth_phone',
        ];
    }

    public function getPhoneValues()
    {
        return [
            'first_phone' => $this->first_phone,
            'second_phone' => $this->second_phone,
            'third_phone' => $this->third_phone,
            'fourth_phone' => $this->fourth_phone,
        ];
    }

    public static function getEmailCols()
    {
        return [
            'first_email',
            'second_email',
        ];
    }

    public function getEmailValues()
    {
        return [
            'first_email' => $this->first_email,
            'second_email' => $this->second_email,
        ];
    }

    public static function getLocationCols()
    {
        return [
            'country',
            'region',
            'area',
            'city',
            'street',
            'house',
            'flat',
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
            $this->$phone_key = isset($contact_phones['exists'][$i]) ? $contact_phones['exists'][$i] : NULL;
            $i++;
        }
        $i = 0;
        foreach ($self_emails['empty'] as $email_key => $email_val) {
            $this->$email_key = isset($contact_emails['exists'][$i]) ? $contact_emails['exists'][$i] : NULL;
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
        $pattern = '/' . $cols_str . '/';
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
            $this->$col = NULL;
        }
    }

    public static function getContactByPhone($phone)
    {
        return self::find()->where(['is_deleted' => '0'])
            ->andWhere(['or', ['first_phone' => $phone], ['second_phone' => $phone], ['third_phone' => $phone], ['fourth_phone' => $phone]])
            ->one();
    }

    public function getManagerId()
    {
        $manager = $this->manager;
        if ($manager && $manager->role == User::ROLE_MANAGER) {
            return $manager->id;
        }
        return NULL;
    }

    public static function getManagerById($id)
    {
        $contact = self::find()
            ->with('manager')
            ->where(['id' => $id])
            ->one();
        return $contact->manager;
    }

    public static function getById($id)
    {
        return self::find()->where(['id' => $id])->one();
    }

    /**
     * @param int|array $id
     * @return bool
     */
    public static function deleteById($id)
    {
        if (empty($id)) {
            return false;
        }
        foreach ((is_array($id) ? $id : [$id]) as $contactId) {
            $contact = self::find()->with('tags')->where(['id' => $contactId])->one();
            if ($contact) {
                // Delete temporary records
                Contact::removeContInPool($contactId);

                // Delete tags
//                $contact->unlink('tags', $contact->tags[0]);

                $contact->is_deleted = 1;
                $contact->save();

                continue;
            }
            return false;
        }
        return true;
    }

    public function edit($related)
    {
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
//                $call->setContactIdByPhone($this->new_phone, $this->id);
            }
            $transaction->commit();

            return true;
        } catch (\Exception $ex) {
            $transaction->rollback();
            return false;
        }
    }

    public function getActions()
    {
        return $this->hasMany(Action::className(), ['contact_id' => 'id']);
    }

    public function getComments()
    {
        return $this->hasMany(ContactComment::className(), ['contact_id' => 'id']);
    }

    public function getCalls()
    {
        return $this->hasMany(Call::className(), ['contact_id' => 'id']);
    }


    public function getTags()
    {
        $use_archived_tags = false;
        if (Yii::$app->user->identity) {
            $use_archived_tags = Yii::$app->user->identity->getSetting('use_archive_tags');
        }

        if ($use_archived_tags) {
            return $this->hasMany(Tag::className(), ['id' => 'tag_id'])
                ->viaTable('contact_tag', ['contact_id' => 'id']);
        }
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])
            ->andOnCondition(['`tag`.`is_deleted`' => 0])
            ->viaTable('contact_tag', ['contact_id' => 'id']);
    }


    public function setTags($new_tags)
    {
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

    public static function getByTag($tag_id, $user_role, $filters, $for_export)
    {
        TempContactsPool::clearForManagers([Yii::$app->user->identity]);
        $query = new Query();
        $select = [
            '`contact`.*',
            '`user`.`firstname` as `operator_name`',
            '`call`.`status` as `call_status`',
            '`call`.`type` as `call_type`',
            '`call`.`comment` as `call_comment`',
            '`call`.`attitude_level` as `call_attitude_level`',
        ];
        $query
            ->join('LEFT JOIN', '`call`', '`contact`.`id` = `call`.`contact_id` AND `call`.`id` in (SELECT MAX(`call`.`id`) FROM `call` WHERE `contact`.`id` = `call`.`contact_id` AND `call`.`tag_id`=' . $tag_id . ')')
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
            $query->join('LEFT JOIN', '`contact_history`', '`contact`.`id` = `contact_history`.`contact_id` AND (`contact_history`.`type` = "imported_comment" OR `contact_history`.`type` = "comment")');
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
            $notCalledContactQuery->andWhere(['is', '`call`.`status`', NULL]);
            $contactsIdInPool = Contact::getContactsIdInPool($tag_id, Yii::$app->user->identity->id);
            $notCalledContactQuery->andWhere(['NOT IN', '`contact`.`id`', $contactsIdInPool]);
            $notCalledContactQuery->limit(1);
            //$dump = $notCalledContactQuery->createCommand()->rawSql;
            $notCalledContact = $notCalledContactQuery->all();
            if (count($notCalledContact)) {
                Contact::addContInPool($notCalledContact[0]['id'], Yii::$app->user->identity->id, $tag_id, NULL);
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

    public static function getCountByTag($tag_id)
    {
        $query = new Query();
        $query
            ->select(['COUNT(DISTINCT `contact`.`id`) as `count`', '`call`.`status` as `status`'])
            //->join('LEFT JOIN', '`call`', '`contact`.`id` = `call`.`contact_id` AND `call`.`tag_id`='.$tag_id)
            ->join('LEFT JOIN', '(SELECT * FROM `call` WHERE `call`.`tag_id`=' . $tag_id . ' GROUP BY `call`.`contact_id` ORDER BY `id` DESC) as `call`', '`contact`.`id` = `call`.`contact_id`')
            //->join('LEFT JOIN', 'call_manager', '`call`.`id` = `call_manager`.`call_id`')
            //->join('LEFT JOIN', '`user`', '`user`.`id` = `call_manager`.`manager_id`')
            ->join('LEFT JOIN', '`contact_tag`', '`contact`.`id` = `contact_tag`.`contact_id`')
            //->join('LEFT JOIN', '`tag`', '`tag`.`id` = `contact_tag`.`tag_id`')
            ->where(['`contact_tag`.`tag_id`' => $tag_id])
            ->andWhere(['`contact`.is_deleted' => 0])
            ->from('`contact`')
            ->groupBy('`call`.`status`')
            ->orderBy(['`call`.id' => SORT_DESC]);
        $dump = $query->createCommand()->rawSql;
        $counts = $query->all();
        $all_count = 0;
        $called_count = 0;
        foreach ($counts as $count) {
            if ($count['status'] != NULL) {
                $called_count += $count['count'];
            }
            $all_count += $count['count'];
        }
        return [
            'all' => $all_count,
            'called' => $called_count,
        ];
    }

    public static function getContactsIdInPool($tag_id, $operator_id)
    {
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttractionChannel()
    {
        return $this->hasOne(AttractionChannel::className(), ['id' => 'attraction_channel_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage()
    {
        return $this->hasOne(ContactLanguage::className(), ['id' => 'language_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotificationService()
    {
        return $this->hasOne(ContactNotificationService::className(), ['id' => 'notification_service_id']);
    }

    /**
     * @param $oid
     */
    public function getMediumObject($oid)
    {
        $url = self::MEDIUM_API_URL.self::MEDIUM_API_OBJECT.$oid;
        $response = (new HttpRequest($url, 'GET'))->send();

        $client = new Client([
            'baseUrl' => $url,
            'responseConfig' => [
                'format' => Client::FORMAT_XML
            ],
        ]);

        $request = $client->createRequest();
        $response = $request->send();
        if ($response->isOk) {
            $data = $response->data;
            var_dump($data);die;
        }
        echo $response->format; // outputs: 'json'
    }
    public function postMediumObject($oid){
        $url = self::MEDIUM_API_URL.self::MEDIUM_API_OBJECT.$oid;
        $response = (new HttpRequest($url, 'GET'))->send();

        $client = new Client([
            'baseUrl' => $url,
            'responseConfig' => [
                'format' => Client::FORMAT_XML
            ],
        ]);
        $client->setMethod('POST');

        $request = $client->createRequest();
        $response = $request->send();

        if ($response->isOk) {
            $newUserId = $response->data['id'];
            var_dump($newUserId);die;
        }
    }
    public static function getMediumObjects(){
        $url = self::MEDIUM_API_URL.self::MEDIUM_API_ITEM.'PACK';
//        $response = (new HttpRequest($url, 'GET'))->send();
        $client = new Client([
            'baseUrl' => $url,
            'responseConfig' => [
                'format' => Client::FORMAT_XML
            ],
        ]);
        $clients = [];
        $request = $client->createRequest();
        $response = $request->send();
        if ($response->isOk) {
            $clients = $response->data;
        }
        return $clients;
    }

    /**
     * @return bool
     */
    public function sendToCRM()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Amz-Meta-Crm-Api-Token: 6e5b4d74875ea09f3f888601c7825211']);


        $crm_host = Yii::$app->params['crm_host'];
        $url = $crm_host . "/api/v1/callcenter/contacts";

        $contact['PhoneNumber'] = $this->first_phone;
        if (!empty($this->second_phone))
            $contact['Phone2'] = $this->second_phone;
        if (!empty($this->third_phone))
            $contact['Phone3'] = $this->third_phone;
        if (!empty($this->fourth_phone))
            $contact['Phone4'] = $this->fourth_phone;
        if (!empty($this->first_email))
            $contact['Email1'] = $this->first_email;
        if (!empty($this->second_email))
            $contact['Email2'] = $this->second_email;
        if (!empty($this->name))
            $contact['FirstName'] = $this->name;
        if (!empty($this->surname))
            $contact['LastName'] = $this->surname;
        if (!empty($this->middle_name))
            $contact['MiddleName'] = $this->middle_name;
        if (!empty($this->country))
            $contact['Address[Country]'] = $this->country;
        if (!empty($this->region))
            $contact['Address[Region]'] = $this->region;
        if (!empty($this->area))
            $contact['Address[Area]'] = $this->area;
        if (!empty($this->city))
            $contact['Address[City]'] = $this->city;
        if (!empty($this->street))
            $contact['Address[Street]'] = $this->street;
        if (!empty($this->house))
            $contact['Address[Home]'] = $this->house;

        $history = ContactHistory::getByContactId($this->id);
        $contact['Comment'] = "";
        foreach ($history as $history_item) {
            $contact['Comment'] .= $history_item['datetime'] . ' ' . $history_item['text'] . PHP_EOL;
        }
//        $history = array_map(function($history_item) {
//            return ['datetime' => $history_item['datetime'], 'text' => $history_item['text']];
//        },$history);
        //$contact['Comment'] = $history;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $contact);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($contact));
        //curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($contact));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        //TODO temp
        $response_log_data = $response;
        if ($response == false) {
            $response_log_data = curl_error($ch);
        }
        curl_close($ch);
        $request_data = urldecode(http_build_query($contact));
        $log_data = date("j-m-Y G:i:s", time()) . "\r\n" . "Request: " . $request_data . "\r\n\r\n";
        file_put_contents(Yii::getAlias('@runtime_log_folder') . '/api_export_contact.log', $log_data, FILE_APPEND);
        file_put_contents(Yii::getAlias('@runtime_log_folder') . '/api_export_contact.log', "Response: " . $response_log_data . "\r\n", FILE_APPEND);
        file_put_contents(Yii::getAlias('@runtime_log_folder') . '/api_export_contact.log', "=============================================\r\n\r\n", FILE_APPEND);

        try {
            if (!is_array($response)) {
                $response = (array)json_decode($response);
            }

            if (!isset($response['Status']) || $response['Status'] == 0) {
                FailExportContacts::add($this->id);
                return false;
            } else {
                $this->sended_crm = 1;
                $this->save();
            }
        } catch (Exception $e) {
            FailExportContacts::add($this->id);
            return false;
        }

        return true;
    }
}
