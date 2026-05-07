<?php

namespace common\models;

/**
 * This is the model class for table "indicator_groups".
 *
 * @property int $id
 * @property int|null $parent_group_id
 * @property string $code
 * @property string $label
 * @property int $order
 * @property int $weight
 *
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
            [['parent_group_id'], 'default', 'value' => null],
            [['parent_group_id', 'order', 'weight'], 'integer'],
            [['code', 'label', 'order', 'weight'], 'required'],
            [['code', 'label'], 'string', 'max' => 255],
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
            'parent_group_id' => 'Parent Group ID',
            'code' => 'Code',
            'label' => 'Label',
            'order' => 'Order',
            'weight' => 'Weight',
        ];
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

}
