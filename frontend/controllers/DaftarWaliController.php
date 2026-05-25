<?php

namespace frontend\controllers;

use common\enums\ApprovalStatus;
use common\enums\UserRole;
use common\models\form\RegisterSaspriKForm;
use common\models\SaspriK;
use common\models\User;
use common\services\SaspriKService;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
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
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        try {
            $saspri_k = $this->findSaspriKOrFail();
            return $this->render('index', [
                'saspri_k' => $saspri_k,
                'documents' => $saspri_k ? $saspri_k->saspriKDocuments : [],
            ]);
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

    protected function findSaspriKOrFail(): SaspriK|null
    {
        $user = User::findOne(Yii::$app->user->id);
        if ($user->saspri_k_id) {
            throw new UnprocessableEntityHttpException('Anda sudah tergabung dalam SASPRI-K');
        }

        $saspri_k = SaspriK::find()
            ->where(['coordinator_id' => $user->id])
            ->andWhere(['!=', 'request_status', ApprovalStatus::APPROVED])
            ->one();

        return $saspri_k;
    }
}
