<?php

namespace frontend\controllers;

use common\models\PriceList;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;

class PriceListController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * Limit HTTP methods
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                    'update' => ['POST'],
                    'view'   => ['GET'],
                ],
            ],
        ];
    }

    /**
     * Create a new price list for the current user
     * Redirects to harga-jual/data.php
     */
    public function actionCreate()
    {
        $model = new PriceList();
        $model->scenario = PriceList::SCENARIO_CREATE;
        $post = Yii::$app->request->post();

        // Assign posted values to the model attributes
        $model->attributes = [
            'electricity_water'     => $post['electricity_water'] ?? 0,
            'inflation'              => $post['inflation'] ?? 0,
            'employee'               => $post['employee'] ?? 0,
            'wage'                   => $post['wage'] ?? 0,
            'livestock_per_employee' => $post['livestock_per_employee'] ?? 1,
            'margin'                 => $post['margin'] ?? 0,
            'land'                   => $post['land'] ?? 0,
        ];

        if ($model->validate() && $model->save()) {
            Yii::$app->session->setFlash('success', 'Data berhasil disimpan.');
        } else {
            Yii::$app->session->setFlash('error', implode('<br>', array_map(function($errors) {
                return implode(', ', $errors);
            }, $model->getErrors())));
        }

        return $this->redirect('/harga-jual/data');
    }

    /**
     * Update the current user's price list
     * Redirects to harga-jual/data.php
     */
    public function actionUpdate()
    {
        $userId = Yii::$app->user->identity->id;
        $model = PriceList::findOne(['user_id' => $userId]);

        if (!$model) {
            Yii::$app->session->setFlash('error', 'Data tidak ditemukan. Silakan buat terlebih dahulu.');
            return $this->redirect('/harga-jual/data');
        }

        $model->scenario = PriceList::SCENARIO_UPDATE;
        $post = Yii::$app->request->post();

        $model->attributes = [
            'electricity_water'     => $post['electricity_water'] ?? $model->electricity_water,
            'inflation'              => $post['inflation'] ?? $model->inflation,
            'employee'               => $post['employee'] ?? $model->employee,
            'wage'                   => $post['wage'] ?? $model->wage,
            'livestock_per_employee' => $post['livestock_per_employee'] ?? $model->livestock_per_employee,
            'margin'                 => $post['margin'] ?? $model->margin,
            'land'                   => $post['land'] ?? $model->land,
        ];

        if ($model->validate() && $model->save()) {
            Yii::$app->session->setFlash('success', 'Data berhasil diperbarui.');
        } else {
            Yii::$app->session->setFlash('error', implode('<br>', array_map(function($errors) {
                return implode(', ', $errors);
            }, $model->getErrors())));
        }

        return $this->redirect('/harga-jual/data');
    }

    /**
     * View the current user's price list
     * Redirects to harga-jual/data.php
     */
    public function actionView()
    {
        return $this->redirect('/harga-jual/data');
    }
}
