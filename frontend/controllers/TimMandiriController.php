<?php

namespace frontend\controllers;

use common\enums\ApprovalStatus;
use common\enums\CertificationStatus;
use common\enums\UserRole;
use common\enums\TeamRole;
use common\helpers\TeamHelper;
use common\models\Certification;
use common\models\SelfTeamMember;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class TimMandiriController extends Controller
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
              'setuju' => ['post'],
              'tolak' => ['post'],
              'simpan-sementara-self-review' => ['post'],
              'finalisasi-self-review' => ['post'],
            ],
          ],
        ];
    }

    public function actionIndex()
    {
        $base_query = SelfTeamMember::find()
            ->joinWith('certification')
            ->where(['user_id' => Yii::$app->user->id])
            ->with('certification.saspriK');

        $self_team_member_request = (clone $base_query)
            ->andWhere(['certifications.status' => CertificationStatus::PENDING_SELF_TEAM_FORMATION])
            ->all();

        $self_team_member_uncompleted = (clone $base_query)
            ->andWhere([
                'not in',
                'certifications.status',
                [
                    CertificationStatus::PENDING_SELF_TEAM_FORMATION,
                    CertificationStatus::COMPLETED,
                ]
            ])
            ->all();

        $self_team_member_completed = (clone $base_query)
            ->andWhere(['certifications.status' => CertificationStatus::COMPLETED])
            ->all();

        return $this->render('index', [
            'self_team_member_request' => $self_team_member_request,
            'self_team_member_uncompleted' => $self_team_member_uncompleted,
            'self_team_member_completed' => $self_team_member_completed,
        ]);
    }

    public function actionSetuju(int $self_team_member_id)
    {
        try {
            $member = $this->findPendingSelfTeamMemberOrFail($self_team_member_id);
            $member->approveRequest()->save(false);

            Yii::$app->session->setFlash('success', 'Berhasil menyetujui permintaan bergabung Tim Mandiri');
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

    public function actionTolak(int $self_team_member_id)
    {
        try {
            $member = $this->findPendingSelfTeamMemberOrFail($self_team_member_id);
            $member->rejectRequest()->save(false);

            Yii::$app->session->setFlash('success', 'Berhasil menolak permintaan bergabung Tim Mandiri');
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

    public function actionSelfReview(int $certification_id, int $page = 1)
    {
        try {
            $member = $this->checkSelfReviewPermission($certification_id);
            $certification = $this->findCertificationOrFail($certification_id);

            $root_groups = $certification->assessment->getAllRootGroups();
            $current_root_group = $certification->assessment
                ->getCurrentRootGroupOrFail($page, $root_groups);
            $current_child_group = $certification->assessment
                ->getCurrentChildGroups($current_root_group, $certification_id);

            return $this->render('selfReview', [
                'is_leader' => $member->role === TeamRole::LEADER,
                'saspri_k' => $certification->saspriK,
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
                    $error instanceof ForbiddenHttpException ||
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

    public function actionSimpanSementaraSelfReview(int $certification_id, int $page = 1)
    {
        try {
            $this->checkSelfReviewPermission($certification_id);
            $this->findCertificationOrFail($certification_id)
                ->saveSelfReviewScores(Yii::$app->request->post('indicator_scores', []));

            Yii::$app->session->setFlash('success', 'Perubahan berhasil disimpan sementara');
            $targetPage = Yii::$app->request->post('target_page', $page);
            return $this->redirect([
                'self-review', 
                'certification_id' => $certification_id,
                'page' => $targetPage,
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof BadRequestHttpException) {
                    return $this->redirect([
                        'self-review', 
                        'certification_id' => $certification_id, 
                        'page' => $page
                    ]);
                } else if (
                    $error instanceof ForbiddenHttpException ||
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionFinalisasiSelfReview(int $certification_id)
    {
        try {
            $member = $this->checkSelfReviewPermission($certification_id);
            TeamHelper::isMemberALeader($member);

            $this->findCertificationOrFail($certification_id)
                ->saveSelfReviewScores(Yii::$app->request->post('indicator_scores'))
                ->submitSelfReview()
                ->save(false);

            Yii::$app->session->setFlash('success', 'Self Review berhasil difinalisasi');
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof BadRequestHttpException ||
                    (
                        $error instanceof UnprocessableEntityHttpException && 
                        str_contains($error->getMessage(), 'ketua')
                    )
                ) {
                    return $this->redirect([
                        'self-review', 
                        'certification_id' => $certification_id, 
                        'page' => 1
                    ]);
                } else if (
                    $error instanceof ForbiddenHttpException ||
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    private function findPendingSelfTeamMemberOrFail(int $self_team_member_id): SelfTeamMember
    {
        $member = SelfTeamMember::find()
            ->joinWith('certification')
            ->where([
                'self_team_members.id' => $self_team_member_id,
                'self_team_members.user_id' => Yii::$app->user->id,
            ])
            ->one();

        if (!$member) {
            throw new NotFoundHttpException('Data tidak ditemukan atau Anda bukan anggota Tim Mandiri ini');
        }
        if ($member->certification->status !== CertificationStatus::PENDING_SELF_TEAM_FORMATION) {
            throw new UnprocessableEntityHttpException('Permintaan sudah tidak dapat diubah karena status sertifikasi sudah berjalan');
        }
        if ($member->status !== ApprovalStatus::PENDING) {
            throw new UnprocessableEntityHttpException('Permintaan ini sudah direspon sebelumnya');
        }

        return $member;
    }

    private function checkSelfReviewPermission(int $certification_id): SelfTeamMember
    {
        $member = SelfTeamMember::find()
            ->where([
                'certification_id' => $certification_id,
                'user_id' => Yii::$app->user->id,
                'status' => ApprovalStatus::APPROVED,
            ])
            ->one();
        if (!$member) {
            throw new ForbiddenHttpException('Akses ditolak karena Anda bukan anggota dari Tim Mandiri');
        }
        return $member;
    }

    protected function findCertificationOrFail(int $certification_id): Certification
    {
        $certification = Certification::findOne($certification_id);
        if (!$certification) {
            throw new NotFoundHttpException('Sertifikasi tidak ditemukan');
        }
        if ($certification->status !== CertificationStatus::SELF_REVIEW) {
            throw new UnprocessableEntityHttpException(
                'Sertifikasi tidak dalam tahap ' . CertificationStatus::list()[CertificationStatus::SELF_REVIEW]
            );
        }
        return $certification;
    }    
}
