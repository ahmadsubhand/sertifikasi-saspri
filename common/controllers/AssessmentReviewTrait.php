<?php

namespace common\controllers;

use common\models\Certification;
use common\models\Indicator;
use common\models\IndicatorGroup;
use common\models\IndicatorScore;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

trait AssessmentReviewTrait
{
    protected function findCertification(int $id): Certification
    {
        $certification = Certification::findOne($id);
        if (!$certification) {
            throw new NotFoundHttpException('Sertifikasi tidak ditemukan');
        }
        return $certification;
    }

    protected function prepareAssessmentGroups(Certification $certification, int $page): array
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

    protected function findAllAncestorGroupIds(array $groupIds): array
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

    protected function findIndicatorsByRootGroup(
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

    protected function getDescendantGroupIds(int $parentId, array $allowedGroupIds): array
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

    protected function findIndicatorScores(
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

    protected function findOrCreateIndicatorScore(
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

    protected function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    protected function deleteOldEvidence(IndicatorScore $score): void
    {
        if (!$score->evidence_url) {
            return;
        }

        $oldFile = Yii::getAlias('@frontend/web' . $score->evidence_url);

        if (is_file($oldFile)) {
            unlink($oldFile);
        }
    }
}
