<?php

namespace common\models\form;

use yii\base\Model;

class UnregisterFcmTokenForm extends Model
{
    /** @var string */
    public $token;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['token', 'string'],
            ['token', 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'token' => 'Token',
        ];
    }
}
