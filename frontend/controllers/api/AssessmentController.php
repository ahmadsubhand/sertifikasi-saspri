<?php

namespace frontend\controllers\api;

use common\enums\UserRole;
use common\helpers\ModelHelper;
use common\models\Assessment;
use common\models\form\ChangeLevelForm;
use common\models\form\CreateAssessmentForm;
use common\models\form\UpdateAssessmentTitleForm;
use common\services\AssessmentService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;

class AssessmentController extends ActiveController
{
    public $modelClass = Assessment::class;

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
                'create' => ['POST'],
                'detail' => ['GET'],
                'update-title' => ['PATCH'],
                'activate' => ['POST'],
                'change-level' => ['POST'],
                'delete' => ['DELETE'],
            ]
        ];

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => [UserRole::ADMIN],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        return Assessment::find()->orderBy(['updated_at' => SORT_DESC])->all();
    }

    public function actionCreate(?int $assessment_id = null)
    {
        $data = null;
        if (!$assessment_id) {
            $data = new CreateAssessmentForm();
            ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        }
        return AssessmentService::create($assessment_id, $data);
    }

    public function actionDetail(int $assessment_id)
    {
        $assessment = AssessmentService::findOrFail($assessment_id);
        return [
            'assessment' => $assessment,
            'root_groups' => $assessment->getRootGroups()
                ->with(['childGroups.indicators.indicatorOptions'])
                ->asArray()
                ->all(),
        ];
    }

    public function actionUpdateTitle(int $assessment_id)
    {
        $data = new UpdateAssessmentTitleForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return AssessmentService::updateTitle($assessment_id, $data);
    }

    public function actionActivate(int $assessment_id)
    {
        return AssessmentService::activate($assessment_id);
    }

    public function actionChangeLevel(int $assessment_id)
    {
        $data = new ChangeLevelForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return AssessmentService::changeLevel($assessment_id, $data);
    }

    public function actionDelete(int $assessment_id)
    {
        return AssessmentService::delete($assessment_id);
    }
}
