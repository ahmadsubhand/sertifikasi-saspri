<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "districts".
 *
 * @property int $id
 * @property int $regency_id
 * @property string $name
 * @property string $code
 *
 * @property Regency $regency
 * @property SaspriK[] $saspriKs
 */
class District extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'districts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['regency_id', 'name', 'code'], 'required'],
            [['regency_id'], 'integer'],
            [['name', 'code'], 'string', 'max' => 255],
            [['regency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Regency::class, 'targetAttribute' => ['regency_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'regency_id' => 'Regency ID',
            'name' => 'Name',
            'code' => 'Code',
        ];
    }

    /**
     * Gets query for [[Regency]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRegency()
    {
        return $this->hasOne(Regency::class, ['id' => 'regency_id']);
    }

    /**
     * Gets query for [[SaspriKs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSaspriKs()
    {
        return $this->hasMany(SaspriK::class, ['district_id' => 'id']);
    }

}
