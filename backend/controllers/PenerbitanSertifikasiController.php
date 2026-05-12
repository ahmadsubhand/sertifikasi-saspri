<?php

namespace backend\controllers;

use common\enums\CertificationStatus;
use common\models\Certification;
use yii\web\Controller;

class PenerbitanSertifikasiController extends Controller
{
    public function actionIndex()
    {
        $certifications = Certification::find()
            ->where(['status' => CertificationStatus::EXTERNAL_REVIEW])
            ->with(['saspriK'])
            ->all();
        return $this->render('index', [
            'certifications' => $certifications,
        ]);
    }
}