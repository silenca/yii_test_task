<?php
namespace app\controllers\api;

use app\controllers\BaseController;
use app\models\Contact;
use app\models\ContactHistory;
use app\models\forms\ImportContactForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ContactController extends BaseController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return ['access' => ['class' => AccessControl::className(), 'rules' => [['actions' => ['add',], 'allow' => true, 'roles' => ['?'],]]], 'verbs' => ['class' => VerbFilter::className(), 'actions' => ['save' => ['post']],],];
    }

    public function actionAdd()
    {
        $headers = Yii::$app->request->headers;
        $token = $headers->get('X-Amz-Meta-Crm-Api-Token');
        if ($token != '6e5b4d74875ea09f3f888601c7825211') {
            $this->json(false, 415, 'Invalid token');
        }
        try {
            $json = file_get_contents('php://input');
            $post = json_decode($json, true);
            //TODO temp
            $log_data = date("j-m-Y G:i:s", time()) . "   " . $json . "\r\n\r\n";
            $log_data .= "=============================================\r\n\r\n";
            file_put_contents(Yii::getAlias('@runtime_log_folder') . '/api_import.log', $log_data, FILE_APPEND);

            $post_contacts = isset($post['contacts']) ? $post['contacts'] : NULL;
            $response_data = [];
            foreach ($post_contacts as $i => $post_contact) {
                $import_contact_form = new ImportContactForm();
                $import_contact_form->isApi = true;
                $import_contact_form->phones = isset($post_contact['phone1']) ? $post_contact['phone1'] : NULL;
                if (isset($post_contact['phone2']) && strlen($post_contact['phone2'])) {
                    $import_contact_form->phones .= ',' . $post_contact['phone2'];
                }
                if (isset($post_contact['phone3']) && strlen($post_contact['phone3'])) {
                    $import_contact_form->phones .= ',' . $post_contact['phone3'];
                }
                if (isset($post_contact['phone4']) && strlen($post_contact['phone4'])) {
                    $import_contact_form->phones .= ',' . $post_contact['phone4'];
                }
                $import_contact_form->emails = isset($post_contact['email1']) ? $post_contact['email1'] : NULL;
                if (isset($post_contact['email2']) && strlen($post_contact['email2'])) {
                    $import_contact_form->emails .= ',' . $post_contact['email2'];
                }
                $import_contact_form->name = isset($post_contact['first_name']) ? $post_contact['first_name'] : NULL;
                $import_contact_form->surname = isset($post_contact['last_name']) ? $post_contact['last_name'] : NULL;
                $import_contact_form->middle_name = isset($post_contact['middle_name']) ? $post_contact['middle_name'] : NULL;
                $import_contact_form->country = isset($post_contact['country']) ? $post_contact['country'] : NULL;
                $import_contact_form->region = isset($post_contact['region']) ? $post_contact['region'] : NULL;
                $import_contact_form->area = isset($post_contact['area']) ? $post_contact['area'] : NULL;
                $import_contact_form->city = isset($post_contact['city']) ? $post_contact['city'] : NULL;
                $import_contact_form->street = isset($post_contact['street']) ? $post_contact['street'] : NULL;
                $import_contact_form->house = isset($post_contact['house']) ? $post_contact['house'] : NULL;
                $import_contact_form->flat = isset($post_contact['flat']) ? $post_contact['flat'] : NULL;
                $import_contact_form->tags_str = isset($post_contact['tag']) ? $post_contact['tag'] : NULL;
                $validateResult = $import_contact_form->validate();
                if ($validateResult) {
                    $contact = new Contact();
                    $contact->attributes = $import_contact_form->attributes;
                    if ($import_contact_form->checkPhone($import_contact_form->second_phone, 'second_phone')) {
                        $contact->second_phone = $import_contact_form->second_phone;
                    } else {
                        $contact->second_phone = NULL;
                    }
                    if ($import_contact_form->checkPhone($import_contact_form->third_phone, 'third_phone')) {
                        $contact->third_phone = $import_contact_form->third_phone;
                    } else {
                        $contact->third_phone = NULL;
                    }
                    if ($import_contact_form->checkPhone($import_contact_form->fourth_phone, 'fourth_phone')) {
                        $contact->fourth_phone = $import_contact_form->fourth_phone;
                    } else {
                        $contact->fourth_phone = NULL;
                    }


                    if ($contact->edit(['tags' => $import_contact_form->tags])) {
                        if (isset($post_contact['comment'])) {
                            $comment = iconv(mb_detect_encoding($post_contact['comment'], mb_detect_order(), true), "UTF-8", $post_contact['comment']);
                            $contact_history = new ContactHistory();
                            $contact_history->add($contact->id, 'Комментарий при импорте c API: ' . $comment, 'imported_comment');
                            $contact_history->save();
                        }
                        $response_data[$i]['phone'] = $contact->first_phone;
                        $response_data[$i]['status'] = 1;
                    } else {
                        $response_data[$i]['phone'] = $post_contact['phone1'];
                        $response_data[$i] ['status'] = 0;
                    }
                } else {
                    $errors = $import_contact_form->getErrors();
                    if (isset($errors['phones'])) {
                        unset($errors['phones']);
                    }
                    if (count($errors) == 0 && $import_contact_form->conflict_id !== NULL) {
                        $cur_contact = Contact::findOne($import_contact_form->conflict_id);
                        if ($cur_contact !== NULL) {
                            $cur_contact->name = $import_contact_form->name;
                            $cur_contact->surname = $import_contact_form->surname;
                            $cur_contact->middle_name = $import_contact_form->middle_name;
                            $cur_contact->country = $import_contact_form->country;
                            $cur_contact->region = $import_contact_form->region;
                            $cur_contact->area = $import_contact_form->area;
                            $cur_contact->city = $import_contact_form->city;
                            $cur_contact->street = $import_contact_form->street;
                            $cur_contact->house = $import_contact_form->house;
                            $cur_contact->flat = $import_contact_form->flat;
                            $cur_contact->first_email = $import_contact_form->first_email;
                            $cur_contact->second_email = $import_contact_form->second_email;
                            //$cur_contact->attributes = $import_contact_form->attributes;
                            if ($import_contact_form->checkPhone($import_contact_form->second_phone, 'second_phone')) {
                                $cur_contact->second_phone = $import_contact_form->second_phone;
                            }
                            if ($import_contact_form->checkPhone($import_contact_form->third_phone, 'third_phone')) {
                                $cur_contact->third_phone = $import_contact_form->third_phone;
                            }
                            if ($import_contact_form->checkPhone($import_contact_form->fourth_phone, 'fourth_phone')) {
                                $cur_contact->fourth_phone = $import_contact_form->fourth_phone;
                            }

                            $cur_contact->edit(['tags' => $import_contact_form->tags]);
                            if (isset($post_contact['comment'])) {
                                $comment = iconv(mb_detect_encoding($post_contact['comment'], mb_detect_order(), true), "UTF-8", $post_contact['comment']);
                                $contact_history = new ContactHistory();
                                $contact_history->add($cur_contact->id, 'Комментарий при импорте c API: ' . $comment, 'imported_comment');
                                $contact_history->save();

                            }
                            $response_data[$i]['phone'] = $cur_contact->first_phone;
                            $response_data[$i] ['status'] = 1;
                        }
                    } else {
                        $response_data[$i]['phone'] = $post_contact['phone1'];
                        $response_data[$i] ['status'] = 0;
                    }
                }
            }
            $this->json($response_data, 200);
        } catch (\Exception $ex) {
            $this->json(false, 500);
        }
    }
}