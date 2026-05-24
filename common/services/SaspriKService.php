<?php

namespace common\services;

use common\models\User;
use Yii;
use yii\web\ForbiddenHttpException;

class SaspriKService
{
    public static function findSaspriKAsCoordinatorOrFail()
    {
        $saspri_k = User::findOne(['id' => Yii::$app->user->id])
            ->saspriKAsCoordinator;
        if (!$saspri_k) {
            throw new ForbiddenHttpException('Hanya wali yang boleh mengakses halaman ini');
        }
        return $saspri_k;
    }
}