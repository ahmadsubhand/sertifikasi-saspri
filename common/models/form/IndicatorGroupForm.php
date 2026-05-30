<?php

namespace common\models\form;

use yii\base\Model;

class IndicatorGroupForm extends Model
{
    /** @var int */
    public $assessment_id;

    /** @var int|null */
    public $parent_group_id;

    /** @var string */
    public $code;

    /** @var string */
    public $label;

    /** @var int */
    public $order;

    /** @var int */
    public $weight;

    public function rules()
    {
        return [
            [['assessment_id', 'code', 'label', 'order', 'weight'], 'required'],
            [['code', 'label'], 'string', 'max' => 255],
            [['assessment_id', 'parent_group_id', 'order'], 'integer', 'min' => 0],
            [['weight'], 'integer', 'min' => 0, 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'parent_group_id' => 'Grup Induk',
            'code' => 'Kode',
            'label' => 'Label',
            'order' => 'Urutan',
            'weight' => 'Bobot',
        ];
    }
}