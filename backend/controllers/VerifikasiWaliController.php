<?php

namespace backend\controllers;

use common\enums\ApprovalStatus;
use common\helpers\TeamHelper;
use common\models\Certification;
use common\models\SaspriK;
use common\models\User;
use Exception;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\UnprocessableEntityHttpException;

class VerifikasiWaliController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'ganti-wali' => ['post'],
                    'daftarkan-wali' => ['post'],
                    'simpan-skor-pendaftaran' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(
        ?int $limit = 10,
        ?int $offset_registration = 0,
        ?int $offset_change = 0,
    ) {
        $registration_query = SaspriK::find()
            ->where(['request_status' => ApprovalStatus::PENDING]);

        $change_query = SaspriK::find()
            ->where(['change_status' => ApprovalStatus::PENDING]);

        $registrations = $registration_query
            ->with(['coordinator', 'district'])
            ->orderBy(['updated_at' => SORT_ASC])
            ->limit($limit + 1)
            ->offset($offset_registration)
            ->all();
        $registration_has_next = count($registrations) > $limit;
        if ($registration_has_next) array_pop($registrations);

        $changes = $change_query
            ->with(['coordinator', 'newCoordinator', 'district'])
            ->orderBy(['updated_at' => SORT_ASC])
            ->limit($limit + 1)
            ->offset($offset_change)
            ->all();
        $change_has_next = count($changes) > $limit;
        if ($change_has_next) array_pop($changes);

        return $this->render('index', [
            'registration_requests' => $registrations,
            'registration_prev_link' => $offset_registration > 0 ? Url::current(['offset_registration' => max(0, $offset_registration - $limit)]) : null,
            'registration_next_link' => $registration_has_next ? Url::current(['offset_registration' => $offset_registration + $limit]) : null,
            'offset_registration' => $offset_registration,

            'change_requests' => $changes,
            'change_prev_link' => $offset_change > 0 ? Url::current(['offset_change' => max(0, $offset_change - $limit)]) : null,
            'change_next_link' => $change_has_next ? Url::current(['offset_change' => $offset_change + $limit]) : null,
            'offset_change' => $offset_change,
            'limit' => $limit,
        ]);
    }

    public function actionPermintaanPergantianWali(int $saspri_k_id)
    {
        try {
            $saspri_k = $this->findSaspriKWithRequestChangeOrFail($saspri_k_id);

            return $this->render('permintaanPergantianWali', [
                'saspri_k' => $saspri_k,
                'valid_certificate' => $saspri_k->validCertificate,
            ]);
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

    public function actionGantiWali(int $saspri_k_id)
    {
        try {
            $saspri_k = $this->findSaspriKWithRequestChangeOrFail($saspri_k_id);

            $message = '';
            $action = Yii::$app->request->post('action');
            if ($action === 'approve') {
                $old_coordinator = User::findOne($saspri_k->coordinator_id);
                $old_coordinator->demoteFromCoordinator();

                $new_coordinator = User::findOne($saspri_k->new_coordinator_id);
                $new_coordinator->promoteToCoordinator();

                $saspri_k->approveCoordinatorChange()->save(false);
                $message = 'Pergantian wali berhasil disetujui';
            } elseif ($action === 'reject') {
                $reason = Yii::$app->request->post('change_rejection_reason');
                $saspri_k->rejectCoordinatorChange($reason)->save(false);
                $message = 'Pergantian wali berhasil ditolak';
            } else {
                throw new BadRequestHttpException('Wajib memilih antara setuju atau tolak');
            }

            Yii::$app->session->setFlash('success', $message);
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                } elseif ($error instanceof BadRequestHttpException) {
                    return $this->redirect(['permintaan-pergantian-wali']);
                }
            }
            throw $error;
        }
    }

    public function actionPermintaanPendaftaranWali(int $saspri_k_id, ?int $page = 1)
    {
        try {
            $saspri_k = $this->findSaspriKWithRegistrationOrFail($saspri_k_id);

            $certification = $saspri_k->getCertifications()->one();
            [
                'root_groups' => $root_groups,
                'current_root_group' => $current_root_group,
                'current_child_groups' => $current_child_groups
            ] = TeamHelper::getAllIndicators($certification, $page);

            return $this->render('permintaanPendaftaranWali', [
                'saspri_k' => $saspri_k,
                'documents' => $saspri_k ? $saspri_k->saspriKDocuments : [],
                'coordinator' => $saspri_k->coordinator,
                'certification' => $certification,
                'current_root_group' => $current_root_group,
                'current_child_groups' => $current_child_groups,
                'page' => $page,
                'total_pages' => count($root_groups),
            ]);
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

    public function actionSimpanSementaraPermintaanPendaftaran(int $saspri_k_id, int $page = 1)
    {
        try {
            $saspri_k = $this->findSaspriKWithRegistrationOrFail($saspri_k_id);
            /** @var Certification $certification */
            $certification = $saspri_k->getCertifications()->one();
            $certification->saveExternalReviewScores(Yii::$app->request->post('indicator_scores', []));

            Yii::$app->session->setFlash('success', 'Perubahan berhasil disimpan sementara');
            $targetPage = Yii::$app->request->post('target_page', $page);
            return $this->redirect([
                'permintaan-pendaftaran-wali',
                'saspri_k_id' => $saspri_k_id,
                'page' => $targetPage,
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof BadRequestHttpException) {
                    return $this->redirect([
                        'permintaan-pendaftaran-wali',
                        'saspri_k_id' => $saspri_k_id,
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

    public function actionDaftarkanWali(int $saspri_k_id)
    {
        try {
            $saspri_k = $this->findSaspriKWithRegistrationOrFail($saspri_k_id);

            $action = Yii::$app->request->post('action');
            if ($action === 'approve') {
                /** @var Certification $certification */
                $certification = $saspri_k->getCertifications()->one();
                $certification->saveExternalReviewScores(Yii::$app->request->post('indicator_scores', []))
                    ->submitExternalReview()
                    ->calculateTotalScore('final_score')
                    ->setGrade()
                    ->generateCertificationCode()
                    ->setNextCertificationDueDateToNow()
                    ->save(false);

                $coordinator = $saspri_k->coordinator;
                $coordinator->promoteToCoordinator();
                $saspri_k->addMembers([$coordinator->id]);

                $saspri_k->approveRegistration()->save(false);
                $message = 'Pendaftaran wali berhasil disetujui';
            } elseif ($action === 'reject') {
                $reason = Yii::$app->request->post('request_rejection_reason');
                $saspri_k->rejectRegistration($reason)->save(false);
                $message = 'Pendaftaran wali berhasil ditolak';
            } else {
                throw new BadRequestHttpException('Wajib memilih antara setuju atau tolak');
            }

            Yii::$app->session->setFlash('success', $message);
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                } elseif ($error instanceof BadRequestHttpException) {
                    return $this->redirect([
                        'permintaan-pendaftaran-wali',
                        'saspri_k_id' => $saspri_k_id,
                    ]);
                }
            }
            throw $error;
        }
    }

    protected function findSaspriKWithRequestChangeOrFail(int $id)
    {
        $saspri_k = SaspriK::findOne($id);
        if (!$saspri_k) {
            throw new NotFoundHttpException('SASPRI-K tidak ditemukan');
        }
        if ($saspri_k->change_status !== ApprovalStatus::PENDING) {
            throw new UnprocessableEntityHttpException(
                'Tidak ditemukan permintaan pergantian wali oleh SASPRI-K ' . $saspri_k->region_name .
                ' atau permintaan sudah pernah ditanggapi'
            );
        }
        return $saspri_k;
    }

    protected function findSaspriKWithRegistrationOrFail(int $id)
    {
        $saspri_k = SaspriK::findOne($id);
        if (!$saspri_k) {
            throw new NotFoundHttpException('SASPRI-K tidak ditemukan');
        }
        if ($saspri_k->request_status !== ApprovalStatus::PENDING) {
            throw new UnprocessableEntityHttpException(
                'Tidak ditemukan permintaan pendaftaran wali ' . $saspri_k->region_name .
                ' atau permintaan sudah pernah ditanggapi'
            );
        }
        return $saspri_k;
    }
}
