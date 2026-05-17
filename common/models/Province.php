<?php

namespace common\models;

/**
 * This is the model class for table "province".
 *
 * @property int $id
 * @property string $name
 * @property string $code
 *
 * @property Regency[] $regencies
 */
class Province extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'province';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'code'], 'required'],
            [['name', 'code'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Code',
        ];
    }

    /**
     * Gets query for [[Regencies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRegencies()
    {
        return $this->hasMany(Regency::class, ['province_id' => 'id']);
    }

}
