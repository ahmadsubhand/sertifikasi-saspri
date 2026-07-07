<?php

namespace frontend\controllers;
use common\services\CertificationService;
use yii\web\Controller;

class SertifikatController extends Controller
{
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
