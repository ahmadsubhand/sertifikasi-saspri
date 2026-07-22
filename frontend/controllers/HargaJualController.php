<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\HargaJual;

class HargaJualController extends Controller
{
    public function actionIndex()
    {
        $model = new HargaJual();
        $total = 0;

        if ($model->load(Yii::$app->request->post())) {
            $total = $model->biaya_pakan +
                     $model->biaya_suplemen +
                     $model->biaya_obat +
                     $model->biaya_peralatan +
                     $model->upah_tenaga_kerja +
                     $model->biaya_anak_sapi;
        }

        return $this->render('index', [
            'model' => $model,
            'total' => $total,
        ]);
    }

    public function actionHistory()
    {
        return $this->render('history');
    }

}
