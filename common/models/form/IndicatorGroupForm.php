<?php

namespace common\models\form;

use yii\base\Model;

class IndicatorGroupForm extends Model
{
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
            [['code', 'label', 'order', 'weight'], 'required'],
            [['code', 'label'], 'string', 'max' => 255],
            [['parent_group_id'], 'integer', 'min' => 0],
            [['order'], 'integer'],
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