<?php

namespace common\enums;

class CertificationPurpose
{
    const LEVEL_UP = 'level_up';
    const RENEWAL = 'renewal';

    public static function list()
    {
        return [
            self::LEVEL_UP => 'Naik Tingkat',
            self::RENEWAL => 'Pengulangan',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}