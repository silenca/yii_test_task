<?php

namespace app\components\widgets;

use app\components\Filter;
use app\models\{Call, User};
use yii\base\Widget;
use Yii;

class CallTableWidget extends Widget {

    public $calls;
    public $user_role;
    public $user_id;
    public $columns;
//    public $calls_missed;

//    public function init() {
//        parent::init();
//    }
    public function run()
    {
        $data = [];

        foreach($this->calls as $call) {
            /**@var $call Call*/
            $item = [
                'id' => $call->id,
                'date' => date('d-m-Y', strtotime($call->date_time)),
                'time' => date('H-i-s', strtotime($call->date_time)),
                'type' => Call::getCallStatusLabel($call->type, $call->status),
                'manager' => implode(', ', array_map(function($managerRelation){
                    return $managerRelation->manager->firstname;
                }, $call->callManagers)),
            ];

            // Contact data
            $contactName = 'Телефон';
            switch(\Yii::$app->user->identity->role) {
                case User::ROLE_OPERATOR:
                    if($call->contact) {
                        $contactName = $call->contact->int_id;
                    }
                    break;
                default:
                    if($call->contact && $call->contact->name) {
                        $contactName = $call->contact->name;
                    } else {
                        $contactName = $call->phone_number;
                    }
                    break;
            }

            $item['contact'] = '<a class="contact btn-audio-call" data-id="'.$call->contact_id.'" data-number="'.$call->phone_number.'" data-contact_id="'.$call->contact_id.'" data-phone="'.$call->phone_number.'" href="javascript:void(0)">'.$contactName.'</a>';

            if($this->canListen() && $call->record) {
                $item['record'] = "<audio controls src='".$call->record."' type='audio/mpeg'>";
            } else {
                $item['record'] = '';
            }

            $data[] = $this->prepareIndexedData($item);
        }

        return $data;
    }

    protected function can($permission)
    {
        return \Yii::$app->user->can($permission);
    }

    protected function canListen()
    {
        return $this->can('listen_call');
    }

    protected function prepareIndexedData(array $item)
    {
        $indexed = [];
        foreach($this->columns as $idx=>$column) {
            $indexed[$idx] = $item[$column['name']] ?? '';
        }
        return $indexed;
    }
}
