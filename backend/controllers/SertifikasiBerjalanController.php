<?php

namespace backend\controllers;

use common\enums\CertificationStatus;
use common\enums\UserRole;
use common\helpers\UserHelper;
use common\models\Certification;
use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class SertifikasiBerjalanController extends Controller
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
        ];
    }

    public function actionIndex()
    {
        $certifications = Certification::find()
            ->andWhere([
                'not in',
                'status',
                [
                    CertificationStatus::PENDING_SELF_TEAM_FORMATION, // mau mulai dari sini atau sebelumnya lagi?
                    CertificationStatus::COMPLETED,
                ]
            ])
            ->with(['saspriK'])
            ->orderBy(['updated_at' => SORT_DESC])
            ->all();
        return $this->render('index', [
            'certifications' => $certifications,
        ]);
    }

    public function actionDetail(int $case_id)
    {
        try {
            $cert = Certification::findOne(['id' => $case_id]);
            if (
                !$cert || // mau mulai dari sini atau sebelumnya lagi?
                $cert->status === CertificationStatus::PENDING_SELF_TEAM_FORMATION || 
                $cert->status === CertificationStatus::COMPLETED
            ) {
                throw new NotFoundHttpException('Sertifikasi tidak ditemukan dalam sertifikasi yang sedang berjalan');
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
                if ($error instanceof NotFoundHttpException) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }
}