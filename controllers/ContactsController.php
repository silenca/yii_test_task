<?php

namespace app\controllers;

use app\components\Filter;
use app\components\MediumApi;
use app\components\Notification;
use app\components\widgets\ContactTableWidget;
use app\models\Action;
use app\models\ActionType;
use app\models\Call;
use app\models\Cdr;
use app\models\Contact;
use app\models\ContactComment;
use app\models\ContactHistory;
use app\models\ContactRingRound;
use app\models\ContactScheduledCall;
use app\models\ContactScheduledEmail;
use app\models\ContactStatusHistory;
use app\models\ContactsVisits;
use app\models\ContactTag;
use app\models\ContactVisitLog;
use app\models\Departments;
use app\models\forms\CommentForm;
use app\models\forms\ContactForm;
use app\models\Speciality;
use app\models\User;
use SimpleXML;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Request;

class ContactsController extends BaseController
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
//                            'index',
                            'view',
                            'history',
                            'addcomment',
//                            'getdata',
                            'edit',
//                            'hide-columns',
                            'get-contact-by-phone',
                            'get-contact-by-phone',
//                            'search',
//                            'link-with',
                            'objectschedulecall',
                            'objectscheduleemail',
                            'ring-round',
                            'accept-call'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [
                            'save',
                        ],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [
                            'index',
                            'search-visit',
                            'send-visit',
                            'getdata',
                            'hide-columns',
                            'search',
                            'link-with',
                        ],
                        'allow' => false,
                        'roles' => ['operator'],
                    ],
                    [
                        'actions' => ['delete', 'delete-filtered'],
                        'allow' => true,
                        'roles' => ['admin', 'supervisor'],
                    ],
                    [
                        'actions' => [
                            'remove-tag',
                            'index',
                            'search-visit',
                            'send-visit',
                            'getdata',
                            'new-view',
                            'hide-columns',
                            'search',
                            'contacts',
                            'get-medium-object',
                            'update-medium-client',
                            'save-medium-client',
                            'link-with',
                            'sync-contacts'
                        ],
                        'allow' => true,
                        'roles' => ['admin', 'manager', 'supervisor'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'edit' => ['post'],
                    'delete' => ['post'],
                    'delete-filtered' => ['post'],
                    'addcomment' => ['post'],
                    'objectschedulecall' => ['post'],
                    'objectscheduleemail' => ['post'],
                    'sync-contacts' => ['get'],
                    'ring-round' => ['post'],
                    'link-with' => ['post'],
                    'search' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $session = Yii::$app->session;
        $hide_columns = $session->get('contact_hide_columns');
        /**
         * @var $user User
         */
        $user = User::find()->where(['id' => Yii::$app->user->identity->getId()])->one();
        $cols = $user->cols_config;
        if ($cols !== null) {
            $cols = \json_decode($cols, true);
        } else {
            $cols = [];
        }

        $detect = new \Mobile_Detect();
        if (!$hide_columns) {
            if (isset($cols['contacts'])) {
                $hide_columns = $cols['contacts'];
            } else {
                if ($detect->isMobile()) {
                    $hide_columns = ["middle_name", "emails", "country", "city", "delete_button", 'int_id', 'link_with', 'tags'];
                } else {
                    $hide_columns = ["surname", "name", "middle_name", "emails", "country", "delete_button"];
                }
            }
        }
        $cols['contacts'] = $hide_columns;
        $user->cols_config = \json_encode($cols);
        $user->save();


        $table_cols = Contact::getColsForTableView();
        $filter_cols = Contact::getColsForTableView();

        if ($user->getUserRole() === 'manager') {
            unset($table_cols['delete_button']);
            unset($filter_cols['delete_button']);
        }

        $config = $user->filter_config;
        if ($config !== null) {
            $config = \json_decode($config, true);
            if (isset($config['contacts'])) {
                foreach ($config['contacts'] as $k => $v) {
                    if (!empty($k) && !empty($filter_cols[$k]['label']) && isset($filter_cols[$k]))
                        $filter_cols[$k]['value'] = $v;
                }
            }
        }
        $key = 1;
        unset($filter_cols['id']);
        return $this->render('index', ['hide_columns' => $hide_columns, 'table_cols' => $table_cols, 'filter_cols' => $filter_cols, 'key' => $key]);
    }

    public function actionSearchVisit(): string
    {
        $response = ['data'=>[], 'error'=>'', 'notify'];
        if(Yii::$app->request->isAjax){
            $visitSpecialityId = intval(Yii::$app->request->post('visitSpeciality'));
            $visitDepartmentsId = intval(Yii::$app->request->post('visitDepartments'));
            $visitDate = date("Y-m-d", strtotime(Yii::$app->request->post('visitDate')));

            $visitSpeciality = Speciality::find()->where(['id'=>$visitSpecialityId])->one();
            $visitDepartments = Departments::find()->where(['id'=>$visitDepartmentsId])->one();
            if($visitSpeciality && $visitDepartments && $visitDate){
                // Список доктаров
                $doctorsSchedule = Yii::$app->medium->doctorsSchedule($visitDepartments->api_url, $visitDate, $visitSpeciality->oid);

                if(empty($doctorsSchedule['error'])){
                    // Список забронированих сиансов к доктору
                    $doctorsVisit = Yii::$app->medium->doctorsVisit($visitDepartments->api_url, $visitDate);
                    if(!empty($doctorsVisit['data'])){
                        foreach ($doctorsVisit['data'] as $doctorVisit){
                            $timeStart = strtotime($doctorVisit['date']);
                            $timeStop = $timeStart + $doctorVisit['time'] * 60;
                            for($timeFor = $timeStart; $timeFor < $timeStop;$timeFor = $timeFor + 1800){
                                if(isset($doctorsSchedule['data'][$doctorVisit['oidV']]['gr_time'][$timeFor])){
                                    $doctorsSchedule['data'][$doctorVisit['oidV']]['gr_time'][$timeFor]['class'] = 'disable';
                                }
                            }
                        }
                    }else{
//                        $response['error'] = $doctorsVisit['error'];
                    }
                }else{
                    $response['notify'] = $doctorsSchedule['error'];
                    $response['error'] = 'Нет доступных "окон" для формирования визита';
                }

                // Список свободних кабинетов
                $cabinetsList = Yii::$app->medium->cabinetList($visitDepartments->api_url, $visitDate);
                if(empty($cabinetsList['error'])){
                    // Список забронированых кабинетов
                    $cabinetsSchedule = Yii::$app->medium->cabinetSchedule($visitDepartments->api_url,$visitDate);
                    if(!empty($cabinetsSchedule['data'])){
                        foreach ($cabinetsSchedule['data'] as $cabinetSchedule){
                            $timeStartK = strtotime($cabinetSchedule['date']);
                            $timeStopK = $timeStartK + $cabinetSchedule['time'] * 60;
                            for($timeForK = $timeStartK; $timeForK < $timeStopK;$timeForK = $timeForK + 1800){
                                if(isset($cabinetsList['data'][$cabinetSchedule['oidK']]['gr_time'][$timeForK])){
                                    $cabinetsList['data'][$cabinetSchedule['oidK']]['gr_time'][$timeForK]['class'] = 'disable';
                                }
                            }
                        }
                    }else{
//                        $response['error'] .= $cabinetsSchedule['error'];
                    }
                }else{
                    $response['notify'] .= " " . $cabinetsList['error'];
                    $response['error'] .= " " . 'Нет доступных "окон" для формирования визита';
                }

                $response['data']['doctors'] = $this->renderPartial('//parts/contact_visit',[
                    'type' => 'doctor',
                    'visitDate' => $visitDate,
                    'data' => $doctorsSchedule['data']
                ]);
                $response['data']['cabinets'] = $this->renderPartial('//parts/contact_visit',[
                    'type' => 'cabinet',
                    'visitDate' => $visitDate,
                    'data' => $cabinetsList['data']
                ]);

            }else{
                $response['error'] = "Нужно указать все данные";
            }
        }
        return Json::encode($response);
    }

    public function actionSendVisit(): string
    {
        $request = Yii::$app->request;
        /**@var $request Request*/
        $response = ['data'=>[], 'notify'=>'', 'error'=>''];
        try {
            if(!$request->isAjax) {
                throw new \Exception('This method is accessible only using AJAX request');
            }

            $contactId = intval($request->post('contactId'));// ID Пациент
            $speciality = intval($request->post('speciality'));
            $departmentId = intval($request->post('department'));

            $cabinetId = htmlspecialchars($request->post('cabinetId'));
            $doctorName = htmlspecialchars($request->post('doctorName'));//Врач
            $cabinetName = htmlspecialchars($request->post('cabinetName'));//Кабинет
            $visitComment = htmlspecialchars($request->post('visitComment'));//Визит
            $doctorId = htmlspecialchars($request->post('doctorId'));//Врач

            $bookingDate = date("Y-m-d", strtotime($request->post('bookingDate')));//ДатаПриема

            $doctorStartTime = strtotime($bookingDate . ' ' . $request->post('doctorStartTime'));//ДатаПриема
            $doctorEndTime = strtotime($bookingDate . ' ' . $request->post('doctorEndTime'));
            $cabinetStartTime = strtotime($bookingDate . ' ' . $request->post('cabinetStartTime'));
            $cabinetEndTime = strtotime($bookingDate . ' ' . $request->post('cabinetEndTime'));

            $timeReceipt = ($doctorEndTime - $doctorStartTime) / 60;//ВремяПриема
            $visitDate = date(ContactsVisits::DATE_FORMAT, $doctorStartTime);

            if(($doctorStartTime != $cabinetStartTime) || ($doctorEndTime != $cabinetEndTime)) {
                throw new \Exception('Время записи к доктору, должно совпадать с временем записи в кабинет');
            }

            $department = Departments::findOne($departmentId);
            if(!$department) {
                throw new \Exception('Отделение с ID '.$departmentId.' не найдено');
            }

            $contact = Contact::findOne($contactId);
            if(!$contact) {
                throw new \Exception('Контакт не найден');
            }

            // Save visit
            $visit = new ContactsVisits();
            $visit->setAttributes([
                'create_date' => date(ContactsVisits::DATE_FORMAT),
                'edit_date' => date(ContactsVisits::DATE_FORMAT),
                'visit_date' => $visitDate,
                'contact_id' => $contact->id,
                'department_id' => $department->id,
                'status' => ContactsVisits::STATUS_PENDING,
                'manager_id' => Yii::$app->user->id,
                'sync_status' => ContactsVisits::SYNC_STATUS_NEW,
                'cabinet_oid' => $cabinetId,
                'cabinet_name' => $cabinetName,
                'doctor_oid' => $doctorId,
                'doctor_name' => $doctorName,
                'comment' => $visitComment,
                'time' => $timeReceipt,
            ]);

            if(!$visit->save()) {
                throw new \Exception(implode('; ', $visit->getErrorSummary(true)));
            }

            $response['notify'] = 'Запись была успешно сохранена. ID визита #'.$visit->id;

            // Create visit action
            $action = new Action();
            $action_type = ActionType::find()->where(['name' => 'scheduled_visit'])->one();
            $action->add($contact->id, $action_type->id, [], $visitDate);

            // Create history item
            $contact_history = new ContactHistory();
            $contact_history->add($contact->id, 'Запланированный визит на '.$visitDate, 'scheduled_visit');
            $contact_history->save();
        } catch(\Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return Json::encode($response);
    }

    public function actionGetdata(): void
    {
        $request_data = Yii::$app->request->get();
        $userEntity = Yii::$app->user->getIdentity();
        $contact_tableName = Contact::tableName();
        $query = Contact::find()->with('manager', 'tags')->distinct($contact_tableName . '.id');
        $query->where([$contact_tableName . '.is_deleted' => '0']);
        $columns = Contact::getColsForTableView();
        /** @var User $userEntity */
        $user_id = $userEntity->id;
        $user_role = $userEntity->role;

        //Sorting
        if (isset($request_data['order'])) {
            $order_by_sort = $request_data['order'][0]['dir'] === 'asc' ? SORT_ASC : SORT_DESC;
            $sort_column = array_keys($columns)[$request_data['order'][0]['column']];
            if (isset($columns[$sort_column]['db_cols'])) {
                $sort_column = $columns[$sort_column]['db_cols'][0];
            }

            $sorting = [
                $contact_tableName . '.' . $sort_column => $order_by_sort,
            ];
        } else {
            $sorting = [
                $contact_tableName . '.id' => SORT_DESC,
            ];
        }

        if ($user_role === 'operator') {
            $query->joinWith('tags.users')->andWhere(['user.id' => $user_id]);
        }
        $query_total = clone $query;
        $total_count = $query_total->count();

        //Filtering
        $user = User::find()->where(['id' => Yii::$app->user->identity->getId()])->one();
        $config = $user->filter_config;
        $config = \json_decode($config, true);
        if (!isset($config['contacts'])) {
            $config['contacts'] = [];
        }
        foreach ($request_data['columns'] as $column) {
            if (!empty($column['search']['value'])) {
                if (isset($columns[$column['name']]['db_cols'])) {
                    $db_cols_where = ['or'];
                    foreach ($columns[$column['name']]['db_cols'] as $db_col_i => $db_col_v) {
                        $db_cols_where[] = ['like', $contact_tableName . '.' . $db_col_v, $column['search']['value']];
                    }
                    $query->andWhere($db_cols_where);
                } elseif ($column['name'] === 'tags') {
                    $query->joinWith('tags')->andWhere(['like', 'tag.name', $column['search']['value']]);
                } else {
                    $query->andWhere(['like', $contact_tableName . '.' . $column['name'], $column['search']['value']]);
                }
            }
            if (!empty($column['name']))
                $config['contacts'][$column['name']] = $column['search']['value'];
        }
        $user->filter_config = \json_encode($config);
        $user->save();


        $total_filtering_count = $query->count();

//        $query_ids = clone $query;
//        $contact_ids = $query_ids->asArray()->all();
//        $contact_ids = implode(',', array_map(function($item) { return $item['id']; }, $contact_ids));

        $query
            ->orderBy($sorting)
            ->limit($request_data['length'])
            ->offset($request_data['start']);

        $contacts = $query->all();
        $contact_widget = new ContactTableWidget();
        $contact_widget->contacts = $contacts;
        $contact_widget->user_id = $user_id;
        $contact_widget->user_role = $user_role;
        $data = $contact_widget->run();
        $json_data = [
            "draw" => intval($request_data['draw']),
            "recordsTotal" => intval($total_count),
            "recordsFiltered" => intval($total_filtering_count),
            "data" => $data,   // total data array
            //"contact_ids" => $contact_ids
        ];
        echo json_encode($json_data);
        die;
    }

    public function actionEdit(): void
    {
        $post = Yii::$app->request->post();

        $contact_form = new ContactForm();
        if ($post['id']) {
            $contact_form->edited_id = $post['id'];
        }
        $contact_form->attributes = $post;
        if ($contact_form->validate()) {
            try {
                $contact = NULL;
                if (isset($post['id']) && !empty($post['id'])) {
                    $contact = Contact::getById($post['id']);
                    if (!Yii::$app->user->can('updateContact', ['contact' => $contact])) {
                        $this->json(false, 403, 'Недостаточно прав для редактирования');
                    }

                    if(!Yii::$app->user->can('editStatusContact') && !empty($contact->status) && $contact->status != $contact_form->status){
                        $this->json(false, 403, 'Недостаточно прав для редактирования статуса');
                    }

                    //if contact is deleted then make alive
                    if ($contact->is_deleted) {
                        $contact->is_deleted = 0;
                    }
                    if (!$contact->medium_oid && $contact_form->status == Contact::CONTACT) {
                        $contact->medium_oid = Yii::$app->medium->putContact($contact_form->attributes);
                    } elseif($contact_form->status == Contact::CONTACT) {
                        Yii::$app->medium->putContact($contact_form->attributes, $contact->medium_oid);
                    }
                    if(!$contact->manager_id) {
                        $contact->manager_id = Yii::$app->user->identity->getId();
                    }
                    if ($contact->manager_id !== Yii::$app->user->identity->getId() && !Yii::$app->user->can('supervisor') && !Yii::$app->user->can('admin')) {
                        $contact_form->manager_id = $contact->manager_id;
                    }
                } else {
                    $contact = new Contact();

                    /*if (!isset($post['manager_id'])
                        || ($contact->manager_id !== Yii::$app->user->identity->getId() &&
                            !Yii::$app->user->can('supervisor') && !Yii::$app->user->can('admin'))) {
                        $contact_form->manager_id = Yii::$app->user->identity->id;
                    } else {
                        $contact_form->manager_id = Yii::$app->user->identity->id;
                    }*/
                    if(empty($post['manager_id'])){
                        $contact_form->manager_id = Yii::$app->user->identity->id;
                    }
                    if ((!empty($contact_form->status) && $contact_form->status == Contact::CONTACT
                            && !Yii::$app->user->can('editStatusContact'))
                        || empty($contact_form->status)
                    ) {
                        $contact_form->status = Contact::LEAD;
                    }

                    if($contact_form->status == Contact::LEAD){
                        $contact->is_new_lead = 1;
                    }

                    if(Yii::$app->user->can('editStatusContact') && $contact_form->status == Contact::CONTACT){
                        $contact->medium_oid = Yii::$app->medium->putContact($contact_form->attributes);
                    }
                }
                unset($post['_csrf'], $post['id']);
                $contact->attributes = $contact_form->attributes;
                $contact->remove_tags = true;
                try {
                    $editEvent = $contact->edit([]);
                } catch (\Exception $exception) {

                }
                if ($editEvent) {
                    $contact->save();
                    $this->json(['id' => $contact->id], 200);
                } else {
                    $contact_form->getErrors();
                    $this->json(false, 415, $contact->getErrors());
                }
            } catch (\Exception $ex) {
                $this->json(false, 500, $ex->getMessage());
            }
        } else {
            $errors = $contact_form->getErrors();
            $this->json(false, 415, $errors);
        }
    }

    public function actionSave(): void
    {

        $post = Yii::$app->request->post();
        $contact_form = $post;
        if ($contact_form->validate()) {
            try {
                $contact = new Contact();

                $contact->first_phone = $post['phone1'];
                $contact->second_phone = $post['phone2'];
                $contact->third_phone = $post['phone3'];
                $contact->fourth_phone = $post['phone4'];
                $contact->first_email = $post['email1'];
                $contact->second_email = $post['email2'];
                $contact->name = $post['first_name'];
                $contact->surname = $post['last_name'];
                $contact->middle_name = $post['middle_name'];
                $contact->country = $post['country'];
                $contact->city = $post['city'];
                $contact->int_id = $post['internal_no'];
                $contact->status = $post['status'];
                $contact->birthday = $post['birthday'];

                if ($contact->save()) {
                    $contact_history = new ContactHistory();
                    $contact_history->add($this->id, 'создан контакт (API)', 'new_contact');
                    $contact_history->save();
                    $contactStatusHistory = new ContactStatusHistory();
                    $contactStatusHistory->add($this->id, $this->manager_id, 'lead');
                    $contactStatusHistory->save();
                    $this->json(['id' => $contact->id], 200);
                } else {
                    $this->json(false, 415, $contact->getErrors());
                }
            } catch (\Exception $ex) {
                $this->json(false, 500);
            }
        } else {
            $errors = $contact_form->getErrors();
            $this->json(false, 415, $errors);
        }
    }

    public function actionSearch(): void
    {
        $search_term = Yii::$app->request->post('search_term');
        $id = Yii::$app->request->post('id');

        $query = Contact::find()->select(['id', 'int_id', 'surname', 'name', 'middle_name', 'first_phone', 'second_phone', 'third_phone', 'fourth_phone', 'first_email', 'second_email']);
        $contact_tableName = Contact::tableName();
        $query = Contact::find()->with('manager', 'tags')->distinct($contact_tableName . '.id');

        $query->andWhere(['like', $contact_tableName . '.first_phone', $search_term])
            ->orWhere(['like', $contact_tableName . '.second_phone', $search_term])
            ->orWhere(['like', $contact_tableName . '.third_phone', $search_term]);

        $query->andWhere(['is_deleted' => '0']);

        $user_id = Yii::$app->user->identity->getId();
        $user_role = Yii::$app->user->identity->getUserRole();

        if ($user_role === 'manager' || $user_role === 'operator') {
            $query->joinWith('tags.users')->andWhere(['user.id' => $user_id]);
        }

        $contacts = $query->asArray()->all();

        /** @var Contact $contact */
        foreach ($contacts as $key => &$contact) {
            // don't show user for himself
            if ($contact['id'] === $id) {
                unset($contacts[$key]);
                continue;
            }

            $contact['fio'] = implode(' ', array_filter([$contact['surname'], $contact['name'], $contact['middle_name']]));
            $contact['phones'] = implode("<br>", array_filter([$contact['first_phone'], $contact['second_phone'], $contact['third_phone'], $contact['fourth_phone']]));
            $contact['emails'] = implode("<br>", array_filter([$contact['first_email'], $contact['second_email']]));
        }

        if (\count($contacts) > 0) {
            $json_data = [
                "status" => 200,
                "data" => $contacts,
            ];
        } else {
            $json_data = [
                "status" => 404,
            ];
        }

        echo json_encode($json_data);
        die;
    }

    public function actionLinkWith(): void
    {
        $linked_contact_id = Yii::$app->request->post('linked_contact_id');
        $link_to_contact_id = Yii::$app->request->post('link_to_contact_id');

        $linked_contact = Contact::find()->where(['id' => $linked_contact_id])->one();
        $link_to_contact = Contact::find()->where(['id' => $link_to_contact_id])->one();

        if ($link_to_contact->mergeTogether($linked_contact)) {
            if ($link_to_contact->save()) {
                $linked_contact->is_deleted = 1;
                $linked_contact->save();
                $this->json(false, 200);
            } else {
                $this->json(false, 415, $link_to_contact->getErrors());
            }
        } else {
            $this->json(false, 415, $link_to_contact->getErrors());
        }
    }

    public function actionView(): bool
    {
        $contact_id = Yii::$app->request->get('id');
        if(!$contact_id) {
            throw new \Exception('Параметр "id" является обязательным');
        }
        $contact = Contact::getById($contact_id);
        /**@var $contact Contact*/
        if(!$contact) {
            throw new \Exception('Не удалось найти контакт');
        }

        if($contact->medium_oid) {
            // Try to sync with medium
            $mediumApi = \Yii::$app->medium;
            try {
                $mediumContact = $mediumApi->getContact($contact->medium_oid);
                if($mediumContact && !$mediumApi::isUpToDate($contact, $mediumContact)) {
                    $contact = self::updateContact($mediumContact, $contact);
                }
            } catch (\Exception $e) {

            }
        }

        $contact_data = $contact->attributes;

        $contact_data['phones'] = Filter::dataImplode($contact->getPhoneValues());
        $contact_data['emails'] = Filter::dataImplode($contact->getEmailValues());

        $contact_data['calls'] = Call::fetchByContactId($contact->id);

        $manager = User::findOne($contact_data['manager_id']);
        /**@var $manager User*/
        $contact_data['manager_name'] = $manager?$manager->firstname:'';

        $this->json($contact_data, 200);
    }

    public function actionNewView($contact)
    {
        $contact = $contact->one();
        $email = 'E-mail';
        $birthday = 'ДатаРождения';
        $phone = 'ТелефонМоб';
        $city = 'Город';
        if ($contact->medium_oid) {
            $syncData = Contact::getMediumObject($contact->medium_oid);
            $name = explode(' ', $syncData->name);
            $contact->surname = $name[0];
            $contact->name = $name[1];
            $contact->middle_name = $name[2];
            $contact->first_phone = get_object_vars($syncData)['@attributes'][$phone];
            $contact->city = get_object_vars($syncData)['@attributes'][$city];
            $brd_data =  get_object_vars($syncData)['@attributes'][$birthday];
            if(!empty($brd_data)) {
                $birthday = \DateTime::createFromFormat('Y-m-d\TH:i:s',$brd_data);
                if($birthday) {
                    $contact->birthday = $birthday->format('Y-m-d');
                } else {
                    $birthday = \DateTime::createFromFormat('Y-m-d\TH:i:s',$brd_data);
                    if($birthday) {
                        $contact->birthday = $birthday->format('Y-m-d');
                    }
                }
            }
            $email_data = trim(get_object_vars($syncData)['@attributes'][$email]);
            if(!empty($email_data)) {
                $contact->first_email = $email_data;
            }
            $contact->status = 2;
        } else {
            $contact = $contact->one();
        }
        return $contact;
    }

    public function actionHistory(): void
    {
        $contact_id = Yii::$app->request->get('id');
        $history = ContactHistory::getByContactId($contact_id);
        $this->json($history, 200);
    }

    public function actionAddcomment(): void
    {
        $post = Yii::$app->request->post();
        $comment_form = new CommentForm();
        $comment_form->load($post);
        if ($comment_form->validate()) {
            $contact_id = Yii::$app->request->post('id');
            $comment_text = $comment_form->comment;
            $contact_comment = new ContactComment();
            if ($contact_comment->add($contact_id, $comment_text)) {
                $contact_history = new ContactHistory();
                $comment_text = "комментарий - " . $comment_text;
                $contact_history->add($contact_id, $comment_text, 'comment', $contact_comment->datetime);
                $response_date = [
                    'text' => $comment_text,
                    'datetime' => date("d-m-Y G:i:s", strtotime($contact_comment->datetime)),
                ];
                $this->json($response_date, 200);
            } else {
                $this->json(false, 500);
            }
        } else {
            $errors = $comment_form->getErrors();
            $this->json(false, 415, $errors);
        }
    }

    public function actionDelete(): void
    {
        if (Contact::deleteById(Yii::$app->request->post('id'))) {
            $this->json(false, 200);
        }
    }

    public function actionDeleteFiltered(): void
    {
        $contact_tableName = Contact::tableName();
        $query = Contact::find()->with('manager', 'tags')->distinct($contact_tableName . '.id');
        $query->where([$contact_tableName . '.is_deleted' => '0']);
        $columns = Contact::getColsForTableView();
        foreach (Yii::$app->request->post() as $column => $value) {
            if (!empty($value) && isset($columns[$column])) {
                if (isset($columns[$column]['db_cols'])) {
                    $db_cols_where = ['or'];
                    foreach ($columns[$column]['db_cols'] as $db_col_v) {
                        $db_cols_where[] = ['like', $contact_tableName . '.' . $db_col_v, $value];
                    }
                    $query->andWhere($db_cols_where);
                } elseif ($column == 'tags') {
                    $query->joinWith('tags')->andWhere(['like', 'tag.name', $value]);
                } else {
                    $query->andWhere(['like', $contact_tableName . '.' . $column, $value]);
                }
            }
        }
        if (Contact::deleteById(ArrayHelper::map($query->all(), 'id', 'id'))) {
            $this->json(false, 200);
        }
    }

    public function actionHideColumns(): void
    {
        $hide_columns = Yii::$app->request->get('hide_columns');
        Yii::$app->session->set('contact_hide_columns', $hide_columns);
        $this->json(false, 200);
    }

    public function actionRingRound(): void
    {
        $contact_id = Yii::$app->request->post('id');
        $action_comment_text = Yii::$app->request->post('action_comment');
        $call_order_token = Yii::$app->request->post('call_order_token');
        $attitude_level = Yii::$app->request->post('attitude');
        $contact_ring_round = new ContactRingRound();
        $contact_ring_round->manager_id = Yii::$app->user->identity->getId();
        if ($contact_ring_round->add($contact_id, $action_comment_text, $call_order_token, $attitude_level)) {
            $history_text = $contact_ring_round->getHistoryText();
            $response_date = [
                'id' => $contact_ring_round->id,
                'system_date' => date('d-m-Y G:i:s', strtotime($contact_ring_round->system_date)),
                'history' => $history_text,
            ];
//            $call = Call::find(['call_order_token' => $call_order_token])->one();
//            $call->sendToCRM(Yii::$app->user->identity, $call_order_token);
            $this->json($response_date, 200);
        }
        $this->json(false, 500);
    }

    public function actionObjectschedulecall(): void
    {
        $contact_id = Yii::$app->request->post('id');
        $schedule_date = Yii::$app->request->post('schedule_date');
        $action_comment_text = Yii::$app->request->post('action_comment');
        $call_order_token = Yii::$app->request->post('call_order_token');
        $attitude_level = Yii::$app->request->post('attitude');
        $contact_schedule_call = new ContactScheduledCall();
        $contact_schedule_call->manager_id = Yii::$app->user->identity->getId();
        if ($contact_schedule_call->add($contact_id, $schedule_date, $action_comment_text, $call_order_token, $attitude_level)) {
            $history_text = $contact_schedule_call->getHistoryText();
            $response_date = [
                'id' => $contact_schedule_call->id,
                'system_date' => date('d-m-Y G:i:s', strtotime($contact_schedule_call->system_date)),
                'history' => $history_text,
            ];
            $this->json($response_date, 200);
        }
        $this->json(false, 500);
    }

    public function actionObjectscheduleemail(): void
    {
        $contact_id = Yii::$app->request->post('id');
        $schedule_date = Yii::$app->request->post('schedule_date');
        $action_comment_text = Yii::$app->request->post('action_comment');
        $contact_schedule_email = new ContactScheduledEmail();
        $contact_schedule_email->manager_id = Yii::$app->user->identity->id;
        if ($contact_schedule_email->add($contact_id, $schedule_date, $action_comment_text)) {
            $history_text = $contact_schedule_email->getHistoryText();
            $response_date = [
                'id' => $contact_schedule_email->id,
                'system_date' => date('d-m-Y G:i:s', strtotime($contact_schedule_email->system_date)),
                'history' => $history_text,
            ];
            $this->json($response_date, 200);
        }
        $this->json(false, 500);
    }

    public function actionGetContactByPhone(): void
    {
        $contactData = [];

        $phone = Yii::$app->request->get('phone');

        $contact = Contact::getContactByPhone($phone);
        /**@var $contact Contact*/
        if($contact) {
            $contactData['contact_id'] = $contact->id;
            $contactData['full_name'] = $contact->getFullName();
        } else {
            // Try to find current call to and appropriate attraction_channel
            // Active incoming call with matched phone number
            $call = Call::find()->where([
                'type' => Call::TYPE_INCOMING,
                'phone_number' => $phone,
                'status' => Call::CALL_STATUS_NEW,
            ])->orderBy('id DESC')->one();
            if($call && ($attractionChannel = $call->findAttractionChannel())) {
                $contactData['attraction_channel'] = $attractionChannel->id;
            }
        }

        $this->json($contactData, 200);
    }

    public function actionRemoveTag(): void
    {
        $contact_id = Yii::$app->request->post('id');
        $tag_id = Yii::$app->request->post('tag_id');
        //$tag = Tag::getById($tag_id);
        if ($tag_id && $contact_id) {
            ContactTag::deleteAll(['contact_id' => $contact_id, 'tag_id' => $tag_id]);
            $this->json([], 200);
        }
        $this->json([], 415, 'Tag not found');
    }

    public function actionAcceptCall(): void
    {
        $call_id = Yii::$app->request->post('call_id', null);
        if ($call_id == null)
            $this->json([], 400, 'Call id required');
        /**
         * @var $call Call
         */
        $call = Call::find()->where(['id' => (int)$call_id])->one();
        if ($call == null)
            $this->json([], 404, 'Call not found');
        $call->accepted = 1;
        $call->save();
        Notification::closeCall(['call_id' => $call->id]);
    }

    public function actionGetMediumObject($oid)
    {
//        try{
        $contacts = Contact::getMediumObject($oid);
//            var_dump($contacts);;
        return json_encode($contacts);
    }

    public static function updateContact(array $mediumData, Contact $contactToUpdate = null)
    {
        $oid = $mediumData['oid'] ?? 0;

        if(!$contactToUpdate && $oid) {
            $existingContact = Contact::findOne(['medium_oid' => $oid]);
            if($existingContact && MediumApi::isUpToDate($existingContact, $mediumData)) {
                return $existingContact;
            }
        } else {
            $existingContact = $contactToUpdate;
        }

        if(!$existingContact) {
            $existingContact = new Contact();
            $existingContact->is_broadcast = null;
            $existingContact->medium_oid = $mediumData['oid'];
        }

        // Parse name data
        $nameData = array_filter(explode(' ', trim($mediumData['FIO'])));

        $surname = count($nameData)?array_shift($nameData):'';
        $name = count($nameData)?array_shift($nameData):'';

        $middle_name = '';
        if(count($nameData)) {
            $middle_name = implode(' ', $nameData);
        }

        $existingContact->setAttributes([
            'name' => $name,
            'surname' => $surname,
            'middle_name' => $middle_name,
            'first_phone' => $mediumData['Phone'] ?? '',
            'city' => $mediumData['City'] ?? '',
            'first_email' => $mediumData['Email'] ?? '',
            'status' => Contact::CONTACT,
        ]);

        $birthday = \DateTime::createFromFormat('Y-m-d\TH:i:s', $mediumData['Birth'] ?? null);
        if($birthday) {
            $existingContact->birthday =  $birthday->format('Y-m-d');
        }

        if($existingContact->save()) {
            return $existingContact;
        }

        return ['errors' => $existingContact->getErrors(),'data' => $existingContact->toArray()];
    }

    public function actionMediumUpdate($data): bool
    {

    }

    protected static function parsePhoneString($phoneString)
    {
        // Add spaces for strings in brackets
        $prepared = preg_replace('/\(([^\)]+)\)/', ' $1 ', $phoneString);
        // Replcae invalid chars
        $cleaned = trim(preg_replace('/[\-\.\,\=\(\)]|(\s\s)/', '', $prepared));

        // Fetch string from source
        $strings = [];
        preg_match_all('/[^\+\d\s\;]+/', $cleaned, $strings);

        // remove strings
        $cleaned = preg_replace('/[^\d\s\;]/', '', $cleaned);

        // Split by spaces or semicolon
        $numeric = preg_split('/[\s\;]/', $cleaned);// preg_replace('/[^\d]/', '', $cleaned);
        foreach ($numeric as $k => $v) {
            if (is_numeric($v)) {
                if (strlen($v) >= 10) {
                    // Remove +38 if it exists
                    $numeric[$k] = preg_replace('/^\+?3?8?(\d{10,})$/', '$1', $v);
                }
            } else {
                unset($numeric[$k]);
            }
        }

        // Merge all parts together
        $parts = array_filter(array_merge($numeric, $strings[0]));

        // Return imploded parse data
        return implode(' ', $parts);
    }
}
