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
                        'actions' => ['index', 'getdata'],
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
        if (Yii::$app->user->identity->role == User::ROLE_MANAGER) {
            return $this->render('index');
        } else {
            $managers = User::find()->where(['role' => User::ROLE_MANAGER])->all();
            return $this->render('index', [
                        'managers' => $managers
            ]);
        }
    }

    public function actionGetdata() {
        $request_data = Yii::$app->request->get();
        $columns = Action::getTableColumns();
        $query = new Query();
        $from_query = new Query();
        if (isset($request_data['order'])) {
            $order_by_sort = $request_data['order'][0]['dir'] == 'asc' ? SORT_ASC : SORT_DESC;
            $sort_column = $columns[$request_data['order'][0]['column']];
            $sorting = [
                $sort_column => $order_by_sort
            ];
        } else {
            $sorting = [
                '`a`.`id`' => SORT_DESC
            ];
        }
        $from_query->from(Action::tableName() . " `a`")
                ->limit($request_data['length'])
                ->offset($request_data['start'])
                ->orderBy($sorting);

        if (Yii::$app->user->identity->role == User::ROLE_MANAGER) {
            $from_query->where(['`a`.`manager_id`' => Yii::$app->user->identity->id]);
            $total_filtering_count = $from_query->count();
            $total_count = $from_query->count();
        } else {
            if (!empty($request_data['columns'][0]['search']['value'])) {
                $from_query->where(['`a`.`manager_id`' => $request_data['columns'][0]['search']['value']]);
            }
            $total_filtering_count = $from_query->count();
            $total_count = $from_query->count();
        }
        
        $from_query_sql = $from_query->createCommand()->rawSql;
        $query->from("(" . $from_query_sql . ") `a`");
        $query->select(implode(',', $columns))
                //->from("(SELECT * FROM " . Action::tableName() . " LIMIT " . $request_data['length'] . ") `a`")
                ->join('LEFT JOIN', ActionType::tableName() . ' `at`', '`at`.`id` = `a`.`action_type_id`')
                ->join('LEFT JOIN', Contact::tableName() . ' `c`', '`c`.`id` = `a`.`contact_id`')
                ->join('LEFT JOIN', User::tableName() . ' `u`', '`u`.`id` = `a`.`manager_id`')
                ->join('LEFT JOIN', ContactComment::tableName() . ' `cc`', '`cc`.`id` = (SELECT MAX(`id`) FROM `contact_comment` WHERE `contact_comment`.`contact_id` = `a`.`contact_id`)')
                ->join('LEFT OUTER JOIN', ActionObject::tableName() . ' `ao`', '`a`.`id` = `ao`.`action_id`')
                ->join('LEFT OUTER JOIN', ObjectApartment::tableName() . ' `oa`', '`oa`.`id` = `ao`.`object_id`');
        //$dump = $query->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;
        $query->orderBy($sorting);
        $actions = $query->all();

        $action_widget = new ActionTableWidget();
        $action_widget->actions = $actions;
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

}
