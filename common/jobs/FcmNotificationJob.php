<?php

namespace common\jobs;

use yii\base\BaseObject;
use yii\queue\JobInterface;
use Yii;
use common\models\UserFcmToken;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

class FcmNotificationJob extends BaseObject implements JobInterface
{
    public int|null $recipientId;
    public string|null $title;
    public string|null $body;
    public $dataPayload = [];

    public function execute($queue)
    {
        // Ambil semua token aktif milik user tersebut (bisa lebih dari 1 jika login di HP & Web)
        $tokens = UserFcmToken::find()
            ->select('token')
            ->where(['user_id' => $this->recipientId])
            ->column();

        if (empty($tokens)) {
            return; // User tidak memiliki perangkat terdaftar, batalkan pengiriman
        }

        try {
            // Inisialisasi Firebase Factory dengan file credential JSON
            $factory = (new Factory)
                ->withServiceAccount(Yii::getAlias('@common/config/firebase-credentials.json'));
            
            $messaging = $factory->createMessaging();

            $frontendUrl = Yii::$app->params['frontendUrl'];
            $logoUrl = $frontendUrl . '/images/matasapi.svg';

            // Siapkan pesan. 'Notification' untuk UI popup, 'Data' untuk data proses di background (silent)
            $message = CloudMessage::new()
                ->withNotification(FcmNotification::create($this->title, $this->body, $logoUrl))
                ->withData($this->dataPayload);

            // Gunakan sendMulticast karena satu user mungkin memiliki banyak token (device)
            $report = $messaging->sendMulticast($message, $tokens);

            // Jika user uninstall aplikasi, tokennya menjadi invalid. 
            // Harus hapus dari database agar tidak membebani database dan request FCM di masa depan.
            $invalidTokens = $report->invalidTokens();
            if (!empty($invalidTokens)) {
                UserFcmToken::deleteAll(['token' => $invalidTokens]);
                Yii::info('Deleted ' . count($invalidTokens) . ' invalid FCM tokens for user ' . $this->recipientId, __METHOD__);
            }

            if ($report->hasFailures()) {
                Yii::warning("FCM sent with some failures to user {$this->recipientId}", __METHOD__);
            }

        } catch (\Throwable $e) {
            // Tangkap error agar jika FCM gagal (misal: koneksi Google down), aplikasi tidak crash
            Yii::error('FCM Queue Error: ' . $e->getMessage(), __METHOD__);
        }
    }
}