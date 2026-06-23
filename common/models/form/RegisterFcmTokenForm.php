<?php

namespace common\models\form;

use common\enums\DeviceType;
use yii\base\Model;

class RegisterFcmTokenForm extends Model
{
    /** @var string */
    public $token;

    /** @var string */
    public $device_type;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['token', 'device_type'], 'string'],
            [['token', 'device_type'], 'required'],
            ['device_type', 'in', 'range' => DeviceType::values()],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'token' => 'Token',
            'device_type' => 'Jenis perangkat',
        ];
    }
}
