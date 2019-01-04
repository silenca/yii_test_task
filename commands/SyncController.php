<?php

namespace app\commands;

use app\controllers\ContactsController;
use app\models\Contact;

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
            $request = $client->createRequest();
            $request->setUrl('http://91.225.122.210:8080/api/H:1D13C88C20AA6C6/D:WORK/D:1D13C9303C946F9/C:1D45F18F27C737D/I:PACK');
            $request->setData('for $ob in //PACK/OBJECT[oda:left(@update,19) > oda:left("' . $dateFrom . '",19) and oda:left(@update,19) < oda:left("' . $dateTo . '",19) ]       
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
            
                    }');
            $response = $request->send();
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

            echo "SAVE|UPDATE {$cnt} items";
        }else{
            echo $speciality['error'];
        }
    }
}