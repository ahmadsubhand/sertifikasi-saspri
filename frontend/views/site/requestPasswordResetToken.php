<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var common\models\form\PasswordResetRequestForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Request password reset';
?>
<div class="site-request-password-reset d-flex justify-content-center align-items-center mx-auto">
    <div class="border shadow-sm rounded-1 bg-white p-5 width-50-100">
        <h1 class="fw-bold text-start text-dark h2 mb-2 font-monospace"><?= Html::encode($this->title) ?></h1>

        <p class="text-muted text-start">Silakan masukkan alamat email Anda. Tautan untuk mengatur ulang password akan dikirimkan ke sana.</p>

        <div class="row w-100">
            <div >
                <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>

                <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>

                <div class="form-group">
                    <?= Html::submitButton('Send', ['class' => 'btn s-btn-main w-100']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>