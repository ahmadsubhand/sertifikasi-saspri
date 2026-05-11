<?php

namespace frontend\controllers;

use common\controllers\AssessmentReviewTrait;
use common\enums\ApprovalStatus;
use common\enums\CertificationStatus;
use common\enums\IndicatorStatus;
use common\enums\TeamRole;
use common\enums\UserRole;
use common\models\Certification;
use common\models\IndicatorScore;
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
            ->with('certification.saspriK.district');

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

    public function actionSetuju(int $id)
    {
        try {
            $member = $this->checkApprovalPermission($id);
            $member->status = ApprovalStatus::APPROVED;
            $member->save(false);

            Yii::$app->session->setFlash('success', 'Berhasil menyetujui permintaan bergabung Tim Sebaya');
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                return $this->redirect(['index']);
            }
            throw $error;
        }
    }

    public function actionTolak(int $id)
    {
        try {
            $member = $this->checkApprovalPermission($id);
            $member->status = ApprovalStatus::REJECTED;
            $member->save(false);

            Yii::$app->session->setFlash('success', 'Berhasil menolak permintaan bergabung Tim Sebaya');
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                return $this->redirect(['index']);
            }
            throw $error;
        }
    }

    public function actionPeerReview(int $id, int $page = 1)
    {
        try {
            $this->checkPeerReviewPermission($id);
            $certification = $this->findCertification($id);
            $assessmentData = $this->prepareAssessmentGroups($certification, $page);
            $indicators = $this->findIndicatorsByRootGroup(
                $assessmentData['currentRootGroup'],
                $assessmentData['indicatorIds'],
                $assessmentData['allGroupIds'],
            );
            $scores = $this->findIndicatorScores($id, $assessmentData['indicatorIds']);
            return $this->render('peer-review', [
                'certification' => $certification,
                'rootGroups' => $assessmentData['rootGroups'],
                'currentRootGroup' => $assessmentData['currentRootGroup'],
                'indicators' => $indicators,
                'scores' => $scores,
                'page' => $assessmentData['page'],
                'totalPages' => $assessmentData['totalPages'],
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                return $this->redirect(['index']);
            }
            throw $error;
        }
    }

    public function actionSimpanSementaraPeerReview(int $id, int $page = 1)
    {
        try {
            $this->checkPeerReviewPermission($id);
            $this->saveScores($id, Yii::$app->request->post('IndicatorScore', []));

            Yii::$app->session->setFlash('success', 'Perubahan berhasil disimpan sementara');
            $targetPage = Yii::$app->request->post('target_page', $page);
            return $this->redirect(['peer-review', 'id' => $id, 'page' => $targetPage]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof ForbiddenHttpException) {
                    return $this->redirect(['index']);
                } elseif (
                    $error instanceof UnprocessableEntityHttpException ||
                    $error instanceof BadRequestHttpException
                ) {
                    return $this->redirect(['peer-review', 'id' => $id, 'page' => $page]);
                }
            }
            throw $error;
        }
    }

    public function actionFinalisasiPeerReview(int $id)
    {
        try {
            $member = $this->checkPeerReviewPermission($id);
            $this->isMemberALeader($member);

            $certification = $this->saveScores($id, Yii::$app->request->post('IndicatorScore', []));
            $this->areAllIndicatorsFilledIn($certification);

            $certification->status = CertificationStatus::EXTERNAL_REVIEW;
            $certification->save(false);
            Yii::$app->session->setFlash('success', 'Peer Review berhasil difinalisasi');
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof ForbiddenHttpException) {
                    return $this->redirect(['index']);
                } elseif (
                    $error instanceof UnprocessableEntityHttpException ||
                    $error instanceof BadRequestHttpException
                ) {
                    return $this->redirect(['peer-review', 'id' => $id, 'page' => Yii::$app->request->get('page', 1)]);
                }
            }
            throw $error;
        }
    }

    private function checkApprovalPermission(int $id): PeerTeamMember
    {
        $member = PeerTeamMember::find()
            ->joinWith('certification')
            ->where([
                'peer_team_members.id' => $id,
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

    private function checkPeerReviewPermission(int $id): PeerTeamMember
    {
        $member = PeerTeamMember::find()
            ->where([
                'certification_id' => $id,
                'user_id' => Yii::$app->user->id,
                'status' => ApprovalStatus::APPROVED,
            ])
            ->one();
        if (!$member) {
            throw new ForbiddenHttpException('Akses ditolak karena Anda bukan anggota dari Tim Sebaya');
        }
        return $member;
    }

    private function saveScores(int $certificationId, array $postData): Certification
    {
        $certification = $this->findCertification($certificationId);
        $this->checkCertificationStatusIsPeerReview($certification);

        foreach ($postData as $indicatorId => $data) {
            $score = $this->findOrCreateIndicatorScore(
                $certificationId,
                $indicatorId,
            );

            $this->fillPeerTeamScore($score, $data);
            $this->fillPeerTeamStatus($score, $data);
            $score->save(false);
        }

        return $certification;
    }

    private function isMemberALeader(PeerTeamMember $member) 
    {
        if ($member->role !== TeamRole::LEADER) {
            throw new UnprocessableEntityHttpException('Hanya ketua tim yang dapat melakukan finalisasi');
        }
    }

    private function areAllIndicatorsFilledIn(Certification $certification) 
    {
        $indicatorIds = array_map(fn ($i) => $i->id, $certification->assessment->indicators);
        $existingScores = IndicatorScore::find()
            ->where(['certification_id' => $certification->id, 'indicator_id' => $indicatorIds])
            ->indexBy('indicator_id')
            ->all();
        foreach ($indicatorIds as $reqId) {
            if (!isset($existingScores[$reqId]) || !$existingScores[$reqId]->peer_team_score || !$existingScores[$reqId]->status) {
                throw new UnprocessableEntityHttpException(
                    'Seluruh indikator wajib diberikan penilaian dan status sebelum finalisasi'
                );
            }
        }
    }

    private function fillPeerTeamStatus(
        IndicatorScore $score,
        array $data,
    ): void {
        if (!isset($data['status']) || empty($data['status'])) {
            $score->status = null;
        } else {
            $status = $data['status'];
            if (!in_array($status, IndicatorStatus::values())) {
                throw new BadRequestHttpException('Status penilaian tidak valid' . $data['status']);
            }
    
            if ($score->peer_team_score === $score->self_team_score) {
                if ($status !== IndicatorStatus::IDENTICAL) {
                    throw new BadRequestHttpException('Status harus Identical jika skor sama');
                }
            } else {
                if ($status === IndicatorStatus::IDENTICAL) {
                    throw new BadRequestHttpException('Status tidak boleh Identical jika skor berbeda');
                }
            }
    
            $score->status = $status;
        }
    }

    private function checkCertificationStatusIsPeerReview(Certification $certification)
    {
        if (
            !$certification ||
            $certification->status !== CertificationStatus::PEER_REVIEW
        ) {
            throw new UnprocessableEntityHttpException(
                'Sertifikasi tidak dalam tahap peer review'
            );
        }
    }

    private function fillPeerTeamScore(
        IndicatorScore $score,
        array $data,
    ): void {
        if (!isset($data['peer_team_score'])) {
            return;
        }

        $value = (int) $data['peer_team_score'];

        if ($value < 0 || $value > 100) {
            throw new BadRequestHttpException(
                'Terdapat penilaian yang di luar rentang 0-100'
            );
        }

        $score->peer_team_score = $value;
    }
}