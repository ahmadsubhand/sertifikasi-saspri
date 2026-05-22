<?php

namespace common\models\form;

use yii\base\Model;

class LoginForm extends Model
{
    /** @var string */
    public $username;

    /** @var string */
    public $password;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'string'],
            ['username', 'required'],

            ['password', 'string'],
            ['password', 'required'],
        ];
    }
}
