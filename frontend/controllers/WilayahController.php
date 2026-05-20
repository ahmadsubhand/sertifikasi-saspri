<?php

namespace frontend\controllers;

use common\models\District;
use common\models\Province;
use common\models\Regency;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class WilayahController extends Controller
{
    public function actionProvinsi()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $provinces = Province::find()->asArray()->all();
        return $provinces;
    }

    public function actionKabupatenKota(int $province_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $regencies = Regency::find()->where(['province_id' => $province_id])->asArray()->all();
        return $regencies;
    }

    public function actionKecamatan(int $regency_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $districts = District::find()->where(['regency_id' => $regency_id])->asArray()->all();
        return $districts;
    }
}