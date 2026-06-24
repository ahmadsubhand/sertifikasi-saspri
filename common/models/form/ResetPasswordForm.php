<?php

namespace common\models\form;

use yii\base\Model;

class ResetPasswordForm extends Model
{
    /** @var string */
    public $token;

    /** @var string */
    public $password;

    public function rules()
    {
        return [
            ['token', 'string'],
            ['token', 'required'],

            ['password', 'required'],
            ['password', 'string', 'min' => 8],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'token' => 'Token',
            'password' => 'Kata Sandi',
        ];
    }
}
