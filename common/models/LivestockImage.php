<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "livestock_images".
 *
 * @property int $id
 * @property int $livestock_id
 * @property string $image_path
 *
 * @property Livestock $livestock
 */
class LivestockImage extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%livestock_images}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['livestock_id', 'image_path'], 'required'],
            [['livestock_id'], 'integer'],
            [['image_path'], 'string', 'max' => 255],
            [['livestock_id'], 'exist', 'skipOnError' => true, 'targetClass' => Livestock::class, 'targetAttribute' => ['livestock_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'livestock_id' => 'Livestock ID',
            'image_path' => 'Image Path',
        ];
    }

    /**
     * Gets query for [[Livestock]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLivestock()
    {
        return $this->hasOne(Livestock::class, ['id' => 'livestock_id']);
    }
}
