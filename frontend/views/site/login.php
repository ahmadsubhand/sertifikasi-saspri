<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var common\models\form\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Login';
?>
<div class="site-login d-flex justify-content-center align-items-center mx-auto">
    <div class="border shadow-sm rounded-1 bg-white p-5 width-50-100">
        <h1 class="fw-bold text-start text-dark h2 mb-2 font-monospace"><?= Html::encode($this->title) ?></h1>

        <p class="text-muted text-start">Silahkan isi form berikut untuk login:</p>

        <div class="row">
            <div>
                <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                <?= $form->field($model, 'username')->textInput(['autofocus' => true])->label('Username') ?>

                <div class="position-relative">
                    <div class="d-flex justify-content-between align-items-center position-absolute end-0 top-0" style="z-index: 5;">
                        <?= Html::a('Lupa password?', ['site/request-password-reset'], ['class' => 'text-decoration-none small text-muted', 'style' => 'font-size: 0.8rem;']) ?>
                    </div>
                    <?= $form->field($model, 'password')->passwordInput()->label('Password') ?>
                </div>

                <div class="form-group w-100">
                    <?= Html::submitButton('Login', ['class' => 'btn s-btn-main w-100', 'name' => 'login-button']) ?>
                </div>

                <div class="mt-4 mx-auto text-center" style="color:#999;">
                    Belum Memiliki Akun?
                    <?= Html::a('Daftar', ['site/signup']) ?>
                </div>
                <div class="mt-2 mx-auto text-center" style="color:#999;">
                    Perlu kirim ulang email verifikasi?
                    <?= Html::a('Kirim Ulang', ['site/resend-verification-email']) ?>
                </div>
                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>