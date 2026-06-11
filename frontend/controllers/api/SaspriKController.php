<?php

namespace frontend\controllers\api;

use common\enums\ApprovalStatus;
use common\enums\RequestResponse;
use common\enums\UserRole;
use common\helpers\ModelHelper;
use common\helpers\UserHelper;
use common\models\form\AddMembersForm;
use common\models\form\ChangeLevelForm;
use common\models\form\CoordinatorChangeForm;
use common\models\form\ExternalReviewForm;
use common\models\form\RegisterSaspriKForm;
use common\models\form\RequestResponseForm;
use common\models\form\UpdateSaspriKForm;
use common\models\SaspriK;
use common\models\User;
use common\services\SaspriKService;
use Yii;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\helpers\Url;
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
                'coordinator-registration' => ['GET'],
                'coordinator-registration-detail' => ['GET'],
                'change-registration-level' => ['POST'],
                'save-draft-registration' => ['POST'],
                'coordinator-registration-response' => ['POST'],
                'coordinator-change' => ['GET'],
                'coordinator-change-detail' => ['GET'],
                'coordinator-change-response' => ['POST'],
                'on-going-certification' => ['GET'],
                'add-members' => ['POST'],
                'remove-member' => ['DELETE'],
                'change-coordinator' => ['POST'],
                'cancel-coordinator-change' => ['POST'],
                'update' => ['PUT'],
                'register' => ['POST'],
                'detail-registration' => ['GET'],
                'cancel-registration' => ['DELETE'],
            ]
        ];

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => [
                'coordinator-registration',
                'coordinator-registration-detail',
                'change-registration-level',
                'save-draft-registration',
                'coordinator-registration-response',
                'coordinator-change',
                'coordinator-change-detail',
                'coordinator-change-response',
                'on-going-certification',
                'add-members',
                'remove-member',
                'change-coordinator',
                'cancel-coordinator-change',
                'update',
                'register',
                'detail-registration',
                'cancel-registration',
            ]
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => [
                'coordinator-registration',
                'coordinator-registration-detail',
                'change-registration-level',
                'save-draft-registration',
                'coordinator-registration-response',
                'coordinator-change',
                'coordinator-change-detail',
                'coordinator-change-response',
                'on-going-certification',
                'add-members',
                'remove-member',
                'change-coordinator',
                'cancel-coordinator-change',
                'update',
                'register',
                'cancel-registration',
            ],
            'rules' => [
                [
                    'allow' => true,
                    'roles' => [UserRole::COORDINATOR],
                    'actions' => [
                        'on-going-certification',
                        'add-members',
                        'remove-member',
                        'change-coordinator',
                        'cancel-coordinator-change',
                        'update',
                    ],
                ],
                [
                    'allow' => true,
                    'roles' => [UserRole::ADMIN],
                    'actions' => [
                        'coordinator-registration',
                        'coordinator-registration-detail',
                        'change-registration-level',
                        'save-draft-registration',
                        'coordinator-registration-response',
                        'coordinator-change',
                        'coordinator-change-detail',
                        'coordinator-change-response',
                    ],
                ],
                [
                    'allow' => true,
                    'roles' => [UserRole::USER],
                    'actions' => [
                        'register',
                        'cancel-registration',
                        'detail-registration'
                    ],
                ]
            ]
        ];

        return $behaviors;
    }

    public function actionDetail(int $saspri_k_id)
    {
        $saspri_k = SaspriKService::findOrFail($saspri_k_id);
        $district = $saspri_k->district;
        return [
            ...$saspri_k,
            'district' => $district,
        ];
    }

    public function actionMembers(int $saspri_k_id, ?string $q = '', ?int $limit = 5, ?int $offset = 0)
    {
        $saspri_k = SaspriKService::findOrFail($saspri_k_id);
        $users = $saspri_k->getUsers()
            ->joinWith('role r')
            ->andWhere(['like', 'username', $q])
            ->select([
                ...UserHelper::$basicSelect,
                'role' => new \yii\db\Expression('MIN(r.item_name)')
            ])
            ->groupBy(User::tableName() . '.id')
            ->orderBy([
                new \yii\db\Expression(
                    "MIN(CASE WHEN r.item_name = :role THEN 0 ELSE 1 END)",
                    [':role' => UserRole::COORDINATOR]
                ),
            ])
            ->limit($limit + 1)
            ->offset($offset)
            ->asArray()
            ->all();

        $has_next = count($users) > $limit;
        if ($has_next) array_pop($users);

        return [
            'members' => $users,
            'prev_link' => $offset > 0 ? Url::current(['offset' => max(0, $offset - $limit)], true) : null,
            'next_link' => $has_next ? Url::current(['offset' => $offset + $limit], true) : null,
            'offset' => $offset,
        ];
    }

    public function actionValidCertificate(int $saspri_k_id)
    {
        $saspri_k = SaspriKService::findOrFail($saspri_k_id);
        return $saspri_k->validCertificate;
    }

    public function actionLatestCompletedCertification(int $saspri_k_id)
    {
        $saspri_k = SaspriKService::findOrFail($saspri_k_id);
        return $saspri_k->latestCompletedCertification;
    }

    public function actionCertifications(int $saspri_k_id, ?int $limit = 5, ?int $offset = 0)
    {
        $saspri_k = SaspriKService::findOrFail($saspri_k_id);
        $certifications = $saspri_k->getCertifications()
            ->orderBy(['updated_at' => SORT_DESC])
            ->limit($limit + 1)
            ->offset($offset)
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
        $saspri_ks = SaspriK::find()
            ->where(['request_status' => ApprovalStatus::PENDING])
            ->with([
                'coordinator' => function (ActiveQuery $query) {
                    $query->select(UserHelper::$basicSelect);
                }, 
                'district',
            ])
            ->orderBy(['updated_at' => SORT_ASC])
            ->limit($limit + 1)
            ->offset($offset)
            ->asArray()
            ->all();

        $has_next = count($saspri_ks) > $limit;
        if ($has_next) array_pop($saspri_ks);

        return [
            'registration_requests' => $saspri_ks,
            'prev_link' => $offset > 0 ? Url::current(['offset' => max(0, $offset - $limit)], true) : null,
            'next_link' => $has_next ? Url::current(['offset' => $offset + $limit], true) : null,
            'offset' => $offset,
        ];
    }

    public function actionCoordinatorChange(?int $limit = 5, ?int $offset = 0)
    {
        $saspri_ks = SaspriK::find()
            ->where(['change_status' => ApprovalStatus::PENDING])
            ->with([
                'newCoordinator' => function (ActiveQuery $query) {
                    $query->select(UserHelper::$basicSelect);
                },
                'coordinator' => function (ActiveQuery $query) {
                    $query->select(UserHelper::$basicSelect);
                }, 
                'district',
            ])
            ->orderBy(['updated_at' => SORT_ASC])
            ->limit($limit + 1)
            ->offset($offset)
            ->asArray()
            ->all();

        $has_next = count($saspri_ks) > $limit;
        if ($has_next) array_pop($saspri_ks);

        return [
            'change_requests' => $saspri_ks,
            'prev_link' => $offset > 0 ? Url::current(['offset' => max(0, $offset - $limit)], true) : null,
            'next_link' => $has_next ? Url::current(['offset' => $offset + $limit], true) : null,
            'offset' => $offset,
        ];
    }

    public function actionAddMembers()
    {
        $data = new AddMembersForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return SaspriKService::addMembers($data);
    }

    public function actionRemoveMember(int $user_id)
    {
        return SaspriKService::removeMember($user_id);
    }

    public function actionRegister()
    {
        $data = new RegisterSaspriKForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return SaspriKService::register($data);
    }

    public function actionDetailRegistration()
    {
        return SaspriKService::detailRegistration();
    }

    public function actionCancelRegistration()
    {
        return SaspriKService::cancelRegistration();
    }

    public function actionCoordinatorRegistrationDetail(int $saspri_k_id, ?int $page = 1)
    {
        $data = SaspriKService::coordinatorRegistrationDetail($saspri_k_id, $page);

        $data['current_child_groups'] = array_map(
            fn ($group) => $group->toArray([], [
                'indicators',
                'indicators.indicatorOptions',
                'indicators.indicatorScores',
            ]),
            $data['current_child_groups']
        );

        return $this->asJson($data);
    }

    public function actionChangeRegistrationLevel(int $saspri_k_id)
    {
        $data = new ChangeLevelForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->post());
        return SaspriKService::changeRegistrationLevel($saspri_k_id, $data);
    }

    public function actionSaveDraftRegistration(int $saspri_k_id)
    {
        $data = new ExternalReviewForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return SaspriKService::saveRegistration($saspri_k_id, $data);
    }

    public function actionCoordinatorRegistrationResponse(int $saspri_k_id)
    {
        $request = Yii::$app->request->getBodyParams();
        $data = new RequestResponseForm();
        ModelHelper::loadAndValidateOrFail($data, $request);

        if ($data->action === RequestResponse::APPROVE) {
            $scores = new ExternalReviewForm();
            ModelHelper::loadAndValidateOrFail($scores, $request);
            SaspriKService::saveRegistration($saspri_k_id, $scores);
        }

        return SaspriKService::registrationRequestResponse($saspri_k_id, $data);
    }

    public function actionChangeCoordinator()
    {
        $data = new CoordinatorChangeForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return SaspriKService::changeCoordinator($data);
    }

    public function actionCancelCoordinatorChange()
    {
        return SaspriKService::cancelCoordinatorChange();
    }

    public function actionCoordinatorChangeDetail(int $saspri_k_id)
    {
        return SaspriKService::coordinatorChangeDetail($saspri_k_id);
    }

    public function actionCoordinatorChangeResponse(int $saspri_k_id)
    {
        $data = new RequestResponseForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return SaspriKService::coordinatorChangeResponse($saspri_k_id, $data);
    }

    public function actionUpdate()
    {
        $data = new UpdateSaspriKForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return SaspriKService::update($data);
    }
}
