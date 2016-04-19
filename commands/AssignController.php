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
        
        $operatorRole = $authManager->getRole('operator');
        $authManager->assign($operatorRole, 3);
        $authManager->assign($operatorRole, 4);
        $authManager->assign($operatorRole, 5);
    }
}