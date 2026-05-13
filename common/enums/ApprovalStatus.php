<?php 

namespace common\enums;

class ApprovalStatus
{
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';

    public static function list()
    {
        return [
            self::PENDING => 'Menunggu',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}