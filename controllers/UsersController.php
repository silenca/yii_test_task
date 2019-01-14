<?php

namespace app\controllers;

use app\models\UserTag;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\ChannelAttraction;
use app\models\User;
use app\models\UploadDoc;
use app\models\forms\UserForm;
use app\models\forms\CommentForm;
use app\models\Tag;
use app\components\widgets\UserTableWidget;
use yii\web\UploadedFile;

class UsersController extends BaseController
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
                            'getdata',
                            'edit',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['admin'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $session = Yii::$app->session;
        $hide_columns = $session->get('user_hide_columns');
        if (!$hide_columns) {
            $hide_columns = [];
        }
        $table_cols = User::getColsForTableView();
        $filter_cols = User::getColsForTableView();
        unset($filter_cols['id']);
        return $this->render('index', ['hide_columns' => $hide_columns, 'table_cols' => $table_cols, 'filter_cols' => $filter_cols]);
    }

    public function actionGetdata()
    {
        $request_data = Yii::$app->request->get();
        $user_tableName = User::tableName();
        $query = User::find()->with('tags');
        $query_total = clone $query;
        $total_count = $query_total->count();
        $columns = User::getColsForTableView();
        //Sorting
        if (isset($request_data['order'])) {
            $order_by_sort = $request_data['order'][0]['dir'] == 'asc' ? SORT_ASC : SORT_DESC;
            $sort_column = array_keys($columns)[$request_data['order'][0]['column']];
            if (isset($columns[$sort_column]['db_cols'])) {
                $sort_column = $columns[$sort_column]['db_cols'][0];
            }
            $sorting = [
                $user_tableName.'.'.$sort_column => $order_by_sort
            ];
        } else {
            $sorting = [
                $user_tableName.'.id' => SORT_DESC
            ];
        }
//        $query = User::find()->with('tags');
        //join Tags
//        $query->leftJoin(UserTag::tableName() . ' `ct`', '`ct`.`user_id` = user.`id`')
//            ->leftJoin(Tag::tableName() . ' `t`', '`t`.`id` = `ct`.`tag_id`');

        //Filtering
        foreach ($request_data['columns'] as $column) {
            if (!empty($column['search']['value'])) {
                if (isset($columns[$column['name']]['db_cols'])) {
                    $db_cols_where = ['or'];
                    foreach ($columns[$column['name']]['db_cols'] as $db_col_i => $db_col_v) {
                        $db_cols_where[] = ['like', $user_tableName.'.'.$db_col_v, $column['search']['value']];
                    }
                    $query->andWhere($db_cols_where);
                } elseif ($column['name'] == 'tags') {
                    $query->joinWith('tags')->andWhere(['like', Tag::tableName().'.name', $column['search']['value']]);
                } else {
                    $query->andWhere(['like', $user_tableName.'.'.$column['name'], $column['search']['value']]);
                }
            }
        }
//        foreach ($request_data['columns'] as $column) {
//            if (!empty($column['search']['value'])) {
//                if (isset($columns[$column['name']]['db_cols'])) {
//                    foreach ($columns[$column['name']]['db_cols'] as $db_col_i => $db_col_v) {
//                        if ($db_col_i == 0) {
//                            $query->andWhere(['like', 'user.'.$db_col_v, $column['search']['value']]);
//                        } else {
//                            $query->orWhere(['like', 'user.'.$db_col_v, $column['search']['value']]);
//                        }
//                    }
//                } elseif ($column['name'] == 'tags') {
//                    $query->andWhere(['like', 't.name', $column['search']['value']]);
//                } else {
//                    $query->andWhere(['like', 'user.'.$column['name'], $column['search']['value']]);
//                }
//            }
//        }

        $total_filtering_count = $query->count();
        $query
            ->orderBy($sorting)
            ->limit($request_data['length'])
            ->offset($request_data['start']);

        $users = $query->all();
        $user_widget = new UserTableWidget();
        $user_widget->users = $users;
        $data = $user_widget->run();

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
        $user_form = new UserForm();
        if ($post['id']) {
            $user_form->edited_id = $post['id'];
        }
        if ($post['edit_tags']) {
            $user_form->edit_tags = true;
        }
        $user_form->attributes = $post;

        if ($user_form->validate()) {
            try {
                $user = null;
                if (isset($post['id']) && !empty($post['id'])) {
                    $user = User::find()->where(['id' => $post['id']])->one();
                    if (!Yii::$app->user->can('updateUser')) {
                        $this->json(false, 403, 'Недостаточно прав для редактирования');
                    }

                } else {
                    $user = new User();
                    $user->auth_key = '';
                }
                // set or change user's password
                if (isset($post['user_password'], $post['user_password_confirm']) && $post['user_password'] != '') {
                    // change user password
                    if ($post['user_password'] == $post['user_password_confirm']) {
                        $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash($post['user_password']);
                    }
                }
                unset($post['_csrf']);
                unset($post['id']);
                $user->attributes = $user_form->attributes;
                $user->remove_tags = true;
                if ($user->edit(['tags' => $user_form->tags])) {
                    $this->json(['id' => $user->id], 200);
                } else {
                    $this->json(false, 415, $user->getErrors());
                }
            } catch (\Exception $ex) {
                $this->json(false, 500);
            }
        } else {
            $errors = $user_form->getErrors();
            $this->json(false, 415, $errors);
        }
    }

    public function actionView()
    {
        $user_id = Yii::$app->request->get('id');
        $user = User::find()->with('tags')->where(['id' => $user_id]);
        $user2 = clone $user;
        $user2 = $user2->one();
        $user_arr = $user->asArray()->one();
        $user_data = array_intersect_key($user_arr, array_flip(User::$safe_fields));

        if (count($user_arr['tags']) > 0) {
            $user_data['tags'] = $user_arr['tags'];
        }

        $this->json($user_data, 200);
    }

    public function actionDelete()
    {
        $user_id = Yii::$app->request->post('id');
        if (User::deleteById($user_id)) {
            $this->json(false, 200);
        }
    }
}
