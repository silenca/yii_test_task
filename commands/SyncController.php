<?php

namespace app\commands;

use app\controllers\ContactsController;
use app\models\Contact;

use app\models\ContactsVisits;
use app\models\helpers\MediumLogsApi;
use app\models\Speciality;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\console\Controller;
use yii\web\HttpException;
use Yii;

class SyncController extends Controller
{
    public function actionMedium()
    {
        $client = new Client();
        $dateFrom = date("Y-m-d") . 'T' . date("H:i:s");
        $date = strtotime(date("Y-m-d H:i:s")) + (60 * 2);
        $dateTo = date("Y-m-d", $date) . 'T' . date("H:i:s", $date);
        try {
            $url = 'http://91.225.122.210:8080/api/H:1D13C88C20AA6C6/D:WORK/D:1D13C9303C946F9/C:1D45F18F27C737D/I:PACK';
            $data = 'for $ob in //PACK/OBJECT[oda:left(@update,19) > oda:left("' . $dateFrom . '",19) and oda:left(@update,19) < oda:left("' . $dateTo . '",19) ]       
                    return element OBJECT
                    {
                    
                    attribute ID { $ob/@oid },
                    
                    attribute FIO { $ob/@name },
                    
                    attribute Phone { $ob/@ТелефонМоб },
                    
                    attribute Birth { $ob/@ДатаРождения },
                    
                    attribute IstInfo { $ob/@ИсточникИнфомации },
                    
                    attribute City { $ob/@Город },
                    
                    attribute Email { $ob/@E-mail },
                    
                    attribute update {$ob/@update}
            
                    }';
            $log = MediumLogsApi::setRequestData($url, $data);
            $request = $client->createRequest();
            $request->setUrl($url);
            $request->setData($data);
            $response = $request->send();
            $log->setResponse($response->getContent());
            $contactsSaved = [];
            $cnt = 0;
            if(!empty($response->getData())){
                foreach ($response->getData()['OBJECT'] as $contact) {
                    $contactsSaved[$cnt]['contact_oid'] = ContactsController::actionSaveContacts($contact);
                    $contactsSaved['count'] = $cnt++;
                }
                print_r($contactsSaved);
            }else{
                echo 'No data on Medium';
            }


        } catch (HttpException $ex) {
            echo $ex;
        }
    }

    public function actionSpeciality()
    {
        $cnt = 0;
        $speciality = Yii::$app->medium->speciality();
        if(empty($speciality['error'])){
            foreach ($speciality['data'] as $datum){
                if(Speciality::import($datum)){
                    $cnt++;
                }else{
                    echo "ERROR SAVE " .  print_r($datum) . "\n";
                }
            }

            echo "SAVE|UPDATE {$cnt} items \n";
        }else{
            echo $speciality['error'];
        }
    }

    public function actionVisitStatus()
    {
        $contactsVisits = ContactsVisits::find()
            ->where('visit_date < :date',[':date'=>date('Y-m-d H:i:s')])
            ->andWhere(['status'=>ContactsVisits::STATUS_PENDING])
            ->all();
        if($contactsVisits){
            foreach ($contactsVisits as $contactsVisit){
                if($contactsVisit->department){
                    $visitStatus = Yii::$app->medium->visitStatus($contactsVisit->department->api_url, $contactsVisit->medium_oid);
                    if(!empty($visitStatus['error'])){
                        echo "Error update status visit " . $contactsVisit->medium_oid . $visitStatus['error'] . "\n";
                    }else{
                        if($visitStatus == ContactsVisits::STATUS_TAKE_PLACE_MEDIUM){
                            $contactsVisit->status = ContactsVisits::STATUS_TAKE_PLACE;
                            if($contactsVisit->save() && $contactsVisit->contact){
                                $contactsVisit->contact->status = strval(Contact::CONTACT);
                                if($contactsVisit->contact->save()){
                                    echo "Update status visit " . $contactsVisit->medium_oid . "\n";
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}