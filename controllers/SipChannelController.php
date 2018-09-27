<?php
/**
 * SipChannelController.php
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */

namespace app\controllers;


use app\components\widgets\SipChannelTableWidget;
use app\models\forms\SipChannelForm;
use app\models\SipChannel;
use Yii;
use yii\filters\AccessControl;
use yii\web\Response;

class SipChannelController extends BaseController
{
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'get-data',
                            'edit',
                            'view',
                            'hide-columns',
                            'delete'
                        ],
                        'allow' => true,
                        'roles' => ['admin', 'supervisor'],
                    ],
                ]
            ]
        ];
    }

    public function actionIndex(){
        $table_cols = SipChannel::getColsForTableView();
        $filter_cols = SipChannel::getColsForTableView();
        $session = Yii::$app->session;
        $hide_columns = $session->get('sip_channel_hide_columns');
        if (!$hide_columns) {
            $hide_columns = [];
        }
        unset($filter_cols['id']);
        return $this->render('index',['table_cols'=>$table_cols, 'filter_cols' => $filter_cols, 'hide_columns' => $hide_columns]);
    }

    public function actionGetData() {

        $request_data = Yii::$app->request->get();
        $query = SipChannel::find();
        $table_name = SipChannel::tableName();

        $query_total = clone $query;
        $total_count = $query_total->count();
        $columns = SipChannel::getColsForTableView();

        if (isset($request_data['order'])) {
            $order_by_sort = $request_data['order'][0]['dir'] == 'asc' ? SORT_ASC : SORT_DESC;
            $sort_column = array_keys($columns)[$request_data['order'][0]['column']];
            if (isset($columns[$sort_column]['db_cols'])) {
                $sort_column = $columns[$sort_column]['db_cols'][0];
            }
            $sorting = [
                $table_name.'.'.$sort_column => $order_by_sort
            ];
        } else {
            $sorting = [
                $table_name.'.id' => SORT_DESC
            ];
        }
        foreach ($request_data['columns'] as $column) {
            if (!empty($column['search']['value'])) {
                if (isset($columns[$column['name']]['db_cols'])) {
                    $db_cols_where = ['or'];
                    foreach ($columns[$column['name']]['db_cols'] as $db_col_i => $db_col_v) {
                        $db_cols_where[] = ['like', $table_name.'.'.$db_col_v, $column['search']['value']];
                    }
                    $query->andWhere($db_cols_where);
                } else {
                    $query->andWhere(['like', $table_name.'.'.$column['name'], $column['search']['value']]);
                }
            }
        }

        $total_filtering_count = $query->count();
        $query
            ->orderBy($sorting)
            ->limit($request_data['length'])
            ->offset($request_data['start']);

        $channels = $query->all();

        $channels_data = new SipChannelTableWidget();
        $channels_data->sip_channels = $channels;
        $data = $channels_data->run();
        $json_data = [
            "draw" => intval($request_data['draw']),
            "recordsTotal" => intval($total_count),
            "recordsFiltered" => intval($total_filtering_count),
            "data" => $data,
        ];

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $json_data;
    }

    public function actionEdit() {
        $post = Yii::$app->request->post();
        $sip_channel_form = new SipChannelForm();
        if(isset($post['id']) && !empty($post['id'])) {
            $sip_channel_form->edited_id = (int)$post['id'];
        }

        $sip_channel_form->attributes = $post;
        if($sip_channel_form->validate()) {
            try {
                $sip_channel = null;
                if(isset($post['id'])  && !empty($post['id'])) {
                    $sip_channel = SipChannel::find()->where(['id'=>(int)$post['id']])->one();
                } else {
                    $sip_channel = new SipChannel();
                }
                unset($post['_csrf']);
                unset($post['id']);
                $sip_channel->attributes = $sip_channel_form->attributes;
                if($sip_channel->save()) {
                    $this->json(['id' => $sip_channel->id], 200);
                } else {
                    $this->json(false, 415,$sip_channel->getErrors());
                }
            } catch (\Exception $ex) {
                $this->json(false, 500, $ex->getMessage());
            }
        } else {
            $this->json(false,415,$sip_channel_form->getErrors());
        }
    }

    public function actionView(){
        $sip_channel_id = Yii::$app->request->get('id');
        $sip_channel = SipChannel::find()->where(['id' => (int)$sip_channel_id]);
        $sip_channel_arr = $sip_channel->asArray()->one();
        $sip_channel_data = $sip_channel_arr;//array_intersect_key($sip_channel_arr, array_flip(SipChannel::$safe_fields));

        $this->json($sip_channel_data, 200);
    }

    public function actionHideColumns()
    {
        $hide_columns = Yii::$app->request->get('hide_columns');
        Yii::$app->session->set('sip_channel_hide_columns', $hide_columns);
        $this->json(false, 200);
    }

    public function actionDelete()
    {
        $sip_channel_id = Yii::$app->request->post('id');
        if (SipChannel::deleteById($sip_channel_id)) {
            $this->json(false, 200);
        }
    }
}