<?php

namespace common\models;

use common\enums\IndicatorScoreAttribute;
use common\enums\IndicatorStatus;
use common\helpers\FileHelper;
use Exception;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;

/**
 * This is the model class for table "indicator_score".
 *
 * @property int $id
 * @property int $indicator_id
 * @property int $certification_id
 * @property int|null $self_team_score
 * @property int|null $peer_team_score
 * @property int|null $final_score
 * @property string|null $status
 * @property string|null $evidence_url
 *
 * @property Certification $certification
 * @property Indicator $indicator
 */
class IndicatorScore extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'indicator_score';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['self_team_score', 'peer_team_score', 'final_score', 'status', 'evidence_url'], 'default', 'value' => null],
            [['indicator_id', 'certification_id'], 'required'],
            [['indicator_id', 'certification_id', 'self_team_score', 'peer_team_score', 'final_score'], 'integer'],
            [['status', 'evidence_url'], 'string', 'max' => 255],
            [['indicator_id', 'certification_id'], 'unique', 'targetAttribute' => ['indicator_id', 'certification_id']],
            [['certification_id'], 'exist', 'skipOnError' => true, 'targetClass' => Certification::class, 'targetAttribute' => ['certification_id' => 'id']],
            [['indicator_id'], 'exist', 'skipOnError' => true, 'targetClass' => Indicator::class, 'targetAttribute' => ['indicator_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'indicator_id' => 'Indicator ID',
            'certification_id' => 'Certification ID',
            'self_team_score' => 'Self Team Score',
            'peer_team_score' => 'Peer Team Score',
            'final_score' => 'Final Score',
            'status' => 'Status',
            'evidence_url' => 'Evidence Url',
        ];
    }

    /**
     * Gets query for [[Certification]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCertification()
    {
        return $this->hasOne(Certification::class, ['id' => 'certification_id']);
    }

    /**
     * Gets query for [[Indicator]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicator()
    {
        return $this->hasOne(Indicator::class, ['id' => 'indicator_id']);
    }

    public function fillScore(array $indicator_score, string $attribute_name) {
        if (!in_array($attribute_name, IndicatorScoreAttribute::values())) {
            throw new Exception('Invalid attribute name of indicator score');
        }

        $value = (int) $indicator_score[$attribute_name] ?? 0;
        if ($value < 0 || $value > 100) {
            throw new BadRequestHttpException(
                'Terdapat penilaian yang di luar rentang 0-100'
            );
        }
        $this->$attribute_name= $value;

        return $this;
    }

    public function fillStatus(?string $status)
    {
        if (!$status) {
            $this->status = null;
            return $this;
        }

        if (!in_array($status, IndicatorStatus::values())) {
            throw new BadRequestHttpException('Status penilaian tidak valid: ' . $status);
        }

        if ($this->peer_team_score === $this->self_team_score) {
            if ($status !== IndicatorStatus::IDENTICAL) {
                throw new BadRequestHttpException('Status harus ' . IndicatorStatus::list()[IndicatorStatus::IDENTICAL] . ' jika skor sama');
            }
        } else {
            if ($status === IndicatorStatus::IDENTICAL) {
                throw new BadRequestHttpException('Status tidak boleh ' . IndicatorStatus::list()[IndicatorStatus::IDENTICAL] . ' jika skor berbeda');
            }
        }

        $this->status = $status;
        return $this;
    }

    public function handleEvidenceUpload(int $certification_id)
    {
        $file = UploadedFile::getInstanceByName(
            "indicator_scores[$this->indicator_id][evidence]"
        );
        if (!$file) {
            return $this;
        }

        $relative_dir = '/uploads/evidence/' . $certification_id;
        $absolute_dir = Yii::getAlias('@frontend/web' . $relative_dir);

        FileHelper::ensureDirectoryExists($absolute_dir);
        $this->deleteOldEvidence();
        $fileName = $this->generateEvidenceFileName($file->extension);

        if ($file->saveAs($absolute_dir . '/' . $fileName)) {
            $this->evidence_url = $relative_dir . '/' . $fileName;
            return $this;
        } else {
            throw new Exception('Failed to save evidence file');
        }
    }

    protected function deleteOldEvidence(): void
    {
        if (!$this->evidence_url) {
            return;
        }
        $oldFile = Yii::getAlias('@frontend/web' . $this->evidence_url);
        FileHelper::deleteFile($oldFile);
    }

    protected function generateEvidenceFileName(string $extension): string 
    {
        return sprintf(
            'self_%d_%d.%s',
            $this->id,
            time(),
            $extension,
        );
    }
}
