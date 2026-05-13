<?php

namespace common\enums;

class TeamRole
{
    const LEADER = 'leader';
    const MEMBER = 'member';
    const FACILITATOR = 'facilitator';

    public static function list()
    {
        return [
            self::LEADER => 'Ketua',
            self::MEMBER => 'Anggota',
            self::FACILITATOR => 'Pendamping',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}