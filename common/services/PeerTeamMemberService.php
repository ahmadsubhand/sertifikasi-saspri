<?php

namespace common\services;

use common\enums\ApprovalStatus;
use common\enums\CertificationStatus;
use common\enums\RequestResponse;
use common\models\form\RequestResponseForm;
use common\models\Notification;
use common\models\PeerTeamMember;
use common\services\NotificationService;
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
        $certification = $member->certification;

        if ($member->user_id !== Yii::$app->user->id) {
            throw new ForbiddenHttpException('Anda bukan anggota Tim Sebaya ini');
        }

        if ($member->status !== ApprovalStatus::PENDING) {
            throw new UnprocessableEntityHttpException('Permintaan ini sudah direspon sebelumnya');
        }

        if ($certification->status !== CertificationStatus::PENDING_PEER_TEAM_FORMATION) {
            throw new UnprocessableEntityHttpException(
                'Sertifikasi tidak dalam tahap ' . CertificationStatus::list()[CertificationStatus::PENDING_PEER_TEAM_FORMATION]
            );
        }

        $member_username = $member->user->username ?? 'Anggota';
        $saspri_k = $certification->saspriK;
        $district_name = $saspri_k->district->name ?? 'Kawasan';
        $admin_id = Notification::find()
            ->where([
                'recipient_id' => Yii::$app->user->id,
                'title' => 'Undangan Bergabung Tim Sebaya',
                'api_link' => 'peer-team-member/join-request-response?peer_team_member_id=' . $member->id,
            ])
            ->orderBy(['created_at' => SORT_DESC])
            ->select('sender_id')
            ->one()
            ->sender_id;

        if ($data->action === RequestResponse::APPROVE) {
            $member->approveRequest();
            NotificationService::send(
                $admin_id,
                'Penerimaan Undangan Tim Sebaya',
                "{$member_username} telah menyetujui undangan untuk bergabung ke dalam Tim Sebaya di SASPRI-K kawasan {$district_name}.",
                [
                    'sender_id' => Yii::$app->user->id,
                    'web_link' => 'penentuan-tim-sebaya/pembentukan-tim-sebaya?certification_id=' . $certification->id,
                    'api_link' => 'certification/full-peer-team-members',
                    'channels' => ['db', 'fcm'],
                ]
            );
        } else if ($data->action === RequestResponse::REJECT) {
            $member->rejectRequest();
            NotificationService::send(
                $admin_id,
                'Penolakan Undangan Tim Sebaya',
                "{$member_username} telah menolak undangan untuk bergabung ke dalam Tim Sebaya di SASPRI-K kawasan {$district_name}.",
                [
                    'sender_id' => Yii::$app->user->id,
                    'web_link' => 'penentuan-tim-sebaya/pembentukan-tim-sebaya?certification_id=' . $certification->id,
                    'api_link' => 'certification/full-peer-team-members',
                    'channels' => ['db', 'fcm'],
                ]
            );
        } else {
            throw new BadRequestHttpException('Wajib memilih antara ' . implode(' atau ', RequestResponse::list()));
        }

        $member->save();

        return $member;
    }
}