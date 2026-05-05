<?php

namespace common\enums;

class UserRole
{
    const USER = 'user';
    const WALI = 'wali';
    const ADMIN = 'admin';

    public static function list()
    {
        return [
            self::USER => 'User',
            self::WALI => 'Wali',
            self::ADMIN => 'Admin',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}