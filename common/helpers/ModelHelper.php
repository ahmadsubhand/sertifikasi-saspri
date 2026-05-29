<?php

namespace common\helpers;

use yii\base\Model;
use yii\web\BadRequestHttpException;

class ModelHelper
{
    public static function loadAndValidateOrFail(Model $model, array|string $request_data)
    {
        $model->load($request_data, '');
        if (!$model->validate()) {
            throw new BadRequestHttpException(implode(', ', $model->firstErrors));
        }
    }
}