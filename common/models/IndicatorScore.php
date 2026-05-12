<?php

namespace common\models;

use Exception;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;

/**
 * This is the model class for table "indicator_scores".
 *
 * @property int $id
 * @property int $indicator_id
 * @property int $certification_id
 * @property int|null $self_team_score
 * @property int|null $peer_team_score
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
        return 'indicator_scores';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['self_team_score', 'peer_team_score', 'status', 'evidence_url'], 'default', 'value' => null],
            [['indicator_id', 'certification_id'], 'required'],
            [['indicator_id', 'certification_id', 'self_team_score', 'peer_team_score'], 'integer'],
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

    public function fillSelfTeamScore(int $score)
    {
        $value = (int) $score;
        if ($value < 0 || $value > 100) {
            throw new BadRequestHttpException(
                'Terdapat penilaian yang di luar rentang 0-100'
            );
        }
        $this->self_team_score = $value;
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

        $this->ensureDirectoryExists($absolute_dir);
        $this->deleteOldEvidence();
        $fileName = $this->generateEvidenceFileName($file->extension);

        if ($file->saveAs($absolute_dir . '/' . $fileName)) {
            $this->evidence_url = $relative_dir . '/' . $fileName;
            return $this;
        } else {
            throw new Exception('Failed to save evidence file');
        }
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    protected function deleteOldEvidence(): void
    {
        if (!$this->evidence_url) {
            return;
        }
        $oldFile = Yii::getAlias('@frontend/web' . $this->evidence_url);
        if (is_file($oldFile)) {
            unlink($oldFile);
        }
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
