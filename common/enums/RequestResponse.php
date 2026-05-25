<?php 

namespace common\enums;

class RequestResponse
{
    const APPROVE = 'approve';
    const REJECT = 'reject';

    public static function list()
    {
        return [
            self::APPROVE => 'Menyetujui',
            self::REJECT => 'Menolak',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}