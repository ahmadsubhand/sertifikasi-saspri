<?php

/** @var yii\web\View$this  */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var common\models\form\ResendVerificationEmailForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Kirim Ulang Email Verifikasi';
?>
<div class="site-resend-verification-email d-flex justify-content-center align-items-center mx-auto">
    <div class="border shadow-sm rounded-1 bg-white p-5 width-50-100">
        <h1 class="fw-bold text-start text-dark h2 mb-2 font-monospace"><?= Html::encode($this->title) ?></h1>
    
        <p class="text-muted text-start">Silahkan isi alamat email akun anda. Email verifikasi akan dikirimkan ke alamat tersebut.</p>
    
        <div class="row">
            <div>
                <?php $form = ActiveForm::begin(['id' => 'resend-verification-email-form']); ?>
    
                <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>
    
                <div class="form-group">
                    <?= Html::submitButton('Send', ['class' => 'btn btn-primary']) ?>
                </div>
    
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>