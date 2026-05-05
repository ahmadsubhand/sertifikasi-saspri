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
            self::IDENTICAL => 'Identical',
            self::AGREED => 'Agreed',
            self::DIFFERENT => 'Different',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}