<?php

namespace common\services;

use common\enums\UserRole;
use common\helpers\EmailHelper;
use common\helpers\UserHelper;
use common\models\form\LoginForm;
use common\models\form\RegisterForm;
use common\models\form\VerifyEmailForm;
use common\models\User;
use Yii;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

class UserService
{
    public static function login(LoginForm $data)
    {
        $user = User::findByUsername($data->username);
        if (!$user || !$user->validatePassword($data->password)) {
            throw new NotFoundHttpException('Email atau password salah');
        }
        $user->generateAccessToken();
        $user->save();

        $ONE_MONTH = 3600 * 24 * 30;
        Yii::$app->user->login($user, $ONE_MONTH);

        return [
            'access_token' => $user->access_token,
        ];
    }

    public static function register(RegisterForm $data)
    {
        $user_already_exits = User::find()
            ->where([
                'or',
                ['username' => $data->username],
                ['email' => $data->email],
            ])
            ->exists();
        if ($user_already_exits) {
            throw new ConflictHttpException('Username atau email sudah digunakan');
        }

        $user = new User();
        $user->username = $data->username;
        $user->email = $data->email;
        $user->setPassword($data->password);
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();
        $user->generateAccessToken();
        $user->save();

        $auth = Yii::$app->authManager;
        $userRole = $auth->getRole(UserRole::USER);
        $auth->assign($userRole, $user->id);
        EmailHelper::sendEmailVerification($user);

        $ONE_MONTH = 3600 * 24 * 30;
        Yii::$app->user->login($user, $ONE_MONTH);

        return [
            'access_token' => $user->access_token,
        ];
    }

    public static function verifyEmail(VerifyEmailForm $data)
    {
        $user = User::findByVerificationToken($data->token);
        if (!$user) {
            throw new NotFoundHttpException('Token salah');
        }

        $user->status = User::STATUS_ACTIVE;
        $user->save();
        
        return [
            'access_token' => $user->access_token,
        ];
    }

    public static function detail(int $user_id): User
    {
        $user = User::find()->where(['id' => $user_id])->select(UserHelper::$basicSelect)->one();
        if (!$user) {
            throw new NotFoundHttpException('Akun belum terdaftar dalam sistem');
        }
        return $user;
    }

    public static function me()
    {
        $user = User::findOne(Yii::$app->user->id);
        if (!$user) {
            throw new UnauthorizedHttpException('Akun belum terdaftar dalam sistem');
        }
        return $user;
    }

    public static function findSaspriKAsCoordinatorOrFail()
    {
        $saspri_k = User::findOne(['id' => Yii::$app->user->id])
            ->saspriKAsCoordinator;
        if (!$saspri_k) {
            throw new ForbiddenHttpException('Hanya wali yang boleh mengakses halaman ini');
        }
        return $saspri_k;
    }
}