<?php 

namespace common\enums;

class CertificateGrade
{
    const A = 'a';
    const AB = 'ab';
    const B = 'b';
    const BC = 'bc';
    const C = 'c';

    public static function list()
    {
        return [
            self::A => 'A',
            self::AB => 'AB',
            self::B => 'B',
            self::BC => 'BC',
            self::C => 'C',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}