<?php

namespace frontend\controllers\api;

use common\enums\UserRole;
use common\helpers\ModelHelper;
use common\helpers\UserHelper;
use common\models\Certification;
use common\models\form\AddMembersForm;
use common\models\form\ChangeMemberRoleForm;
use common\models\form\ExternalReviewForm;
use common\models\form\PeerReviewForm;
use common\models\form\SelfReviewForm;
use common\services\CertificationService;
use Yii;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\rest\ActiveController;

class CertificationController extends ActiveController
{
    public $modelClass = Certification::class;

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
                'index' => ['GET'],
                'detail' => ['GET'],
                'saspri-k' => ['GET'],
                'self-team-members' => ['GET'],
                'peer-team-members' => ['GET'],
                'full-self-team-members' => ['GET'],
                'add-self-team-members' => ['POST'],
                'remove-self-team-member' => ['DELETE'],
                'change-self-team-member-role' => ['POST'],
                'submit-for-self-review' => ['POST'],
                'save-self-review' => ['POST'],
                'finalize-self-review' => ['POST'],
                'full-peer-team-members' => ['GET'],
                'add-peer-team-members' => ['POST'],
                'remove-peer-team-member' => ['DELETE'],
                'change-peer-team-member-role' => ['POST'],
                'submit-for-peer-review' => ['POST'],
                'save-peer-review' => ['POST'],
                'finalize-peer-review' => ['POST'],
                'save-external-review' => ['POST'],
                'finalize-external-review' => ['POST'],
            ]
        ];

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => [
                'add-self-team-members',
                'remove-self-team-member',
                'change-self-team-member-role',
                'submit-for-self-review',
                'save-self-review',
                'finalize-self-review',
                'add-peer-team-members',
                'remove-peer-team-member',
                'change-peer-team-member-role',
                'submit-for-peer-review',
                'save-peer-review',
                'finalize-peer-review',
                'save-external-review',
                'finalize-external-review',
            ]
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => [
                'full-self-team-members',
                'add-self-team-members',
                'remove-self-team-member',
                'change-self-team-member-role',
                'submit-for-self-review',
                'save-self-review',
                'finalize-self-review',
                'full-peer-team-members',
                'add-peer-team-members',
                'remove-peer-team-member',
                'change-peer-team-member-role',
                'submit-for-peer-review',
                'save-peer-review',
                'finalize-peer-review',
                'save-external-review',
                'finalize-external-review',
            ],
            'rules' => [
                [
                    'allow' => true,
                    'roles' => [UserRole::COORDINATOR],
                    'actions' => [
                        'full-self-team-members',
                        'add-self-team-members',
                        'remove-self-team-member',
                        'change-self-team-member-role',
                        'submit-for-self-review',
                    ],
                ],
                [
                    'allow' => true,
                    'roles' => [UserRole::ADMIN],
                    'actions' => [
                        'full-peer-team-members',
                        'add-peer-team-members',
                        'remove-peer-team-member',
                        'change-peer-team-member-role',
                        'submit-for-peer-review',
                        'save-external-review',
                        'finalize-external-review',
                    ],
                ],
                [
                    'allow' => true,
                    'roles' => [UserRole::USER],
                    'actions' => [
                        'save-self-review',
                        'finalize-self-review',
                        'save-peer-review',
                        'finalize-peer-review',
                    ],
                ],
                [
                    'allow' => true,
                    'roles' => ['@'],
                    'actions' => [
                        'save-peer-review',
                        'finalize-peer-review',
                    ],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionDetail(int $certification_id)
    {
        $certification = CertificationService::findOrFail($certification_id);
        return $certification;
    }

    public function actionSaspriK(int $certification_id)
    {
        $certification = CertificationService::findOrFail($certification_id);
        return $certification->saspriK;
    }

    public function actionSelfTeamMembers(int $certification_id, ?int $limit = 5, ?int $offset = 0)
    {
        $certification = CertificationService::findOrFail($certification_id);
        $members = $certification->getSelfTeamMembers()
            ->with(['user' => function (ActiveQuery $query) {
                $query->select(UserHelper::$basicSelect);
            }])
            ->orderBy(['role' => SORT_ASC])
            ->limit($limit + 1)
            ->offset($offset)
            ->asArray()
            ->all();

        $has_next = count($members) > $limit;
        if ($has_next) array_pop($members);

        return [
            'members' => $members,
            'prev_link' => $offset > 0 ? Url::current(['offset' => max(0, $offset - $limit)], true) : null,
            'next_link' => $has_next ? Url::current(['offset' => $offset + $limit], true) : null,
            'offset' => $offset,
        ];
    }

    public function actionPeerTeamMembers(int $certification_id, ?int $limit = 5, ?int $offset = 0)
    {
        $certification = CertificationService::findOrFail($certification_id);
        $members = $certification->getPeerTeamMembers()
            ->with(['user' => function (ActiveQuery $query) {
                $query->select(UserHelper::$basicSelect);
            }])
            ->orderBy(['role' => SORT_ASC])
            ->limit($limit + 1)
            ->offset($offset)
            ->asArray()
            ->all();

        $has_next = count($members) > $limit;
        if ($has_next) array_pop($members);

        return [
            'members' => $members,
            'prev_link' => $offset > 0 ? Url::current(['offset' => max(0, $offset - $limit)], true) : null,
            'next_link' => $has_next ? Url::current(['offset' => $offset + $limit], true) : null,
            'offset' => $offset,
        ];
    }

    public function actionFullSelfTeamMembers(int $certification_id, ?int $limit = 5, ?int $offset = 0)
    {
        $certification = CertificationService::findOrFail($certification_id);
        $members = $certification->getFullSelfTeamMembers()
            ->with(['user' => function (ActiveQuery $query) {
                $query->select(UserHelper::$basicSelect);
            }])
            ->orderBy(['role' => SORT_ASC])
            ->limit($limit + 1)
            ->offset($offset)
            ->asArray()
            ->all();

        $has_next = count($members) > $limit;
        if ($has_next) array_pop($members);

        return [
            'members' => $members,
            'prev_link' => $offset > 0 ? Url::current(['offset' => max(0, $offset - $limit)], true) : null,
            'next_link' => $has_next ? Url::current(['offset' => $offset + $limit], true) : null,
            'offset' => $offset,
        ];
    }

    public function actionAddSelfTeamMembers()
    {
        $data = new AddMembersForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return CertificationService::addSelfTeamMembers($data);
    }

    public function actionRemoveSelfTeamMember(int $user_id)
    {
        return CertificationService::removeSelfTeamMember($user_id);
    }

    public function actionChangeSelfTeamMemberRole(int $user_id)
    {
        $data = new ChangeMemberRoleForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return CertificationService::changeSelfTeamMemberRole($user_id, $data);
    }

    public function actionSubmitForSelfReview()
    {
        return CertificationService::submitForSelfReview();
    }
    
    public function actionSaveSelfReview(int $certification_id)
    {
        $data = new SelfReviewForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return CertificationService::saveSelfReview($certification_id, $data);
    }

    public function actionFinalizeSelfReview(int $certification_id)
    {
        $data = new SelfReviewForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return CertificationService::finalizeSelfReview($certification_id, $data);
    }

    public function actionFullPeerTeamMembers(int $certification_id, ?int $limit = 5, ?int $offset = 0)
    {
        $certification = CertificationService::findOrFail($certification_id);
        $members = $certification->getFullPeerTeamMembers()
            ->with(['user' => function (ActiveQuery $query) {
                $query->select(UserHelper::$basicSelect);
            }])
            ->orderBy(['role' => SORT_ASC])
            ->limit($limit + 1)
            ->offset($offset)
            ->asArray()
            ->all();

        $has_next = count($members) > $limit;
        if ($has_next) array_pop($members);

        return [
            'members' => $members,
            'prev_link' => $offset > 0 ? Url::current(['offset' => max(0, $offset - $limit)], true) : null,
            'next_link' => $has_next ? Url::current(['offset' => $offset + $limit], true) : null,
            'offset' => $offset,
        ];
    }

    public function actionAddPeerTeamMembers(int $certification_id)
    {
        $data = new AddMembersForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return CertificationService::addPeerTeamMembers($certification_id, $data);
    }

    public function actionRemovePeerTeamMember(int $certification_id, int $user_id)
    {
        return CertificationService::removePeerTeamMember($certification_id, $user_id);
    }

    public function actionChangePeerTeamMemberRole(int $certification_id, int $user_id)
    {
        $data = new ChangeMemberRoleForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return CertificationService::changePeerTeamMemberRole($certification_id, $user_id, $data);
    }

    public function actionSubmitForPeerReview(int $certification_id)
    {
        return CertificationService::submitForPeerReview($certification_id);
    }

    public function actionSavePeerReview(int $certification_id)
    {
        $data = new PeerReviewForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return CertificationService::savePeerReview($certification_id, $data);
    }

    public function actionFinalizePeerReview(int $certification_id)
    {
        $data = new PeerReviewForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return CertificationService::finalizePeerReview($certification_id, $data);
    }

    public function actionSaveExternalReview(int $certification_id)
    {
        $data = new ExternalReviewForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return CertificationService::saveExternalReview($certification_id, $data);
    }

    public function actionFinalizeExternalReview(int $certification_id)
    {
        $data = new ExternalReviewForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return CertificationService::finalizeExternalReview($certification_id, $data);
    }
}
