<?php

namespace frontend\controllers;

use common\enums\ApprovalStatus;
use common\enums\UserRole;
use common\models\SaspriK;
use common\models\User;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\UploadedFile;

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
            $documents = UploadedFile::getInstancesByName('saspri_k_documents');
            if (empty($documents)) {
                throw new BadRequestHttpException(
                    'Wajib menyertakan dokumen pendukung minimal sertifikasi tingkat Natalia'
                );
            }

            $types = Yii::$app->request->post('SaspriK')['saspri_k_documents'];
            if (count($documents) !== count($types)) {
                throw new BadRequestHttpException('Tipe dokumen wajib disertakan');
            }

            $saspri_k = $this->findSaspriKOrFail() ?: new SaspriK();
            if ($saspri_k->request_status === ApprovalStatus::PENDING) {
                throw new UnprocessableEntityHttpException(
                    'SASPRI-Kawasan sudah pernah didaftarkan dan masih dalam proses tinjauan SASPRI-Nasional'
                );
            }

            $saspri_k->load(Yii::$app->request->post());
            $saspri_k->requestRegistration(Yii::$app->user->id)->save(false);

            $saspri_k->deleteOldDocuments();
            $saspri_k->uploadNewDocuments($documents, $types);

            if (!$saspri_k->getCertifications()->exists()) {
                $saspri_k->createCertificationForNewSaspriK()->save(false);
            }

            Yii::$app->session->setFlash(
                'success',
                'SASPRI-Kawasan ' . $saspri_k->district->name .
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
