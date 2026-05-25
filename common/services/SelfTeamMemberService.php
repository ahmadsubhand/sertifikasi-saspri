<?php

namespace common\services;

use common\enums\RequestResponse;
use common\helpers\TeamHelper;
use common\models\form\RequestResponseForm;
use common\models\SelfTeamMember;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class SelfTeamMemberService
{
    public static function findOrFail(int $certification_id, int $user_id)
    {
        $member = SelfTeamMember::findOne([
            'certification_id' => $certification_id,
            'user_id' => $user_id,
        ]);
        if (!$member) {
            throw new NotFoundHttpException('Anggota tidak ditemukan atau bukan anggota Tim Mandiri');
        }
        return $member;
    }

    public static function joinRequestResponse(int $self_team_member_id, RequestResponseForm $data)
    {
        $member = TeamHelper::findPendingSelfTeamMemberOrFail($self_team_member_id);
        if ($data->action === RequestResponse::APPROVE) {
            $member->approveRequest();
        } else if ($data->action === RequestResponse::REJECT) {
            $member->rejectRequest();
        } else {
            throw new BadRequestHttpException('Wajib memilih antara ' . implode(' atau ', RequestResponse::list()));
        }
        $member->save();

        return $member;
    }
}