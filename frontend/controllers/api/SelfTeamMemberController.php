<?php

namespace frontend\controllers\api;

use common\helpers\ModelHelper;
use common\models\form\RequestResponseForm;
use common\models\SelfTeamMember;
use common\services\SelfTeamMemberService;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;

class SelfTeamMemberController extends ActiveController
{
    public $modelClass = SelfTeamMember::class;

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
                'join-request-response' => ['POST'],
            ]
        ];

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];

        return $behaviors;
    }

    public function actionJoinRequestResponse(int $self_team_member_id)
    {
        $data = new RequestResponseForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return SelfTeamMemberService::joinRequestResponse($self_team_member_id, $data);
    }
}
