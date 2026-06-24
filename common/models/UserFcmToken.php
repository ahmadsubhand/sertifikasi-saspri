<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Ini adalah model class untuk tabel "user_fcm_token".
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string|null $device_type
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 */
class UserFcmToken extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_fcm_token}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['user_id', 'token'], 'required'],
            [['user_id'], 'integer'],
            [['token'], 'string', 'max' => 500],
            [['device_type'], 'string', 'max' => 50],
            [['token'], 'unique', 'targetAttribute' => ['user_id', 'token'], 'message' => 'Token ini sudah terdaftar untuk pengguna ini.'],
        ];
    }

    /**
     * Relasi ke model User
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}