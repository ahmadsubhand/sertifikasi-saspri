<?php

/** @var yii\web\View $this */
/** @var common\models\User $user */

$verifyLink = \Yii::$app->urlManager->createAbsoluteUrl(['site/verify-email', 'token' => $user->verification_token]);
?>
Halo <?= $user->username ?>,

Terima kasih telah mendaftar. 

Untuk memastikan keamanan akun Anda dan melanjutkan proses di aplikasi <?= \Yii::$app->name ?>, silakan verifikasi alamat email Anda dengan menyalin dan membuka tautan di bawah ini pada peramban (browser) Anda:

<?= $verifyLink ?>

Jika Anda tidak merasa melakukan pendaftaran ini, silakan abaikan email ini.

Salam hangat,
Tim <?= \Yii::$app->name ?>