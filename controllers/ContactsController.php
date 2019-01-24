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
        $response = ['data'=>[], 'notify'=>'', 'error'=>''];
        if(Yii::$app->request->isAjax) {
            $contactId = intval(Yii::$app->request->post('contactId'));// ID Пациент
			$speciality = intval(Yii::$app->request->post('speciality'));
			$departmentId = intval(Yii::$app->request->post('department'));
			$doctorName = htmlspecialchars(Yii::$app->request->post('doctorName'));//Врач
			$bookingDate = date("Y-m-d", strtotime(Yii::$app->request->post('bookingDate')));//ДатаПриема
			$cabinetName = htmlspecialchars(Yii::$app->request->post('cabinetName'));//Кабинет
			$visitComment = htmlspecialchars(Yii::$app->request->post('visitComment'));//Визит
			$doctorId = htmlspecialchars(Yii::$app->request->post('doctorId'));//Врач
			$doctorStartTime = strtotime($bookingDate . ' ' . Yii::$app->request->post('doctorStartTime'));//ДатаПриема
			$doctorEndTime = strtotime($bookingDate . ' ' . Yii::$app->request->post('doctorEndTime'));
			$cabinetId = htmlspecialchars(Yii::$app->request->post('cabinetId'));
			$cabinetStartTime = strtotime($bookingDate . ' ' . Yii::$app->request->post('cabinetStartTime'));
			$cabinetEndTime = strtotime($bookingDate . ' ' . Yii::$app->request->post('cabinetEndTime'));

            $timeReceipt = ($doctorEndTime - $doctorStartTime) / 60;//ВремяПриема

            if($doctorStartTime == $cabinetStartTime && $doctorEndTime == $cabinetEndTime) {
                $department = Departments::findOne($departmentId);
                if($department) {
                    $contact = Contact::findOne($contactId);//Пациент
                    if ($contact) {
                        $data = '<OBJECT ДатаПриема="' . date("Y-m-d\TH:i:s", $doctorStartTime) . '" ВремяПриема="' . $timeReceipt . '" '
                            . 'Пациент="' . $contact->surname . ' ' . $contact->name . ' ' . $contact->middle_name . '" '
                            . 'Врач="' . $doctorName . '" Кабинет="' . $cabinetName . '" ИсточЗапис="Через контакт центр" '
                            . 'Статус="В ожидании" Визит="Новый" Комментарий="' . htmlspecialchars($visitComment) . '" '
                            . 'ИсточникИнформации="DOC.UA">' . "\n"
                            . '<Пациент link="C:1CDA3A9DBD62FBC/O:' . $contact->medium_oid . '"/>' . "\n"
                            . '<Врач link="C:1CDCBA80BCACD1E/O:' . $doctorId . '"/>' . "\n"
                            . '<Кабинет link="C:1CE311BD5A26671/O:' . $cabinetId . '"/>' . "\n"
                            . '</OBJECT>';
                        $response['data'] = $data;

                        $records =  Yii::$app->medium->records($department->api_send_url, $data);
                        if(empty($records['error'])){
                            $response['notify'] = 'Запись была успешно отправлена. ID визита ' . $records['data']['oid'];
                        }else{
                            $response['error'] = $records['error'];
                        }
                        $log = new ContactVisitLog();
                        $log->date = date('Y-m-d H:i:s');
                        $log->date_visit = date('Y-m-d H:i:s', $doctorStartTime);
                        $log->contact_id = $contact->id;
                        $log->medium_oid = $records['data']['oid'];
                        $log->request = $data;
                        $log->response = $records['data']['response'];
                        $log->save();

                        $visit = new ContactsVisits();
                        $visit->create_date = date('Y-m-d H:i:s');
                        $visit->edit_date = date('Y-m-d H:i:s');
                        $visit->visit_date = date('Y-m-d H:i:s', $doctorStartTime);
                        $visit->contact_id = $contact->id;
                        $visit->medium_oid = $records['data']['oid'];
                        $visit->department_id = $department->id;
                        $visit->status = ContactsVisits::STATUS_PENDING;
                        $visit->manager_id = Yii::$app->user->id;
                        $visit->save();

                        $action = new Action();
                        $action_type = ActionType::find()->where(['name' => 'scheduled_visit'])->one();
                        $action->add($contact->id, $action_type->id, [], date('Y-m-d H:i:s', $doctorStartTime));

                        $contact_history = new ContactHistory();
                        $contact_history->add($contact->id, 'Запланированный визит на ' . date('d-m-Y H:i:s', $doctorStartTime), 'scheduled_visit');
                        $contact_history->save();
                    } else {
                        $response['error'] = "Контакт не найден";
                    }
                }else{
                    $response['error'] = "Отделение с ID {$departmentId} не найдено";
                }
            }else{
                $response['error'] = "Время записи к доктору, должно совпадать с временем записи в кабинет";
            }
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
                        $contact->medium_oid = Contact::postMediumObject($contact_form->attributes);
                    } elseif($contact_form->status == Contact::CONTACT) {
                        Contact::updateMediumObject($contact->medium_oid, $contact_form->attributes);
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
                        $contact->medium_oid = Contact::postMediumObject($contact_form->attributes);
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

//TODO clean or finish this methods:
//    public function actionSyncAllMediumContacts()
//    {
//        $user = User::find()
//            ->where([
//                'id' => Yii::$app->user->identity->getId()
//            ])
//            ->one();
//        //TODO **refactoring-lowprior change user model's column names
//        $crmContacts = $user->cols_config;
//        $mediumContacts = Contact::getMediumObjects();
//        foreach ($mediumContacts as $mediumContact) {
//            foreach ($crmContacts as $crmContact) {
////                ($crmContact);
//            }
//        }
//    }
//
//    public function actionForceUpdateContact($oid)
//    {
//        $request = Yii::$app->request->post();
//        try {
//            $contact = new Contact();
//            $xml = new XMLparser();
//            $xmlObject = $xml->parse($request);
//            foreach ($xmlObject as $key => $value) {
//                switch ($key) {
//                    case 'name' || 'FIO':
//                        break;
//                    case 'ТелефонМоб' || 'Phone':
//                        break;
//                    case 'ДатаРождения' || 'birth':
//                        break;
//                    case 'ИсточникИнфомации' || 'ИсточникИнфомации':
//                        break;
//                    case 'ТелефонБинотел' || '':
//                        break;
//                }
//                $client[$key] = $value;
//            }
//
//            $contact->first_phone = $post['phone1'];
//            $contact->second_phone = $post['phone2'];
//            $contact->third_phone = $post['phone3'];
//            $contact->fourth_phone = $post['phone4'];
//            $contact->first_email = $post['email1'];
//            $contact->second_email = $post['email2'];
//            $contact->name = $post['first_name'];
//            $contact->surname = $post['last_name'];
//            $contact->middle_name = $post['middle_name'];
//            $contact->country = $post['country'];
//            $contact->city = $post['city'];
//            $contact->int_id = $post['internal_no'];
//            $contact->status = $post['status'];
//            $contact->birthday = $post['birthday'];
//
//            if ($contact->save()) {
//                $contact_history = new ContactHistory();
//                $contact_history->add($this->id, 'создан контакт (API)', 'new_contact');
//                $contact_history->save();
//                $contactStatusHistory = new ContactStatusHistory();
//                $contactStatusHistory->add($this->id, $this->manager_id, 'lead');
//                $contactStatusHistory->save();
//                $this->json(['id' => $contact->id], 200);
//            } else {
//                $this->json(false, 415, $contact->getErrors());
//            }
//        } catch (\Exception $ex) {
//            $this->json(false, 500);
//        }
//
//    }

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
            try {
                $mediumContact = MediumApi::getContact($contact->medium_oid);
                if($mediumContact && !MediumApi::isUpToDate($contact, $mediumContact)) {
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

//        $newContactData = new Contact($contact->one());
//        var_dump($contact);die;
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
            //$contact->save();
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
        $phone = Yii::$app->request->get('phone');

        if ($contact = Contact::getContactByPhone($phone)) {
            $this->json([
                'contact_id' => $contact->id,
                'full_name' => $contact->getFullName(),
            ], 200);
        } else {
            $this->json(false, 404);
        }
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

    public function actionSyncContacts(): void
    {
        $contacts = Contact::getMediumObjects();
        $xmlParser = xml_parser_create();
        xml_parse_into_struct($xmlParser, $contacts->getContent(), $array, $index);
//            var_dump($array);
//            var_dump($contacts);
//        print_r($array['oid']);
//        $contacts = json_decode($this->actionContacts());
        foreach ($array as $contact) {
            if ($contact['attributes']['OID']) {
                self::actionSaveContacts($contact);
            }

        }
    }

    public static function actionSaveContacts($contact)
    {
        if(!empty($contact['@attributes'])){
            $localContact = Contact::find()->where(['medium_oid' => $contact['@attributes']['oid']])->one();
        }elseif (!empty($contact['attributes'])){
            $localContact = Contact::find()->where(['medium_oid' => $contact['@attributes']['oid']])->one();
        }else {
            $localContact = Contact::find()->where(['medium_oid' => $contact['oid']])->one();
        }
        if(!empty($localContact) && Contact::checkLatestUpdate($localContact)){
            return Contact::updateMediumObject($localContact->attributes['medium_oid'], $localContact->attributes);
        }else{
            if (!empty($contact['attributes'])) {
                $attrs = $contact['attributes'];
                $isExists = Contact::find()->where(['medium_oid' => $attrs['OID']])->one();
            } else if(!empty($contact['@attributes'])) {
                $attrs = $contact['@attributes'];
                $isExists = Contact::find()->where(['medium_oid' => $attrs['oid']])->one();
            }else{
                $attrs = $contact;
            }
//            else{
//                $isExists = Contact::find()->where(['medium_oid' => $contact['oid']])->one();
////                if(empty($isExists))
////                    return null;
//            }
            if(!empty($isExists)){
                $newFlag = 0;
                $newContact = $isExists;
            }else{
                $newFlag = 1;
                $newContact = new Contact();
            }
//            $newContact = (!empty($isExists)) ? $isExists : new Contact();

            if (!empty($attrs['NAME']) || !empty($attrs['name'])) {
                $surname = !empty(explode(' ', $attrs['name'])[0]) ? explode(' ', $attrs['name'])[0] : " ";
                $name = !empty(explode(' ', $attrs['name'])[1]) ? explode(' ', $attrs['name'])[1] : " ";
                $middle_name = !empty(explode(' ', $attrs['name'])[2]) ? explode(' ', $attrs['name'])[2] : " ";
                $newContact->surname = !empty($attrs['NAME']) ? explode(' ', $attrs['NAME'])[0] : $surname;
                $newContact->name = !empty($attrs['NAME']) ? explode(' ', $attrs['NAME'])[1] : $name;
                $newContact->middle_name = !empty($attrs['NAME']) ? explode(' ', $attrs['NAME'])[2] : $middle_name;
            }
            if (!empty($attrs['ТелефонМоб']) || !empty($attrs['ТМлМфонМоб']))
                $newContact->first_phone = $attrs['ТМлМфонМоб'] ?? $attrs['ТелефонМоб'];
            if (!empty($attrs['Город']))
                $newContact->city = $attrs['Город'];
            if (!empty($attrs['E-mail']) || !empty($attrs['E-MAIL']))
                $newContact->first_email = !empty($attrs['E-MAIL']) ? $attrs['E-MAIL'] : $attrs['E-mail'];
            if (!empty($attrs['ДатаРождения'])) {
                $birthday = \DateTime::createFromFormat('Y-m-d\TH:i:s',$attrs['ДатаРождения']);
                if($birthday) {
                    $newContact->birthday =  $birthday->format('Y-m-d');
                } else {
                    $birthday = \DateTime::createFromFormat('Y-m-d\TH:i:s',$attrs['ДатаРождения']);
                    if($birthday) {
                        $newContact->birthday =  $birthday->format('Y-m-d');
                    }
                }
            }

            $newContact->medium_oid = (!empty($attrs['OID'])) ? $attrs['OID'] : $attrs['oid'];
            $newContact->status = Contact::$statuses[2];
            $newContact->is_broadcast = $newFlag ? null : $newContact->is_broadcast;
            if($newContact->save()) {
                return $newContact->medium_oid;
            }
        }
        return ['errors' => $newContact->getErrors(),'data' => $newContact->toArray()];
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

        list($surname, $name, $middle_name) = explode(' ', $mediumData['FIO']);
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

    public function actionContacts()
    {
        $contacts = Contact::getMediumObjects();
        $xmlParser = xml_parser_create();
        $struct = xml_parse_into_struct($xmlParser, $contacts->getContent(), $array, $index);
        $oids = [];
        $clients = [];
        $crmContacts = [];
        foreach ($array as $item) {
            if (!empty($item['attributes'])) {
                $attr = $item['attributes'];
                $oid = $attr['OID'];
                $crmContacts = Contact::findOne(['medium_oid' => $oid]);
//                var_dump($crmContacts );die;
                $clients[$oid] = $attr;
            }
        }
        return json_encode($clients);
//        var_dump($parsed);die;

//        try {
//            return true;
//        } catch (\Exception $e) {
//            return ['text'=>$e->getMessage()];
//        }
    }

    public function actionSaveMediumClient($data): bool
    {
        return Contact::postMediumObject($data) === true;
    }

    public function actionMediumUpdate($data): bool
    {

    }

    public function actionUpdateMediumClient($oid, $data): bool
    {
        return Contact::updateMediumObject($oid, $data) === true;
    }
}
