<?php

namespace frontend\controllers\api;

use common\helpers\ModelHelper;
use common\models\form\RequestResponseForm;
use common\models\PeerTeamMember;
use common\services\PeerTeamMemberService;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;

class PeerTeamMemberController extends ActiveController
{
    public $modelClass = PeerTeamMember::class;

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

    public function actionJoinRequestResponse(int $peer_team_member_id)
    {
        $data = new RequestResponseForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return PeerTeamMemberService::joinRequestResponse($peer_team_member_id, $data);
    }
}
