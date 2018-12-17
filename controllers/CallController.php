<?php

namespace app\controllers;

use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\db\Query;
use app\models\User;
use app\models\Contact;
use app\models\Call;
use app\models\CallManager;
use app\models\MissedCall;
use app\models\Cdr;
use app\components\widgets\CallTableWidget;

class CallController extends BaseController {
    public function actionIndex(){
        $cdr = Cdr::find()->all();
        foreach ($cdr as $call) {
            $calls[] = $this->buildTable($call);
        }
        $calls = array_reverse($calls);
        $call_statuses = Cdr::getCallStatuses();
        $managers = User::find()->all();
        $pages = new Pagination();
        return $this->render('index', [
            'calls' => $calls,
            'call_statuses' => $call_statuses,
            'managers' => $managers
        ]);
    }

    //perform call to table view
    public function buildTable($call){
        $data = $call->attributeLabels();
        $call_date = $call['start'];
        if($call_date){
            $data['date'] = substr($call_date ,0,10);
            $data['time'] = substr($call_date ,11);
        }
        $data['type'] = $call->getType();
        $data['manager'] = $call->getManager()['firstname'];
        $data['contact'] = $call->getContact();
        $data['record'] = $call->getUnique();
//        $data['uniqueid'] = $call['uniqueid'];
        return $data;
    }

    public function actionGetdata() {
        $cdr = Cdr::find()->all();
        foreach ($cdr as $call) {
            $data[] = $this->buildTable($call);
            if(gettype(end($data)['contact']) === 'object'){
                $data[sizeof($data)-1]['contact'] = end($data)['contact']->attributes;
            }
        }
        $data = array_reverse($data);
        $json_data = array(
            "draw" => sizeof($data),
            "recordsTotal" => sizeof($data),
            "recordsFiltered" => sizeof($data),
            "data" => $data   // total data array
        );
        echo json_encode($json_data);
        die;
    }

}