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
    const MEDIUM_FETCH_URL = 'http://91.225.122.210:8080/api/H:1D13C88C20AA6C6/D:WORK/D:1D13C9303C946F9/C:1D45F18F27C737D/I:PACK';
    const MEDIUM_FETCH_DATA_TPL = <<<'CODE'
let $d1 := '{DATE_FROM}'
let $d2 := '{DATE_TO}'
return
for $ob in //PACK/OBJECT[@update >= $d1 and @update < $d2]
return element OBJECT
{
    attribute oid { $ob/@oid },
    attribute update {  $ob/@update },
    attribute FIO { $ob/@name },
    attribute Email { $ob/@E-mail },
    attribute Phone { $ob/@ТелефонМоб },
    attribute Birth { $ob/@ДатаРождения },
    attribute City { $ob/@Город }
}
CODE
    ;

    const MEDIUM_DATE_FORMAT = 'Y-m-d\TH:i:s';

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

        $client = new Client();
        try {
            $url = self::MEDIUM_FETCH_URL;
            $data = str_replace([
                '{DATE_FROM}',
                '{DATE_TO}',
            ], [
                $this->dateString($dateFrom),
                $this->dateString($dateTo),
            ], self::MEDIUM_FETCH_DATA_TPL);

            $log = MediumLogsApi::setRequestData($url, $data);

            $response = $client
                            ->createRequest()
                                ->addHeaders(['Content-type' => 'application/x-www-form-urlencoded'])
                                ->setUrl($url)
                                ->setContent($data)
                                ->send();
            $response->setFormat(Client::FORMAT_XML);
            $log->setResponse($response->getContent());

            $contacts = MediumApi::parseContactsXml($response->getContent());
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
                        if($visitStatus['data'] == ContactsVisits::STATUS_TAKE_PLACE_MEDIUM){
                            $contactsVisit->status = ContactsVisits::STATUS_TAKE_PLACE;
                            if($contactsVisit->save() && $contactsVisit->contact){
                                $contactsVisit->contact->status = strval(Contact::CONTACT);
                                if (!$contactsVisit->contact->medium_oid) {
                                    $contactsVisit->contact->medium_oid = Contact::postMediumObject($contactsVisit->contact);
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

    protected function dateString(\DateTime $date)
    {
        return $date->format(self::MEDIUM_DATE_FORMAT);
    }
}