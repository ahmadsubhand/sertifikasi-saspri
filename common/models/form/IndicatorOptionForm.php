<?php

namespace common\models\form;

use yii\base\Model;

class IndicatorOptionForm extends Model
{
    /** @var int */
    public $indicator_id;

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
            [['indicator_id', 'code', 'label', 'order', 'weight'], 'required'],
            [['indicator_id'], 'integer', 'min' => 0],
            [['code', 'label', 'order'], 'string', 'max' => 255],
            [['weight'], 'integer', 'min' => 0, 'max' => 100],
        ];
    }
}