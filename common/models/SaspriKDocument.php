<?php

namespace common\models;

/**
 * This is the model class for table "saspri_k_documents".
 *
 * @property int $id
 * @property string $url
 * @property string $type
 * @property int $saspri_k_id
 *
 * @property SaspriK $saspriK
 */
class SaspriKDocument extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'saspri_k_documents';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['url', 'type', 'saspri_k_id'], 'required'],
            [['saspri_k_id'], 'integer'],
            [['url', 'type'], 'string', 'max' => 255],
            [['saspri_k_id'], 'exist', 'skipOnError' => true, 'targetClass' => SaspriK::class, 'targetAttribute' => ['saspri_k_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Url',
            'type' => 'Type',
            'saspri_k_id' => 'Saspri K ID',
        ];
    }

    /**
     * Gets query for [[SaspriK]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSaspriK()
    {
        return $this->hasOne(SaspriK::class, ['id' => 'saspri_k_id']);
    }

}
