<?php

namespace common\services;

use common\enums\CertificationStatus;
use common\enums\IndicatorScoreAttribute;
use common\helpers\CertificationHelper;
use common\helpers\TeamHelper;
use common\models\form\ExternalReviewForm;
use common\models\form\PeerReviewForm;
use common\models\form\SelfReviewForm;

class CertificationService
{
    public static function saveSelfReview(int $certification_id, SelfReviewForm $data)
    {
        TeamHelper::checkSelfReviewPermission($certification_id);
        $certification = CertificationHelper::findCertificationOrFail($certification_id, CertificationStatus::SELF_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::SELF_REVIEW);

        return $certification;
    }

    public static function finalizeSelfReview(int $certification_id, SelfReviewForm $data)
    {
        $member = TeamHelper::checkSelfReviewPermission($certification_id);
        TeamHelper::isMemberALeader($member);

        $certification = CertificationHelper::findCertificationOrFail($certification_id, CertificationStatus::SELF_REVIEW);
        $certification->saveScores($data->indicator_scores, IndicatorScoreAttribute::SELF_REVIEW)
            ->ensureAllScoresFilled(IndicatorScoreAttribute::SELF_REVIEW)
            ->submitSelfReview()
            ->save();

        return $certification;
    }

    public static function savePeerReview(int $certification_id, PeerReviewForm $data)
    {
        TeamHelper::checkPeerReviewPermission($certification_id);
        $certification = CertificationHelper::findCertificationOrFail($certification_id, CertificationStatus::PEER_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::PEER_REVIEW);

        return $certification;
    }

    public static function finalizePeerReview(int $certification_id, PeerReviewForm $data)
    {
        $member = TeamHelper::checkPeerReviewPermission($certification_id);
        TeamHelper::isMemberALeader($member);

        $certification = CertificationHelper::findCertificationOrFail($certification_id, CertificationStatus::PEER_REVIEW);
        $certification->saveScores($data->indicator_scores, IndicatorScoreAttribute::PEER_REVIEW)
            ->ensureAllScoresFilled(IndicatorScoreAttribute::PEER_REVIEW)
            ->calculateTotalScore(IndicatorScoreAttribute::PEER_REVIEW)
            ->setGrade()
            ->submitPeerReview()
            ->save();

        return $certification;
    }

    public static function saveExternalReview(int $certification_id, ExternalReviewForm $data)
    {
        $certification = CertificationHelper::findCertificationOrFail($certification_id, CertificationStatus::EXTERNAL_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::EXTERNAL_REVIEW);

        return $certification;
    }

    public static function finalizeExternalReview(int $certification_id, ExternalReviewForm $data)
    {
        $certification = CertificationHelper::findCertificationOrFail($certification_id, CertificationStatus::EXTERNAL_REVIEW);
        $certification->saveScores($data->indicator_scores, IndicatorScoreAttribute::EXTERNAL_REVIEW)
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