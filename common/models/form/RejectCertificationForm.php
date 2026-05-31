<?php

namespace common\models\form;

use yii\base\Model;

class RejectCertificationForm extends Model
{
    /** @var string */
    public $rejection_reason;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['rejection_reason', 'required'],
            ['rejection_reason', 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'rejection_reason' => 'Alasan Penolakan',
        ];
    }
}
