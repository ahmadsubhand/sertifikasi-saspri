<?php

namespace common\models\form;

use common\enums\CertificateLevel;
use yii\base\Model;

class ChangeLevelForm extends Model
{
    /** @var string */
    public $level = [];

    public function rules()
    {
        return [
            ['level', 'string'],
            ['level', 'required'],
            ['level', 'in', 'range' => CertificateLevel::values()],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'level' => 'Tingkat',
        ];
    }
}
