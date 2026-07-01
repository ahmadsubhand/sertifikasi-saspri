<?php

namespace common\helpers;

use common\enums\UserRole;
use common\models\User;
use Yii;

class UserHelper
{
    public static function isUserAnAdmin(int $user_id): bool
    {
        if (Yii::$app->authManager->getAssignment(UserRole::ADMIN, $user_id)) {
            return true;
        } else {
            return false;
        }
    }

    public static function basicSelect(): array
    {
        $table = User::tableName();

        return [
            "$table.id",
            "$table.username",
            "$table.full_name",
            "$table.phone_number",
        ];
    }
}