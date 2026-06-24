<?php

namespace common\services;

use common\jobs\FcmNotificationJob;
use common\models\form\RegisterFcmTokenForm;
use common\models\form\UnregisterFcmTokenForm;
use common\models\Notification;
use common\models\UserFcmToken;
use Mpdf\Container\NotFoundException;
use Yii;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\UnprocessableEntityHttpException;

class NotificationService
{
    /**
     * Sends a notification to one or multiple recipients.
     *
     * @param int $recipientId ID of the user receiving the notification
     * @param string $title Notification title
     * @param string $body Notification body content
     * @param array $options Optional configurations:
     * - 'sender_id' (int|null): ID of the user sending the notification, or null for system
     * - 'web_link' (string|null): Redirect link for web application
     * - 'api_link' (string|null): Redirect link/route for mobile application
     * - 'channels' (array): List of target channels. Defaults to ['db']. Available: 'db', 'fcm'.
     * @return Notification[] List of created database Notification models
     */
    public static function send(
        int|array $recipientId,
        string $title,
        string $body,
        array $options = []
    ) {
        $recipientId = (int) $recipientId;
        $senderId = $options['sender_id'] ?? null;
        $webLink = $options['web_link'] ?? null;
        $apiLink = $options['api_link'] ?? null;
        $channels = $options['channels'] ?? ['db'];

        $notification = null;

        // 1. Simpan ke Database (Lokal)
        if (in_array('db', $channels)) {
            $notification = new Notification();
            $notification->recipient_id = (int) $recipientId;
            $notification->sender_id = $senderId;
            $notification->title = $title;
            $notification->body = $body;
            $notification->web_link = $webLink;
            $notification->api_link = $apiLink;
            if (!$notification->save()) {
                Yii::error('Failed to save notification: ' . json_encode($notification->errors), __METHOD__);
            }
        }

        // 2. Kirim via Firebase Cloud Messaging (FCM)
        if (in_array('fcm', $channels)) {
            Yii::$app->queue->push(new FcmNotificationJob([
                'recipientId' => $recipientId,
                'title' => $title,
                'body' => $body,
                'dataPayload' => [
                    'web_link' => $webLink ?? '',
                    'api_link' => $apiLink ?? '',
                    'notification_id' => $notification ? (string) $notification->id : '', 
                ],
            ]));
        }

        return $notification;
    }

    public static function list(?int $limit = 10, ?int $offset = 0)
    {
        $notifications = Notification::find()
            ->where(['recipient_id' => Yii::$app->user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit + 1)
            ->offset($offset)
            ->all();

        $has_next = count($notifications) > $limit;
        if ($has_next) array_pop($notifications);

        return [
            'notifications' => $notifications,
            'prev_link' => $offset > 0 ? Url::current(['offset' => max(0, $offset - $limit)], true) : null,
            'next_link' => $has_next ? Url::current(['offset' => $offset + $limit], true) : null,
        ];
    }

    public static function unreadCount()
    {
        $total = Notification::find()
            ->where([
                'recipient_id' => Yii::$app->user->id,
                'read_at' => null,
            ])
            ->count();
        return [
            'total' => $total,
        ];
    }

    public static function markRead(int $notification_id)
    {
        $notification = Notification::findOne(['id' => $notification_id]);

        if (!$notification) {
            throw new NotFoundException('Notifikasi tidak ditemukan');
        }
        if ($notification->recipient_id !== Yii::$app->user->id) {
            throw new ForbiddenHttpException('Anda hanya dapat menandai notifikasi milik sendiri sebagai sudah dibaca');
        }
        if ($notification->read_at) {
            throw new UnprocessableEntityHttpException('Notifikasi sudah ditandai sebagai dibaca');
        }

        $notification->markAsRead();

        return $notification;
    }

    public static function markAllRead()
    {
        /** @var Notification[] */
        $unreadNotifications = Notification::find()
            ->where([
                'recipient_id' => Yii::$app->user->id,
                'read_at' => null,
            ])
            ->all();
        foreach ($unreadNotifications as $notification) {
            $notification->markAsRead();
        }
        return [
            'total' => count($unreadNotifications)
        ];
    }

    public static function registerToken(RegisterFcmTokenForm $data)
    {
        $userId = Yii::$app->user->id;

        // Cek apakah token ini sudah pernah disimpan untuk user ini
        $fcmToken = UserFcmToken::findOne(['user_id' => $userId, 'token' => $data->token]);

        if (!$fcmToken) {
            // Jika belum ada, buat baru
            $fcmToken = new UserFcmToken();
            $fcmToken->user_id = $userId;
            $fcmToken->token = $data->token;
        }

        // Update device type jika ada perubahan/baru
        $fcmToken->device_type = $data->device_type;
        $fcmToken->save();

        return $fcmToken;
    }

    public static function unregisterToken(UnregisterFcmTokenForm $data)
    {
        $deletedCount = UserFcmToken::deleteAll([
            'user_id' => Yii::$app->user->id,
            'token' => $data->token,
        ]);

        return [
            'count' => $deletedCount,
        ];
    }
}