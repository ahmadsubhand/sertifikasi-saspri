<?php

namespace common\models\form;

use yii\base\Model;

class UpdateAssessmentTitleForm extends Model
{
    /** @var string */
    public $title;

    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'title' => 'Judul',
        ];
    }
}