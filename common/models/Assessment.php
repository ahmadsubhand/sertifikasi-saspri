<?php

namespace common\models;

/**
 * This is the model class for table "assessments".
 *
 * @property int $id
 * @property string $title
 * @property int|null $is_active
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $released_at
 *
 * @property AssessmentIndicatorRelation[] $assessmentIndicatorRelations
 * @property Certification[] $certifications
 * @property Indicator[] $indicators
 */
class Assessment extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'assessments';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['released_at'], 'default', 'value' => null],
            [['is_active'], 'default', 'value' => 0],
            [['title', 'created_at', 'updated_at'], 'required'],
            [['is_active'], 'integer'],
            [['created_at', 'updated_at', 'released_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['is_active'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'is_active' => 'Is Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'released_at' => 'Released At',
        ];
    }

    /**
     * Gets query for [[AssessmentIndicatorRelations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAssessmentIndicatorRelations()
    {
        return $this->hasMany(AssessmentIndicatorRelation::class, ['assessment_id' => 'id']);
    }

    /**
     * Gets query for [[Certifications]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCertifications()
    {
        return $this->hasMany(Certification::class, ['assessment_id' => 'id']);
    }

    /**
     * Gets query for [[Indicators]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicators()
    {
        return $this->hasMany(Indicator::class, ['id' => 'indicator_id'])->viaTable('assessment_indicator_relations', ['assessment_id' => 'id']);
    }

}
