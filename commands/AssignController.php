<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

class AssignController extends Controller {
    
    public function actionInit() {
        $authManager = Yii::$app->authManager;
        $authManager->removeAllAssignments();
        
        $adminRole = $authManager->getRole('admin');
        $authManager->assign($adminRole, 1);
        
        $managerRole = $authManager->getRole('manager');
        $authManager->assign($managerRole, 2);
        
        $supervisorRole = $authManager->getRole('operator');
        $authManager->assign($supervisorRole, 3);
        $authManager->assign($supervisorRole, 4);
        $authManager->assign($supervisorRole, 5);
    }
}