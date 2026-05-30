<?php

namespace frontend\controllers\api;

use common\enums\UserRole;
use common\helpers\ModelHelper;
use common\models\form\IndicatorOptionForm;
use common\models\IndicatorOption;
use common\services\IndicatorOptionService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;

class IndicatorOptionController extends ActiveController
{
    public $modelClass = IndicatorOption::class;

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

    public function actionIndex(int $indicator_id)
    {
        return IndicatorOption::find()
            ->where(['indicator_id' => $indicator_id])
            ->orderBy(['order' => SORT_ASC])
            ->all();
    }

    public function actionSave(?int $indicator_option_id = null)
    {
        $data = new IndicatorOptionForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return IndicatorOptionService::save($indicator_option_id, $data);
    }

    public function actionDetail(int $indicator_option_id)
    {
        return IndicatorOptionService::findOrFail($indicator_option_id);
    }

    public function actionDelete(int $indicator_option_id)
    {
        return IndicatorOptionService::delete($indicator_option_id);
    }
}
