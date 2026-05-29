<?php

namespace common\models\form;

use yii\base\Model;

class IndicatorForm extends Model
{
    /** @var int */
    public $indicator_group_id;

    /** @var string */
    public $code;

    /** @var string */
    public $label;

    /** @var int */
    public $order;

    public function rules()
    {
        return [
            [['indicator_group_id', 'code', 'label', 'order'], 'required'],
            [['indicator_group_id', 'order'], 'integer'],
            [['code', 'label'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'indicator_group_id' => 'Grup Indikator',
            'code' => 'Kode',
            'label' => 'Label',
            'order' => 'Urutan',
        ];
    }
}