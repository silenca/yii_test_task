<?php

namespace app\components\widgets;

use yii\base\Widget;
use app\models\Contact;
use app\components\Filter;

class ActionTableWidget extends Widget
{

    public $actions;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $data = [];
        //$existing = [];
        //$i = 0;
        foreach ($this->actions as $i => $action) {
            //$i = $action['id'];
            //if (($index = array_search($action['id'], $existing)) !== false) {
            //    $data[$index][3] .= "<div><a href='" . $action['object_link'] . "' target='_blank'>" . $action['object_link'] . "</a></div>";
            //} else {
            $existing[$i] = $action['id'];
            $data[$i][] = $action['id'];
            $data[$i][] = date("d-m-Y", strtotime($action['system_date']));
            switch ($action['type']) {
                case "scheduled_call":
                    if (is_null($action['schedule_date'])) {
                        $data[$i][] = "Звонок клиенту";
                    } else {
                        $data[$i][] = "Запланирован звонок";
                    }
                    break;
                case "scheduled_email":
                    $data[$i][] = "Запланирован Email";
                    break;
                case "ring_round":
                    $data[$i][] = "Прозвон контакта";
                    break;
            }

            $data[$i][] = Filter::dataImplode([$action['surname'], $action['name'], $action['middle_name']], ' ', "<div><a class='open_contact' data-id='" . $action['contact_id'] . "'>{value}</a></div>");
            if ($action['schedule_date']) {
                $data[$i][] = date("d-m-Y G:i:s", strtotime($action['schedule_date']));
            } else {
                $data[$i][] = '';
            }
            $data[$i][] = $action['contact_comment'];
            $data[$i][] = $action['manager_name'];
            $data[$i][] = $action['viewed'];
            // $i++;
            //}
        }
        return $data;
    }

}
