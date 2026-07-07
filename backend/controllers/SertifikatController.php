<?php

namespace backend\controllers;

use common\enums\UserRole;
use common\services\CertificationService;
use yii\filters\AccessControl;
use yii\web\Controller;

class SertifikatController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [UserRole::ADMIN],
                    ]
                ]
            ],
        ];
    }

    public function actionDownloadTranscript(int $certification_id)
    {
        $pdf = CertificationService::donwload($certification_id);
        return $this->response->sendContentAsFile(
            $pdf,
            'transcript.pdf',
            [
                'mimeType' => 'application/pdf',
                'inline' => true,
            ]
        );
    }
}
