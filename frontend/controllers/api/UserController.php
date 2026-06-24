<?php

namespace frontend\controllers\api;

use common\enums\UserRole;
use common\helpers\ModelHelper;
use common\helpers\UserHelper;
use common\models\Certification;
use common\models\form\LoginForm;
use common\models\form\PasswordResetRequestForm;
use common\models\form\RegisterForm;
use common\models\form\ResendVerificationEmailForm;
use common\models\form\ResetPasswordForm;
use common\models\form\VerifyEmailForm;
use common\models\PeerTeamMember;
use common\models\SelfTeamMember;
use common\models\User;
use common\services\UserService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;

class UserController extends ActiveController
{
    public $modelClass = User::class;

    public function actions()
    {
        return [];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'register' => ['POST'],
                'verify-email' => ['POST'],
                'resend-verification-email' => ['POST'],
                'request-password-reset' => ['POST'],
                'reset-password' => ['POST'],
                'login' => ['POST'],
                'me' => ['GET'],
                'certifications' => ['GET'],
                'detail' => ['GET'],
                'available-for-saspri-k' => ['GET'],
                'available-for-self-team' => ['GET'],
                'available-for-peer-team' => ['GET'],
            ]
        ];

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => [
                'logout',
                'me',
                'certifications',
                'available-for-saspri-k',
                'available-for-self-team',
                'available-for-peer-team',
            ]
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => [
                'available-for-saspri-k',
                'available-for-self-team',
                'available-for-peer-team'
            ],
            'rules' => [
                [
                    'allow' => true,
                    'roles' => [UserRole::COORDINATOR],
                    'actions' => [
                        'available-for-saspri-k',
                        'available-for-self-team',
                    ],
                ],
                [
                    'allow' => true,
                    'roles' => [UserRole::ADMIN],
                    'actions' => [
                        'available-for-peer-team',
                    ],
                ]
            ]
        ];

        return $behaviors;
    }

    public function actionRegister()
    {
        $data = new RegisterForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return UserService::register($data);
    }

    public function actionResendVerificationEmail()
    {
        $data = new ResendVerificationEmailForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return UserService::resendVerificationEmail($data);
    }

    public function actionVerifyEmail()
    {
        $data = new VerifyEmailForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return UserService::verifyEmail($data);
    }

    public function actionRequestPasswordReset()
    {
        $data = new PasswordResetRequestForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return UserService::requestPasswordReset($data);
    }

    public function actionResetPassword()
    {
        $data = new ResetPasswordForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return UserService::resetPassword($data);
    }

    public function actionLogin()
    {
        $data = new LoginForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return UserService::login($data);
    }

    public function actionLogout()
    {
        $user = User::findOne(Yii::$app->user->id);
        $user->removeAccessToken();
        $user->save();
        return [
            'access_token' => $user->access_token
        ];
    }

    public function actionMe()
    {
        $user = User::find()->where(['id' => Yii::$app->user->id])->with('role')->one();
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }
        return $user;
    }

    public function actionDetail(int $user_id)
    {
        $user = User::find()
            ->where(['id' => $user_id])
            ->select(UserHelper::$basicSelect)
            ->asArray()
            ->one();
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }
        return $user;
    }

    public function actionCertifications(?int $limit = 5, ?int $offset = 0)
    {
        $user_id = Yii::$app->user->id;
        $certifications = Certification::find()
            ->distinct()
            ->joinWith('selfTeamMembers')
            ->joinWith('peerTeamMembers')
            ->joinWith('saspriK.district')
            ->andWhere([
                'or',
                [SelfTeamMember::tableName() . '.user_id' => $user_id],
                [PeerTeamMember::tableName() . '.user_id' => $user_id],
            ])
            ->orderBy(['updated_at' => SORT_DESC])
            ->limit($limit + 1)
            ->offset($offset)
            ->asArray()
            ->all();

        $has_next = count($certifications) > $limit;
        if ($has_next) array_pop($certifications);

        return [
            'certifications' => $certifications,
            'prev_link' => $offset > 0 ? Url::current(['offset' => max(0, $offset - $limit)], true) : null,
            'next_link' => $has_next ? Url::current(['offset' => $offset + $limit], true) : null,
            'offset' => $offset,
        ];
    }

    public function actionAvailableForSaspriK(?string $q = '', ?int $limit = 10, ?int $offset = 0)
    {
        $users = User::find()
            ->availableForSaspriK()
            ->andWhere(['like', 'username', $q])
            ->select(UserHelper::$basicSelect)
            ->limit($limit + 1)
            ->offset($offset)
            ->all();

        $has_next = count($users) > $limit;
        if ($has_next) array_pop($users);

        return [
            'users' => $users,
            'prev_link' => $offset > 0 ? Url::current(['offset' => max(0, $offset - $limit)], true) : null,
            'next_link' => $has_next ? Url::current(['offset' => $offset + $limit], true) : null,
            'offset' => $offset,
        ];
    }

    public function actionAvailableForSelfTeam(?string $q = '', ?int $limit = 10, ?int $offset = 0)
    {
        $user = User::findOne(Yii::$app->user->id);
        $saspri_k = $user->saspriKAsCoordinator;
        $certification = $saspri_k->onGoingCertification;
        $users = User::find()
            ->availableForSelfTeam($saspri_k, $certification)
            ->andWhere(['like', 'username', $q])
            ->select(UserHelper::$basicSelect)
            ->limit($limit + 1)
            ->offset($offset)
            ->all();

        $has_next = count($users) > $limit;
        if ($has_next) array_pop($users);

        return [
            'users' => $users,
            'prev_link' => $offset > 0 ? Url::current(['offset' => max(0, $offset - $limit)], true) : null,
            'next_link' => $has_next ? Url::current(['offset' => $offset + $limit], true) : null,
            'offset' => $offset,
        ];
    }

    public function actionAvailableForPeerTeam(int $certification_id, ?string $q = '', ?int $limit = 10, ?int $offset = 0)
    {
        $certification = Certification::findOne(['id' => $certification_id]);
        $users = User::find()
            ->availableForPeerTeam($certification)
            ->andWhere(['like', 'username', $q])
            ->select(UserHelper::$basicSelect)
            ->limit($limit + 1)
            ->offset($offset)
            ->all();

        $has_next = count($users) > $limit;
        if ($has_next) array_pop($users);

        return [
            'users' => $users,
            'prev_link' => $offset > 0 ? Url::current(['offset' => max(0, $offset - $limit)], true) : null,
            'next_link' => $has_next ? Url::current(['offset' => $offset + $limit], true) : null,
            'offset' => $offset,
        ];
    }
}
