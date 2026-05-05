<?php

namespace common\models;

use Yii;

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

}
