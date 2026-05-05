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
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}