<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\User $user */

$resetLink = \Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
<div class="password-reset" style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <p>Halo <strong><?= Html::encode($user->username) ?></strong>,</p>

    <p>Kami menerima permintaan untuk mengatur ulang kata sandi (<em>reset password</em>) akun Anda di aplikasi <strong><?= Html::encode(\Yii::$app->name) ?></strong>.</p>

    <p>Silakan klik tombol di bawah ini untuk membuat kata sandi baru:</p>

    <p style="margin: 20px 0;">
        <?= Html::a('Atur Ulang Kata Sandi', $resetLink, [
            'style' => 'background-color: #0056b3; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'
        ]) ?>
    </p>

    <p style="font-size: 0.9em; color: #666;">
        <em>Jika tombol di atas tidak berfungsi, Anda juga dapat menyalin dan menempelkan tautan berikut ke peramban (browser) Anda:</em><br>
        <?= Html::encode($resetLink) ?>
    </p>

    <p style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee;">
        Jika Anda tidak merasa meminta pengaturan ulang kata sandi ini, silakan abaikan pesan ini. Akun Anda akan tetap aman dan kata sandi Anda tidak akan berubah.
    </p>

    <br>
    <p>Salam hangat,<br>
    <strong>Tim <?= Html::encode(\Yii::$app->name) ?></strong></p>
</div>