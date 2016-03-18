<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\rbac\UserRoleRule;
use app\rbac\ContactAuthorRule;

class RbacController extends Controller {

    public function actionInit() {
        $authManager = Yii::$app->authManager;
        $authManager->init();
        $authManager->removeAll();

        $manager = $authManager->createRole('manager');
        $supervisor = $authManager->createRole('supervisor');
        $fin_director = $authManager->createRole('fin_dir');
        $admin = $authManager->createRole('admin');

        $contacts = $authManager->createPermission('contacts');
        $objects = $authManager->createPermission('objects');
        $actions = $authManager->createPermission('actions');
        $reports = $authManager->createPermission('reports');
        $receivables = $authManager->createPermission('receivables');
        $calls = $authManager->createPermission('calls');
        $notifications = $authManager->createPermission('notifications');
        $contracts = $authManager->createPermission('contracts');
        $listen_call = $authManager->createPermission('listen_call');
        $delete_contact = $authManager->createPermission('delete_contact');
        $show_payments = $authManager->createPermission('show_payments');
        $edit_comment = $authManager->createPermission('edit_comment');
        $updateOwnContact = $authManager->createPermission('updateOwnContact');
        $updateContact = $authManager->createPermission('updateContact');
        $tags = $authManager->createPermission('tags');

        $authManager->add($contacts);
        $authManager->add($objects);
        $authManager->add($actions);
        $authManager->add($reports);
        $authManager->add($receivables);
        $authManager->add($calls);
        $authManager->add($notifications);
        $authManager->add($contracts);
        $authManager->add($listen_call);
        $authManager->add($delete_contact);
        $authManager->add($show_payments);
        $authManager->add($edit_comment);
        $authManager->add($updateContact);
        $authManager->add($updateOwnContact);
        $authManager->add($tags);

        $user_role_rule = new UserRoleRule();
        $authManager->add($user_role_rule);
        $contactAuthorRule = new ContactAuthorRule();
        $authManager->add($contactAuthorRule);

        $manager->ruleName = $user_role_rule->name;
        $supervisor->ruleName = $user_role_rule->name;
        $fin_director->ruleName = $user_role_rule->name;
        $admin->ruleName = $user_role_rule->name;
        $updateOwnContact->ruleName = $contactAuthorRule->name;

        $authManager->add($manager);
        $authManager->add($supervisor);
        $authManager->add($fin_director);
        $authManager->add($admin);

        $authManager->addChild($updateOwnContact, $updateContact);

        $authManager->addChild($manager, $contacts);
        $authManager->addChild($manager, $objects);
        $authManager->addChild($manager, $actions);
        $authManager->addChild($manager, $calls);
        $authManager->addChild($manager, $notifications);
        $authManager->addChild($manager, $updateOwnContact);
        
        $authManager->addChild($supervisor, $contacts);
        $authManager->addChild($supervisor, $objects);
        $authManager->addChild($supervisor, $actions);
        $authManager->addChild($supervisor, $reports);
        $authManager->addChild($supervisor, $calls);
        $authManager->addChild($supervisor, $listen_call);
        $authManager->addChild($supervisor, $delete_contact);       
        $authManager->addChild($supervisor, $show_payments);
        $authManager->addChild($supervisor, $edit_comment);
        $authManager->addChild($supervisor, $updateContact);
        
        
        $authManager->addChild($fin_director, $contracts);
        $authManager->addChild($fin_director, $contacts);
        $authManager->addChild($fin_director, $objects);
        $authManager->addChild($fin_director, $actions);
        $authManager->addChild($fin_director, $reports);
        $authManager->addChild($fin_director, $receivables);
        $authManager->addChild($fin_director, $calls);
        $authManager->addChild($fin_director, $listen_call);
        $authManager->addChild($fin_director, $show_payments);
        $authManager->addChild($fin_director, $updateOwnContact);
        
        $authManager->addChild($admin, $contacts);
        $authManager->addChild($admin, $objects);
        $authManager->addChild($admin, $actions);
        $authManager->addChild($admin, $reports);
        $authManager->addChild($admin, $calls);
        $authManager->addChild($admin, $listen_call);
        $authManager->addChild($admin, $delete_contact);
        $authManager->addChild($admin, $updateOwnContact);
        $authManager->addChild($admin, $tags);
        
    }

}
