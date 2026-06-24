<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var common\models\form\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Login';
?>
<div class="site-login">
    <div class="mt-5 offset-lg-3 col-lg-6">
        <!-- <div class="d-flex gap-2 align-items-center text-decoration-none text-white s-bg-main">
            <?= Html::img('@web/images/matasapi.svg', [
                'alt' => 'Matasapi Digdaya Logo',
                'class' => 'bg-white rounded-3 p-1',
                'style' => 'width: 45px; height: 45px; object-fit: contain;'
            ]) ?>
            <div class="d-flex flex-column lh-sm">
                <h2 class="mb-0 fs-5 fw-bold text-uppercase tracking-wide">Sertifikasi</h2>
                <small class="text-white-50 font-monospace" style="font-size: 10px; letter-spacing: 0.5px;">SASPRI-K</small>
            </div>
        </div> -->
        <h1><?= Html::encode($this->title) ?></h1>

        <p>Please fill out the following fields to login:</p>

        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

        <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

        <?= $form->field($model, 'password')->passwordInput() ?>

        <div class="form-group">
            <?= Html::submitButton('Login', ['class' => 'btn btn-primary btn-block', 'name' => 'login-button']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>