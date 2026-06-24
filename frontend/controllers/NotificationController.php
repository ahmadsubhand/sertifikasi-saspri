<?php

namespace frontend\controllers;

use common\helpers\ModelHelper;
use common\models\form\RegisterFcmTokenForm;
use common\models\form\UnregisterFcmTokenForm;
use common\services\NotificationService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class NotificationController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'list' => ['GET'],
                    'unread-count' => ['GET'],
                    'mark-read' => ['POST'],
                    'mark-all-read' => ['POST'],
                    'register-token' => ['POST'],
                    'unregister-token' => ['POST'],
                ],
            ],
        ];
    }

    // Pastikan semua action merespons dalam format JSON
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionList(?int $limit = 10, ?int $offset = 0)
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
        ModelHelper::loadAndValidateOrFail($data, [
            'token' => Yii::$app->request->post('token'),
            'device_type' => 'web',
        ]);
        return NotificationService::registerToken($data);
    }

    public function actionUnregisterToken()
    {
        $data = new UnregisterFcmTokenForm();
        ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->post(''));
        return NotificationService::unregisterToken($data);
    }
}