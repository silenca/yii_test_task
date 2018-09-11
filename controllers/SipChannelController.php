<?php
/**
 * SipChannelController.php
 * @copyright ©yii_test
 * @author Valentin Stepanenko catomik13@gmail.com
 */

namespace app\controllers;


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
                            'get-data'
                        ],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ]
            ]
        ];
    }

    public function actionIndex(){
        $table_cols = SipChannel::getColsForTableView();
        return $this->render('index',['table_cols'=>$table_cols]);
    }

    public function actionGetData() {

        $request_data = Yii::$app->request->get();
        $query = SipChannel::find();

        $total_count = $query->count();

        $query
            ->orderBy(['id'=>SORT_DESC])
            ->limit($request_data['length'])
            ->offset($request_data['start']);

        $channels = $query->asArray()->all();
        $json_data = [
            "draw" => intval($request_data['draw']),
            "recordsTotal" => intval($total_count),
            "recordsFiltered" => intval($total_count),
            "data" => $channels,   // total data array
            //"contact_ids" => $contact_ids
        ];

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $json_data;
    }

    public function actionEdit() {
        $post = Yii::$app->request->post();
    }
}