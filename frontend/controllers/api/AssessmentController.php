<?php

namespace frontend\controllers\api;

use common\enums\UserRole;
use common\helpers\ModelHelper;
use common\models\Assessment;
use common\models\form\ChangeLevelForm;
use common\models\form\CreateAssessmentForm;
use common\models\form\IndicatorForm;
use common\models\form\IndicatorGroupForm;
use common\models\form\IndicatorOptionForm;
use common\models\form\UpdateAssessmentTitleForm;
use common\services\AssessmentService;
use common\services\IndicatorGroupService;
use common\services\IndicatorOptionService;
use common\services\IndicatorService;
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
                'save-group' => ['PATCH'],
                'delete-group' => ['DELETE'],
                'save-indicator' => ['PATCH'],
                'delete-indicator' => ['DELETE'],
                'save-option' => ['PATCH'],
                'delete-option' => ['DELETE'],
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

    public function actionSaveGroup(int $assessment_id, ?int $indicator_group_id = null)
    {
        $data = new IndicatorGroupForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return IndicatorGroupService::save($assessment_id, $indicator_group_id, $data);
    }

    public function actionDeleteGroup(int $indicator_group_id)
    {
        return IndicatorGroupService::delete($indicator_group_id);
    }

    public function actionSaveIndicator(int $assessment_id, ?int $indicator_id = null)
    {
        $data = new IndicatorForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return IndicatorService::save($assessment_id, $indicator_id, $data);
    }

    public function actionDeleteIndicator(int $indicator_id)
    {
        return IndicatorService::delete($indicator_id);
    }

    public function actionSaveOption(?int $indicator_option_id = null)
    {
        $data = new IndicatorOptionForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return IndicatorOptionService::save($indicator_option_id, $data);
    }

    public function actionDeleteOption(int $indicator_option_id)
    {
        return IndicatorOptionService::delete($indicator_option_id);
    }
}
