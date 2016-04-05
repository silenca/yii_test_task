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
        $table_cols = Contact::getColsForTableView();
        $filter_cols = Contact::getColsForTableView();
        unset($filter_cols['id']);
        return $this->render('index', ['hide_columns' => $hide_columns, 'table_cols' => $table_cols, 'filter_cols' => $filter_cols]);
    }

    public function actionGetdata()
    {
        $request_data = Yii::$app->request->get();
        $query = Contact::find()->with('manager', 'tags');
        $query->andWhere(['contact.is_deleted' => '0']);
        $query_total = clone $query;
        $total_count = $query_total->count();
        $columns = Contact::getColsForTableView();
        //Sorting
        $sorting = [];
        if (isset($request_data['order'])) {
            $order_by_sort = $request_data['order'][0]['dir'] == 'asc' ? SORT_ASC : SORT_DESC;
//            $sort_column = $columns[$request_data['order'][0]['column']];
            $sort_column = array_keys($columns)[$request_data['order'][0]['column']];

            $sorting = [
                $sort_column => $order_by_sort
            ];
        } else {
            $sorting = [
                'id' => SORT_DESC
            ];
        }
//        $query = Contact::find()->with('manager', 'tags');
//        $query->andWhere(['contact.is_deleted' => '0']);
        //join Tags
//        $query->leftJoin(ContactTag::tableName() . ' `ct`', '`ct`.`contact_id` = contact.`id`')
//            ->leftJoin(Tag::tableName() . ' `t`', '`t`.`id` = `ct`.`tag_id`');

        //Filtering
        foreach ($request_data['columns'] as $column) {
            if (!empty($column['search']['value'])) {
                if (isset($columns[$column['name']]['db_cols'])) {
                    foreach ($columns[$column['name']]['db_cols'] as $db_col_i => $db_col_v) {
                        if ($db_col_i == 0) {
                            $query->andWhere(['like', 'contact.'.$db_col_v, $column['search']['value']]);
                        } else {
                            $query->orWhere(['like', 'contact.'.$db_col_v, $column['search']['value']]);
                        }
                    }
                } elseif ($column['name'] == 'tags') {
                    $query->joinWith('tags')->andWhere(['like', 'tag.name', $column['search']['value']]);
                } else {
                    $query->andWhere(['like', 'contact.'.$column['name'], $column['search']['value']]);
                }
            }
        }

        $dump = $query->createCommand()->rawSql;
        $total_filtering_count = $query->count();
        $query
            ->orderBy($sorting)
            ->limit($request_data['length'])
            ->offset($request_data['start']);

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

    public function actionEdit()
    {
        $post = Yii::$app->request->post();
        $contact_form = new ContactForm();
        if ($post['id']) {
            $contact_form->edited_id = $post['id'];
        }
        $contact_form->attributes = $post;

//        $contact_form->tags_str = 'полисмен, комбайнер';
//        $contact_form->tags_str = 'трактарист, бизнесмен, учитель';

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
//                $contact->setTags($contact_form->tags);
                $contact->attributes = $contact_form->attributes;
                if ($contact->edit(['tags' => $contact_form->tags])) {
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
        $action_comment_text = Yii::$app->request->post('action_comment');
        $contact_schedule_call = new ContactScheduledCall();
        $contact_schedule_call->manager_id = Yii::$app->user->identity->id;
        if ($contact_schedule_call->add($contact_id, $schedule_date, $action_comment_text)) {
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
