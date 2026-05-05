<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "indicator_groups".
 *
 * @property int $id
 * @property int $parent_group_id
 * @property string $code
 * @property string $label
 * @property int $order
 * @property float $weight
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
            [['parent_group_id', 'code', 'label', 'order', 'weight'], 'required'],
            [['parent_group_id', 'order'], 'integer'],
            [['weight'], 'number'],
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
