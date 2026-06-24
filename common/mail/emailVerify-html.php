<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\User $user */

$verifyLink = \Yii::$app->urlManager->createAbsoluteUrl(['site/verify-email', 'token' => $user->verification_token]);
?>
<div class="verify-email" style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <p>Halo <strong><?= Html::encode($user->username) ?></strong>,</p>

    <p>Terima kasih telah mendaftar.</p>

    <p>Untuk memastikan keamanan akun Anda dan melanjutkan proses di aplikasi <?= Html::encode(\Yii::$app->name) ?>, silakan klik tautan di bawah ini untuk memverifikasi alamat email Anda:</p>

    <p style="margin: 20px 0;">
        <?= Html::a('Verifikasi Email', $verifyLink, [
            'style' => 'background-color: #0056b3; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'
        ]) ?>
    </p>

    <p style="font-size: 0.9em; color: #666;">
        <em>Jika tombol di atas tidak berfungsi, Anda juga dapat menyalin dan menempelkan tautan berikut ke peramban (browser) Anda:</em><br>
        <?= Html::encode($verifyLink) ?>
    </p>

    <p>Jika Anda tidak merasa melakukan pendaftaran ini, silakan abaikan pesan ini.</p>

    <br>
    <p>Salam hangat,<br>
    <strong>Tim <?= Html::encode(\Yii::$app->name) ?></strong></p>
</div>