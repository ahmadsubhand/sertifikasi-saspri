<?php

namespace common\services;

use common\models\IndicatorOption;
use common\models\form\IndicatorOptionForm;
use yii\web\NotFoundHttpException;

class IndicatorOptionService
{
    public static function findOrFail(int $id): IndicatorOption
    {
        $option = IndicatorOption::findOne($id);
        if (!$option) throw new NotFoundHttpException('Opsi tidak ditemukan');
        return $option;
    }

    public static function save(?int $option_id, IndicatorOptionForm $data): IndicatorOption
    {
        IndicatorService::findOrFail($data->indicator_id);

        $model = $option_id ? self::findOrFail($option_id) : new IndicatorOption();
        $model->setAttributes($data->attributes);
        $model->save();

        return $model;
    }

    public static function delete(int $id): IndicatorOption
    {
        $model = self::findOrFail($id);
        $model->delete();
        return $model;
    }
}