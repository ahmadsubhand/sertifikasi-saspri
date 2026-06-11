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
            'marginLeft' => 30,
            'marginRight' => 30,
            'marginTop' => 20,
            'marginBottom' => 10,
            'content' => $content,
            'cssInline' => '
                body { font-family: sans-serif; font-size: 12px; }
                .table { 
                    width: 100%; 
                    border-collapse: separate; 
                    border-spacing: 0; 
                    margin-bottom: 20px; 
                    border-top: 1px solid #000000;
                    border-left: 1px solid #000000;
                }
                .table th, .table td { 
                    border-top: 1px solid #000000; 
                    border-bottom: 1px solid #000000; 
                    border-right: 1px solid #000000; 
                    padding: 2px 8px; 
                    vertical-align: middle; 
                    }
                .table th { 
                    text-align: center; 
                    font-weight: bold;
                    background-color: #ffffff;
                }
                .thead{
                    border-bottom: 1px solid #000000;
                }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .font-weight-bold { font-weight: bold; }
                .font-weight-normal { font-weight: normal; }
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
