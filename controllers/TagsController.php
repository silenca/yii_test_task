<?php

namespace app\controllers;

use app\components\CSVExport;
use app\components\widgets\ContactTagTableWidget;
use app\components\widgets\TagContactsTableWidget;
use app\components\widgets\TagsSelectWidget;
use app\models\Call;
use app\models\Contact;
use app\models\Tag;
use app\models\User;
use app\models\UserTag;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

//use app\models\ContactCalled;
//use app\models\TempContactsPool;

class TagsController extends BaseController {
    public function behaviors() {
        return ['access' => ['class' => AccessControl::className(), 'rules' => [['actions' => ['index', 'gettags', 'getdata', 'getusers', 'get-contacts-another-tag', 'edit', 'delete', 'restore', 'export-csv', 'update-users', 'add-contacts-by-filter'], 'allow' => true, 'roles' => ['admin', 'manager'],], ['actions' => ['index', 'gettags', 'getdata', 'getusers'], 'allow' => true, 'roles' => ['operator']]]], 'verbs' => ['class' => VerbFilter::className(), 'actions' => ['delete' => ['post'], 'edit' => ['post'], 'restore' => ['post']]]];
    }

    public function actionIndex() {
        $session = Yii::$app->session;
        $hide_contact_columns = $session->get('contact_hide_columns');
        if (!$hide_contact_columns) {
            $hide_contact_columns = [];
        }

        $contact_cols = Tag::getContactsForTagTableView();

        $table_contact_cols = $contact_cols;
        $filter_contact_cols = $contact_cols;
        unset($filter_contact_cols['id']);

        $data = ['hide_contact_columns' => $hide_contact_columns, 'table_contact_cols' => $table_contact_cols, 'filter_contact_cols' => $filter_contact_cols,//            'contacts_list' => $contacts_list
        ];

        $call_statuses = Call::getCallStatuses();
        $call_statuses[0]['name'] = Call::CALL_STATUS_ANSWERED;
        $call_statuses[0]['label'] = 'Успешный';
        $call_statuses[2]['name'] = Call::CALL_STATUS_MISSED;
        $call_statuses[3]['name'] = Call::CALL_STATUS_FAILURE;
        unset($call_statuses[1]);

        $attitude_levels = Call::getAttitudeLevels();

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $managers = User::find()->all();
        } else if (Yii::$app->user->identity->role == User::ROLE_MANAGER) {
            $managers = User::find()->where(['role' => [User::ROLE_MANAGER, User::ROLE_OPERATOR]])->all();
        } else {
            $managers = NULL;
        }
        $data['managers'] = $managers;
        $data['call_statuses'] = $call_statuses;
        $data['attitude_levels'] = $attitude_levels;

        return $this->render('index', $data);
    }

    public function actionEdit() {
        $post = Yii::$app->request->post();

        try {
            $tag = NULL;
            if (isset($post['id']) && !empty($post['id'])) {
                $tag = Tag::getById($post['id']);
            } else {
                $tag = new Tag();
            }
            unset($post['_csrf']);
            unset($post['id']);
            $tag->attributes = $post;
            //if ($tag->edit(['user_ids' => $post['tag_users'], 'contact_ids' => explode(',', $post['tag_contacts'])])) {
            if ($tag->edit()) {
                $this->json(['id' => $tag->id], 200);
            } else {
                $this->json(false, 415, $tag->getErrors());
            }
        } catch (\Exception $ex) {
            $this->json(false, 500);
        }
    }

    public function actionGettags() {
        $request_data = Yii::$app->request->get();
        $term = $request_data['term'];
        $query = Tag::find();
        $user_id = Yii::$app->user->identity->getId();
        $user_role = Yii::$app->user->identity->getUserRole();
        if ($user_role == 'manager' || $user_role == 'operator') {
            $query->andWhere(['tag.is_deleted' => 0]);
            $query->joinWith('users')->andWhere(['=', 'user.id', $user_id]);
            if ($user_role == 'operator') {
                $query->andWhere(['=', 'tag.as_task', 1]);
            }
        }
        $query->andWhere(['like', 'tag.name', $term]);

        $tags_widget = new TagsSelectWidget();
        $tags_widget->tags = $query->all();

        if (count($tags_widget->tags) > 0) {
            $this->json(['items' => $tags_widget->run()], 200);
        } else {
            $this->json(['items' => []], 404);
        }
    }

    public function actionGetusers() {
        $query = new Query();
        $query->select(['`id`', '`firstname` as `text`']);
        $query->from(User::tableName());
        $user_role = Yii::$app->user->identity->getUserRole();
        switch ($user_role) {
            case 'admin':
                $query->where(['role' => [User::ROLE_OPERATOR, User::ROLE_MANAGER]]);
                break;
            case 'manager':
            case 'operator':
                $query->where(['role' => User::ROLE_OPERATOR]);
                break;
        }
        $users = $query->all();

        if (count($users) > 0) {
            $this->json(['items' => $users], 200);
        } else {
            $this->json(['items' => []], 404);
        }
    }

    // Получение списка контактов для вывода в модальном окне.
    public function actionGetContactsAnotherTag() {
        $request_data = Yii::$app->request->get();
        $tag_id = $request_data['tag_id'];
        if (!is_int((int)$tag_id) || (int)$tag_id <= 0) {
            $this->json([], 415);
        }
        $query = new Query();
        $query->from(Contact::tableName())->select(['`contact`.*'])->where(['`contact`.is_deleted' => 0]);

        //Sub-query for find contacts by tag
        $contactsByTagQuery = (new Query())->select('`contact_id`')->from('`contact_tag`')->where(['`contact_tag`.`tag_id`' => $tag_id]);
        $query->andWhere(['NOT IN', '`contact`.`id`', $contactsByTagQuery]);

        if (Yii::$app->user->identity->role == User::ROLE_MANAGER) {

            //Sub-query for search for contacts whose tag is identical to a manager tag
            $contactsByManagerTagQuery = (new Query())->select('`contact_id`')->from('`contact_tag`')
                ->where(['IN', '`contact_tag`.`tag_id`', (new Query())
                    ->select('`tag_id`')->from('`user_tag`')
                    ->where(['`user_id`' => Yii::$app->user->identity->id])]);
            $query->andWhere(['IN', '`contact`.`id`', $contactsByManagerTagQuery]);
        }

        $columns = Tag::getContactsForTagTableView();

        //Sorting
        if (isset($request_data['order'])) {
            $order_by_sort = $request_data['order'][0]['dir'] == 'asc' ? SORT_ASC : SORT_DESC;
            $sort_column = array_keys($columns)[$request_data['order'][0]['column']];
            $sorting = [$sort_column => $order_by_sort];
        } else {
            $sorting = ['`contact`.`id`' => SORT_DESC];
        }
        //$dump = $query->createCommand()->rawSql;
        //$total_count = $query->count();

        //Filtering
        foreach ($request_data['columns'] as $requestColumn) {
            if (!empty($requestColumn['search']['value']) && $columns[$requestColumn['name']]['have_search']) {
                if (isset($columns[$requestColumn['name']]['db_cols'])) {
                    $db_cols_where = ['or'];
                    foreach ($columns[$requestColumn['name']]['db_cols'] as $db_col_i => $db_col_v) {
                        $db_cols_where[] = ['like', 'contact.' . $db_col_v, $requestColumn['search']['value']];
                    }
                    $query->andWhere($db_cols_where);
                } elseif ($requestColumn['name'] == 'tags') {
                    $contactsByTagNameQuery = (new Query())->select('`contact_id`')->from('`contact_tag`')->where(['IN', '`contact_tag`.`tag_id`',
                        (new Query())->select('`id`')->from('`tag`')->where(['LIKE', '`name`', $requestColumn['search']['value']]),
                    ]);
                    $query->andWhere(['IN', '`contact`.`id`', $contactsByTagNameQuery]);
                } else {
                    $query->andWhere(['like', 'contact.' . $requestColumn['name'], $requestColumn['search']['value']]);
                }
            }
        }

        $query->orderBy($sorting);
        $query->limit($request_data['length'])->offset($request_data['start']);
        $dump = $query->createCommand()->rawSql;


        $total_filtering_count = $query->count();
        $contacts = $query->all();
        $contacts_id = [];
        foreach ($contacts as $contact) {
            $contacts_id[] = $contact['id'];
        }
        $tagByContactIdsQuery = new Query();
        $tagByContactIdsQuery->from('`tag`')->select(['`contact_tag`.`contact_id` as `contact_id`', '`tag`.`name` as `tag_name`'])->join('LEFT JOIN', '`contact_tag`', '`contact_tag`.`tag_id` = `tag`.`id`')->where(['IN', '`contact_tag`.`contact_id`', $contacts_id]);
        $dump = $tagByContactIdsQuery->createCommand()->rawSql;
        $contactTags = $tagByContactIdsQuery->all();
        $contact_widget = new ContactTagTableWidget();
        $contact_widget->contacts = $contacts;
        $contact_widget->contacts_tags = $contactTags;
        $data = $contact_widget->run();

        $json_data = ["draw" => intval($request_data['draw']), "recordsTotal" => intval($total_count), "recordsFiltered" => intval($total_filtering_count), "data" => $data   // total data array
        ];
        echo json_encode($json_data);
        die;
    }

    public function actionExportCsv() {
        $request_data = Yii::$app->request->post();
        $tag_id = $request_data['tag_id'];
        if ($tag_id && !empty($tag_id)) {
            $user_role = Yii::$app->user->identity->getUserRole();

            $filters = [];
            if (!empty($request_data['manager_id'])) {
                $filters['manager_id'] = $request_data['manager_id'];
            }
            if (!empty($request_data['status'])) {
                $filters['status'] = $request_data['status'];
            }
            if (!empty($request_data['comment'])) {
                $filters['comment'] = $request_data['comment'];
            }
            if (!empty($request_data['attitude_level'])) {
                $filters['attitude_level'] = $request_data['attitude_level'];
            }
            $contacts = Contact::getByTag($tag_id, $user_role, $filters, true);
            $tag_contacts_widget = new TagContactsTableWidget();
            $tag_contacts_widget->tag_contacts = $contacts;
            $tag_contacts_widget->user_role = $user_role;
            $tag_contacts_widget->export = true;
            $data = $tag_contacts_widget->run();

            $csv_export = new CSVExport(['filename' => 'called_contacts', 'csvOptions' => ['delimiter' => ';']]);

            $columns = ['int_id', 'surname', 'Phones', 'operator', 'status', 'comment', 'attitude', 'city', 'street', 'house', 'flat', 'history'];
            $csv_export->addRow($columns);
            foreach ($data as $item) {
                $csv_export->addRow($item);
            }
            $csv_export->export();
        }
        $this->redirect('/tags');
    }

    public function actionGetdata() {
        $request_data = Yii::$app->request->post();
        $tag_id = $request_data['columns'][1]['search']['value']; //Tag
        if ($tag_id && !empty($tag_id)) {
            $user_role = Yii::$app->user->identity->role;

            $filters = [];
            if (!empty($request_data['columns'][4]['search']['value'])) {
                $filters['manager_id'] = $request_data['columns'][4]['search']['value']; //Manager
            }
            if (!empty($request_data['columns'][5]['search']['value'])) {
                $filters['status'] = $request_data['columns'][5]['search']['value']; //Status
            }
            if (!empty($request_data['columns'][6]['search']['value'])) {
                $filters['comment'] = $request_data['columns'][6]['search']['value']; //Comment
            }
            if (!empty($request_data['columns'][7]['search']['value'])) {
                $filters['attitude_level'] = $request_data['columns'][7]['search']['value']; //Attitude Level
            }
            $contacts = Contact::getByTag($tag_id, $user_role, $filters, false);
//            if (count($contacts) > 0 && $user_role == User::ROLE_OPERATOR) {
//                $contact = $contacts[0];
//                Contact::addContInPool($contact['id'],Yii::$app->user->identity->id, $tag_id, null);
//            }
            $counts = Contact::getCountByTag($tag_id);
            $tag_contacts_widget = new TagContactsTableWidget();
            $tag_contacts_widget->tag_contacts = $contacts;
            $tag_contacts_widget->user_role = $user_role;
            $data = $tag_contacts_widget->run();
            $json_data = ["draw" => intval($request_data['draw']), "recordsTotal" => count($data), "recordsFiltered" => 7, "data" => $data,   // total data array
                "contact_count" => ["count_all" => $counts['all'], "count_called" => $counts['called'],]];
            echo json_encode($json_data);
            die;
        } else {
            $json_data = ["draw" => intval($request_data['draw']), "recordsTotal" => 0, "recordsFiltered" => 0, "data" => [],   // total data array
                "contact_count" => ["count_all" => 0, "count_called" => 0]];
            echo json_encode($json_data);
            die;
        }
    }

    public function actionUpdateUsers() {
        $request_data = Yii::$app->request->post();
        $tag_id = $request_data['tag_id'];
        $users = $request_data['users'];
        $tag = Tag::getById($tag_id);

        if (!$tag) {
            $this->json([], 404);
        }
        $userRole = Yii::$app->user->identity->role;
        if ($userRole == User::ROLE_ADMIN) {
            $where = "(`" . User::tableName() . "`.`role` = " . User::ROLE_OPERATOR . " OR `" . User::tableName() . "`.`role` = " . User::ROLE_MANAGER . ")";
        } else if ($userRole == User::ROLE_MANAGER) {
            $where = "`" . User::tableName() . "`.`role` = " . User::ROLE_OPERATOR;
        }

        $where .= " AND `" . UserTag::tableName() . "`.`tag_id` = " . $tag_id;
        //$where[] = ['tag_id' => $tag_id];
        $deleteQuery = "DELETE " . UserTag::tableName() . " FROM `" . UserTag::tableName() . "` LEFT JOIN `" . User::tableName() . "` ON `" . UserTag::tableName() . "`.`user_id` = `" . User::tableName() . "`.`id` WHERE " . $where;

        Yii::$app->db->createCommand($deleteQuery)->execute();
        $tag->setUsers($users);
        $this->json([], 200);
    }

    public function actionAddContactsByFilter() {
        $request_data = Yii::$app->request->post();
        $tag_id = $request_data['tag_id'];
        $filters = $request_data['filters'];

        $query = new Query();
        $query->select(['`contact`.`id`']);
        $contactsByTagQuery = (new Query())->select('`contact_id`')->from('`contact_tag`')->where(['`contact_tag`.`tag_id`' => $tag_id]);
        $query->andWhere(['NOT IN', '`contact`.`id`', $contactsByTagQuery]);

        if (Yii::$app->user->identity->role == User::ROLE_MANAGER) {

            //Sub-query for search for contacts whose tag is identical to a manager tag
            $contactsByManagerTagQuery = (new Query())->select('`contact_id`')->from('`contact_tag`')
                ->where(['IN', '`contact_tag`.`tag_id`', (new Query())
                    ->select('`tag_id`')->from('`user_tag`')
                    ->where(['`user_id`' => Yii::$app->user->identity->id])]);
            $query->andWhere(['IN', '`contact`.`id`', $contactsByManagerTagQuery]);
        }
        $query->andWhere(['`contact`.`is_deleted`' => 0]);

        //Filtering
        $columns = Tag::getContactsForTagTableView();
        $filtered = false;
        foreach ($filters as $filterName => $filterValue) {
            if (!empty($filterValue) && $columns[$filterName]['have_search']) {
                if (isset($columns[$filterName]['db_cols'])) {
                    $db_cols_where = ['or'];
                    foreach ($columns[$filterName]['db_cols'] as $db_col_i => $db_col_v) {
                        $db_cols_where[] = ['like', 'contact.' . $db_col_v, $filterValue];
                    }
                    $query->andWhere($db_cols_where);
                    $filtered = true;
                } elseif ($filterName == 'tags') {
                    $contactsByTagNameQuery = (new Query())->select('`contact_id`')->from('`contact_tag`')->where(['IN', '`contact_tag`.`tag_id`',
                        (new Query())->select('`id`')->from('`tag`')->where(['LIKE', '`name`', $filterValue]),
                    ]);
                    $query->andWhere(['IN', '`contact`.`id`', $contactsByTagNameQuery]);
                    $filtered = true;
                } elseif ($filterName = 'id') {
                    $query->andWhere(['IN', 'contact.' . $filterName, $filterValue]);
                    $filtered = true;
                } else {
                    $query->andWhere(['like', 'contact.' . $filterName, $filterValue]);
                    $filtered = true;
                }
            }
        }
        if (!$filtered) {
            $this->json([], 415, "Filter not found");
        }
        $query->from('`contact`');
        $query->groupBy('`contact`.`id`');
        //$dump = $query->createCommand()->rawSql;
        $contacts = $query->all();
        $insert_data = [];
        foreach ($contacts as $contact) {
            $insert_data[] = [$contact['id'], $request_data['tag_id']];
        }
        if (Yii::$app->db->createCommand()->batchInsert('`contact_tag`', ['contact_id', 'tag_id'], $insert_data)->execute()) {
            $this->json([], 200);
        }
        $this->json([], 500);

    }

    public function actionDelete() {
        $tag_id = Yii::$app->request->post('id');
        $tag = Tag::getById($tag_id);
        if ($tag->archive()) {
            $this->json(false, 200);
        }
        $this->json(false, 500);
    }

    public function actionRestore() {
        $tag_id = Yii::$app->request->post('id');
        $tag = Tag::getById($tag_id);
        if ($tag->restore()) {
            $this->json(false, 200);
        }
        $this->json(false, 500);
    }

}