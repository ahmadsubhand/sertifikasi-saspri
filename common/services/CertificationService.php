<?php

namespace common\services;

use common\enums\CertificationStatus;
use common\enums\IndicatorScoreAttribute;
use common\helpers\TeamHelper;
use common\models\Certification;
use common\models\form\AddMembersForm;
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
            ->submitSelfReview()
            ->save();

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
            ->submitPeerReview()
            ->save();

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