<?php

namespace frontend\controllers;

use common\enums\UserRole;
use common\models\form\RegisterSaspriKForm;
use common\services\SaspriKService;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class DaftarWaliController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [UserRole::USER],
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'daftar-saspri-k' => ['post'],
                    'batalkan-pendaftaran-saspri-k' => ['delete'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        try {
            return $this->render('index', SaspriKService::detailRegistration());
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof UnprocessableEntityHttpException) {
                    return $this->goHome();
                }
            }
            throw $error;
        }
    }

    public function actionDaftarSaspriK()
    {
        try {
            $data = new RegisterSaspriKForm();
            $data->load(Yii::$app->request->post(), 'SaspriK');
            if (!$data->validate()) {
                throw new BadRequestHttpException(implode(', ', $data->firstErrors));
            }
            $saspri_k = SaspriKService::register($data);

            Yii::$app->session->setFlash(
                'success',
                'SASPRI-Kawasan ' . $saspri_k['district']['name'] .
                ' berhasil didaftarkan. Sedang menunggu proses verifikasi SASPRI-Nasional'
            );
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof BadRequestHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionBatalkanPendaftaranSaspriK()
    {
        try {
            $saspri_k = SaspriKService::cancelCoordinatorChange();

            Yii::$app->session->setFlash(
                'success',
                'Pendaftaran SASPRI-Kawasan ' . $saspri_k['district']['name'] .
                ' berhasil dibatalkan. Sekarang Anda tersedia untuk diundang ke dalam SASPRI-K lain'
            );
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof NotFoundHttpException) {
                    return $this->redirect(['index']);
                } else if ($error instanceof UnprocessableEntityHttpException) {
                    return $this->redirect(['saspri-k/index']);
                }
            }
            throw $error;
        }
    }
}
