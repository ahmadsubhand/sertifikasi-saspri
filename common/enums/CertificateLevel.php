<?php

namespace common\enums;

class CertificateLevel
{
    public const NATALIA = 'natalia';
    public const WEANIA = 'weania';
    public const PREMATURA = 'prematura';
    public const MATURA = 'matura';

    public static function prev()
    {
        return [
            self::NATALIA => self::list()[self::NATALIA],
            self::WEANIA => self::list()[self::NATALIA],
            self::PREMATURA => self::list()[self::WEANIA],
            self::MATURA => self::list()[self::PREMATURA],
        ];
    }

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
            self::NATALIA => self::list()[self::WEANIA],
            self::WEANIA => self::list()[self::PREMATURA],
            self::PREMATURA => self::list()[self::MATURA],
            self::MATURA => self::list()[self::MATURA],
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}
