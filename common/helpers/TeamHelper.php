<?php

namespace common\helpers;

use common\enums\ApprovalStatus;
use common\enums\CertificationStatus;
use common\enums\TeamRole;
use common\models\Certification;
use common\models\IndicatorGroup;
use common\models\SelfTeamMember;
use common\models\PeerTeamMember;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
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

    public static function checkSelfReviewPermission(int $certification_id): SelfTeamMember
    {
        $member = SelfTeamMember::find()
            ->where([
                'certification_id' => $certification_id,
                'user_id' => Yii::$app->user->id,
                'status' => ApprovalStatus::APPROVED,
            ])
            ->one();
        if (!$member) {
            throw new ForbiddenHttpException('Akses ditolak karena Anda bukan anggota dari Tim Mandiri');
        }
        return $member;
    }

    public static function checkPeerReviewPermission(int $certification_id): PeerTeamMember
    {
        $member = PeerTeamMember::find()
            ->where([
                'certification_id' => $certification_id,
                'user_id' => Yii::$app->user->id,
                'status' => ApprovalStatus::APPROVED,
            ])
            ->one();
        if (!$member) {
            throw new ForbiddenHttpException('Akses ditolak karena Anda bukan anggota dari Tim Sebaya');
        }
        return $member;
    }

    public static function findPendingSelfTeamMemberOrFail(int $self_team_member_id): SelfTeamMember
    {
        $member = SelfTeamMember::find()
            ->alias('stm')
            ->joinWith('certification')
            ->where([
                'stm.id' => $self_team_member_id,
                'stm.user_id' => Yii::$app->user->id,
            ])
            ->one();

        if (!$member) {
            throw new NotFoundHttpException('Data tidak ditemukan atau Anda bukan anggota Tim Mandiri ini');
        }
        if ($member->certification->status !== CertificationStatus::PENDING_SELF_TEAM_FORMATION) {
            throw new UnprocessableEntityHttpException(
                'Sertifikasi tidak dalam tahap ' . CertificationStatus::list()[CertificationStatus::PENDING_SELF_TEAM_FORMATION]
            );
        }
        if ($member->status !== ApprovalStatus::PENDING) {
            throw new UnprocessableEntityHttpException('Permintaan ini sudah direspon sebelumnya');
        }

        return $member;
    }

    public static function findPendingPeerTeamMemberOrFail(int $peer_team_member_id): PeerTeamMember
    {
        $member = PeerTeamMember::find()
            ->alias('ptm')
            ->joinWith('certification')
            ->where([
                'ptm.id' => $peer_team_member_id,
                'ptm.user_id' => Yii::$app->user->id,
            ])
            ->one();

        if (!$member) {
            throw new NotFoundHttpException('Data tidak ditemukan atau Anda bukan anggota Tim Sebaya ini');
        }
        if ($member->certification->status !== CertificationStatus::PENDING_PEER_TEAM_FORMATION) {
            throw new UnprocessableEntityHttpException(
                'Sertifikasi tidak dalam tahap ' . CertificationStatus::list()[CertificationStatus::PENDING_PEER_TEAM_FORMATION]
            );
        }
        if ($member->status !== ApprovalStatus::PENDING) {
            throw new UnprocessableEntityHttpException('Permintaan ini sudah direspon sebelumnya');
        }

        return $member;
    }
}