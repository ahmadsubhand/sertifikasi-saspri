<?php

namespace common\services;

use common\models\IndicatorOption;
use common\models\form\IndicatorOptionForm;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class IndicatorOptionService
{
    public static function findOrFail(int $id): IndicatorOption
    {
        $option = IndicatorOption::findOne($id);
        if (!$option) throw new NotFoundHttpException('Opsi tidak ditemukan');
        return $option;
    }

    public static function save(?int $option_id, IndicatorOptionForm $data)
    {
        IndicatorService::findOrFail($data->indicator_id);

        $model = $option_id ? self::findOrFail($option_id) : new IndicatorOption();
        $model->setAttributes($data->attributes);

        if ($model->indicator->indicatorGroup->assessment->getCertifications()->exists()) {
            throw new UnprocessableEntityHttpException(
                'Opsi tidak dapat ditambah/diubah karena asesmen sudah digunakan dalam proses sertifikasi'
            );
        }

        $model->save();
        return $model;
    }

    public static function delete(int $id): IndicatorOption
    {
        $model = self::findOrFail($id);

        if ($model->indicator->indicatorGroup->assessment->getCertifications()->exists()) {
            throw new UnprocessableEntityHttpException(
                'Opsi tidak dapat dihapus karena asesmen sudah digunakan dalam proses sertifikasi'
            );
        }

        $model->delete();
        return $model;
    }
}