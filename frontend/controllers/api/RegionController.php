<?php

namespace frontend\controllers\api;

use common\models\District;
use common\models\Province;
use common\models\Regency;
use yii\filters\VerbFilter;
use yii\rest\Controller;

class RegionController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'province' => ['GET'],
                'regency' => ['GET'],
                'district' => ['GET'],
            ]
        ];
        return $behaviors;
    }

    public function actionProvince()
    {
        return Province::find()->asArray()->all();
    }

    public function actionRegency(int $province_id)
    {
        return Regency::find()->where(['province_id' => $province_id])->asArray()->all();
    }

    public function actionDistrict(int $regency_id)
    {
        return District::find()->where(['regency_id' => $regency_id])->asArray()->all();
    }
}
