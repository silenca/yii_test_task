<?php

namespace app\controllers;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\db\Query;
use app\models\Action;
use app\models\ActionObject;
use app\models\ActionType;
use app\models\Contact;
use app\models\ContactComment;
use app\models\ObjectApartment;
use app\models\User;
use app\components\widgets\ActionTableWidget;
use Yii;

class ActionController extends BaseController {

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'getdata', 'view'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
        ];
    }

    public function actionIndex() {
        $action_types = ActionType::find()->all();
        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $managers = User::find()->all();
            return $this->render('index', [
                'managers' => $managers,
                'action_types' => $action_types
            ]);
        } else if (Yii::$app->user->identity->role == User::ROLE_MANAGER) {
            $managers = User::find()->where(['role' => [User::ROLE_MANAGER, User::ROLE_OPERATOR]])->all();
            return $this->render('index', [
                        'managers' => $managers,
                        'action_types' => $action_types
                   ]);
        } else {
            return $this->render('index', [
                'action_types' => $action_types
            ]);
        }
    }

    public function actionGetdata() {
        $request_data = Yii::$app->request->get();
        $columns = Action::getTableColumns();
        $query = Action::find()->joinWith(['user', 'actionType'])->with('actionType', 'contact.tags', 'user', 'comment');
//        $from_query = new Query();
        $user_id = Yii::$app->user->identity->getId();
        $user_role = Yii::$app->user->identity->getUserRole();

        //Sorting
        if (isset($request_data['order'])) {
            $order_by_sort = $request_data['order'][0]['dir'] == 'asc' ? SORT_ASC : SORT_DESC;
            $sort_column = $columns[$request_data['order'][0]['column']];
            $sorting = [
                $sort_column => $order_by_sort
            ];
        } else {
            $sorting = [
                Action::tableName().'.`id`' => SORT_DESC
            ];
        }

        //User role restrictions
        switch ($user_role) {
            case 'operator':
                $query->andWhere([User::tableName().'.id' => $user_id]);
                break;
            case 'manager':
                $query->andWhere([User::tableName().'.role' => [User::ROLE_MANAGER, User::ROLE_OPERATOR]]);
                break;
        }
        $total_count = $query->count();

        //Filtering
        $filter_action_type_id = $request_data['columns'][2]['search']['value'];
        $filter_phone = $request_data['columns'][3]['search']['value'];
        $filter_tag_name = $request_data['columns'][4]['search']['value'];
        $filter_comment = $request_data['columns'][6]['search']['value'];
        $filter_manager_id = $request_data['columns'][8]['search']['value'];
        if (!empty($filter_action_type_id)) {
            $query->andWhere([ActionType::tableName().'.id' => $filter_action_type_id]);
        }
        if (!empty($filter_phone)) {
            $query->joinWith('contact')->where(['like', 'contact.first_phone', $filter_phone])
                ->orWhere(['like', 'contact.second_phone', $filter_phone])
                ->orWhere(['like', 'contact.third_phone', $filter_phone])
                ->orWhere(['like', 'contact.fourth_phone', $filter_phone]);
        }
        if (!empty($filter_tag_name)) {
            $query->joinWith('contact.tags')->andWhere(['like', 'tag.name', $filter_tag_name]);
        }
        if (!empty($filter_comment)) {
            $query->joinWith('comment')->andWhere(['like', 'comment', $filter_comment]);
        }
        if (!empty($filter_manager_id)) {
            $query->andWhere([User::tableName().'.id' => $filter_manager_id]);
        }
        $total_filtering_count = $query->count();

        $query->limit($request_data['length'])
            ->offset($request_data['start'])
            ->orderBy($sorting);

        $actions = $query->all();
        $action_widget = new ActionTableWidget();
        $action_widget->actions = $actions;
        $action_widget->user_role = $user_role;
        $action_widget->user_id = $user_id;
        $data = $action_widget->run();

        $json_data = array(
            "draw" => intval($request_data['draw']),
            "recordsTotal" => intval($total_count),
            "recordsFiltered" => intval($total_filtering_count),
            "data" => $data   // total data array
        );
        echo json_encode($json_data);
        die;
    }

    public function actionView() {
        $id = Yii::$app->request->post('id');
        $date = Yii::$app->request->post('date');
        $date = strtotime(date('Y-m-d', strtotime($date)));
        $today = mktime(0, 0, 0);
        if ($today >= $date) {
            Action::updateAll(['viewed' => '1'], ['id' => $id]);
            $this->json([], 200);
        }
        $this->json([], 412);
    }

    public function actionAddcomment() {
        $action_id = Yii::$app->request->post('id');
        $comment_text = Yii::$app->request->post('comment');
        $action_comment = new ContactComment();
        if ($action_comment->add($action_id, $comment_text)) {
//            $contact_history = new ContactHistory();
//            $comment_text = "комментарий - " . $comment_text;
//            $contact_history->add($action_id, $comment_text, '', 'comment', $contact_comment->datetime);
//            $response_date = [
//                'text' => $comment_text,
//                'datetime' => date("d-m-Y G:i:s", strtotime($contact_comment->datetime))
//            ];
//            $this->json($response_date, 200);
            $this->json([], 200);
        } else {
            $errors = $action_comment->getErrors();
            $this->json(false, 415, $errors);
        }
    }

}
