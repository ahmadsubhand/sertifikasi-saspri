<?php

namespace common\jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use common\models\User;
use common\helpers\EmailHelper; 

class SendEmailJob extends BaseObject implements JobInterface
{
    /**
     * @var int ID user yang akan dikirimkan email
     */
    public int $userId;

    /**
     * @var string Tipe email yang akan dikirim (contoh: EmailHelper::EMAIL_VERIFICATION, EmailHelper::RESET_PASSWORD_REQUEST)
     */
    public string $type;

    public function execute($queue)
    {
        // Cari data user terbaru dari database berdasarkan ID
        $user = User::findOne($this->userId);
        
        if ($user === null) {
            Yii::warning("SendEmailJob dibatalkan: User dengan ID {$this->userId} tidak ditemukan.", __METHOD__);
            return;
        }

        try {
            $sent = false;

            // Tentukan fungsi helper mana yang dipanggil berdasarkan tipe
            switch ($this->type) {
                case EmailHelper::EMAIL_VERIFICATION:
                    $sent = EmailHelper::sendEmailVerification($user);
                    break;
                case EmailHelper::RESET_PASSWORD_REQUEST:
                    $sent = EmailHelper::sendResetPasswordRequest($user);
                    break;
                // Tambahkan case baru di sini dan di EmailHelper jika nanti ada email lain
                default:
                    Yii::error("Tipe email tidak valid: {$this->type}", __METHOD__);
                    return;
            }
            
            if (!$sent) {
                Yii::error("Gagal mengirimkan email '{$this->type}' ke: {$user->email}", __METHOD__);
            }
        } catch (\Throwable $e) {
            // Tangkap error agar antrean queue tidak macet
            Yii::error('Email Queue Error: ' . $e->getMessage(), __METHOD__);
        }
    }
}