<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bcs_images".
 *
 * @property int $id
 * @property int $bcs_id
 * @property string $image_path
 *
 * @property BodyCountScore $bcs
 */
class BcsImage extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bcs_images}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['bcs_id', 'image_path'], 'required'],
            [['bcs_id'], 'integer'],
            [['image_path'], 'string', 'max' => 255],
            [['bcs_id'], 'exist', 'skipOnError' => true, 'targetClass' => BodyCountScore::class, 'targetAttribute' => ['bcs_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bcs_id' => 'BCS ID',
            'image_path' => 'Image Path',
        ];
    }

    /**
     * Gets query for [[BCS]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBcs()
    {
        return $this->hasOne(BodyCountScore::class, ['id' => 'bcs_id']);
    }
}
