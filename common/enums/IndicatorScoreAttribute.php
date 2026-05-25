<?php

namespace common\enums;

class IndicatorScoreAttribute
{
    const SELF_REVIEW = 'self_team_score';
    const PEER_REVIEW = 'peer_team_score';
    const EXTERNAL_REVIEW = 'final_score';

    public static function list()
    {
        return [
            self::SELF_REVIEW => 'Skor Mandiri',
            self::PEER_REVIEW => 'Skor Sebaya',
            self::EXTERNAL_REVIEW => 'Penilaian Final',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}