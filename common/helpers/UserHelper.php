<?php

namespace common\helpers;

use common\enums\UserRole;
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

    public static $basicSelect = [
        'id',
        'username',
        'phone_number',
    ];
}