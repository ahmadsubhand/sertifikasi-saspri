<?php

namespace common\models;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "indicator_groups".
 *
 * @property int $id
 * @property int|null $assessment_id
 * @property int|null $parent_group_id
 * @property string $code
 * @property string $label
 * @property int $order
 * @property int $weight
 *
 * @property Assessment $assessment
 * @property IndicatorGroup[] $indicatorGroups
 * @property Indicator[] $indicators
 * @property IndicatorGroup $parentGroup
 */
class IndicatorGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'indicator_groups';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['assessment_id', 'parent_group_id'], 'default', 'value' => null],
            [['assessment_id', 'parent_group_id', 'order', 'weight'], 'integer'],
            [['code', 'label', 'order', 'weight'], 'required'],
            [['code', 'label'], 'string', 'max' => 255],
            [['assessment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Assessment::class, 'targetAttribute' => ['assessment_id' => 'id']],
            [['parent_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => IndicatorGroup::class, 'targetAttribute' => ['parent_group_id' => 'id']],
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
            'parent_group_id' => 'Parent Group ID',
            'code' => 'Code',
            'label' => 'Label',
            'order' => 'Order',
            'weight' => 'Weight',
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
     * Gets query for [[IndicatorGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicatorGroups()
    {
        return $this->hasMany(IndicatorGroup::class, ['parent_group_id' => 'id']);
    }

    /**
     * Gets query for [[Indicators]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicators()
    {
        return $this->hasMany(Indicator::class, ['indicator_group_id' => 'id']);
    }

    /**
     * Gets query for [[ParentGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParentGroup()
    {
        return $this->hasOne(IndicatorGroup::class, ['id' => 'parent_group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildGroups(int $certification_id)
    {
        return $this->hasMany(IndicatorGroup::class, ['parent_group_id' => 'id'])
            ->alias('ig')
            ->with([
                'indicators.indicatorOptions' => function (ActiveQuery $query) {
                    $query->alias('io')->orderBy(['io.order' => SORT_ASC]);
                }, 
                'indicators.indicatorScores' => function (ActiveQuery $query) use ($certification_id) {
                    $query->alias('is')->where(['is.certification_id' => $certification_id]);
                },
            ])
            ->orderBy(['ig.order' => SORT_ASC]);
    }
}
