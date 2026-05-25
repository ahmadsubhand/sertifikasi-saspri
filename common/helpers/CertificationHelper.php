<?php

namespace common\helpers;

use common\enums\CertificationStatus;
use common\models\Certification;
use Exception;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class CertificationHelper
{
    public static function findCertificationOrFail(int $certification_id, string $status): Certification
    {
        if (!in_array($status, CertificationStatus::values())) {
            throw new Exception('Invalid certification status');
        }

        $certification = Certification::findOne($certification_id);
        if (!$certification) {
            throw new NotFoundHttpException('Sertifikasi tidak ditemukan');
        }
        if ($certification->status !== $status) {
            throw new UnprocessableEntityHttpException(
                'Sertifikasi tidak dalam tahap ' . CertificationStatus::list()[$status]
            );
        }

        return $certification;
    }
}