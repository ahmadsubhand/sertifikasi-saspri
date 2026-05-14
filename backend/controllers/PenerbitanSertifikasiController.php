<?php

namespace backend\controllers;

use common\enums\CertificationStatus;
use common\enums\UserRole;
use common\models\Certification;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class PenerbitanSertifikasiController extends Controller
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
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'tambah-anggota-tim-sebaya' => ['post'],
                    'hapus-anggota-tim-sebaya' => ['delete'],
                    'ubah-peran-anggota-tim-sebaya' => ['post'],
                    'ajukan-peer-review' => ['post'],
                    'simpan-sementara-penerbitan-sertifikasi' => ['post'],
                    'finalisasi-penerbitan-sertifikasi' => ['post'],
                ],
            ],
        ];
    }

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

    public function actionExternalReview(int $certification_id, int $page = 1)
    {
        try {
            $certification = $this->findCertificationOrFail($certification_id);

            $root_groups = $certification->assessment->getAllRootGroups();
            $current_root_group = $certification->assessment
                ->getCurrentRootGroupOrFail($page, $root_groups);
            $current_child_group = $certification->assessment
                ->getCurrentChildGroups($current_root_group, $certification_id);

            return $this->render('externalReview', [
                'certification' => $certification,
                'current_root_group' => $current_root_group,
                'current_child_group' => $current_child_group,
                'page' => $page,
                'total_pages' => count($root_groups),
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException ||
                    $error instanceof BadRequestHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionSimpanSementaraPenerbitanSertifikasi(int $certification_id, int $page = 1)
    {
        try {
            $this->findCertificationOrFail($certification_id)
                ->saveExternalReviewScores(Yii::$app->request->post('indicator_scores', []));

            Yii::$app->session->setFlash('success', 'Perubahan berhasil disimpan sementara');
            $targetPage = Yii::$app->request->post('target_page', $page);
            return $this->redirect([
                'external-review', 
                'certification_id' => $certification_id,
                'page' => $targetPage,
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof BadRequestHttpException) {
                    return $this->redirect([
                        'external-review', 
                        'certification_id' => $certification_id, 
                        'page' => $page
                    ]);
                } else if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionFinalisasiPenerbitanSertifikasi(int $certification_id)
    {
        try {
            $this->findCertificationOrFail($certification_id)
                ->saveExternalReviewScores(Yii::$app->request->post('indicator_scores', []))
                ->submitExternalReview()
                ->updateFinalTotalScoresAndGrade()
                ->save(false);

            Yii::$app->session->setFlash('success', 'Penerbitan Sertifikasi berhasil difinalisasi');
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof BadRequestHttpException) {
                    return $this->redirect([
                        'external-review', 
                        'certification_id' => $certification_id, 
                        'page' => 1
                    ]);
                } else if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    protected function findCertificationOrFail(int $certification_id): Certification
    {
        $certification = Certification::findOne($certification_id);
        if (!$certification) {
            throw new NotFoundHttpException('Sertifikasi tidak ditemukan');
        }
        if ($certification->status !== CertificationStatus::EXTERNAL_REVIEW) {
            throw new UnprocessableEntityHttpException(
                'Sertifikasi tidak dalam tahap ' . CertificationStatus::list()[CertificationStatus::EXTERNAL_REVIEW]
            );
        }
        return $certification;
    }
}