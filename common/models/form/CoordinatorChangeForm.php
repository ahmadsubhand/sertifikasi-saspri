<?php

namespace common\models\form;

use yii\base\Model;

class CoordinatorChangeForm extends Model
{
    /** @var int */
    public $new_coordinator_id;

    /** @var string */
    public $change_request_reason;

    public function rules()
    {
        return [
            ['new_coordinator_id', 'required'],
            ['new_coordinator_id', 'integer', 'min' => 1],

            ['change_request_reason', 'required'],
            ['change_request_reason', 'trim'],
            ['change_request_reason', 'string', 'max' => 1000],
        ];
    }
}