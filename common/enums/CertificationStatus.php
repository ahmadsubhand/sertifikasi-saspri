<?php

namespace common\enums;

class CertificationStatus
{
    const PENDING_SELF_TEAM_FORMATION = 'pending_self_team_formation';
    const SELF_REVIEW = 'self_review';
    const PENDING_PEER_TEAM_FORMATION = 'pending_peer_team_formation';
    const PEER_REVIEW = 'peer_review';
    const EXTERNAL_REVIEW = 'external_review';
    const COMPLETED = 'completed';

    public static function list()
    {
        return [
            self::PENDING_SELF_TEAM_FORMATION => 'Pending Self Team Formation',
            self::SELF_REVIEW => 'Self Review',
            self::PENDING_PEER_TEAM_FORMATION => 'Pending Peer Team Formation',
            self::PEER_REVIEW => 'Peer Review',
            self::EXTERNAL_REVIEW => 'External Review',
            self::COMPLETED => 'Completed',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}