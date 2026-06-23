<?php

namespace frontend\controllers\api;

use common\helpers\ModelHelper;
use common\models\form\RegisterFcmTokenForm;
use common\models\form\UnregisterFcmTokenForm;
use common\models\Notification;
use common\services\NotificationService;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;

class NotificationController extends ActiveController
{
    public $modelClass = Notification::class;

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
                'unread-count' => ['GET'],
                'mark-as-read' => ['POST'],
                'mark-all-as-read' => ['POST'],
                'register-token' => ['POST'],
                'unregister-token' => ['POST'],
            ]
        ];

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => [
                'index',
                'unread-count',
                'mark-read',
                'mark-all-read',
                'register-token',
                'unregister-token'
            ]
        ];

        return $behaviors;
    }

    public function actionIndex(?int $limit = 10, ?int $offset = 0)
    {
        return NotificationService::list($limit, $offset);
    }

    public function actionUnreadCount()
    {
        return NotificationService::unreadCount();
    }

    public function actionMarkRead(int $notification_id)
    {
        return NotificationService::markRead($notification_id);
    }

    public function actionMarkAllRead()
    {
        return NotificationService::markAllRead();
    }

    public function actionRegisterToken()
    {
        $data = new RegisterFcmTokenForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return NotificationService::registerToken($data);
    }

    public function actionUnregisterToken()
    {
        $data = new UnregisterFcmTokenForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->getBodyParams());
        return NotificationService::unregisterToken($data);
    }
}
