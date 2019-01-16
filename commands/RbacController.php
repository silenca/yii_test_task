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
        $operator = $authManager->createRole('operator');
        $admin = $authManager->createRole('admin');
        $supervisor = $authManager->createRole('supervisor');

        $contacts = $authManager->createPermission('contacts');
        $editStatusContact = $authManager->createPermission('editStatusContact');
        $actions = $authManager->createPermission('action');
        $reports = $authManager->createPermission('reports');
        $calls = $authManager->createPermission('calls');
        $notifications = $authManager->createPermission('notifications');
        $listen_call = $authManager->createPermission('listen_call');
        $delete_contact = $authManager->createPermission('delete_contact');
        $edit_comment = $authManager->createPermission('edit_comment');
//        $updateOwnContact = $authManager->createPermission('updateOwnContact');
        $updateContact = $authManager->createPermission('updateContact');
        $tags = $authManager->createPermission('tags');
        $import = $authManager->createPermission('import');
        $users = $authManager->createPermission('users');
        $updateUser = $authManager->createPermission('updateUser');
        $delete_user = $authManager->createPermission('delete_user');
        $edit_tag =  $authManager->createPermission('edit_tag');

        $use_archived_tags = $authManager->createPermission('use_archived_tags');

        $authManager->add($contacts);
        $authManager->add($editStatusContact);
        $authManager->add($actions);
        $authManager->add($reports);
        $authManager->add($calls);
        $authManager->add($notifications);
        $authManager->add($listen_call);
        $authManager->add($delete_contact);
        $authManager->add($edit_comment);
        $authManager->add($updateContact);
        $authManager->add($tags);
        $authManager->add($import);
        $authManager->add($users);
        $authManager->add($updateUser);
        $authManager->add($delete_user);
        $authManager->add($edit_tag);
        $authManager->add($use_archived_tags);

        $user_role_rule = new UserRoleRule();
        $authManager->add($user_role_rule);
        $contactAuthorRule = new ContactAuthorRule();
        $authManager->add($contactAuthorRule);

        $manager->ruleName = $user_role_rule->name;
        $operator->ruleName = $user_role_rule->name;
        $admin->ruleName = $user_role_rule->name;
        $supervisor->ruleName = $user_role_rule->name;

        $authManager->add($manager);
        $authManager->add($operator);
        $authManager->add($admin);
        $authManager->add($supervisor);

        $authManager->addChild($manager, $contacts);
        $authManager->addChild($manager, $actions);
        $authManager->addChild($manager, $calls);
        $authManager->addChild($manager, $notifications);
        $authManager->addChild($manager, $tags);
        $authManager->addChild($manager, $updateContact);
        $authManager->addChild($manager, $listen_call);
        $authManager->addChild($manager, $edit_tag);

//        $authManager->addChild($operator, $contacts);
        $authManager->addChild($operator, $actions);
        $authManager->addChild($operator, $calls);
        $authManager->addChild($operator, $notifications);
//        $authManager->addChild($operator, $listen_call);
//        $authManager->addChild($operator, $delete_contact);
//        $authManager->addChild($operator, $edit_comment);
        $authManager->addChild($operator, $updateContact);
        $authManager->addChild($operator, $tags);
        
        $authManager->addChild($admin, $contacts);
        $authManager->addChild($admin, $editStatusContact);
        $authManager->addChild($admin, $actions);
        $authManager->addChild($admin, $notifications);
        $authManager->addChild($admin, $reports);
        $authManager->addChild($admin, $calls);
        $authManager->addChild($admin, $listen_call);
        $authManager->addChild($admin, $delete_contact);
        $authManager->addChild($admin, $updateContact);
        $authManager->addChild($admin, $tags);
        $authManager->addChild($admin, $import);
        $authManager->addChild($admin, $users);
        $authManager->addChild($admin, $updateUser);
        $authManager->addChild($admin, $delete_user);
        $authManager->addChild($admin, $edit_tag);
        $authManager->addChild($admin, $use_archived_tags);

        $authManager->addChild($supervisor, $contacts);
        $authManager->addChild($supervisor, $editStatusContact);
        $authManager->addChild($supervisor, $updateContact);
        $authManager->addChild($supervisor, $actions);
        $authManager->addChild($supervisor, $calls);
        $authManager->addChild($supervisor, $listen_call);
        $authManager->addChild($supervisor, $reports);
        $authManager->addChild($supervisor, $tags);
        $authManager->addChild($supervisor, $edit_tag);
        $authManager->addChild($supervisor, $import);

    }

}
