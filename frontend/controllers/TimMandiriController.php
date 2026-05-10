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

    private function saveScores(int $certificationId, array $postData)
    {
        // 1. Cek status certification
        $certification = Certification::findOne($certificationId);
        if (!$certification || $certification->status !== CertificationStatus::SELF_REVIEW) {
            throw new UnprocessableEntityHttpException('Sertifikasi tidak dalam tahap self review');
        }

        foreach ($postData as $indicatorId => $data) {
            $score = IndicatorScore::find()
                ->where(['certification_id' => $certificationId, 'indicator_id' => $indicatorId])
                ->one() ?: new IndicatorScore([
                    'certification_id' => $certificationId,
                    'indicator_id' => $indicatorId
                ]);

            // 2. Simpan score setiap indicator
            if (isset($data['self_team_score']) && $data['self_team_score'] !== "") {
                $val = (int) $data['self_team_score'];
                // Rentang penilaian minimal 0 dan maksimal 100
                if ($val < 0 || $val > 100) {
                    throw new BadRequestHttpException('Terdapat penilaian yang di luar rentang 0-100');
                }
                $score->self_team_score = $val;
            }

            // 3. Simpan evidence setiap indicator
            $file = UploadedFile::getInstanceByName("IndicatorScore[$indicatorId][evidence]");
            if ($file) {
                $relativeDir = '/uploads/evidence/' . $certificationId;
                $absoluteDir = Yii::getAlias('@frontend/web' . $relativeDir);
                if (!is_dir($absoluteDir)) {
                    mkdir($absoluteDir, 0777, true);
                }

                if ($score->evidence_url) {
                    $oldFile = Yii::getAlias('@frontend/web' . $score->evidence_url);
                    if (is_file($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $fileName = 'self_' . $indicatorId . '_' . time() . '.' . $file->extension;
                if ($file->saveAs($absoluteDir . '/' . $fileName)) {
                    $score->evidence_url = $relativeDir . '/' . $fileName;
                }
            }

            // 4. Save semua perubahan terkait indicator
            $score->save(false);
        }

        return $certification;
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
            // 1. Check permission
            $this->checkSelfReviewPermission($id);

            /** @var Certification $certification*/
            $certification = Certification::find()
                ->where(['id' => $id])
                ->with(['assessment', 'assessment.indicators'])
                ->one();
            if (!$certification) {
                throw new NotFoundHttpException('Sertifikasi tidak ditemukan');
            }

            // 2. Identify top level groups for pagination
            // Get all indicators in the assessment
            $assessmentIndicators = $certification->assessment->indicators;
            $indicatorIds = array_map(fn ($i) => $i->id, $assessmentIndicators);

            // Get all groups that have these indicators
            $groupIds = array_unique(array_map(fn ($i) => $i->indicator_group_id, $assessmentIndicators));

            // Find all ancestor groups to find the root groups
            $allGroupIds = [];
            $currentGroups = IndicatorGroup::findAll($groupIds);
            while (!empty($currentGroups)) {
                $nextGroups = [];
                foreach ($currentGroups as $group) {
                    $allGroupIds[] = $group->id;
                    if ($group->parent_group_id && !in_array($group->parent_group_id, $allGroupIds)) {
                        $parent = IndicatorGroup::findOne($group->parent_group_id);
                        if ($parent) {
                            $nextGroups[] = $parent;
                        }
                    }
                }
                $currentGroups = $nextGroups;
            }
            $allGroupIds = array_unique($allGroupIds);

            // Top level groups (roots)
            $rootGroups = IndicatorGroup::find()
                ->where(['id' => $allGroupIds, 'parent_group_id' => null])
                ->orderBy(['order' => SORT_ASC])
                ->all();

            if (empty($rootGroups)) {
                throw new UnprocessableEntityHttpException('Assessment tidak memiliki grup indikator');
            }

            // Total pages = count of root groups
            $totalPages = count($rootGroups);
            if ($page < 1 || $page > $totalPages) {
                $page = 1;
            }

            $currentRootGroup = $rootGroups[$page - 1];

            // 3. Fetch data for the current root group
            // All descendant groups of the current root group that are in allGroupIds
            $descendantGroupIds = $this->getDescendantGroupIds($currentRootGroup->id, $allGroupIds);

            // Indicators in the current root group or its descendants
            $indicators = Indicator::find()
                ->where(['indicator_group_id' => $descendantGroupIds, 'id' => $indicatorIds])
                ->with(['indicatorOptions', 'indicatorGroup'])
                ->orderBy(['order' => SORT_ASC])
                ->all();

            // 4. Fetch existing scores
            $scores = IndicatorScore::find()
                ->where(['certification_id' => $id, 'indicator_id' => $indicatorIds])
                ->indexBy('indicator_id')
                ->all();

            return $this->render('self-review', [
                'certification' => $certification,
                'rootGroups' => $rootGroups,
                'currentRootGroup' => $currentRootGroup,
                'indicators' => $indicators,
                'scores' => $scores,
                'page' => $page,
                'totalPages' => $totalPages,
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
            // 1. Hanya leader yang boleh finalisasi
            if ($member->role !== TeamRole::LEADER) {
                throw new ForbiddenHttpException('Hanya ketua tim yang dapat melakukan finalisasi');
            }

            // 2. Simpan score saat ini
            $certification = $this->saveScores($id, Yii::$app->request->post('IndicatorScore', []));

            // 3. Seluruh indikator wajib diisi
            $indicatorIds = array_map(fn ($i) => $i->id, $certification->assessment->indicators);
            $existingScores = IndicatorScore::find()
                ->where(['certification_id' => $id, 'indicator_id' => $indicatorIds])
                ->indexBy('indicator_id')
                ->all();
            foreach ($indicatorIds as $reqId) {
                if (!isset($existingScores[$reqId]) || !$existingScores[$reqId]->self_team_score) {
                    throw new UnprocessableEntityHttpException(
                        'Seluruh indikator wajib diberikan penilaian sebelum finalisasi'
                    );
                }
            }

            // 4. Update Status ke fase selanjutnya
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
}
