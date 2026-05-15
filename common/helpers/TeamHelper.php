<?php

namespace common\helpers;

use common\enums\TeamRole;
use common\models\Certification;
use common\models\IndicatorGroup;
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

    public static function getAllIndicators(Certification $certification, int $page)
    {
        $root_groups = $certification->assessment->rootGroups;
        $current_root_group = $certification->assessment
            ->getCurrentRootGroupOrFail($page, $root_groups);
        /** @var IndicatorGroup[] $current_child_groups */
        $current_child_groups = $current_root_group->getChildGroupsWithScore($certification->id)->all();

        return [
           'root_groups' => $root_groups,
           'current_root_group' => $current_root_group,
           'current_child_groups' => $current_child_groups,
        ];
    }
}