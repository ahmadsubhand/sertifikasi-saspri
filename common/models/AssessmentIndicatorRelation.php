<?php

namespace common\models;

/**
 * This is the model class for table "assessment_indicator_relations".
 *
 * @property int $id
 * @property int $assessment_id
 * @property int $indicator_id
 *
 * @property Assessment $assessment
 * @property Indicator $indicator
 */
class AssessmentIndicatorRelation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'assessment_indicator_relations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['assessment_id', 'indicator_id'], 'required'],
            [['assessment_id', 'indicator_id'], 'integer'],
            [['assessment_id', 'indicator_id'], 'unique', 'targetAttribute' => ['assessment_id', 'indicator_id']],
            [['assessment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Assessment::class, 'targetAttribute' => ['assessment_id' => 'id']],
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
            'assessment_id' => 'Assessment ID',
            'indicator_id' => 'Indicator ID',
        ];
    }

    /**
     * Gets query for [[Assessment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAssessment()
    {
        return $this->hasOne(Assessment::class, ['id' => 'assessment_id']);
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
