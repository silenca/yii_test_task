<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\db\Query;
use app\models\User;
use app\models\Contact;
use app\models\Call;
use app\models\CallManager;
use app\models\MissedCall;
use app\components\widgets\CallTableWidget;

class CallController extends BaseController {

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
                //'delete' => ['post']
                ],
            ],
        ];
    }

    public function actionIndex() {
        $call_statuses = Call::getCallStatuses();
        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $managers = User::find()->all();
            return $this->render('index', [
                'managers' => $managers,
                'call_statuses' => $call_statuses
            ]);
        } else if (Yii::$app->user->identity->role == User::ROLE_MANAGER) {
            $managers = User::find()->where(['role' => [User::ROLE_MANAGER, User::ROLE_OPERATOR]])->all();
            return $this->render('index', [
                'managers' => $managers,
                'call_statuses' => $call_statuses
            ]);
        } else {
            return $this->render('index', [
                'call_statuses' => $call_statuses
            ]);
        }
    }

    public function actionView() {
        $call_id = Yii::$app->request->post('id');
        $manager_id = Yii::$app->user->identity->id;
        if (MissedCall::remove($call_id, $manager_id)) {
            $this->json([], 200);
        }
        $this->json([], 404);
    }

    public function actionGetdata() {
        $request_data = Yii::$app->request->get();

        $query = Call::find()
            ->joinWith(['callManagers.manager'])
            ->with('callManagers', 'callManagers.manager', 'contact.tags', 'missedCall')
            ->andWhere(['<>', '`call`.`status`', 'new'])
            ->distinct();

        $user_id = Yii::$app->user->identity->getId();
        $user_role = Yii::$app->user->identity->getUserRole();
        $columns = Call::getTableColumns();
        $columns_index = array_keys($columns);

        //Sorting
        if (isset($request_data['order'])) {
            $order_by_sort = $request_data['order'][0]['dir'] == 'asc' ? SORT_ASC : SORT_DESC;
            $sort_column = $columns[$columns_index[$request_data['order'][0]['column']]];
            $sorting = [
                $sort_column => $order_by_sort
            ];
        } else {
            $sorting = [
                '`call`.`id`' => SORT_DESC
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

        $query->limit($request_data['length'])
            ->offset($request_data['start'])
            ->orderBy($sorting);

        //Filtering
        $filter_status = $request_data['columns'][4]['search']['value'];
        $filter_tag_name = $request_data['columns'][6]['search']['value'];
        $filter_manager_id = $request_data['columns'][7]['search']['value'];
        if (!empty($filter_status)) {
            $filter_status = explode('|', $filter_status);
            $statuses = explode('_', $filter_status[0]);
            $types = explode('_', $filter_status[1]);
            if (count($statuses) > 0) {
                $query->andWhere([Call::tableName().'.status' => $statuses]);
            }
            if (count($types) > 0) {
                $query->andWhere([Call::tableName().'.type' => $types]);
            }
        }
        if (!empty($filter_tag_name)) {
            $query->joinWith('contact.tags')->andWhere(['like', 'tag.name', $filter_tag_name]);
        }
        if (!empty($filter_manager_id)) {
            $query->andWhere([User::tableName().'.id' => $filter_manager_id]);
        }
//        $dump = $query->createCommand()->rawSql;
        $total_filtering_count = $query->count();

        $dump = $query->createCommand()->rawSql;
        $calls = $query->all();
        $call_widget = new CallTableWidget();
        $call_widget->calls = $calls;
        $call_widget->user_role = $user_role;
        $call_widget->user_id = $user_id;
        $data = $call_widget->run();

        $json_data = array(
            "draw" => intval($request_data['draw']),
            "recordsTotal" => intval($total_count),
            "recordsFiltered" => intval($total_filtering_count),
            "data" => $data   // total data array
        );
        echo json_encode($json_data);
        die;
    }

}
