<?php

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;
use Yii;

class UploadDoc extends Model {

    /**
     * @var UploadedFile
     */
    public $docFile;

    public function rules() {
        return [
            [['docFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'doc, docx, pdf'],
        ];
    }

    public function upload() {
        if ($this->validate()) {
            $file_name = md5(uniqid(rand(), true)) . '.' . $this->docFile->extension;
            $this->docFile->saveAs(Yii::getAlias('@app') . '/agreements/' . $file_name);
            return $file_name;
        } else {
            return false;
        }
    }
    
    public static function remove($doc_name) {
        unlink(Yii::getAlias('@app') . '/agreements/' . $doc_name);
    }

}
