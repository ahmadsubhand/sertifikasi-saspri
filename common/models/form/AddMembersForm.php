<?php

namespace common\models\form;

use yii\base\Model;

class AddMembersForm extends Model
{
    /** @var array */
    public $user_ids = [];

    public function rules()
    {
        return [
            ['user_ids', 'required'],
            ['user_ids', 'each', 'rule' => ['integer', 'min' => 1]],
            ['user_ids', 'validateUserIdsArray'],
        ];
    }

    public function validateUserIdsArray(string $attribute): void
    {
        if (!is_array($this->$attribute)) {
            $this->addError(
                $attribute,
                'Parameter user_ids harus berupa array'
            );
        }
    }
}
