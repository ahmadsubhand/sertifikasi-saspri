<?php

namespace common\models\form;

use yii\base\Model;

class SaspriKListForm extends Model
{
    /** @var int|null */
    public $province_id;

    /** @var int|null */
    public $regency_id;

    /** @var int|null */
    public $district_id;

    /** @var int */
    public $limit;

    /** @var int */
    public $offset;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['province_id', 'regency_id', 'district_id'], 'integer', 'min' => 1],

            ['limit', 'default', 'value' => 10],
            ['limit', 'integer', 'min' => 1, 'max' => 100],

            ['offset', 'default', 'value' => 0],
            ['offset', 'integer', 'min' => 0],
        ];
    }
}
