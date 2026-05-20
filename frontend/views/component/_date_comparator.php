<?php

/** @var common\models\Certification $cert  */

use common\enums\CertificationStatus;
use yii\helpers\Html;

switch ($cert->status) {
  case CertificationStatus::PENDING_SELF_TEAM_FORMATION:
    echo Html::encode(date('d-m-Y', strtotime($cert->self_team_due_date)));
    break;
  case CertificationStatus::SELF_REVIEW:
    echo Html::encode(date('d-m-Y', strtotime($cert->self_review_due_date)));
    break;
  case CertificationStatus::PENDING_PEER_TEAM_FORMATION:
    echo Html::encode(date('d-m-Y', strtotime($cert->peer_team_due_date)));
    break;
  case CertificationStatus::PEER_REVIEW:
    echo Html::encode(date('d-m-Y', strtotime($cert->peer_review_due_date)));
    break;
  case CertificationStatus::EXTERNAL_REVIEW:
    echo Html::encode(date('d-m-Y', strtotime($cert->external_review_due_date)));
    break;
  case CertificationStatus::COMPLETED:
    echo Html::encode(date('d-m-Y', strtotime($cert->issued_at)));
    break;

  default:
    echo '-';
    break;
}
