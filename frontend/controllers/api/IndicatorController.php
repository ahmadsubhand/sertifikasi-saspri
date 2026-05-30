<?php

namespace frontend\controllers\api;

use common\enums\UserRole;
use common\helpers\ModelHelper;
use common\models\form\IndicatorForm;
use common\models\Indicator;
use common\services\IndicatorService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;

class IndicatorController extends ActiveController
{
    public $modelClass = Indicator::class;

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

    public function actionIndex(int $indicator_group_id)
    {
        return Indicator::find()
            ->where(['indicator_group_id' => $indicator_group_id])
            ->orderBy(['order' => SORT_ASC])
            ->all();
    }
    
    public function actionSave(?int $indicator_id = null)
    {
        $data = new IndicatorForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return IndicatorService::save($indicator_id, $data);
    }

    public function actionDetail(int $indicator_id)
    {
        return IndicatorService::findOrFail($indicator_id);
    }

    public function actionDelete(int $indicator_id)
    {
        return IndicatorService::delete($indicator_id);
    }
}
