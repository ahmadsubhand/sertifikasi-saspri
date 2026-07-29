<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%price_list}}".
 *
 * @property int $user_id
 * @property int $electricity_water
 * @property int $inflation
 * @property int $employee
 * @property int $wage
 * @property int $livestock_per_employee
 * @property int $margin
 * @property int $land
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 */
class PriceList extends ActiveRecord
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%price_list}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Required and integer validation for cost fields
            [
                ['electricity_water', 'inflation', 'employee', 'wage', 'livestock_per_employee', 'margin', 'land'],
                'required',
                'message' => '{attribute} tidak boleh kosong.'
            ],
            [
                ['electricity_water', 'inflation', 'employee', 'wage', 'livestock_per_employee', 'margin', 'land'],
                'integer',
                'min' => 0,
                'tooSmall' => '{attribute} harus bernilai positif.'
            ],
            // Ensure one record per user
            ['user_id', 'unique', 'message' => 'Setiap pengguna hanya boleh memiliki satu daftar harga.'],
            // Timestamps are safe
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $fields = [
            'electricity_water',
            'inflation',
            'employee',
            'wage',
            'livestock_per_employee',
            'margin',
            'land'
        ];
        $scenarios[self::SCENARIO_CREATE] = $fields;
        $scenarios[self::SCENARIO_UPDATE] = $fields;
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id'                => 'User ID',
            'electricity_water'      => 'Biaya Listrik & Air',
            'land'                   => 'Biaya Sewa Lahan',
            'employee'               => 'Jumlah Tenaga Kerja',
            'wage'                   => 'Gaji Tenaga Kerja',
            'livestock_per_employee' => 'Ternak per Tenaga Kerja',
            'margin'                 => 'Margin',
            'inflation'              => 'Inflasi',
            'created_at'             => 'Dibuat Pada',
            'updated_at'             => 'Diperbarui Pada',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Before validation, set user_id for new records.
     *
     * @return bool
     */
    public function beforeValidate()
    {
        if ($this->isNewRecord) {
            $this->user_id = Yii::$app->user->identity->id;
        }
        return parent::beforeValidate();
    }
}
