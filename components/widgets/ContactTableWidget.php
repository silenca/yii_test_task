<?php

namespace app\components\widgets;

use app\models\Contact;
use yii\base\Widget;
use Yii;
use app\components\Filter;

class ContactTableWidget extends Widget {

    /**
     * @var $contacts Contact[]
     */
    public $contacts;
    public $user_id;
    public $user_role;

    public function init() {
        parent::init();
    }

    public function run() {
        $data = [];
        foreach ($this->contacts as $i => $contact) {
            $data[$i][] = $contact->id;
            $data[$i][] = $contact->int_id;
            $data[$i][] = $contact->surname;
            $data[$i][] = $contact->name;
            $data[$i][] = $contact->middle_name;
            $data[$i][] = '<div class="dropdown contact_open_disable">
                                <button class="btn btn-sm btn-default dropdown-toggle" href="javascript:void(0)" aria-expanded="true">Связать с...</button>
                            </div>';
            $data[$i][] =  Filter::dataImplode($contact->getPhoneValues(), ', ', '<a class="contact-phone contact_open_disable" href="javascript:void(0)">{value}</a>', true);
            $data[$i][] = Filter::dataImplode($contact->getEmailValues());
            $tag_names = [];
            foreach ($contact->tags as $tag) {
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

            $data[$i][] = $contact->country;
            $data[$i][] = $contact->city;
            $data[$i][] = $contact->birthday;
            $data[$i][] = $contact->medium_oid;
//            $data[$i][] = $contact->link_with;
            $data[$i][] = (isset($contact->attraction_channel_id)?$contact->attractionChannel->name:'');
            if(isset($contact->is_broadcast)){
                $data[$i][] = ($contact->is_broadcast !== null)? Contact::$broadcast[$contact->is_broadcast]:'';
            }else{
                $data[$i][]='';
            }
            $data[$i][] = (isset($contact->notification_service_id) ? $contact->notificationService->name : '');
            $data[$i][] = $contact->language->slug;
            $data[$i][] = (isset($contact->status)? Contact::$statuses[$contact->status] : '');
            $data[$i][] = (isset($contact->manager_id)? $contact->manager->firstname.' '.$contact->manager->lastname : '');

            if (Yii::$app->user->can('delete_contact')) {
                $data[$i][] = '<div class="col-md-offset-3 remove"><i class="fa fa-remove"></i></div>';
            } else {
                $data[$i][] = '';
            }

        }
        return $data;
    }

}
