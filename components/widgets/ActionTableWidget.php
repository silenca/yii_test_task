<?php

namespace app\components\widgets;

use yii\base\Widget;

class ActionTableWidget extends Widget {

    public $actions;

    public function init() {
        parent::init();
    }

    public function run() {
        $data = [];
        $existing = [];
        $i = 0;
        foreach ($this->actions as $action) {
            //$i = $action['id'];
            if (($index = array_search($action['id'], $existing)) !== false) {
                $data[$index][3] .= "<div><a href='" . $action['object_link'] . "' target='_blank'>" . $action['object_link'] . "</a></div>";
            } else {
                $existing[$i] = $action['id'];
                $data[$i][] = date("d-m-Y G:i:s", strtotime($action['system_date']));
                switch ($action['type']) {
                    case "visit":
                        $data[$i][] = "Визит";
                        break;
                    case "contract":
                        $data[$i][] = "Договор";
                        break;
                    case "show":
                        $data[$i][] = "Показ";
                        break;
                    case "scheduled_call":
                        $data[$i][] = "Запланирован звонок";
                        break;
                    case "scheduled_email":
                        $data[$i][] = "Запланирован Email";
                        break;
                    case "payment":
                        $data[$i][] = "Платеж";
                        break;
                }
                $data[$i][] = "<div><a class='contact' href='/contacts#contact=" . $action['contact_id'] . "/'>" . $action['first_name'] . "</a></div>";
                $data[$i][] = "<a href='" . $action['object_link'] . "' target='_blank'>" . $action['object_link'] . "</a>";
                if ($action['schedule_date']) {
                    $data[$i][] = date("d-m-Y G:i:s", strtotime($action['schedule_date']));
                } else {
                    $data[$i][] = '';
                }
                $data[$i][] = $action['comment'];
                $data[$i][] = $action['manager_name'];
                $i++;
            }
        }
        return $data;
    }

}
