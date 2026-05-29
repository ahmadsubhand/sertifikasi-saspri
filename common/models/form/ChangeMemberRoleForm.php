<?php

namespace common\models\form;

use common\enums\TeamRole;
use yii\base\Model;

class ChangeMemberRoleForm extends Model
{
    /** @var string */
    public $role = [];

    public function rules()
    {
        return [
            ['role', 'string'],
            ['role', 'required'],
            ['role', 'in', 'range' => TeamRole::values()],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'role' => 'Peran',
        ];
    }
}
