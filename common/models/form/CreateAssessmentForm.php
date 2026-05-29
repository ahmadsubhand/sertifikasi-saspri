<?php

namespace common\models\form;

use common\enums\CertificateLevel;
use yii\base\Model;

class CreateAssessmentForm extends Model
{
    /** @var string */
    public $title;

    /** @var string */
    public $level;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['title', 'string'],
            ['title', 'required'],

            ['level', 'string'],
            ['level', 'required'],
            ['level', 'in', 'range' => CertificateLevel::values()],
        ];
    }
}
