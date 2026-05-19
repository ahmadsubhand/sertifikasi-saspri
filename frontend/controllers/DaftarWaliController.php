<?php

namespace frontend\controllers;

use common\enums\UserRole;
use common\models\SaspriK;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class DaftarWaliController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [UserRole::USER],
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'tambah-anggota' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionDaftarSaspriK()
    {
        $model = new SaspriK();
        $model->load(Yii::$app->request->post());
        $model->save();

        Yii::$app->session->setFlash(
            'success', 
            'SASPRI-Kawasan ' . $model->district->name . 
            ' berhasil didaftarkan. Sedang menunggu proses verifikasi SASPRI-Nasional'
        );
        return $this->redirect(['index']);
    }
}