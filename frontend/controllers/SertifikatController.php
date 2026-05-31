<?php

namespace frontend\controllers;

use common\models\Certification;

use kartik\mpdf\Pdf;
use yii\helpers\Inflector;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class SertifikatController extends Controller
{
    public function actionDownloadTranscript(int $certification_id)
    {
        // 1. Ambil data model dan transkrip
        $certification = Certification::findOne($certification_id);
        if (!$certification) {
            throw new NotFoundHttpException('Data sertifikasi tidak ditemukan.');
        }
        
        $transcripts = $certification->getTranscripts();

        // 2. Render tampilan HTML ke dalam variabel string
        $content = $this->renderPartial('_transcript_pdf', [
            'certification' => $certification,
            'saspri_k' => $certification->getSaspriK()->with('district')->one(),
            'transcripts' => $transcripts,
        ]);

        // 3. Konfigurasi dan Generate PDF
        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_A4, 
            'orientation' => Pdf::ORIENT_PORTRAIT, 
            'destination' => Pdf::DEST_BROWSER, // Ganti ke DEST_DOWNLOAD jika ingin langsung download
            'filename' => 'Transkrip-SASPRI-K-' . Inflector::slug($certification->saspriK->district->name) . '.pdf',
            'content' => $content,  
            'cssInline' => '
                body { font-family: sans-serif; font-size: 12px; }
                .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .table th, .table td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
                .table th { background-color: #f2f2f2; text-align: center; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .font-weight-bold { font-weight: bold; }
            ', 
            'options' => ['title' => 'Transkrip Nilai Sertifikasi'],
            'methods' => [ 
                'SetHeader' => ['Transkrip Nilai Sertifikasi||Tanggal Cetak: ' . date("d M Y")], 
                'SetFooter' => ['|Halaman {PAGENO}|'],
            ]
        ]);
        
        return $pdf->render(); 
    }
}