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
            self::LEADER => 'Leader',
            self::MEMBER => 'Member',
            self::FACILITATOR => 'Facilitator',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}