<?php

namespace app\controllers;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\components\CSVReader;
use app\models\Contact;
use app\models\forms\ContactForm;
use Yii;

class ImportController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'csv', 'get-status'],
                        'allow' => true,
                        'roles' => ['admin'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionCsv()
    {
        $csv_reader = new CSVReader([
            'filename' => $_FILES['csv_file']['tmp_name'],
            'fgetcsvOptions' => [
                'delimiter' => ';'
            ]
        ]);
        $new_contacts = $csv_reader->readFile();
        if (count($new_contacts[0]) !== 13) {
            return $this->json(null, 415, [
                'Не корректное кол-во колонок'
            ]);
        }
        unset($new_contacts[0]);
        $error = false;
        $imported = 0;

        for ($i = 1; $i <= count($new_contacts); $i++) {
            $contact_data = $new_contacts[$i];
            $contact_form_cols = ContactForm::getAllCols();
            $attributes = [];
            $col_cnt = 0;
            foreach ($contact_form_cols as $col) {
                $attributes[$col] = iconv(mb_detect_encoding($contact_data[$col_cnt], mb_detect_order(), true), "UTF-8", $contact_data[$col_cnt]);
                $col_cnt++;
            }
            $contact_form = new ContactForm();
            $contact_form->attributes = $attributes;
            $report_file_name = time() . '.txt';
            if ($contact_form->validate()) {
                try {
                    $contact = new Contact();
                    $contact->buildData($attributes);
                    if ($contact->isPhoneNumberExists() || $contact->isEmailExists()) {
                        $this->writeReport($report_file_name, $attributes, $contact->getErrors());
                        $error = true;
                    } elseif ($contact->edit()) {
                        $imported++;
                    } else {
                        $this->writeReport($report_file_name, $attributes, $contact->getErrors());
                        $error = true;
                    }
                } catch (\Exception $ex) {
                    $this->json(false, 500);
                }
            } else {
                $this->writeReport($report_file_name, $attributes, $contact_form->getErrors());
                $error = true;
            }
        }
        if ($error) {
            $this->json([
                'report_file' => Yii::getAlias('@web') . '/reports/' . $report_file_name,
                'imported' => $imported,
                'count' => count($new_contacts) - 1
            ], 415);
        } else {
            $this->json([
                'imported' => $imported,
                'count' => count($new_contacts) - 1
            ], 200);
        }

    }

    private function writeReport($file_name, $attributes, $errors)
    {
        $report_file = fopen(Yii::getAlias('@web_folder') . '/reports/' . $file_name, "a+");
        fwrite($report_file, iconv("UTF-8", "windows-1251//TRANSLIT", implode(array_filter($attributes), ',')) . "\n");
        foreach ($errors as $error) {
            fwrite($report_file, iconv("UTF-8", "windows-1251//TRANSLIT", $error[0]) . "\n");
        }
        fwrite($report_file, "-------------------------------------------------" . "\n");
        fclose($report_file);

    }
}