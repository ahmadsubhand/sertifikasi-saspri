<?php

namespace frontend\controllers;

use common\models\SaspriK;
use yii\web\Controller;
use Yii;
use yii\web\UnauthorizedHttpException;

class CoordinatorController extends Controller
{
  public function actionIndex()
  {
    $saspri_k = SaspriK::find()
      ->with('validCertificate', 'certifications', 'users')
      ->where(['coordinator_id' => Yii::$app->user->id])
      ->one();

    if ($saspri_k) {
      return $this->render('index', [
        'saspri_k' => $saspri_k,
      ]);
    }

    throw new UnauthorizedHttpException('Halaman ini hanya boleh diakses oleh Wali SASPRI-K');
  }
}