<?php

namespace frontend\controllers;

use common\controllers\AssessmentReviewTrait;
use common\enums\ApprovalStatus;
use common\enums\CertificationStatus;
use common\enums\TeamRole;
use common\enums\UserRole;
use common\helpers\TeamHelper;
use common\models\Certification;
use common\models\PeerTeamMember;
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

class TimSebayaController extends Controller
{
    use AssessmentReviewTrait;

    public function behaviors()
    {
        return [
          'access' => [
            'class' => AccessControl::class,
            'rules' => [
              [
                'allow' => true,
                'roles' => [UserRole::USER, UserRole::COORDINATOR],
              ]
            ]
          ],
          'verbs' => [
            'class' => VerbFilter::class,
            'actions' => [
              'setuju' => ['post'],
              'tolak' => ['post'],
              'simpan-sementara-peer-review' => ['post'],
              'finalisasi-peer-review' => ['post'],
            ],
          ],
        ];
    }

    public function actionIndex()
    {
        $base_query = PeerTeamMember::find()
            ->joinWith('certification')
            ->where(['peer_team_members.user_id' => Yii::$app->user->id])
            ->with('certification.saspriK');

        $peer_team_member_request = (clone $base_query)
            ->andWhere(['certifications.status' => CertificationStatus::PENDING_PEER_TEAM_FORMATION])
            ->all();

        $peer_team_member_uncompleted = (clone $base_query)
            ->andWhere([
                'not in',
                'certifications.status',
                [
                    CertificationStatus::PENDING_PEER_TEAM_FORMATION,
                    CertificationStatus::COMPLETED,
                ]
            ])
            ->all();

        $peer_team_member_completed = (clone $base_query)
            ->andWhere(['certifications.status' => CertificationStatus::COMPLETED])
            ->all();

        return $this->render('index', [
            'peer_team_member_request' => $peer_team_member_request,
            'peer_team_member_uncompleted' => $peer_team_member_uncompleted,
            'peer_team_member_completed' => $peer_team_member_completed,
        ]);
    }

    public function actionSetuju(int $peer_team_member_id)
    {
        try {
            $member = $this->findPendingPeerTeamMemberOrFail($peer_team_member_id);
            $member->approveRequest()->save(false);

            Yii::$app->session->setFlash('success', 'Berhasil menyetujui permintaan bergabung Tim Sebaya');
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

    public function actionTolak(int $peer_team_member_id)
    {
        try {
            $member = $this->findPendingPeerTeamMemberOrFail($peer_team_member_id);
            $member->rejectRequest()->save(false);

            Yii::$app->session->setFlash('success', 'Berhasil menolak permintaan bergabung Tim Sebaya');
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

    public function actionPeerReview(int $certification_id, int $page = 1)
    {
        try {
            $member = $this->checkPeerReviewPermission($certification_id);
            $certification = $this->findCertificationOrFail($certification_id);

            $root_groups = $certification->assessment->getAllRootGroups();
            $current_root_group = $certification->assessment
                ->getCurrentRootGroupOrFail($page, $root_groups);
            $current_child_group = $certification->assessment
                ->getCurrentChildGroups($current_root_group, $certification_id);

            return $this->render('peerReview', [
                'is_leader' => $member->role === TeamRole::LEADER,
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

    public function actionSimpanSementaraPeerReview(int $certification_id, int $page = 1)
    {
        try {
            $this->checkPeerReviewPermission($certification_id);
            $this->findCertificationOrFail($certification_id)
                ->savePeerReviewScores(Yii::$app->request->post('indicator_scores', []));

            Yii::$app->session->setFlash('success', 'Perubahan berhasil disimpan sementara');
            $targetPage = Yii::$app->request->post('target_page', $page);
            return $this->redirect([
                'peer-review', 
                'certification_id' => $certification_id,
                'page' => $targetPage,
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof BadRequestHttpException) {
                    return $this->redirect([
                        'peer-review', 
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

    public function actionFinalisasiPeerReview(int $certification_id)
    {
        try {
            $member = $this->checkPeerReviewPermission($certification_id);
            TeamHelper::isMemberALeader($member);

            $this->findCertificationOrFail($certification_id)
                ->savePeerReviewScores(Yii::$app->request->post('indicator_scores', []))
                ->submitPeerReview()
                ->save(false);

            Yii::$app->session->setFlash('success', 'Peer Review berhasil difinalisasi');
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
                        'peer-review', 
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

    private function findPendingPeerTeamMemberOrFail(int $peer_team_member_id): PeerTeamMember
    {
        $member = PeerTeamMember::find()
            ->joinWith('certification')
            ->where([
                'peer_team_members.id' => $peer_team_member_id,
                'peer_team_members.user_id' => Yii::$app->user->id,
            ])
            ->one();

        if (!$member) {
            throw new NotFoundHttpException('Data tidak ditemukan atau Anda bukan anggota Tim Sebaya ini');
        }
        if ($member->certification->status !== CertificationStatus::PENDING_PEER_TEAM_FORMATION) {
            throw new UnprocessableEntityHttpException('Permintaan sudah tidak dapat diubah karena status sertifikasi sudah berjalan');
        }
        if ($member->status !== ApprovalStatus::PENDING) {
            throw new UnprocessableEntityHttpException('Permintaan ini sudah direspon sebelumnya');
        }

        return $member;
    }

    private function checkPeerReviewPermission(int $certification_id): PeerTeamMember
    {
        $member = PeerTeamMember::find()
            ->where([
                'certification_id' => $certification_id,
                'user_id' => Yii::$app->user->id,
                'status' => ApprovalStatus::APPROVED,
            ])
            ->one();
        if (!$member) {
            throw new ForbiddenHttpException('Akses ditolak karena Anda bukan anggota dari Tim Sebaya');
        }
        return $member;
    }

    protected function findCertificationOrFail(int $certification_id): Certification
    {
        $certification = Certification::findOne($certification_id);
        if (!$certification) {
            throw new NotFoundHttpException('Sertifikasi tidak ditemukan');
        }
        if ($certification->status !== CertificationStatus::PEER_REVIEW) {
            throw new UnprocessableEntityHttpException(
                'Sertifikasi tidak dalam tahap peer review'
            );
        }
        return $certification;
    }    
}
