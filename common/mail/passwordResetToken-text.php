<?php

/** @var yii\web\View $this */
/** @var common\models\User $user */

$resetLink = \Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
Halo <?= $user->username ?>,

Kami menerima permintaan untuk mengatur ulang kata sandi (reset password) akun Anda di aplikasi <?= \Yii::$app->name ?>.

Silakan salin dan buka tautan di bawah ini pada peramban (browser) Anda untuk membuat kata sandi baru:

<?= $resetLink ?>

Jika Anda tidak merasa meminta pengaturan ulang kata sandi ini, silakan abaikan email ini. Akun Anda akan tetap aman dan kata sandi Anda tidak akan berubah.

Salam hangat,
Tim <?= \Yii::$app->name ?>