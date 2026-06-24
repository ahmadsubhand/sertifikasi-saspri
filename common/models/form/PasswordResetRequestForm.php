<?php

namespace common\models\form;

use yii\base\Model;

class PasswordResetRequestForm extends Model
{
    /** @var string */
    public $email;

    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'email' => 'Email',
        ];
    }
}
