<?php 

namespace common\enums;

class CertificateGrade
{
    const A = 'a';
    const AB = 'ab';
    const B = 'b';
    const C = 'c';
    const D = 'd';

    public static function list()
    {
        return [
            self::A => 'A',
            self::AB => 'AB',
            self::B => 'B',
            self::C => 'C',
            self::D => 'D',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}