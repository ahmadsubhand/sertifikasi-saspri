<?php

namespace common\services;

use common\models\Assessment;
use common\models\form\IndicatorForm;
use common\models\Indicator;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

class IndicatorService
{
    public static function findOrFail(int $id): Indicator
    {
        $indicator = Indicator::findOne($id);
        if (!$indicator) { 
            throw new NotFoundHttpException('Indikator tidak ditemukan');
        }
        return $indicator;
    }

    public static function save(int $assessment_id, ?int $indicator_id, IndicatorForm $data): Indicator
    {
        $indicator = $indicator_id ? self::findOrFail($indicator_id) : new Indicator();
        if (!$data->indicator_group_id) {
            throw new BadRequestHttpException('Indikator wajib memiliki subgrup');
        }

        $isValidGroup = Assessment::findOne($assessment_id)
            ->getChildGroups()
            ->andWhere(['child.id' => $data->indicator_group_id])
            ->exists();
        if (!$isValidGroup) {
            throw new NotFoundHttpException('Subgrup yang dipilih tidak ditemukan atau bukan subgrup yang valid');
        }

        $indicator->setAttributes($data->attributes);
        $indicator->save();

        return $indicator;
    }

    public static function delete(int $id): Indicator
    {
        $indicator = self::findOrFail($id);
        $indicator->delete();
        return $indicator;
    }
}