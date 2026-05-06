<?php

namespace common\models;

/**
 * This is the model class for table "indicators".
 *
 * @property int $id
 * @property int $indicator_group_id
 * @property string $code
 * @property string $label
 * @property int $order
 *
 * @property AssessmentIndicatorRelation[] $assessmentIndicatorRelations
 * @property Assessment[] $assessments
 * @property Certification[] $certifications
 * @property IndicatorGroup $indicatorGroup
 * @property IndicatorScore[] $indicatorScores
 */
class Indicator extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'indicators';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['indicator_group_id', 'code', 'label', 'order'], 'required'],
            [['indicator_group_id', 'order'], 'integer'],
            [['code', 'label'], 'string', 'max' => 255],
            [['indicator_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => IndicatorGroup::class, 'targetAttribute' => ['indicator_group_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'indicator_group_id' => 'Indicator Group ID',
            'code' => 'Code',
            'label' => 'Label',
            'order' => 'Order',
        ];
    }

    /**
     * Gets query for [[AssessmentIndicatorRelations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAssessmentIndicatorRelations()
    {
        return $this->hasMany(AssessmentIndicatorRelation::class, ['indicator_id' => 'id']);
    }

    /**
     * Gets query for [[Assessments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAssessments()
    {
        return $this->hasMany(Assessment::class, ['id' => 'assessment_id'])->viaTable('assessment_indicator_relations', ['indicator_id' => 'id']);
    }

    /**
     * Gets query for [[Certifications]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCertifications()
    {
        return $this->hasMany(Certification::class, ['id' => 'certification_id'])->viaTable('indicator_scores', ['indicator_id' => 'id']);
    }

    /**
     * Gets query for [[IndicatorGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicatorGroup()
    {
        return $this->hasOne(IndicatorGroup::class, ['id' => 'indicator_group_id']);
    }

    /**
     * Gets query for [[IndicatorScores]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicatorScores()
    {
        return $this->hasMany(IndicatorScore::class, ['indicator_id' => 'id']);
    }

}
