<?php

namespace app\rbac;

use Yii;
use yii\rbac\Rule;

class ContactAuthorRule extends Rule {

    public $name = 'isContactAuthor';

    /**
     * @param string|integer $user the user ID.
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return boolean a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        return isset($params['contact']) ? $params['contact']->manager_id == $user : false;
    }

}
