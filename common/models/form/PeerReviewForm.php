<?php

namespace common\models\form;

use common\enums\IndicatorScoreAttribute;
use yii\base\Model;

class PeerReviewForm extends Model
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

            if (!array_key_exists(IndicatorScoreAttribute::PEER_REVIEW, $indicator_score)) {
                $this->addError(
                    $attribute,
                    "Skor untuk indikator dengan id {$indicator_id} wajib diisi"
                );
                continue;
            }

            if (!array_key_exists('status', $indicator_score)) {
                $this->addError(
                    $attribute,
                    "Status untuk indikator {$indicator_id} wajib diisi"
                );
                continue;
            }
        }
    }
}
