<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Contact;
use app\models\Call;
use yii\console\Controller;
use app\models\FailExportCall;
use app\models\FailExportContacts;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ForwardController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionIndex()
    {
        $fail_contacts = FailExportContacts::find()->limit(10)->orderBy(['id' => SORT_DESC])->all();
        foreach ($fail_contacts as $fail_contact) {
            $contact = Contact::find()->where(['id' => $fail_contact->contact_id])->one();
            if ($contact->sendToCRM()) {
                $fail_contact->delete();
            }
        }
        $fail_calls = FailExportCall::find()->limit(10)->orderBy(['id' => SORT_DESC])->all();
        foreach ($fail_calls as $fail_call) {
            $call = Call::find()->where(['id' => $fail_call->call_id])->with('manager')->one();
            $manager = $call->manager[0];
            if ($call->sendToCRM($manager)) {
                $fail_call->delete();
            }
        }
    }
}
