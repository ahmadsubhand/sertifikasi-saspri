<?php

// namespace console\migrations;

use common\enums\UserRole;
use yii\db\Migration;

class m260506_001933_init_rbac extends Migration
{
    public function up()
    {
        $auth = Yii::$app->authManager;

        foreach (UserRole::values() as $userRole) {
            $role = $auth->createRole($userRole);
            $auth->add($role);
        }
    }

    public function down()
    {
        $auth = Yii::$app->authManager;

        $auth->removeAll();
    }
}
