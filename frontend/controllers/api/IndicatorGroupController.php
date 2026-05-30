<?php

namespace frontend\controllers\api;

use common\enums\UserRole;
use common\helpers\ModelHelper;
use common\models\form\IndicatorGroupForm;
use common\models\IndicatorGroup;
use common\services\IndicatorGroupService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;

class IndicatorGroupController extends ActiveController
{
    public $modelClass = IndicatorGroup::class;

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
                'save' => ['PATCH'],
                'detail' => ['GET'],
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

    public function actionIndex(int $assessment_id, ?int $parent_group_id = null)
    {
        return IndicatorGroup::find()
            ->where(['assessment_id' => $assessment_id])
            ->andWhere(['parent_group_id' => $parent_group_id])
            ->orderBy(['order' => SORT_ASC])
            ->all();
    }

    public function actionSave(?int $indicator_group_id = null)
    {
        $data = new IndicatorGroupForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return IndicatorGroupService::save($indicator_group_id, $data);
    }

    public function actionDetail(int $indicator_group_id)
    {
        return IndicatorGroupService::findOrFail($indicator_group_id);
    }

    public function actionDelete(int $indicator_group_id)
    {
        return IndicatorGroupService::delete($indicator_group_id);
    }
}
