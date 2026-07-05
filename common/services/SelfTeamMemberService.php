<?php

namespace common\services;

use common\enums\ApprovalStatus;
use common\enums\CertificationStatus;
use common\enums\RequestResponse;
use common\models\form\RequestResponseForm;
use common\models\SelfTeamMember;
use common\services\NotificationService;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class SelfTeamMemberService
{
    public static function findOrFail(int $id)
    {
        $member = SelfTeamMember::findOne(['id' => $id]);
        if (!$member) {
            throw new NotFoundHttpException('Anggota tidak ditemukan');
        }
        return $member;
    }

    public static function joinRequestResponse(int $self_team_member_id, RequestResponseForm $data)
    {
        $member = SelfTeamMemberService::findOrFail($self_team_member_id);
        $certification = $member->certification;

        if ($member->user_id !== Yii::$app->user->id) {
            throw new ForbiddenHttpException('Anda bukan anggota Tim Sebaya ini');
        }

        if ($member->status !== ApprovalStatus::PENDING) {
            throw new UnprocessableEntityHttpException('Permintaan ini sudah direspon sebelumnya');
        }

        if ($certification->status !== CertificationStatus::PENDING_SELF_TEAM_FORMATION) {
            throw new UnprocessableEntityHttpException(
                'Sertifikasi tidak dalam tahap ' . CertificationStatus::list()[CertificationStatus::PENDING_SELF_TEAM_FORMATION]
            );
        }

        $member_username = $member->user->username ?? 'Anggota';
        $saspri_k = $certification->saspriK;
        $district_name = $saspri_k->district->name ?? 'Kawasan';
        $coordinator_id = $saspri_k->coordinator_id;
        $formatted_due_date = date('d-m-Y H:i', strtotime($certification->self_team_due_date));

        if ($data->action === RequestResponse::APPROVE) {
            $member->approveRequest();
            NotificationService::send(
                $coordinator_id,
                'Penerimaan Undangan Tim Mandiri',
                "{$member_username} telah menyetujui undangan untuk bergabung ke dalam Tim Mandiri di SASPRI-K kawasan {$district_name}. Batas akhir pembentukan tim adalah {$formatted_due_date}.",
                [
                    'sender_id' => Yii::$app->user->id,
                    'web_link' => 'saspri-k/pengajuan-sertifikasi',
                    'api_link' => null,
                    'channels' => ['db', 'fcm'],
                ]
            );
        } else if ($data->action === RequestResponse::REJECT) {
            $member->rejectRequest();
            NotificationService::send(
                $coordinator_id,
                'Penolakan Undangan Tim Mandiri',
                "{$member_username} telah menolak undangan untuk bergabung ke dalam Tim Mandiri di SASPRI-K kawasan {$district_name}. Batas akhir pembentukan tim adalah {$formatted_due_date}.",
                [
                    'sender_id' => Yii::$app->user->id,
                    'web_link' => 'saspri-k/pengajuan-sertifikasi',
                    'api_link' => '/on-going-certification?certification_id=' . $certification->id,
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