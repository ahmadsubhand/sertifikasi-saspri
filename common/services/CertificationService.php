<?php

namespace common\services;

use common\enums\CertificationStatus;
use common\enums\IndicatorScoreAttribute;
use common\helpers\TeamHelper;
use common\helpers\UserHelper;
use common\models\Certification;
use common\models\form\AddMembersForm;
use common\models\form\ChangeMemberRoleForm;
use common\models\form\ExternalReviewForm;
use common\models\form\PeerReviewForm;
use common\models\form\SelfReviewForm;
use common\models\User;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class CertificationService
{
    public static function findCertificationOrFail(int $id) {
        $certification = Certification::findOne($id);
        if (!$certification) {
            throw new NotFoundHttpException('Sertifikasi tidak ditemukan');
        }
        return $certification;
    }

    public static function addSelfTeamMembers(AddMembersForm $data)
    {
        $saspri_k = SaspriKService::findSaspriKAsCoordinatorOrFail();
        $certification = $saspri_k->findOrCreateOnGoingCertification()
            ->validateCertificationStatus(CertificationStatus::PENDING_SELF_TEAM_FORMATION);

        $valid_users = User::find()->availableForSelfTeam($saspri_k, $certification)
            ->andWhere(['id' => $data->user_ids])
            ->select('username')
            ->column();

        if (count($valid_users) !== count($data->user_ids)) {
            throw new BadRequestHttpException('Beberapa user tidak valid atau sudah terdaftar di Tim Mandiri saat ini');
        }

        $certification->save(); // untuk mendapatkan id jika sertifikasi baru diajukan
        $certification->addSelfTeamMembers($data->user_ids);

        return $valid_users;
    }

    public static function removeSelfTeamMember(int $user_id)
    {
        $saspri_k = SaspriKService::findSaspriKAsCoordinatorOrFail();
        $certification = $saspri_k->findOrCreateOnGoingCertification()
            ->validateCertificationStatus(CertificationStatus::PENDING_SELF_TEAM_FORMATION);
        $member = SelfTeamMemberService::findOrFail($certification->id, $user_id);
        $member->delete();
        return [
            ...$member,
            'user' => $member->getUser()->select(UserHelper::$basicSelect)->one(),
        ];
    }

    public static function changeSelfTeamMemberRole(int $user_id, ChangeMemberRoleForm $data)
    {
        $saspri_k = SaspriKService::findSaspriKAsCoordinatorOrFail();
        $certification = $saspri_k->findOrCreateOnGoingCertification()
            ->validateCertificationStatus(CertificationStatus::PENDING_SELF_TEAM_FORMATION);
        $member = SelfTeamMemberService::findOrFail($certification->id, $user_id);
        $member->changeRole($data->role)->save();
        return [
            ...$member,
            'user' => $member->getUser()->select(UserHelper::$basicSelect)->one(),
        ];
    }

    public static function submitForSelfReview()
    {
        $saspri_k = SaspriKService::findSaspriKAsCoordinatorOrFail();
        $certification = $saspri_k->onGoingCertification;
        if (!$certification) {
            throw new NotFoundHttpException('Tidak ada sertifikasi yang sedang berlangsung');
        }
        $certification->validateCertificationStatus(CertificationStatus::PENDING_SELF_TEAM_FORMATION)
            ->validateApprovedSelfTeamComposition()
            ->submitForSelfReview()
            ->save();

        return $certification;
    }

    public static function saveSelfReview(int $certification_id, SelfReviewForm $data)
    {
        TeamHelper::checkSelfReviewPermission($certification_id);
        $certification = CertificationService::findCertificationOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::SELF_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::SELF_REVIEW);

        return $certification;
    }

    public static function finalizeSelfReview(int $certification_id, SelfReviewForm $data)
    {
        $member = TeamHelper::checkSelfReviewPermission($certification_id);
        TeamHelper::isMemberALeader($member);

        $certification = CertificationService::findCertificationOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::SELF_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::SELF_REVIEW)
            ->ensureAllScoresFilled(IndicatorScoreAttribute::SELF_REVIEW)
            ->submitSelfReview();
        $certification->save();

        return $certification;
    }

    public static function addPeerTeamMembers(int $certification_id, AddMembersForm $data)
    {
        $certification = CertificationService::findCertificationOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::PENDING_PEER_TEAM_FORMATION);

        $valid_users = User::find()->availableForPeerTeam($certification)
            ->andWhere(['id' => $data->user_ids])
            ->select('username')
            ->column();

        if (count($valid_users) !== count($data->user_ids)) {
            throw new BadRequestHttpException('Beberapa user tidak valid atau sudah terdaftar di Tim Sebaya saat ini');
        }

        $certification->addPeerTeamMembers($data->user_ids);
        return $valid_users;
    }

    public static function removePeerTeamMember(int $certification_id, int $user_id)
    {
        $certification = CertificationService::findCertificationOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::PENDING_PEER_TEAM_FORMATION);
        $member = PeerTeamMemberService::findOrFail($certification->id, $user_id);
        $member->delete();
        return [
            ...$member,
            'user' => $member->getUser()->select(UserHelper::$basicSelect)->one(),
        ];
    }

    public static function changePeerTeamMemberRole(int $certification_id, int $user_id, ChangeMemberRoleForm $data)
    {
        $certification = CertificationService::findCertificationOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::PENDING_PEER_TEAM_FORMATION);
        $member = PeerTeamMemberService::findOrFail($certification->id, $user_id);
        $member->changeRole($data->role)->save();
        return [
            ...$member,
            'user' => $member->getUser()->select(UserHelper::$basicSelect)->one(),
        ];
    }

    public static function submitForPeerReview(int $certification_id)
    {
        $certification = CertificationService::findCertificationOrFail($certification_id);
        $certification->validateCertificationStatus(CertificationStatus::PENDING_PEER_TEAM_FORMATION)
            ->validateApprovedPeerTeamComposition()
            ->submitForPeerReview()
            ->save();

        return $certification;
    }

    public static function savePeerReview(int $certification_id, PeerReviewForm $data)
    {
        TeamHelper::checkPeerReviewPermission($certification_id);
        $certification = CertificationService::findCertificationOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::PEER_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::PEER_REVIEW);

        return $certification;
    }

    public static function finalizePeerReview(int $certification_id, PeerReviewForm $data)
    {
        $member = TeamHelper::checkPeerReviewPermission($certification_id);
        TeamHelper::isMemberALeader($member);

        $certification = CertificationService::findCertificationOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::PEER_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::PEER_REVIEW)
            ->ensureAllScoresFilled(IndicatorScoreAttribute::PEER_REVIEW)
            ->calculateTotalScore(IndicatorScoreAttribute::PEER_REVIEW)
            ->setGrade()
            ->submitPeerReview();
        $certification->save();

        return $certification;
    }

    public static function saveExternalReview(int $certification_id, ExternalReviewForm $data)
    {
        $certification = CertificationService::findCertificationOrFail($certification_id)    
            ->validateCertificationStatus(CertificationStatus::EXTERNAL_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::EXTERNAL_REVIEW);

        return $certification;
    }

    public static function finalizeExternalReview(int $certification_id, ExternalReviewForm $data)
    {
        $certification = CertificationService::findCertificationOrFail($certification_id)    
            ->validateCertificationStatus(CertificationStatus::EXTERNAL_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::EXTERNAL_REVIEW)
            ->ensureAllScoresFilled(IndicatorScoreAttribute::EXTERNAL_REVIEW)
            ->calculateTotalScore(IndicatorScoreAttribute::EXTERNAL_REVIEW)
            ->setGrade()
            ->generateCertificationCode()
            ->submitExternalReview()
            ->calculateNextCertificationDueDate()
            ->save();

        return $certification;
    }
}