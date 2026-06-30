<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var common\models\form\ResetPasswordForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Reset password';
?>
<div class="site-reset-password d-flex justify-content-center align-items-center mx-auto">
    <div class="border shadow-sm rounded-1 bg-white p-5 width-50-100">
        <h1 class="fw-bold text-start text-dark h2 mb-2 font-monospace"><?= Html::encode($this->title) ?></h1>

        <p class="text-muted text-start">Silahkan masukkan password baru anda.</p>

        <div class="row">
            <div >
                <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>

                <?= $form->field($model, 'password')->passwordInput(['autofocus' => true])->label('Password Baru') ?>

                <div class="form-group w-100">
                    <?= Html::submitButton('Save', ['class' => 'btn s-btn-main w-100']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>