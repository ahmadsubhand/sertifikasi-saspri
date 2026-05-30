<?php

namespace common\models\form;
use yii\base\Model;

class ExternalReviewForm extends Model
{
    /** @var array */
    public $indicator_scores = [];

    public function rules()
    {
        return [
            [['indicator_scores'], 'required'],
            [['indicator_scores'], 'validateIndicatorScores'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'indicator_scores' => 'Skor Indikator',
        ];
    }

    public function validateIndicatorScores(string $attribute)
    {
        if (!is_array($this->$attribute)) {
            $this->addError($attribute, 'Parameter indicator_scores harus berupa array');
            return;
        }

        foreach ($this->$attribute as $indicator_id => $indicator_score) {
            if (!is_array($indicator_score)) {
                $this->addError(
                    $attribute,
                    "Format di dalam indicator_scores dengan id indicator {$indicator_id} tidak valid"
                );
                continue;
            }
        }
    }
}
