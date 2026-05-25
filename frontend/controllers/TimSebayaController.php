<?php

namespace frontend\controllers;

use common\enums\ApprovalStatus;
use common\enums\CertificationStatus;
use common\enums\RequestResponse;
use common\enums\TeamRole;
use common\enums\UserRole;
use common\helpers\CertificationHelper;
use common\helpers\TeamHelper;
use common\helpers\UserHelper;
use common\models\Certification;
use common\models\form\PeerReviewForm;
use common\models\form\RequestResponseForm;
use common\models\PeerTeamMember;
use common\services\CertificationService;
use common\services\PeerTeamMemberService;
use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class TimSebayaController extends Controller
{
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

    public function actionIndex(
        ?int $limit = 10,
        ?int $offset_request = 0,
        ?int $offset_uncompleted = 0,
        ?int $offset_completed = 0,
    ) {
        $base_query = PeerTeamMember::find()
            ->alias('ptm')
            ->joinWith('certification c')
            ->joinWith('certification.saspriK')
            ->where(['user_id' => Yii::$app->user->id]);

        $requests = (clone $base_query)
            ->andWhere(['c.status' => CertificationStatus::PENDING_PEER_TEAM_FORMATION])
            ->orderBy(['c.peer_team_due_date' => SORT_ASC])
            ->limit($limit + 1)
            ->offset($offset_request)
            ->all();
        $request_has_next = count($requests) > $limit;
        if ($request_has_next) array_pop($requests);

        $uncompleted = (clone $base_query)
            ->andWhere(['ptm.status' => ApprovalStatus::APPROVED])
            ->andWhere([
                'not in',
                'c.status',
                [
                    CertificationStatus::PENDING_PEER_TEAM_FORMATION,
                    CertificationStatus::COMPLETED,
                ]
            ])
            ->orderBy(['c.peer_review_due_date' => SORT_ASC])
            ->limit($limit + 1)
            ->offset($offset_uncompleted)
            ->all();
        $uncompleted_has_next = count($uncompleted) > $limit;
        if ($uncompleted_has_next) array_pop($uncompleted);

        $completed = (clone $base_query)
            ->andWhere(['ptm.status' => ApprovalStatus::APPROVED])
            ->andWhere(['c.status' => CertificationStatus::COMPLETED])
            ->orderBy(['c.issued_at' => SORT_DESC])
            ->limit($limit + 1)
            ->offset($offset_completed)
            ->all();
        $completed_has_next = count($completed) > $limit;
        if ($completed_has_next) array_pop($completed);

        return $this->render('index', [
            'peer_team_member_request' => $requests,
            'request_prev_link' => $offset_request > 0 ? Url::current(['offset_request' => max(0, $offset_request - $limit)]) : null,
            'request_next_link' => $request_has_next ? Url::current(['offset_request' => $offset_request + $limit]) : null,
            'offset_request' => $offset_request,

            'peer_team_member_uncompleted' => $uncompleted,
            'uncompleted_prev_link' => $offset_uncompleted > 0 ? Url::current(['offset_uncompleted' => max(0, $offset_uncompleted - $limit)]) : null,
            'uncompleted_next_link' => $uncompleted_has_next ? Url::current(['offset_uncompleted' => $offset_uncompleted + $limit]) : null,
            'offset_uncompleted' => $offset_uncompleted,

            'peer_team_member_completed' => $completed,
            'completed_prev_link' => $offset_completed > 0 ? Url::current(['offset_completed' => max(0, $offset_completed - $limit)]) : null,
            'completed_next_link' => $completed_has_next ? Url::current(['offset_completed' => $offset_completed + $limit]) : null,
            'offset_completed' => $offset_completed,
            'limit' => $limit,
        ]);
    }

    public function actionTanggapiPermintaanBergabung(int $peer_team_member_id)
    {
        try {
            $data = new RequestResponseForm();
            $data->load(Yii::$app->request->post(), '');
            if ($data->validate()) {
                PeerTeamMemberService::joinRequestResponse($peer_team_member_id, $data);
            } else {
                throw new BadRequestHttpException($data->getFirstError('indicator_scores'));
            }

            Yii::$app->session->setFlash(
                'success', 
                'Berhasil ' . strtolower(RequestResponse::list()[$data->action]) .  ' permintaan bergabung Tim Sebaya'
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

    public function actionPeerReview(int $certification_id, int $page = 1)
    {
        try {
            $member = TeamHelper::checkPeerReviewPermission($certification_id);
            $certification = CertificationHelper::findCertificationOrFail(
                $certification_id, CertificationStatus::PEER_REVIEW
            );
            [
                'root_groups' => $root_groups,
                'current_root_group' => $current_root_group,
                'current_child_groups' => $current_child_groups
            ] = TeamHelper::getAllIndicators($certification, $page);

            return $this->render('peerReview', [
                'is_leader' => $member->role === TeamRole::LEADER,
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
            $data = new PeerReviewForm();
            $data->load(Yii::$app->request->post(), '');
            if ($data->validate()) {
                CertificationService::savePeerReview($certification_id, $data);
            } else {
                throw new BadRequestHttpException($data->getFirstError('indicator_scores'));
            }

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
                } elseif (
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
            $data = new PeerReviewForm();
            $data->load(Yii::$app->request->post(), '');
            if ($data->validate()) {
                CertificationService::finalizePeerReview($certification_id, $data);
            } else {
                throw new BadRequestHttpException($data->getFirstError('indicator_scores'));
            }

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
                } elseif (
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

    public function actionDetail(int $case_id)
    {
        try {
            $cert = Certification::findOne(['id' => $case_id]);
            if ($cert->status !== CertificationStatus::PENDING_PEER_TEAM_FORMATION) {
                TeamHelper::checkPeerReviewPermission($case_id);
            } else {
                $member = PeerTeamMember::find()
                    ->where([
                        'certification_id' => $case_id,
                        'user_id' => Yii::$app->user->id,
                    ])
                    ->exists();
                if (!$member) {
                    throw new ForbiddenHttpException('Akses ditolak karena Anda bukan anggota dari Tim Sebaya');
                }
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
                if (
                    $error instanceof ForbiddenHttpException ||
                    $error instanceof NotFoundHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }
}
