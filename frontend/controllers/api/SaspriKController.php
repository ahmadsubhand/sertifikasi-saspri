<?php

namespace frontend\controllers\api;

use common\enums\ApprovalStatus;
use common\enums\UserRole;
use common\helpers\UserHelper;
use common\models\SaspriK;
use common\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;

class SaspriKController extends ActiveController
{
    public $modelClass = SaspriK::class;

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
                'detail' => ['GET'],
                'members' => ['GET'],
                'valid-certificate' => ['GET'],
                'latest-completed-certification' => ['GET'],
                'certifications' => ['GET'],
                'on-going-certification' => ['GET'],
                'coordinator-registration' => ['GET'],
                'coordinator-change' => ['GET'],
            ]
        ];

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => [
                'on-going-certification',
                'coordinator-registration',
                'coordinator-change',
            ]
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => [
                'on-going-certification',
                'coordinator-registration',
                'coordinator-change'
            ],
            'rules' => [
                [
                    'allow' => true,
                    'roles' => [UserRole::COORDINATOR],
                    'actions' => [
                        'on-going-certification',
                    ],
                ],
                [
                    'allow' => true,
                    'roles' => [UserRole::ADMIN],
                    'actions' => [
                        'coordinator-registration',
                        'coordinator-change',
                    ],
                ]
            ]
        ];

        return $behaviors;
    }

    public function actionDetail(int $saspri_k_id)
    {
        $saspri_k = $this->findSaspriKOrFail($saspri_k_id);
        return $saspri_k;
    }

    public function actionMembers(int $saspri_k_id, ?string $q = '', ?int $limit = 5, ?int $offset = 0)
    {
        $saspri_k = $this->findSaspriKOrFail($saspri_k_id);
        $users = $saspri_k->getUsers()
            ->joinWith('role r')
            ->andWhere(['like', 'username', $q])
            ->select(UserHelper::$basicSelect)
            ->orderBy([
                new \yii\db\Expression(
                    "CASE WHEN r.item_name = :role THEN 0 ELSE 1 END",
                    [':role' => UserRole::COORDINATOR]
                ),
            ])
            ->limit($limit)
            ->offset($offset)
            ->asArray()
            ->all();
        return $users;
    }

    public function actionValidCertificate(int $saspri_k_id)
    {
        $saspri_k = $this->findSaspriKOrFail($saspri_k_id);
        return $saspri_k->validCertificate;
    }

    public function actionLatestCompletedCertification(int $saspri_k_id)
    {
        $saspri_k = $this->findSaspriKOrFail($saspri_k_id);
        return $saspri_k->latestCompletedCertification;
    }

    public function actionCertifications(int $saspri_k_id, ?int $limit = 5, ?int $offset = 0)
    {
        $saspri_k = $this->findSaspriKOrFail($saspri_k_id);
        $certifications = $saspri_k->getCertifications()
            ->orderBy(['updated_at' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->asArray()
            ->all();
        return $certifications;
    }

    public function actionOnGoingCertification()
    {
        $user = User::findOne(Yii::$app->user->id);
        $saspri_k = $user->saspriKAsCoordinator;
        if (!$saspri_k) {
            throw new NotFoundHttpException('SASPRI-K not found for this coordinator');
        }
        return $saspri_k->onGoingCertification;
    }

    public function actionCoordinatorRegistration(?int $limit = 5, ?int $offset = 0)
    {
        $saspri_k = SaspriK::find()
            ->where(['request_status' => ApprovalStatus::PENDING])
            ->with([
                'coordinator' => function (ActiveQuery $query) {
                    $query->select(UserHelper::$basicSelect);
                }, 
                'district',
            ])
            ->orderBy(['updated_at' => SORT_ASC])
            ->limit($limit)
            ->offset($offset)
            ->asArray()
            ->all();
        return $saspri_k;
    }

    public function actionCoordinatorChange(?int $limit = 5, ?int $offset = 0)
    {
        $saspri_k = SaspriK::find()
            ->where(['change_status' => ApprovalStatus::PENDING])
            ->with([
                'coordinator' => function (ActiveQuery $query) {
                    $query->select(UserHelper::$basicSelect);
                }, 
                'district',
            ])
            ->orderBy(['updated_at' => SORT_ASC])
            ->limit($limit)
            ->offset($offset)
            ->asArray()
            ->all();
        return $saspri_k;
    }

    protected function findSaspriKOrFail(int $id)
    {
        $saspri_k = SaspriK::findOne($id);
        if (!$saspri_k) {
            throw new NotFoundHttpException('SASPRI-K not found');
        }
        return $saspri_k;
    }
}
