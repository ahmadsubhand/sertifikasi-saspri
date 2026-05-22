<?php

namespace frontend\controllers\api;

use common\enums\UserRole;
use common\helpers\UserHelper;
use common\models\Certification;
use common\models\form\LoginForm;
use common\models\form\RegisterForm;
use common\models\form\VerifyEmailForm;
use common\models\User;
use common\services\UserService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
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
                'login' => ['POST'],
                'register' => ['POST'],
                'verify-email' => ['POST'],
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

    public function actionLogin()
    {
        $data = new LoginForm();
        $data->load(Yii::$app->request->getBodyParams(), '');
        if ($data->validate()) {
            return UserService::login($data);
        }
        return $data;
    }

    public function actionRegister()
    {
        $data = new RegisterForm();
        $data->load(Yii::$app->request->getBodyParams(), '');
        if ($data->validate()) {
            return UserService::register($data);
        }
        return $data;
    }

    public function actionVerifyEmail()
    {
        $data = new VerifyEmailForm();
        $data->load(Yii::$app->request->getBodyParams(), '');
        if ($data->validate()) {
            return UserService::verifyEmail($data);
        }
        return $data;
    }

    public function actionMe()
    {
        $user = User::findOne(Yii::$app->user->id);
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
            ->joinWith('selfTeamMembers stm')
            ->joinWith('peerTeamMembers ptm')
            ->andWhere([
                'or',
                ['stm.user_id' => $user_id],
                ['ptm.user_id' => $user_id],
            ])
            ->orderBy(['updated_at' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->asArray()
            ->all();
        return $certifications;
    }

    public function actionAvailableForSaspriK(?string $q = '', ?int $limit = 10)
    {
        $users = User::find()
            ->availableForSaspriK()
            ->andWhere(['like', 'username', $q])
            ->select(UserHelper::$basicSelect)
            ->limit($limit)
            ->asArray()
            ->all();
        return $users;
    }

    public function actionAvailableForSelfTeam(?string $q = '', ?int $limit = 10)
    {
        $user = User::findOne(Yii::$app->user->id);
        $saspri_k = $user->saspriKAsCoordinator;
        $certification = $saspri_k->onGoingCertification;
        $users = User::find()
            ->availableForSelfTeam($saspri_k, $certification)
            ->andWhere(['like', 'username', $q])
            ->select(UserHelper::$basicSelect)
            ->limit($limit)
            ->asArray()
            ->all();
        return $users;
    }

    public function actionAvailableForPeerTeam(int $certification_id, ?string $q = '', ?int $limit = 10)
    {
        $certification = Certification::findOne(['id' => $certification_id]);
        $users = User::find()
            ->availableForPeerTeam($certification)
            ->andWhere(['like', 'username', $q])
            ->select(UserHelper::$basicSelect)
            ->limit($limit)
            ->asArray()
            ->all();
        return $users;
    }
}
