<?php

namespace common\models\form;

use common\enums\RequestResponse;
use yii\base\Model;

class RequestResponseForm extends Model
{
    /** @var string */
    public $action;

    /** @var string */
    public $rejection_reason;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['action', 'string'],
            ['action', 'required'],
            ['action', 'in', 'range' => RequestResponse::values()],

            ['rejection_reason', 'string'],
        ];
    }
}
