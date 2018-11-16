<?php

namespace app\models;


use app\components\UtilHelper;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\db\StaleObjectException;
use yii\db\Transaction;
use yii\httpclient\Client;
use yii\httpclient\Exception;
use yii\httpclient\XmlParser;

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
 * @property string $city
 * @property string $status
 * @property string $birthday
 * @property integer $manager_id
 * @property boolean $is_deleted
 * @property integer $attraction_channel_id
 * @property integer $notification_service_id
 * @property integer $language_id
 * @property ContactNotificationService $notificationService
 * @property AttractionChannel $attractionChannel
 * @property ContactLanguage $language
 * @property User $manager
 * @property boolean $is_broadcast
 * @property boolean $link_with
 * @property string $medium_oid
 */
class Contact extends ActiveRecord
{

    public const IS_BROADCAST_TRUE = 1;
    public const IS_BROADCAST_FALSE = 0;
    public const LEAD = 1;
    public const CONTACT = 2;
    public const MEDIUM_API_URL = 'http://91.225.122.210:8080/api/H:1D13C88C20AA6C6/D:WORK/D:1D13C9303C946F9/C:1D45F18F27C737D';
    public const MEDIUM_API_OBJECT = '/O:';
    public const MEDIUM_API_ITEM = '/I:';//STATUSES
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
        'birthday',
        'city',
        'status',
        'manager_id',
        'is_deleted',
        'remove_tags',
        'attraction_channel_id',
        'notification_service_id',
        'is_broadcast',
        'language_id',
        'medium_oid',
        'link_with'
    ];//STATUSES
    public static $mediumToCrmFields = [

    ];
    public static $statuses = [
        self::LEAD => 'Лид',
        self::CONTACT => 'Пациент',

    ]; //FOR SINGLE OBJECT
    public static $broadcast = [
        self::IS_BROADCAST_TRUE => 'ДА',
        self::IS_BROADCAST_FALSE => 'НЕТ',
    ]; //FOR LISTINGS
    public $is_called;
    public $remove_tags;
    private $tags;
    private $sended_crm;

    public static function tableName(): string
    {
        return '{{%contact}}';
    }

    public static function getTableColumns(): array
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

    public static function getAllSafeCols(): array
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
            'birthday',
            'city',
            'is_broadcast',
            'language_id',
            'attraction_channel_id',
            'notification_service_id',
            'status',
            'manager_id',
            'medium_oid',
            'manager_id',
            'link_with'
        ];
    }

    public static function getColsForTableView(): array
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
            'city' => ['label' => 'Город проживания', 'have_search' => true, 'orderable' => true],
            'birthday' => ['label' => 'Дата рождения', 'have_search' => true, 'orderable' => true],
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

    public static function getFIOCols(): array
    {
        return [
            'surname',
            'name',
            'middle_name',
        ];
    }

    public static function getContactByPhone($phone)
    {
        return self::find()->where(['is_deleted' => '0'])
            ->andWhere(['or', ['first_phone' => $phone], ['second_phone' => $phone], ['third_phone' => $phone], ['fourth_phone' => $phone]])
            ->one();
    }

    public static function getManagerById($id): User
    {
        $contact = self::find()
            ->with('manager')
            ->where(['id' => $id])
            ->one();
        /** @var Contact $contact */
        return $contact->manager;
    }

    public static function getById($id)
    {
        return self::find()->where(['id' => $id])->one();
    }

    /**
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public static function deleteById($id): bool
    {
        if (empty($id)) {
            return false;
        }
        foreach (\is_array($id) ? $id : [$id] as $contactId) {
            $contact = self::find()->with('tags')->where(['id' => $contactId])->one();
            if ($contact) {
                self::removeContInPool($contactId);
                /** @var Contact $contact */
                $contact->is_deleted = true;
                $contact->save();

                continue;
            }
            return false;
        }
        return true;
    }

    /**
     * @param $contact_id
     * @return bool|false|int
     * @throws \Exception
     */
    public static function removeContInPool($contact_id)
    {
        $cont_pool = TempContactsPool::findOne(['contact_id' => $contact_id]);
        if ($cont_pool) {
            try {
                return $cont_pool->delete();
            } catch (StaleObjectException $e) {
                return $e->errorInfo;
            }
        }
        return false;
    }

    /**
     * @param $tag_id
     * @param $user_role
     * @param $filters
     * @param $for_export
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getByTag($tag_id, $user_role, $filters, $for_export): array
    {
        /** @var User $user */
        $user = self::getIdentity();
        $query = self::selectContactsFromDBByTag($tag_id, $for_export);
        foreach ($filters as $name => $value) {
            switch ($name) {
                case 'manager_id':
                    if ($user_role !== User::ROLE_OPERATOR) {
                        $query->andWhere(['`call_manager`.`manager_id`' => $value]);
                    }
                    break;
                case 'status':
                    $query->andWhere(['`call`.`status`' => $value]);
                    break;
                case 'comment':
                    $query->andWhere("`call`.`comment` LIKE %$value%'");
                    break;
                case 'attitude_level':
                    $query->andWhere(['`call`.`attitude_level`' => $value]);
                    break;
            }
        }
        if ($user_role === User::ROLE_OPERATOR) {
            $notCalledContactQuery = clone $query;
            $contacts = $query->andWhere([
                '`call_manager`.`manager_id`' => $user->id
            ])
                ->all();
            $notCalledContactQuery->andWhere(['is', '`call`.`status`', NULL]);
            $contactsIdInPool = self::getContactsIdInPool($tag_id, $user->id);
            $notCalledContactQuery->andWhere(['NOT IN', '`contact`.`id`', $contactsIdInPool]);
            $notCalledContactQuery->limit(1);
            $notCalledContact = $notCalledContactQuery->all();
            if (\count($notCalledContact)) {
                try {
                    self::addContInPool($notCalledContact[0]['id'], $user->id, $tag_id);
                } catch (\Exception $e) {
                    throw new \yii\db\Exception($e->getMessage());
                }
            }
            $contacts = array_merge($contacts, $notCalledContact);
        } else {
            $contacts = $query->all();
        }
        return $contacts;
    }

    /**
     * @return null|\yii\web\IdentityInterface
     */
    private static function getIdentity(): ?\yii\web\IdentityInterface
    {
        return Yii::$app->user->identity;
    }

    /**
     * @param $tag_id
     * @param $for_export
     * @return Query
     */
    private static function selectContactsFromDBByTag($tag_id, $for_export): Query
    {
        TempContactsPool::clearForManagers([self::getIdentity()]);
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
            ->join('LEFT JOIN', 'call_manager', '`call`.`id` = `call_manager`.`call_id`')
            ->join('LEFT JOIN', '`user`', '`user`.`id` = `call_manager`.`manager_id`')
            ->join('LEFT JOIN', '`contact_tag`', '`contact`.`id` = `contact_tag`.`contact_id`')
            ->where(['`contact_tag`.`tag_id`' => $tag_id])
            ->andWhere(['`contact`.is_deleted' => 0])
            ->from('`contact`')
            ->orderBy(['`call`.id' => SORT_DESC]);
        if ($for_export) {
            $select = array_merge($select, [
                '`contact_history`.`text` as `history_text`',
                '`contact_history`.`datetime` as `history_datetime`',
            ]);
            $query->join('LEFT JOIN', '`contact_history`', '`contact`.`id` = `contact_history`.`contact_id` AND (`contact_history`.`type` = "imported_comment" OR `contact_history`.`type` = "comment")');
        }
        return $query->select($select);
    }

    public static function getContactsIdInPool($tag_id, $operator_id): array
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

    /**
     * @param $contact_id
     * @param $manager_id
     * @param $tag_id
     * @return bool
     * @throws \Exception
     */
    public static function addContInPool($contact_id, $manager_id, $tag_id): bool
    {

        $cont_pool = TempContactsPool::findOne(['contact_id' => $contact_id, 'manager_id' => $manager_id, 'tag_id' => $tag_id]);
        if ($cont_pool) {
            try {
                /** @var TempContactsPool $cont_pool */
                $cont_pool->delete();
            } catch (StaleObjectException $e) {
                return $e->errorInfo;
            }
        }

        $cont_pool = new TempContactsPool(['contact_id' => $contact_id, 'manager_id' => $manager_id, 'tag_id' => $tag_id]);
        return $cont_pool->save();
    }

    public static function getCountByTag($tag_id): array
    {
        $query = new Query();
        $query
            ->select(['COUNT(DISTINCT `contact`.`id`) as `count`', '`call`.`status` as `status`'])
            ->join('LEFT JOIN', '(SELECT * FROM `call` WHERE `call`.`tag_id`=' . $tag_id . ' GROUP BY `call`.`contact_id` ORDER BY `id` DESC) as `call`', '`contact`.`id` = `call`.`contact_id`')
            ->join('LEFT JOIN', '`contact_tag`', '`contact`.`id` = `contact_tag`.`contact_id`')
            ->where(['`contact_tag`.`tag_id`' => $tag_id])
            ->andWhere(['`contact`.is_deleted' => 0])
            ->from('`contact`')
            ->groupBy('`call`.`status`')
            ->orderBy(['`call`.id' => SORT_DESC]);
        $counts = $query->all();
        $all_count = 0;
        $called_count = 0;
        foreach ($counts as $count) {
            if ($count['status'] !== NULL) {
                $called_count += $count['count'];
            }
            $all_count += $count['count'];
        }
        return [
            'all' => $all_count,
            'called' => $called_count,
        ];
    }

    public static function getMediumObject($oid)
    {
        $url = self::MEDIUM_API_URL . self::MEDIUM_API_OBJECT . $oid;
        $client = new Client([
            'responseConfig' => [
                'format' => Client::FORMAT_XML
            ]

        ]);
        try {
            $xml = simplexml_load_string($client->get($url)->send()->getContent());
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return $xml->attributes();
    }

    /**
     * @param array $data
     * @return int|null|string
     */
    public static function postMediumObject($data)
    {
        $url = self::MEDIUM_API_URL . '?method=save_object';
        $client = self::buildMediumRequestBody($data, $url);
        try {
            $newUserResponse = $client->send();

        } catch (Exception $e) {
            return $e->getMessage();
        }
        $newUserId = 0;
        if ($newUserResponse->isOk && $newUserResponse !== 0) {
            $xmlParser = new XmlParser();
            $xml = $xmlParser->parse($newUserResponse);
            $newUserId = $xml[0];
        }
        if ($newUserId !== 0) {
            return $newUserId;
        }
        return $newUserId;
    }

    private static function buildMediumRequestBody($data, $url): \yii\httpclient\Request
    {

        if(!empty($data['birthday'])) {
            $birthday = \DateTime::createFromFormat('Y-m-d',$data['birthday']);
            if($birthday) {
                $birthday = $birthday->format('Y-m-d\TH:i:s.0');
            } else {
                $birthday ="";
            }
        } else {
            $birthday ="";
        }

        $body = '<OBJECT 
                    name="' . $data['surname'] . ' ' . $data['name'] . ' ' . $data['middle_name'] . '" 
                    ТелефонМоб="' . $data['phones'] . '" 
                    E-mail="' . $data['emails'] . ' " 
                    ДатаРождения="' . $birthday . '" 
                    Город="' . $data['city'] . '" 
                    ИсточникИнфомации="' . $data['attraction_channel_id'] . '" 
                    ОбычноОплачивает="Самостоятельный расчет" />';
        $client = new Client([
            'baseUrl' => $url,
            'responseConfig' => [
                'format' => Client::FORMAT_XML
            ]
        ]);
        return $client->post($url, $body);
    }

    public static function updateMediumObject($oid, $data)
    {
        if ($oid) {
            $url = self::MEDIUM_API_URL . self::MEDIUM_API_OBJECT . $oid . '?method=save_object';
            $client = self::buildMediumRequestBody($data, $url);
        } else {
            $url = self::MEDIUM_API_URL . '?method=save_object';
            $client = self::buildMediumRequestBody($data, $url);
        }
        try {
            $newUserResponse = $client->send();
        } catch (Exception $e) {
            return $e->getMessage();
        }
        if ($newUserResponse->isOk && $newUserResponse !== 0) {
            $xmlParser = new XmlParser();
            $xml = $xmlParser->parse($newUserResponse);
            return $xml[0];
        }
        return false;
    }

    /**
     * @return \yii\httpclient\Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public static function getMediumObjects(): \yii\httpclient\Response
    {
        $url = self::MEDIUM_API_URL . self::MEDIUM_API_ITEM . 'PACK';
        $client = new Client([
            'baseUrl' => $url,
            'responseConfig' => [
                'format' => Client::FORMAT_XML
            ],
        ]);
        $request = $client->createRequest();
        return $request->send();
    }

    public function fields()
    {
        return [
            'oid' => 'medium_oid',
            'E-mail' => 'first_email',
            'name' => function () {
                return  $this->surname . ' ' . $this->name . ' ' . $this->middle_name;
            },
            'ТелефонМоб' => 'first_phone',
            'ДатаРождения' => function () {
                if(!empty($this->birthday)) {
                    $birthday = \DateTime::createFromFormat('Y-m-d',$this->birthday);
                    if($birthday) {
                        return $birthday->format('Y-m-d\TH:i:s');
                    }
                }
                return "";
            },
            'ИсточникИнфомации' => 'attraction_channel_id'
        ];

    }

    public function getManager(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::className(), ['id' => 'manager_id']);
    }

    public function beforeValidate(): bool
    {
        if ($this->isNewRecord) {
            $this->int_id = UtilHelper::getRandomNumbers(7);
        }
        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['int_id'], 'required'],
            [['int_id', 'manager_id'], 'integer'],
            [['first_phone', 'second_phone', 'third_phone', 'fourth_phone', 'first_email', 'second_email', 'country', 'city', 'status'], 'string', 'max' => 255],
            [['int_id', 'manager_id', 'notification_service_id', 'language_id', 'attraction_channel_id'], 'integer'],
            [['first_phone', 'second_phone', 'third_phone', 'fourth_phone', 'first_email', 'second_email', 'birthday', 'country', 'city'], 'string', 'max' => 255],
            [['int_id', 'manager_id', 'notification_service_id', 'language_id', 'attraction_channel_id'], 'integer'],
            [['first_phone', 'second_phone', 'third_phone', 'fourth_phone', 'first_email', 'second_email', 'birthday', 'country', 'city'], 'string', 'max' => 255],
            [['name', 'link_with', 'surname', 'middle_name'], 'string', 'max' => 150],
            [['first_email', 'second_email'], 'string', 'max' => 255],
            [['notification_service_id'], 'exist', 'targetClass' => ContactNotificationService::className(), 'targetAttribute' => ['notification_service_id' => 'id']],
            [['attraction_channel_id'], 'exist', 'targetClass' => AttractionChannel::className(), 'targetAttribute' => ['attraction_channel_id' => 'id']],
            [['language_id'], 'exist', 'targetClass' => ContactLanguage::className(), 'targetAttribute' => ['language_id' => 'id']],
            [['sended_crm', 'status', 'medium_oid'], 'safe'],
            [['is_broadcast'], 'boolean', 'trueValue' => 1, 'falseValue' => 0],

        ];
    }

    public function getFIOValues(): array
    {
        return [
            'surname' => $this->surname,
            'name' => $this->name,
            'middle_name' => $this->middle_name,
        ];
    }

    public function getPhoneValues(): array
    {
        return [
            'first_phone' => $this->first_phone,
            'second_phone' => $this->second_phone,
            'third_phone' => $this->third_phone,
            'fourth_phone' => $this->fourth_phone,
        ];
    }

    public function getEmailValues(): array
    {
        return [
            'first_email' => $this->first_email,
            'second_email' => $this->second_email,
        ];
    }

    public function mergeTogether($contact): bool
    {
        foreach ($this->attributes as $prop_key => $prop_val) {
            $this->$prop_key = (empty($prop_val) || $prop_val === '') ? $contact->$prop_key : $contact->$prop_val;
        }
        $phone_cols = self::getPhoneCols();
        $email_cols = self::getEmailCols();
        $location_cols = self::getLocationCols();
        $contact_phones = self::getPropValues($contact, $phone_cols);
        $contact_emails = self::getPropValues($contact, $email_cols);
        $contact_location = self::getPropValues($contact, $location_cols);

        $self_phones = self::getPropValues($this, $phone_cols);
        $self_emails = self::getPropValues($this, $email_cols);

        if (\count($contact_phones['exists']) > \count($self_phones['empty'])) {
            $this->addError('prop_count_miss', 'Ошибка: телефоны переполнены');
            return false;
        }

        if (\count($self_emails['empty']) > \count($contact_emails['exists'])) {
            $this->addError('prop_count_miss', 'Ошибка: email-ы переполнены');
            return false;
        }

        $i = 0;
        foreach ($self_phones['empty'] as $phone_key => $phone_val) {
            $this->$phone_key = $contact_phones['exists'][$i] ?? NULL;
            $i++;
        }
        $i = 0;
        foreach ($self_emails['empty'] as $email_key => $email_val) {
            $this->$email_key = $contact_emails['exists'][$i] ?? NULL;
            $i++;
        }

        if (\count($contact_location['exists']) > 0) {
            $this->setNull($location_cols);

            foreach ($location_cols as $col) {
                $this->$col = $contact->$col;
            }
        }
        return true;
    }

    public static function getPhoneCols(): array
    {
        return [
            'first_phone',
            'second_phone',
            'third_phone',
            'fourth_phone',
        ];
    }

    public static function getEmailCols(): array
    {
        return [
            'first_email',
            'second_email',
        ];
    }

    public static function getLocationCols(): array
    {
        return [
            'country',
            'city'
        ];
    }

    public static function getPropValues($contact, $cols): array
    {
        $values = ['exists' => [], 'empty' => []];
        $cols_str = implode('|', $cols);
        $pattern = '/' . $cols_str . '/';
        foreach ($contact->attributes as $prop_key => $prop_val) {
            if (preg_match($pattern, $prop_key)) {
                if ($prop_val === null || $prop_val === '') {
                    $values['empty'][$prop_key] = $prop_val;
                } else {
                    $values['exists'][] = $prop_val;
                }
            }
        }
        return $values;
    }

    public function setNull($cols): void
    {
        foreach ($cols as $col) {
            $this->$col = NULL;
        }
    }

    public function getManagerId()
    {
        $manager = $this->manager;
        /** @var User $manager */
        if ($manager && $manager->role === User::ROLE_MANAGER) {
            /** @var User $manager */
            return $manager->id;
        }
        return NULL;
    }

    public function edit($related): ?bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        $isSaved = false;
        try {
            if ($this->save()) {
                $isSaved = false;
            }
            if (isset($related['tags'])) {
                $this->tags = $related['tags'];
            }
            if ($this->isNewRecord) {
                $contact_history = new ContactHistory();
                $contact_history->add($this->id, 'создан контакт', 'new_contact');
                $contact_history->save();
                $isSaved = true;
            }
            $transaction->commit();
            /** @var boolean $isSaved */
            return $isSaved ? false : true;
        } catch (\Exception $ex) {
            /** @var Transaction $transaction */
            try {
                $transaction->rollBack();
            } catch (\yii\db\Exception $e) {
                return false;
            }
            return false;
        }
    }

    public function getActions(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Action::className(), ['contact_id' => 'id']);
    }

    public function getComments(): \yii\db\ActiveQuery
    {
        return $this->hasMany(ContactComment::className(), ['contact_id' => 'id']);
    }

    public function getCalls(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Call::className(), ['contact_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags(): \yii\db\ActiveQuery
    {
        $use_archived_tags = false;
        $identity = Yii::$app->user->getIdentity();
        if ($identity) {
            /** @var UserTag $use_archived_tags */
            /** @var User $identity */
            $use_archived_tags = $identity->getSetting('use_archive_tags');
        }

        if ($use_archived_tags) {
            return $this->hasMany(Tag::className(), ['id' => 'tag_id'])
                ->viaTable('contact_tag', ['contact_id' => 'id']);
        }
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])
            ->andOnCondition(['`tag`.`is_deleted`' => 0])
            ->viaTable('contact_tag', ['contact_id' => 'id']);
    }

    public function setTags($new_tags): void
    {
        if ($this->remove_tags === true) {
            $this->unlinkAll('tags');
        }
        foreach ($new_tags as $new_tag) {
            /** @var Tag $new_tag */
            $new_tag->save();
            if (!ContactTag::find()->where(['contact_id' => $this->id, 'tag_id' => $new_tag->id])->exists()) {
                $this->link('tags', $new_tag);
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttractionChannel(): \yii\db\ActiveQuery
    {
        return $this->hasOne(AttractionChannel::className(), ['id' => 'attraction_channel_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage(): \yii\db\ActiveQuery
    {
        return $this->hasOne(ContactLanguage::className(), ['id' => 'language_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotificationService(): \yii\db\ActiveQuery
    {
        return $this->hasOne(ContactNotificationService::className(), ['id' => 'notification_service_id']);
    }

    /**
     * @return bool
     */
    public function sendToCRM(): bool
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Amz-Meta-Crm-Api-Token: 6e5b4d74875ea09f3f888601c7825211']);


        $crm_host = Yii::$app->params['crm_host'];
        $url = $crm_host . '/api/v1/callcenter/contacts';

        $contact['PhoneNumber'] = $this->first_phone;
        if (!empty($this->second_phone)) {
            $contact['Phone2'] = $this->second_phone;
        }
        if (!empty($this->third_phone)) {
            $contact['Phone3'] = $this->third_phone;
        }
        if (!empty($this->fourth_phone)) {
            $contact['Phone4'] = $this->fourth_phone;
        }
        if (!empty($this->first_email)) {
            $contact['Email1'] = $this->first_email;
        }
        if (!empty($this->second_email)) {
            $contact['Email2'] = $this->second_email;
        }
        if (!empty($this->name)) {
            $contact['FirstName'] = $this->name;
        }
        if (!empty($this->surname)) {
            $contact['LastName'] = $this->surname;
        }
        if (!empty($this->middle_name)) {
            $contact['MiddleName'] = $this->middle_name;
        }
        if (!empty($this->country)) {
            $contact['Country'] = $this->country;
        }
        if (!empty($this->city)) {
            $contact['City'] = $this->city;
        }
        if (!empty($this->is_broadcast)) {
            $contact['is_broadcast'] = $this->is_broadcast;
        }
        if (!empty($this->birthday)) {
            $contact['birthday'] = $this->birthday;
        }
        if (!empty($this->status)) {
            $contact['status'] = $this->status;
        }
        if (!empty($this->notification_service_id)) {
            $contact['notification_service_id'] = $this->notification_service_id;
        }
        if (!empty($this->attraction_channel_id)) {
            $contact['attraction_channel_id'] = $this->attraction_channel_id;
        }
        if (!empty($this->language_id)) {
            $contact['language_id'] = $this->language_id;
        }
        if (!empty($this->medium_oid)) {
            $contact['medium_oid'] = $this->medium_oid;
        }
        if (!empty($this->link_with)) {
            $contact['link_with'] = $this->link_with;
        }

        $history = ContactHistory::getByContactId($this->id);
        $contact['Comment'] = '';
        foreach ($history as $history_item) {
            $contact['Comment'] .= $history_item['datetime'] . ' ' . $history_item['text'] . PHP_EOL;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $contact);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $response_log_data = $response;
        if ($response === false) {
            $response_log_data = curl_error($ch);
        }
        curl_close($ch);
        $request_data = urldecode(http_build_query($contact));
        $log_data = date('j-m-Y G:i:s') . "\r\n" . 'Request: ' . $request_data . "\r\n\r\n";
        file_put_contents(Yii::getAlias('@runtime_log_folder') . '/api_export_contact.log', $log_data, FILE_APPEND);
        file_put_contents(Yii::getAlias('@runtime_log_folder') . '/api_export_contact.log', 'Response: ' . $response_log_data . "\r\n", FILE_APPEND);
        file_put_contents(Yii::getAlias('@runtime_log_folder') . '/api_export_contact.log', "=============================================\r\n\r\n", FILE_APPEND);

        if (!\is_array($response)) {
            $response = (array)json_decode($response);
        }

        if (!isset($response['Status']) || $response['Status'] === 0) {
            FailExportContacts::add($this->id);
            return false;
        }

        $this->sended_crm = 1;
        $this->save();

        return true;
    }
}
