<?php
namespace app\commands;

use app\components\MediumApi;
use app\controllers\ContactsController;
use app\models\{
    Contact, ContactsVisits, Speciality, Vars
};
use app\models\helpers\MediumLogsApi;
use yii\httpclient\{
    Client, Exception as ClientException, Request, Response, XmlFormatter, XmlParser
};
use yii\console\Controller;
use Yii;

class SyncController extends Controller
{
    const VAR_LAST_SYNC_TS = 'lastSyncTs';

    public function actionMedium()
    {
        $lastSync = Vars::get(self::VAR_LAST_SYNC_TS, 0);

        if(!$lastSync) {
            $dateFrom = (new \DateTime())->modify('- 2 minutes');
        } else {
            $dateFrom = (new \DateTime())->setTimestamp($lastSync);
        }
        $dateTo = (new \DateTime());
        $lastSync = $dateTo->format('U') - 1;

        try {
            $contacts = Yii::$app->medium->fetchContactsToSync($dateFrom, $dateTo);
            if(count($contacts)) {
                foreach($contacts as $contact) {
                    ContactsController::updateContact($contact);
                }
            } else {
                echo 'No data on Medium';
            }

            Vars::set(self::VAR_LAST_SYNC_TS, $lastSync);
        } catch(ClientException $ex) {
            echo implode(PHP_EOL, [
                'Error quering medium',
                $ex->getMessage(),
                $ex->getTraceAsString(),
            ]).PHP_EOL.PHP_EOL;
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
        $cMedium = Yii::$app->medium;
        /**@var $cMedium MediumApi*/
        $contactsVisits = ContactsVisits::find()
            ->where('visit_date < :date',[':date'=>date('Y-m-d H:i:s')])
            ->andWhere(['status'=>ContactsVisits::STATUS_PENDING])
            ->all();
        if($contactsVisits){
            foreach ($contactsVisits as $contactsVisit){
                if($contactsVisit->department){
                    $visitStatus = $cMedium->visitStatus($contactsVisit->department->api_url, $contactsVisit->medium_oid);
                    if(!empty($visitStatus['error'])){
                        echo "Error update status visit " . $contactsVisit->medium_oid . $visitStatus['error'] . "\n";
                    }else{
                        if($visitStatus['data'] == ContactsVisits::STATUS_TAKE_PLACE_MEDIUM){
                            $contactsVisit->status = ContactsVisits::STATUS_TAKE_PLACE;
                            if($contactsVisit->save() && $contactsVisit->contact){
                                $contactsVisit->contact->status = strval(Contact::CONTACT);
                                if (!$contactsVisit->contact->medium_oid) {
                                    $contactsVisit->contact->medium_oid = $cMedium->putContact($contactsVisit->contact);
                                }
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