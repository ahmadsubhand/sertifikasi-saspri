<?php

namespace frontend\controllers;

use common\enums\ApprovalStatus;
use common\enums\CertificationStatus;
use common\enums\UserRole;
use common\enums\TeamRole;
use common\models\Certification;
use common\models\Indicator;
use common\models\IndicatorGroup;
use common\models\IndicatorScore;
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
use yii\web\UploadedFile;

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
            ->where(['self_team_members.user_id' => Yii::$app->user->id])
            ->with('certification.saspriK.district');

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

    public function actionSetuju(int $id)
    {
        try {
            $member = $this->checkApprovalPermission($id);
            $member->status = ApprovalStatus::APPROVED;
            $member->save(false);

            Yii::$app->session->setFlash('success', 'Berhasil menyetujui permintaan bergabung Tim Mandiri');
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

            Yii::$app->session->setFlash('success', 'Berhasil menolak permintaan bergabung Tim Mandiri');
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                return $this->redirect(['index']);
            }
            throw $error;
        }
    }

    public function actionSelfReview(int $id, int $page = 1)
    {
        try {
            $this->checkSelfReviewPermission($id);
            $certification = $this->findCertification($id);
            $assessmentData = $this->prepareAssessmentGroups($certification, $page);
            $indicators = $this->findIndicatorsByRootGroup(
                $assessmentData['currentRootGroup'],
                $assessmentData['indicatorIds'],
                $assessmentData['allGroupIds'],
            );
            $scores = $this->findIndicatorScores($id, $assessmentData['indicatorIds']);
            return $this->render('self-review', [
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

    public function actionSimpanSementaraSelfReview(int $id, int $page = 1)
    {
        try {
            $this->checkSelfReviewPermission($id);
            $this->saveScores($id, Yii::$app->request->post('IndicatorScore', []));

            Yii::$app->session->setFlash('success', 'Perubahan berhasil disimpan sementara');
            $targetPage = Yii::$app->request->post('target_page', $page);
            return $this->redirect(['self-review', 'id' => $id, 'page' => $targetPage]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof ForbiddenHttpException) {
                    return $this->redirect(['index']);
                } elseif (
                    $error instanceof UnprocessableEntityHttpException ||
                    $error instanceof BadRequestHttpException
                ) {
                    // Redirect back to the same page if validation fails
                    return $this->redirect(['self-review', 'id' => $id, 'page' => $page]);
                }
            }
            throw $error;
        }
    }

    public function actionFinalisasiSelfReview(int $id)
    {
        try {
            $member = $this->checkSelfReviewPermission($id);
            $this->isMemberALeader($member);

            $certification = $this->saveScores($id, Yii::$app->request->post('IndicatorScore', []));
            $this->areAllIndicatorsFilledIn($certification);

            $certification->status = CertificationStatus::PENDING_PEER_TEAM_FORMATION;
            $certification->save(false);
            Yii::$app->session->setFlash('success', 'Self Review berhasil difinalisasi.');
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
                    return $this->redirect(['self-review', 'id' => $id, 'page' => Yii::$app->request->get('page', 1)]);
                }
            }
            throw $error;
        }
    }

    private function checkApprovalPermission(int $id): SelfTeamMember
    {
        $member = SelfTeamMember::find()
            ->joinWith('certification')
            ->where([
                'self_team_members.id' => $id,
                'self_team_members.user_id' => Yii::$app->user->id,
            ])
            ->one();

        if (!$member) {
            throw new NotFoundHttpException('Data tidak ditemukan atau Anda bukan anggota tim ini');
        }
        if ($member->certification->status !== CertificationStatus::PENDING_SELF_TEAM_FORMATION) {
            throw new UnprocessableEntityHttpException('Permintaan sudah tidak dapat diubah karena status sertifikasi sudah berjalan');
        }
        if ($member->status !== ApprovalStatus::PENDING) {
            throw new UnprocessableEntityHttpException('Permintaan ini sudah direspon sebelumnya');
        }

        return $member;
    }

    private function checkSelfReviewPermission(int $id): SelfTeamMember
    {
        $member = SelfTeamMember::find()
            ->where([
                'certification_id' => $id,
                'user_id' => Yii::$app->user->id,
                'status' => ApprovalStatus::APPROVED,
            ])
            ->one();
        if (!$member) {
            throw new ForbiddenHttpException('Akses ditolak karena Anda bukan anggota dari Tim Mandiri');
        }
        return $member;
    }

    private function findCertification(int $id): Certification
    {
        $certification = Certification::findOne($id);
        if (!$certification) {
            throw new NotFoundHttpException('Sertifikasi tidak ditemukan');
        }
        return $certification;
    }

    private function prepareAssessmentGroups(Certification $certification, int $page): array
    {
        $assessmentIndicators = $certification->assessment->indicators;

        $indicatorIds = array_map(
            fn ($indicator) => $indicator->id,
            $assessmentIndicators,
        );

        $groupIds = array_unique(array_map(
            fn ($indicator) => $indicator->indicator_group_id,
            $assessmentIndicators,
        ));

        $allGroupIds = $this->findAllAncestorGroupIds($groupIds);

        $rootGroups = IndicatorGroup::find()
            ->where([
                'id' => $allGroupIds,
                'parent_group_id' => null,
            ])
            ->orderBy(['order' => SORT_ASC])
            ->all();

        if (empty($rootGroups)) {
            throw new UnprocessableEntityHttpException(
                'Assessment tidak memiliki grup indikator'
            );
        }

        $totalPages = count($rootGroups);

        if ($page < 1 || $page > $totalPages) {
            $page = 1;
        }

        return [
            'indicatorIds' => $indicatorIds,
            'allGroupIds' => $allGroupIds,
            'rootGroups' => $rootGroups,
            'currentRootGroup' => $rootGroups[$page - 1],
            'page' => $page,
            'totalPages' => $totalPages,
        ];
    }

    private function findAllAncestorGroupIds(array $groupIds): array
    {
        $allGroupIds = [];

        $currentGroups = IndicatorGroup::findAll($groupIds);

        while (!empty($currentGroups)) {
            $nextGroups = [];

            foreach ($currentGroups as $group) {
                $allGroupIds[] = $group->id;

                if (
                    $group->parent_group_id &&
                    !in_array($group->parent_group_id, $allGroupIds)
                ) {
                    $parent = IndicatorGroup::findOne($group->parent_group_id);

                    if ($parent) {
                        $nextGroups[] = $parent;
                    }
                }
            }

            $currentGroups = $nextGroups;
        }

        return array_unique($allGroupIds);
    }

    private function findIndicatorsByRootGroup(
        IndicatorGroup $rootGroup,
        array $indicatorIds,
        array $allGroupIds,
    ): array {
        $descendantGroupIds = $this->getDescendantGroupIds(
            $rootGroup->id,
            $allGroupIds,
        );

        return Indicator::find()
            ->where([
                'indicator_group_id' => $descendantGroupIds,
                'id' => $indicatorIds,
            ])
            ->with([
                'indicatorOptions',
                'indicatorGroup',
            ])
            ->orderBy(['order' => SORT_ASC])
            ->all();
    }

    private function getDescendantGroupIds(int $parentId, array $allowedGroupIds): array
    {
        $ids = [$parentId];
        $children = IndicatorGroup::find()
            ->where(['parent_group_id' => $parentId, 'id' => $allowedGroupIds])
            ->all();

        foreach ($children as $child) {
            $ids = array_merge($ids, $this->getDescendantGroupIds($child->id, $allowedGroupIds));
        }
        return array_unique($ids);
    }

    private function findIndicatorScores(
        int $certificationId,
        array $indicatorIds,
    ): array {
        return IndicatorScore::find()
            ->where([
                'certification_id' => $certificationId,
                'indicator_id' => $indicatorIds,
            ])
            ->indexBy('indicator_id')
            ->all();
    }

    private function saveScores(int $certificationId, array $postData): Certification
    {
        $certification = $this->findCertification($certificationId);
        $this->checkCertificationStatusIsSelfReview($certification);

        foreach ($postData as $indicatorId => $data) {
            $score = $this->findOrCreateIndicatorScore(
                $certificationId,
                $indicatorId,
            );

            $this->fillSelfTeamScore($score, $data);

            $this->handleEvidenceUpload(
                $score,
                $certificationId,
                $indicatorId,
            );

            $score->save(false);
        }

        return $certification;
    }

    private function isMemberALeader(SelfTeamMember $member) 
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
            if (!isset($existingScores[$reqId]) || !$existingScores[$reqId]->self_team_score) {
                throw new UnprocessableEntityHttpException(
                    'Seluruh indikator wajib diberikan penilaian sebelum finalisasi'
                );
            }
        }
    }

    private function checkCertificationStatusIsSelfReview(Certification $certification)
    {
        if (
            !$certification ||
            $certification->status !== CertificationStatus::SELF_REVIEW
        ) {
            throw new UnprocessableEntityHttpException(
                'Sertifikasi tidak dalam tahap self review'
            );
        }
    }

    private function findOrCreateIndicatorScore(
        int $certificationId,
        int $indicatorId,
    ): IndicatorScore {
        return IndicatorScore::find()
            ->where([
                'certification_id' => $certificationId,
                'indicator_id' => $indicatorId,
            ])
            ->one()
            ?? new IndicatorScore([
                'certification_id' => $certificationId,
                'indicator_id' => $indicatorId,
            ]);
    }

    private function fillSelfTeamScore(
        IndicatorScore $score,
        array $data,
    ): void {
        if (!isset($data['self_team_score'])) {
            return;
        }

        $value = (int) $data['self_team_score'];

        if ($value < 0 || $value > 100) {
            throw new BadRequestHttpException(
                'Terdapat penilaian yang di luar rentang 0-100'
            );
        }

        $score->self_team_score = $value;
    }

    private function handleEvidenceUpload(
        IndicatorScore $score,
        int $certificationId,
        int $indicatorId,
    ): void {
        $file = UploadedFile::getInstanceByName(
            "IndicatorScore[$indicatorId][evidence]"
        );

        if (!$file) {
            return;
        }

        $relativeDir = '/uploads/evidence/' . $certificationId;
        $absoluteDir = Yii::getAlias('@frontend/web' . $relativeDir);

        $this->ensureDirectoryExists($absoluteDir);

        $this->deleteOldEvidence($score);

        $fileName = $this->generateEvidenceFileName(
            $indicatorId,
            $file->extension,
        );

        if ($file->saveAs($absoluteDir . '/' . $fileName)) {
            $score->evidence_url = $relativeDir . '/' . $fileName;
        }
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    private function deleteOldEvidence(IndicatorScore $score): void
    {
        if (!$score->evidence_url) {
            return;
        }

        $oldFile = Yii::getAlias('@frontend/web' . $score->evidence_url);

        if (is_file($oldFile)) {
            unlink($oldFile);
        }
    }

    private function generateEvidenceFileName(
        int $indicatorId,
        string $extension,
    ): string {
        return sprintf(
            'self_%d_%d.%s',
            $indicatorId,
            time(),
            $extension,
        );
    }
}
