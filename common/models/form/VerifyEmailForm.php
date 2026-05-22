<?php

namespace common\models\form;

use yii\base\Model;

class VerifyEmailForm extends Model
{
    /** @var string */
    public $token;

    public function rules()
    {
        return [
            ['token', 'string'],
            ['token', 'required'],
        ];
    }
}
