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
        $authManager->assign($adminRole, 9);
        
        $managerRole = $authManager->getRole('manager');
        $authManager->assign($managerRole, 3);
        $authManager->assign($managerRole, 6);
        $authManager->assign($managerRole, 7);
        $authManager->assign($managerRole, 8);
        $authManager->assign($managerRole, 112);
        
        $supervisorRole = $authManager->getRole('supervisor');
        $authManager->assign($supervisorRole, 4);
        $authManager->assign($supervisorRole, 10);
        
        $fin_dirRole = $authManager->getRole('fin_dir');
        $authManager->assign($fin_dirRole, 5);
        $authManager->assign($fin_dirRole, 113);
    }
}