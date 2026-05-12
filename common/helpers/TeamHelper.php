<?php

namespace common\helpers;

use common\enums\TeamRole;
use common\models\SelfTeamMember;
use common\models\PeerTeamMember;
use yii\web\UnprocessableEntityHttpException;

class TeamHelper
{
    public static function isMemberALeader(SelfTeamMember|PeerTeamMember $member) 
    {
        if ($member->role !== TeamRole::LEADER) {
            throw new UnprocessableEntityHttpException('Hanya ketua tim yang dapat melakukan finalisasi');
        }
    }
}