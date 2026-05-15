<?php 

namespace frontend\controllers;
use yii\web\Controller;


class EnrollController extends Controller{
  public function actionIndex(){
    return $this->render('index');
  }
}