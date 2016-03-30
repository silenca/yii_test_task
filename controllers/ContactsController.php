<?php

namespace app\controllers;

use app\models\ContactTag;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Contact;
use app\models\ChannelAttraction;
use app\models\ContactHistory;
use app\models\ContactComment;
use app\models\ContactShow;
use app\models\ContactContract;
use app\models\ContactVisit;
use app\models\ContactScheduledCall;
use app\models\ContactScheduledEmail;
use app\models\User;
use app\models\UploadDoc;
use app\models\forms\ContactForm;
use app\models\forms\CommentForm;
use app\models\Tag;
use app\components\widgets\ContactTableWidget;
use yii\web\UploadedFile;

class ContactsController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'view',
                            'history',
                            'addcomment',
                            'getdata',
                            'edit',
                            'hide-columns',
                            'get-contact-by-phone',
                            'search',
                            'link-with'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [
                            'objectschedulecall',
                            'objectscheduleemail',
                        ],
                        'allow' => true,
                        'roles' => ['manager', 'supervisor', 'admin'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['supervisor', 'admin'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'addcomment' => ['post'],
                    'objectshow' => ['post'],
                    'objectvisit' => ['post'],
                    'objectcontract' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $session = Yii::$app->session;
        $hide_columns = $session->get('contact_hide_columns');
        if (!$hide_columns) {
            $hide_columns = [];
        }
        return $this->render('index', ['hide_columns' => $hide_columns]);
    }


    public function actionEdit()
    {
        $post = Yii::$app->request->post();
        $contact_form = new ContactForm();
        if ($post['id']) {
            $contact_form->edited_id = $post['id'];
        }
        $contact_form->attributes = $post;

        if ($contact_form->validate()) {
            try {
                $contact = null;
                if (isset($post['id']) && !empty($post['id'])) {
                    $contact = Contact::getById($post['id']);
                    if (!Yii::$app->user->can('updateContact', ['contact' => $contact])) {
                        $this->json(false, 403, 'Недостаточно прав для редактирования');
                    }
                    //if contact is deleted then make alive
                    if ($contact->is_deleted) {
                        $contact->is_deleted = 0;
                    }
                } else {
                    $contact = new Contact();
                    $contact->manager_id = Yii::$app->user->identity->id;
                }
                unset($post['_csrf']);
                unset($post['id']);
                $contact->attributes = $contact_form->attributes;
                if ($contact->edit()) {
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

    public function actionSearch()
    {
        $search_term = Yii::$app->request->post('search_term');

        $query = Contact::find()->select(['id', 'int_id', 'surname', 'name', 'middle_name', 'first_phone', 'second_phone', 'third_phone', 'fourth_phone', 'first_email', 'second_email']);
        $query->andWhere(['is_deleted' => '0']);

        $query->andWhere(['like', 'surname', $search_term])
            ->orWhere(['like', 'name', $search_term])
            ->orWhere(['like', 'middle_name', $search_term]);

//        $dump = $query->createCommand()->rawSql;

        $contacts = $query->asArray()->all();

        foreach ($contacts as &$contact) {
            $contact['fio'] = implode(" ", array_filter([$contact['surname'], $contact['name'], $contact['middle_name']]));
            $contact['phones'] = implode("<br>", array_filter([$contact['first_phone'], $contact['second_phone'], $contact['third_phone'], $contact['fourth_phone']]));
            $contact['emails'] = implode("<br>", array_filter([$contact['first_email'], $contact['second_email']]));
        }

        if (count($contacts) > 0) {
            $json_data = array(
                "status" => 200,
                "data" => $contacts
            );
        } else {
            $json_data = array(
                "status" => 404,
            );
        }

        echo json_encode($json_data);
        die;
    }

    public function actionLinkWith()
    {
        $linked_contact_id = Yii::$app->request->post('linked_contact_id');
        $link_to_contact_id = Yii::$app->request->post('link_to_contact_id');

        $linked_contact = Contact::find()->where(['id' => $linked_contact_id])->one();
        $link_to_contact = Contact::find()->where(['id' => $link_to_contact_id])->one();

        if ($link_to_contact->mergeTogether($linked_contact)) {
            $linked_contact->is_deleted = 1;
            $linked_contact->save();
            if ($link_to_contact->save()) {
                $this->json(false, 200);
            } else {
                $this->json(false, 415, $link_to_contact->getErrors());
            }
        } else {
            $this->json(false, 415, $link_to_contact->getErrors());
        }
    }

    public function actionGetdata()
    {
        $request_data = Yii::$app->request->get();
        $total_count = Contact::find()->where(['is_deleted' => '0'])->count();
        $columns = Contact::getTableColumns();
        //Sorting
        $sorting = [];
        if (isset($request_data['order'])) {
            $order_by_sort = $request_data['order'][0]['dir'] == 'asc' ? SORT_ASC : SORT_DESC;
            $sort_column = $columns[$request_data['order'][0]['column']];

            $sorting = [
                $sort_column => $order_by_sort
            ];
        } else {
            $sorting = [
                'id' => SORT_DESC
            ];
        }
        $query = Contact::find();
        $query->andWhere(['is_deleted' => '0']);

        //Filtering
        if (!empty($request_data['columns'][3]['search']['value'])) {
            $query->where(['like', 'surname', $request_data['columns'][3]['search']['value']]);
        }
        if (!empty($request_data['columns'][4]['search']['value'])) {
            $query->andWhere(['like', 'name', $request_data['columns'][4]['search']['value']]);
        }
        if (!empty($request_data['columns'][5]['search']['value'])) {
            $query->andWhere(['like', 'middle_name', $request_data['columns'][5]['search']['value']]);
        }

        if (!empty($request_data['columns'][6]['search']['value'])) {
            $query->andWhere(['like', 'first_phone', $request_data['columns'][7]['search']['value']])
                ->orWhere(['like', 'second_phone', $request_data['columns'][7]['search']['value']])
                ->orWhere(['like', 'third_phone', $request_data['columns'][7]['search']['value']])
                ->orWhere(['like', 'fourth_phone', $request_data['columns'][7]['search']['value']]);
        }

        if (!empty($request_data['columns'][7]['search']['value'])) {
            $query->andWhere(['like', 'first_email', $request_data['columns'][8]['search']['value']])
                ->orWhere(['like', 'second_email', $request_data['columns'][8]['search']['value']]);
        }

        if (!empty($request_data['columns'][8]['search']['value'])) {
//            $query->leftJoin(Tag::tableName() . ' t', 't.id = ' . Contact::tableName() . '.manager_id');
            $query->leftJoin(ContactTag::tableName() . ' `ct`', '`ct`.`contact_id` = contact.`id`')
                ->leftJoin(Tag::tableName() . ' `t`', '`t`.`id` = `ct`.`tag_id`');
            $query->andWhere(['like', 't.name', $request_data['columns'][9]['search']['value']]);
        } elseif (isset($sort_column) && $sort_column == 't.name') {
            $query->leftJoin(ContactTag::tableName() . ' `ct`', '`ct`.`contact_id` = contact.`id`')
                ->leftJoin(Tag::tableName() . ' `t`', '`t`.`id` = `ct`.`tag_id`');
        }

        $total_filtering_count = $query->count();
        $query
            ->with('manager')
            ->with('tags')
            ->orderBy($sorting)
            ->limit($request_data['length'])
            ->offset($request_data['start']);

//        $dump = $query->createCommand()->rawSql;
        $contacts = $query->all();
        $contact_widget = new ContactTableWidget();
        $contact_widget->contacts = $contacts;
        $data = $contact_widget->run();

        $json_data = array(
            "draw" => intval($request_data['draw']),
            "recordsTotal" => intval($total_count),
            "recordsFiltered" => intval($total_filtering_count),
            "data" => $data   // total data array
        );
        echo json_encode($json_data);
        die;
    }

    public function actionView()
    {
        $contact_id = Yii::$app->request->get('id');
        $contact = Contact::find()->where(['id' => $contact_id]);
        $contact2 = clone $contact;
        $contact2 = $contact2->one();
        $contact_arr = $contact->asArray()->one();
        $contact_data = array_intersect_key($contact_arr, array_flip(Contact::$safe_fields));

        $phones_arr = $contact2->getPhoneColsWithVal();
//        $phones_arr = [( comparison ? $contact_data['first_phone'] : if false );, $contact_data['second_phone'], $contact_data['third_phone'], $contact_data['fourth_phone']];
        $contact_data['phones'] = ContactForm::dataConvert($phones_arr, 'phones', 'implode');
        $emails_arr = $contact2->getEmailColsWithVal();
//        $emails_arr = [$contact_data['first_email'], $contact_data['second_email']];
        $contact_data['emails'] = ContactForm::dataConvert($emails_arr, 'emails', 'implode');
        
//        if (Yii::$app->user->can('show_payments') || $contact_data['manager_id'] == Yii::$app->user->identity->id) {
//            $contact_data['payment_access'] = true;
//        } else {
//            $contact_data['payment_access'] = false;
//        }
        $contact_manager = User::find()->where(['id' => $contact_data['manager_id']])->one();
        $contact_data['manager_name'] = $contact_manager['firstname'];
        $this->json($contact_data, 200);
    }

    public function actionHistory()
    {
        $contact_id = Yii::$app->request->get('id');
        $history = ContactHistory::getByContactId($contact_id);
        $this->json($history, 200);
    }

    public function actionAddcomment()
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
                $contact_history->add($contact_id, $comment_text, '', 'comment', $contact_comment->datetime);
                $response_date = [
                    'text' => $comment_text,
                    'datetime' => date("d-m-Y G:i:s", strtotime($contact_comment->datetime))
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

    public function actionDelete()
    {
        $contact_id = Yii::$app->request->post('id');
        if (Contact::deleteById($contact_id)) {
            $this->json(false, 200);
        }
    }

    public function actionHideColumns()
    {
        $hide_columns = Yii::$app->request->get('hide_columns');
        Yii::$app->session->set('contact_hide_columns', $hide_columns);
        $this->json(false, 200);
    }

    public function actionObjectschedulecall() {
        $contact_id = Yii::$app->request->post('id');
        $schedule_date = Yii::$app->request->post('schedule_date');
        $action_comment = Yii::$app->request->post('action_comment');
        $contact_schedule_call = new ContactScheduledCall();
        $contact_schedule_call->manager_id = Yii::$app->user->identity->id;
        if ($contact_schedule_call->add($contact_id, $schedule_date, $action_comment)) {
            $history_text = $contact_schedule_call->getHistoryText();
            $response_date = [
                'id' => $contact_schedule_call->id,
                'system_date' => date('d-m-Y G:i:s', strtotime($contact_schedule_call->system_date)),
                'history' => $history_text
            ];
            $this->json($response_date, 200);
        }
        $this->json(false, 500);
    }

    public function actionObjectscheduleemail()
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
                'history' => $history_text
            ];
            $this->json($response_date, 200);
        }
        $this->json(false, 500);
    }



    public function actionGetContactByPhone()
    {
        $phone = Yii::$app->request->get('phone');

        if ($contact = Contact::getContactByPhone($phone)) {
            $this->json(['contact_id' => $contact['id']], 200);
        } else {
            $this->json(false, 404);
        }
    }

}
