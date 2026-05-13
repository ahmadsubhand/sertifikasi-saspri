<?php

namespace common\enums;

class IndicatorStatus
{
    const IDENTICAL = 'identical';
    const AGREED = 'agreed';
    const DIFFERENT = 'different';

    public static function list()
    {
        return [
            self::IDENTICAL => 'Sama',
            self::AGREED => 'Sepakat',
            self::DIFFERENT => 'Berbeda',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}