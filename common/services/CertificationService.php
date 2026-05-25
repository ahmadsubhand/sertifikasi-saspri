<?php

namespace common\services;

use common\enums\CertificationStatus;
use common\enums\IndicatorScoreAttribute;
use common\helpers\UserHelper;
use common\models\Certification;
use common\models\form\AddMembersForm;
use common\models\form\ChangeMemberRoleForm;
use common\models\form\ExternalReviewForm;
use common\models\form\PeerReviewForm;
use common\models\form\SelfReviewForm;
use common\models\PeerTeamMember;
use common\models\SelfTeamMember;
use common\models\User;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class CertificationService
{
    public static function findOrFail(int $id) {
        $certification = Certification::findOne($id);
        if (!$certification) {
            throw new NotFoundHttpException('Sertifikasi tidak ditemukan');
        }
        return $certification;
    }

    public static function findSelfTeamMember(int $certification_id, int $user_id): SelfTeamMember
    {
        $member = SelfTeamMember::find()
            ->where([
                'user_id' => $user_id,
                'certification_id' => $certification_id,
            ])
            ->joinWith('user')
            ->one();
        if (!$member) {
            throw new NotFoundHttpException('Anggota tidak ditemukan atau bukan anggota Tim Mandiri ini');
        }
        return $member;
    }

    public static function findPeerTeamMember(int $certification_id, int $user_id): PeerTeamMember
    {
        $member = PeerTeamMember::find()
            ->where([
                'user_id' => $user_id,
                'certification_id' => $certification_id,
            ])
            ->joinWith('user')
            ->one();
        if (!$member) {
            throw new NotFoundHttpException('Anggota tidak ditemukan atau bukan anggota Tim Sebaya ini');
        }
        return $member;
    }

    public static function addSelfTeamMembers(AddMembersForm $data)
    {
        $saspri_k = UserService::findSaspriKAsCoordinatorOrFail();
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
        $saspri_k = UserService::findSaspriKAsCoordinatorOrFail();
        $certification = $saspri_k->findOrCreateOnGoingCertification()
            ->validateCertificationStatus(CertificationStatus::PENDING_SELF_TEAM_FORMATION);
        $member = CertificationService::findSelfTeamMember($certification->id, $user_id);
        $member->delete();
        return [
            ...$member,
            'user' => $member->getUser()->select(UserHelper::$basicSelect)->one(),
        ];
    }

    public static function changeSelfTeamMemberRole(int $user_id, ChangeMemberRoleForm $data)
    {
        $saspri_k = UserService::findSaspriKAsCoordinatorOrFail();
        $certification = $saspri_k->findOrCreateOnGoingCertification()
            ->validateCertificationStatus(CertificationStatus::PENDING_SELF_TEAM_FORMATION);
        $member = CertificationService::findSelfTeamMember($certification->id, $user_id);
        $member->changeRole($data->role)->save();
        return [
            ...$member,
            'user' => $member->getUser()->select(UserHelper::$basicSelect)->one(),
        ];
    }

    public static function submitForSelfReview()
    {
        $saspri_k = UserService::findSaspriKAsCoordinatorOrFail();
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
        CertificationService::findSelfTeamMember($certification_id, Yii::$app->user->id)
            ->checkSelfReviewPermission();

        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::SELF_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::SELF_REVIEW);

        return $certification;
    }

    public static function finalizeSelfReview(int $certification_id, SelfReviewForm $data)
    {
        CertificationService::findSelfTeamMember($certification_id, Yii::$app->user->id)
            ->checkSelfReviewPermission()
            ->checkFinalizationPermission();

        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::SELF_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::SELF_REVIEW)
            ->ensureAllScoresFilled(IndicatorScoreAttribute::SELF_REVIEW)
            ->submitSelfReview();
        $certification->save();

        return $certification;
    }

    public static function addPeerTeamMembers(int $certification_id, AddMembersForm $data)
    {
        $certification = CertificationService::findOrFail($certification_id)
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
        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::PENDING_PEER_TEAM_FORMATION);
        $member = CertificationService::findPeerTeamMember($certification->id, $user_id);
        $member->delete();
        return [
            ...$member,
            'user' => $member->getUser()->select(UserHelper::$basicSelect)->one(),
        ];
    }

    public static function changePeerTeamMemberRole(int $certification_id, int $user_id, ChangeMemberRoleForm $data)
    {
        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::PENDING_PEER_TEAM_FORMATION);
        $member = CertificationService::findPeerTeamMember($certification->id, $user_id);
        $member->changeRole($data->role)->save();
        return [
            ...$member,
            'user' => $member->getUser()->select(UserHelper::$basicSelect)->one(),
        ];
    }

    public static function submitForPeerReview(int $certification_id)
    {
        $certification = CertificationService::findOrFail($certification_id);
        $certification->validateCertificationStatus(CertificationStatus::PENDING_PEER_TEAM_FORMATION)
            ->validateApprovedPeerTeamComposition()
            ->submitForPeerReview()
            ->save();

        return $certification;
    }

    public static function savePeerReview(int $certification_id, PeerReviewForm $data)
    {
        CertificationService::findPeerTeamMember($certification_id, Yii::$app->user->id)
            ->checkPeerReviewPermission();

        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::PEER_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::PEER_REVIEW);

        return $certification;
    }

    public static function finalizePeerReview(int $certification_id, PeerReviewForm $data)
    {
        CertificationService::findPeerTeamMember($certification_id, Yii::$app->user->id)
            ->checkPeerReviewPermission()
            ->checkFinalizationPermission();

        $certification = CertificationService::findOrFail($certification_id)
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
        $certification = CertificationService::findOrFail($certification_id)    
            ->validateCertificationStatus(CertificationStatus::EXTERNAL_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::EXTERNAL_REVIEW);

        return $certification;
    }

    public static function finalizeExternalReview(int $certification_id, ExternalReviewForm $data)
    {
        $certification = CertificationService::findOrFail($certification_id)    
            ->validateCertificationStatus(CertificationStatus::EXTERNAL_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::EXTERNAL_REVIEW)
            ->ensureAllScoresFilled(IndicatorScoreAttribute::EXTERNAL_REVIEW)
            ->calculateTotalScore(IndicatorScoreAttribute::EXTERNAL_REVIEW)
            ->setGrade()
            ->submitExternalReview()
            ->generateCertificationCode()
            ->calculateNextCertificationDueDate()
            ->save();

        return $certification;
    }
}