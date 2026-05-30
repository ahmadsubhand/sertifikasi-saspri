<?php

namespace common\services;

use common\enums\ApprovalStatus;
use common\enums\CertificationStatus;
use common\enums\RequestResponse;
use common\models\form\RequestResponseForm;
use common\models\PeerTeamMember;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class PeerTeamMemberService
{
    public static function findOrFail(int $id)
    {
        $member = PeerTeamMember::findOne(['id' => $id]);
        if (!$member) {
            throw new NotFoundHttpException('Anggota tidak ditemukan');
        }
        return $member;
    }

    public static function joinRequestResponse(int $peer_team_member_id, RequestResponseForm $data)
    {
        $member = PeerTeamMemberService::findOrFail($peer_team_member_id);

        if ($member->user_id !== Yii::$app->user->id) {
            throw new ForbiddenHttpException('Anda bukan anggota Tim Sebaya ini');
        }

        if ($member->status !== ApprovalStatus::PENDING) {
            throw new UnprocessableEntityHttpException('Permintaan ini sudah direspon sebelumnya');
        }

        if ($member->certification->status !== CertificationStatus::PENDING_PEER_TEAM_FORMATION) {
            throw new UnprocessableEntityHttpException(
                'Sertifikasi tidak dalam tahap ' . CertificationStatus::list()[CertificationStatus::PENDING_PEER_TEAM_FORMATION]
            );
        }

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