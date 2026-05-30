<?php

namespace common\services;

use common\enums\CertificateLevel;
use common\models\Assessment;
use common\models\form\ChangeLevelForm;
use common\models\form\CreateAssessmentForm;
use common\models\form\UpdateAssessmentTitleForm;
use Exception;
use yii\web\ConflictHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class AssessmentService
{
    public static function findOrFail(int $id): Assessment
    {
        $assessment = Assessment::findOne($id);
        if (!$assessment) {
            throw new NotFoundHttpException('Asesmen tidak ditemukan');
        }
        return $assessment;
    }

    public static function create(?int $assessment_id, ?CreateAssessmentForm $data): Assessment
    {
        $assessment = new Assessment();

        if ($assessment_id) {
            $cloned_assessment = self::findOrFail($assessment_id);
            $assessment = Assessment::clone($cloned_assessment);
        } else {
            $already_exists = Assessment::find()->where(['title' => $data->title])->exists();
            if ($already_exists) {
                throw new ConflictHttpException('Asesmen dengan judul yang sama sudah ada');
            }

            if (!$data) {
                throw new Exception('New assessment need CreateAssessmentForm validation');
            }

            $assessment->setAttributes($data->attributes);
            $assessment->save();
        }

        return $assessment;
    }

    public static function updateTitle(int $assessment_id, UpdateAssessmentTitleForm $data): Assessment
    {
        $assessment = self::findOrFail($assessment_id);

        $already_exists = Assessment::find()
            ->where(['title' => $data->title])
            ->andWhere(['!=', 'id', $assessment->id])
            ->exists();
        if ($already_exists) {
            throw new ConflictHttpException('Asesmen dengan judul yang sama sudah ada');
        }

        $assessment->title = $data->title;
        $assessment->save();
        return $assessment;
    }

    public static function activate(int $assessment_id): Assessment
    {
        $assessment = self::findOrFail($assessment_id);
        $assessment->activate()->save();
        return $assessment;
    }

    public static function changeLevel(int $assessment_id, ChangeLevelForm $data): Assessment
    {
        $assessment = self::findOrFail($assessment_id);

        if ($assessment->getCertifications()->exists()) {
            throw new UnprocessableEntityHttpException(
                'Tingkat asesmen tidak dapat diubah karena asesmen sudah digunakan dalam proses sertifikasi'
            );
        }
        if ($assessment->active_at_level) {
            throw new UnprocessableEntityHttpException(
                'Tingkat asesmen tidak dapat diubah karena asesmen sedang aktif. ' .
                'Silakan aktifkan asesmen lain pada tingkat ' . CertificateLevel::list()[$assessment->level] .
                ' untuk menggantikan asesmen ini sehingga proses sertifikasi tetap memiliki asesmen aktif'
            );
        }

        $assessment->level = $data->level;
        $assessment->deactivate()->save();
        return $assessment;
    }

    public static function delete(int $assessment_id): Assessment
    {
        $assessment = self::findOrFail($assessment_id);

        if ($assessment->getCertifications()->exists()) {
            throw new UnprocessableEntityHttpException(
                'Asesmen tidak dapat dihapus karena sudah digunakan dalam proses sertifikasi'
            );
        }
        if ($assessment->active_at_level) {
            throw new UnprocessableEntityHttpException(
                'Asesmen tidak dapat dihapus karena sedang aktif. ' .
                'Silakan aktifkan asesmen lain pada tingkat ' . CertificateLevel::list()[$assessment->level] .
                ' untuk menggantikan asesmen ini sebelum menghapusnya'
            );
        }

        $assessment->delete();
        return $assessment;
    }
}