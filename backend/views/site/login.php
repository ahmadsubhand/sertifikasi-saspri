<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var common\models\form\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Login';
?>
<div class="site-login d-flex justify-content-center align-items-center mx-auto min-vh-100">
    <div class="border shadow-sm rounded-1 bg-white p-5 width-50-100">
        <div class="d-flex gap-3 align-items-center text-white s-bg-main p-3 rounded-3 mb-4">
            <div class="bg-white rounded-2 p-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                <?= Html::img('@web/images/matasapi.svg', [
                    'alt' => 'Matasapi Digdaya Logo',
                    'style' => 'max-width: 100%; max-height: 100%; object-fit: contain;'
                ]) ?>
            </div>
            <div class="d-flex flex-column justify-content-center">
                <h2 class="mb-0 fs-5 fw-bold text-uppercase tracking-wider" style="letter-spacing: 0.05em;">Sertifikasi</h2>
                <small class="text-white-70 fw-semibold opacity-75" style="font-size: 11px; letter-spacing: 1px;">SASPRI-K</small>
            </div>
        </div>
        <h1 class="fw-bold text-start text-dark h2 mb-2 font-monospace"><?= Html::encode($this->title) ?></h1>

        <p class="text-muted text-start">Silahkan isi form berikut untuk login:</p>

        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

        <?= $form->field($model, 'username')->textInput(['autofocus' => true])->label('Username') ?>

        <?= $form->field($model, 'password')->passwordInput()->label('Password') ?>

        <div class="form-group w-100">
            <?= Html::submitButton('Login', ['class' => 'btn s-btn-main w-100', 'name' => 'login-button']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>