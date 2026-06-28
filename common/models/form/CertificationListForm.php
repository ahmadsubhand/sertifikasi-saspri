<?php

namespace common\models\form;

use common\enums\CertificateLevel;
use yii\base\Model;

class CertificationListForm extends Model
{
    public const ALL = 'all';
    public const COMPLETED = 'completed';
    public const ONGOING = 'on_going';

    /** @var int|null */
    public $province_id;

    /** @var int|null */
    public $regency_id;

    /** @var int|null */
    public $district_id;

    /** @var string */
    public $status;

    /** @var string */
    public $level;

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

            [['status', 'level'], 'string'],
            [['status', 'level'], 'default', 'value' => self::ALL],

            ['status', 'in', 'range' => [self::ALL, self::COMPLETED, self::ONGOING]],
            ['level', 'in', 'range' => [...CertificateLevel::values(), self::ALL]],

            ['limit', 'default', 'value' => 10],
            ['limit', 'integer', 'min' => 1, 'max' => 100],

            ['offset', 'default', 'value' => 0],
            ['offset', 'integer', 'min' => 0],
        ];
    }
}
