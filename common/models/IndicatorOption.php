<?php

namespace common\models;

/**
 * This is the model class for table "indicator_option".
 *
 * @property int $id
 * @property int $indicator_id
 * @property string $code
 * @property string $label
 * @property int $order
 * @property int $weight
 *
 * @property Indicator $indicator
 */
class IndicatorOption extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'indicator_option';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['indicator_id', 'code', 'label', 'order', 'weight'], 'required'],
            [['indicator_id', 'order', 'weight'], 'integer'],
            [['code', 'label'], 'string', 'max' => 255],
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
            'code' => 'Code',
            'label' => 'Label',
            'order' => 'Order',
            'weight' => 'Weight',
        ];
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
