<?php

namespace common\services;

use common\enums\RequestResponse;
use common\helpers\TeamHelper;
use common\models\form\RequestResponseForm;
use yii\web\BadRequestHttpException;

class PeerTeamMemberService
{
    public static function joinRequestResponse(int $peer_team_member_id, RequestResponseForm $data)
    {
        $member = TeamHelper::findPendingPeerTeamMemberOrFail($peer_team_member_id);
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