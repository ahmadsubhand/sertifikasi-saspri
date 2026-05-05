<?php

namespace common\enums;

class CertificateLevel
{
    const NATALIA = 'natalia';
    const WEANIA = 'weania';
    const PREMATURA = 'prematura';
    const MATURA = 'matura';

    public static function list()
    {
        return [
            self::NATALIA => 'Natalia',
            self::WEANIA => 'Weania',
            self::PREMATURA => 'Prematura',
            self::MATURA => 'Matura',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}