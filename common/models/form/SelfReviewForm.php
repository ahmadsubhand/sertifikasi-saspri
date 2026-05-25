<?php

namespace common\models\form;

use common\enums\IndicatorScoreAttribute;
use common\helpers\FileHelper;
use yii\base\Model;
use yii\web\UploadedFile;

class SelfReviewForm extends Model
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

    public function validateIndicatorScores(string $attribute)
    {
        if (!is_array($this->$attribute)) {
            $this->addError($attribute, 'Parameter indicator scores harus berupa array');
            return;
        }

        foreach ($this->$attribute as $indicator_score_id => $indicator_score) {
            if (!is_array($indicator_score)) {
                $this->addError(
                    $attribute,
                    "Format di dalam indicator scores {$indicator_score_id} tidak valid"
                );
                continue;
            }

            if (!array_key_exists(IndicatorScoreAttribute::SELF_REVIEW, $indicator_score)) {
                $this->addError(
                    $attribute,
                    "Skor untuk indikator {$indicator_score_id} wajib diisi"
                );
                continue;
            }

            if (
                filter_var($indicator_score[IndicatorScoreAttribute::SELF_REVIEW], FILTER_VALIDATE_INT) === false
            ) {
                $this->addError(
                    $attribute,
                    "Skor untuk indikator {$indicator_score_id} harus berupa bilangan bulat"
                );
                continue;
            }

            $score = (int) $indicator_score[IndicatorScoreAttribute::SELF_REVIEW];

            if ($score < 0 || $score > 100) {
                $this->addError(
                    $attribute,
                    "Skor untuk indikator {$indicator_score_id} harus antara 0 sampai 100"
                );
            }

            $file = UploadedFile::getInstanceByName(
                "indicator_scores[{$indicator_score_id}][evidence]"
            );

            if ($file) {
                if (!in_array($file->extension, FileHelper::$allowed_extensions)) {
                    $this->addError(
                        $attribute,
                        "Bukti untuk indikator {$indicator_score_id} harus berupa file pdf, gambar, word, excel, atau csv"
                    );
                }

                if ($file->size > 5 * 1024 * 1024) {
                    $this->addError(
                        $attribute,
                        "Ukuran Bukti untuk indikator {$indicator_score_id} maksimal 5MB"
                    );
                }
            }
        }
    }
}