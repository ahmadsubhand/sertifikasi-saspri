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
            self::USER => 'Anggota',
            self::COORDINATOR => 'Wali',
            self::ADMIN => 'Admin',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}