<?php

namespace common\helpers;

use common\models\User;
use Yii;

class EmailHelper
{
    public const EMAIL_VERIFICATION = 'email_verification';
    public const RESET_PASSWORD_REQUEST = 'reset_password_request';

    public static function sendEmailVerification(User $user)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->params['senderName']])
            ->setTo($user->email)
            ->setSubject('Verifikasi Email - ' . Yii::$app->name)
            ->send();
    }

    public static function sendResetPasswordRequest(User $user)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->params['senderName']])
            ->setTo($user->email)
            ->setSubject('Permintaan Reset Password - ' . Yii::$app->name)
            ->send();
    }
}