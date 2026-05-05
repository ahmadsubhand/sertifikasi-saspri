<?php

namespace common\enums;

class UserRole
{
    const USER = 'user';
    const COORDINATOR = 'coordinator';
    const ADMIN = 'admin';

    public static function list()
    {
        return [
            self::USER => 'User',
            self::COORDINATOR => 'Coordinator',
            self::ADMIN => 'Admin',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}