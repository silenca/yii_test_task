<?php

namespace app\components\widgets;

use yii\base\Widget;
use app\models\Contact;
use app\components\Filter;

class ActionTableWidget extends Widget
{

    public $actions;
    public $user_role;
    public $user_id;

//    public function init()
//    {
//        parent::init();
//    }

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
//            $existing[$i] = $action['id'];

            $action_type = $action->actionType;
            $action_comment = $action->comment;
            $action_user = $action->user;
            $action_contact = $action->contact;
            $cont_comments = $action_contact->comments;
            if (count($cont_comments) >= 1) {
                $action_contactComment = $cont_comments[count($cont_comments) - 1];
            } else {
                $action_contactComment = $cont_comments;
            }

            $data[$i][] = $action['id'];
            $data[$i][] = date("d-m-Y", strtotime($action['system_date']));
            switch ($action_type->name) {
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

            switch ($this->user_role) {
                case 'operator':
                    $data[$i][] = "<div><a class='open_contact' data-id='" . $action['contact_id'] . "'>" . $action_contact->int_id . "</a></div>";
                    break;
                default:
                    $data[$i][] = Filter::dataImplode([$action_contact['surname'], $action_contact['name'], $action_contact['middle_name']], ' ', "<div><a class='open_contact' data-id='" . $action['contact_id'] . "'>{value}</a></div>");
            }

            $tag_names = [];
            foreach ($action->contact->tags as $tag) {
                if ($this->user_role == 'manager' || $this->user_role == 'operator') {
                    $show_tag = false;
                    foreach ($tag->users as $user) {
                        if ($user->id == $this->user_id) {
                            $show_tag = true;
                        }
                    }
                    if ($show_tag) {
                        $tag_names[] = $tag->name;
                    }
                } else {
                    $tag_names[] = $tag->name;
                }
            }
            $data[$i][] = Filter::dataImplode($tag_names, ', ', '<a class="contact_open_disable contact-tags" href="javascript:void(0)">{value}</a>', true);


            if (!is_null($action['schedule_date'])) {
                $data[$i][] = date("d-m-Y G:i:s", strtotime($action['schedule_date']));
            } else {
                $data[$i][] = '';
            }
            if (!is_null($action_comment)) {
                $data[$i][] = $action_comment->comment;
            } else if ($action_contactComment) {
                $data[$i][] = $action_contactComment->comment;
            } else {
                $data[$i][] = '';
            }

            $data[$i][] = $action_user->firstname;
            $data[$i][] = $action['viewed'];
        }

        return $data;
    }

}
