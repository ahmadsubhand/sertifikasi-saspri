<?php

namespace common\enums;

class CertificateLevel
{
    public const NATALIA = 'natalia';
    public const WEANIA = 'weania';
    public const PREMATURA = 'prematura';
    public const MATURA = 'matura';

    public static function list()
    {
        return [
            self::NATALIA => 'Natalia',
            self::WEANIA => 'Weania',
            self::PREMATURA => 'Prematura',
            self::MATURA => 'Matura',
        ];
    }
    public static function next()
    {
        return [
            self::NATALIA => 'Weania',
            self::WEANIA => 'Prematura',
            self::PREMATURA => 'Matura',
            self::MATURA => 'Matura',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}
