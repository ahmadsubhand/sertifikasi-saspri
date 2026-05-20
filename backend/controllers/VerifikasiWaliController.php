<?php

namespace backend\controllers;

use common\enums\ApprovalStatus;
use common\models\SaspriK;
use common\models\User;
use Exception;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
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
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $registration_requests = SaspriK::find()
            ->where(['request_status' => ApprovalStatus::PENDING])
            ->with(['coordinator', 'district'])
            ->all();

        $change_requests = SaspriK::find()
            ->where(['change_status' => ApprovalStatus::PENDING])
            ->with(['coordinator', 'newCoordinator', 'district'])
            ->all();

        return $this->render('index', [
            'registration_requests' => $registration_requests,
            'change_requests' => $change_requests,
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

                $saspri_k->coordinator_id = $new_coordinator->id;
                $saspri_k->new_coordinator_id = null;
                $saspri_k->change_status = ApprovalStatus::APPROVED;
                $saspri_k->change_request_reason = null;
                $saspri_k->change_rejection_reason = null;
                $saspri_k->save(false);

                $message = 'Pergantian wali berhasil disetujui';
            } elseif ($action === 'reject') {
                $reason = Yii::$app->request->post('change_rejection_reason');
                $saspri_k->change_status = ApprovalStatus::REJECTED;
                $saspri_k->change_rejection_reason = $reason;
                $saspri_k->save(false);

                $message = 'Pergantian wali berhasil ditolak';
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
}
