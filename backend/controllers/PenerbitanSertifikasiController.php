<?php

namespace backend\controllers;

use common\enums\CertificationStatus;
use common\enums\UserRole;
use common\helpers\ModelHelper;
use common\helpers\UserHelper;
use common\models\Certification;
use common\models\form\ExternalReviewForm;
use common\models\form\RejectCertificationForm;
use common\services\CertificationService;
use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
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
                    'tolak-sertifikasi' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(?int $limit = 20, ?int $offset = 0)
    {
        $certs = Certification::find()
            ->where(['status' => CertificationStatus::EXTERNAL_REVIEW])
            ->orderBy(['external_review_due_date' => SORT_ASC])
            ->with(['saspriK'])
            ->limit($limit + 1)
            ->offset($offset)
            ->all();

        $has_next = count($certs) > $limit;
        if ($has_next) array_pop($certs);

        return $this->render('index', [
            'certifications' => $certs,
            'prev_link' => $offset > 0 ? Url::current(['offset' => max(0, $offset - $limit)]) : null,
            'next_link' => $has_next ? Url::current(['offset' => $offset + $limit]) : null,
            'offset' => $offset,
        ]);
    }

    public function actionExternalReview(int $certification_id, int $page = 1)
    {
        try {
            return $this->render(
                'externalReview',
                CertificationService::externalReview($certification_id, $page)
            );
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
            $data = new ExternalReviewForm();
            $data->load(Yii::$app->request->post(), '');
            if (!$data->validate()) {
                throw new BadRequestHttpException(implode(', ', $data->firstErrors));
            }
            CertificationService::saveExternalReview($certification_id, $data);

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
                } elseif (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionTranskrip(int $certification_id)
    {
        try {
            return $this->render(
                'transkrip', 
                CertificationService::transcripts($certification_id)
            );
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof BadRequestHttpException) {
                    return $this->redirect([
                        'external-review',
                        'certification_id' => $certification_id,
                        'page' => 1
                    ]);
                } elseif (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
        }
    }

    public function actionFinalisasiPenerbitanSertifikasi(int $certification_id)
    {
        try {
            $data = new ExternalReviewForm();
            $data->load(Yii::$app->request->post(), '');
            if (!$data->validate()) {
                throw new BadRequestHttpException(implode(', ', $data->firstErrors));
            }
            CertificationService::finalizeExternalReview($certification_id, $data);

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
                } elseif (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionDetail(int $case_id)
    {
        try {
            $cert = Certification::findOne(['id' => $case_id]);
            if ($cert->status !== CertificationStatus::EXTERNAL_REVIEW) {
                throw new UnprocessableEntityHttpException(
                    'Sertifikasi tidak dalam tahap ' . CertificationStatus::list()[CertificationStatus::EXTERNAL_REVIEW]
                );
            }
            $saspri_k = $cert->saspriK;
            $self_team = $cert->getSelfTeamMembers()
                ->with([
                    'user' => function (ActiveQuery $query) {
                        $query->select(UserHelper::$basicSelect);
                    },
                ])
                ->all();
            $peer_team = $cert->getPeerTeamMembers()
                ->with([
                    'user' => function (ActiveQuery $query) {
                        $query->select(UserHelper::$basicSelect);
                    },
                ])
                ->all();
    
            return $this->render('detail', [
                'id' => $case_id,
                'saspri' => $saspri_k,
                'cert' => $cert,
                'self_team' => $self_team,
                'peer_team' => $peer_team,
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof UnprocessableEntityHttpException) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionTolakSertifikasi(int $certification_id)
    {
        try {
            $data = new RejectCertificationForm();
            ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->post());
            $certification = CertificationService::rejectExternalReviewRequest($certification_id, $data);
            
            Yii::$app->session->setFlash(
                'success', 
                'Sertifikasi SASPRI-K ' . $certification->saspriK->district->name . ' berhasil ditolak',
            );
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }
}
