<?php

namespace common\services;

use common\models\form\IndicatorForm;
use common\models\Indicator;
use common\models\IndicatorGroup;
use yii\web\NotFoundHttpException;
use yii\web\ConflictHttpException;
use yii\web\UnprocessableEntityHttpException;

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

    public static function save(?int $indicator_id, IndicatorForm $data): Indicator
    {
        $indicator = $indicator_id ? self::findOrFail($indicator_id) : new Indicator();

        if ($indicator_id) {
            if ($indicator->indicatorGroup->assessment->getCertifications()->exists()) {
                throw new UnprocessableEntityHttpException(
                    'Indikator tidak dapat diubah karena asesmen sudah digunakan dalam proses sertifikasi'
                );
            }
        }

        $isValidGroup = IndicatorGroup::find()
            ->where(['id' => $data->indicator_group_id])
            ->andWhere(['is not', 'parent_group_id', null])
            ->exists();
        if (!$isValidGroup) {
            throw new NotFoundHttpException('Subgrup yang dipilih tidak ditemukan atau bukan subgrup yang valid');
        }

        $already_exists = Indicator::find()
            ->where(['indicator_group_id' => $data->indicator_group_id])
            ->andWhere(['code' => $data->code])
            ->andFilterWhere(['!=', 'id', $indicator->id ?? null])
            ->exists();
        if ($already_exists) {
            throw new ConflictHttpException('Indikator dengan kode yang sama sudah ada dalam grup ini');
        }

        $indicator->setAttributes($data->attributes);

        if ($indicator->indicatorGroup->assessment->getCertifications()->exists()) {
            throw new UnprocessableEntityHttpException(
                'Indikator tidak dapat ditambah/diubah karena asesmen sudah digunakan dalam proses sertifikasi'
            );
        }

        $indicator->save();
        return $indicator;
    }

    public static function delete(int $id): Indicator
    {
        $indicator = self::findOrFail($id);

        if ($indicator->indicatorGroup->assessment->getCertifications()->exists()) {
            throw new UnprocessableEntityHttpException(
                'Indikator tidak dapat dihapus karena asesmen sudah digunakan dalam proses sertifikasi'
            );
        }

        $indicator->delete();
        return $indicator;
    }
}