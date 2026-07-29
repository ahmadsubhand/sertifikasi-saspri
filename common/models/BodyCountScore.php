<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class BodyCountScore extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%bcs}}';
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
            [['livestock_id', 'body_weight', 'chest_size', 'hips'], 'required',  'message' => '{attribute} tidak boleh kosong.'],
            [['livestock_id'], 'integer'],
            [['body_weight', 'chest_size', 'hips'], 'number', 'min' => 0, 'tooSmall' => '{attribute} harus bernilai positif.', 'message' => '{attribute} harus berupa angka.', 'skipOnEmpty' => true],
            [['created_at', 'updated_at'], 'safe'],
            [['bcs_image'], 'string'],
            [['bcs_image'], 'file', 'extensions' => ['png', 'jpg', 'jpeg'], 'maxSize' => 1024 * 1024 * 5, 'maxFiles' => 5, 'message' => 'Format file tidak valid atau ukuran file terlalu besar (maksimal 5 MB).'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'livestock_id' => 'ID Ternak',
            'body_weight' => 'Berat Sapi',
            'chest_size' => 'Lingkar Dada',
            'hips' => 'Ukuran Pinggul',
            'bcs_image' => 'Dokumentasi Pemeriksaan',
        ];
    }

    public function fields()
    {
        $fields = ['id'];

        $fields['livestock_id'] = function ($model) {
            return (int) $model->livestock_id;
        };

        $fields = array_merge($fields, [
            'body_weight',
            'chest_size',
            'hips',
        ]);

        $fields['bcs_images'] = function ($model) {
            return array_map(function ($bcsImage) {
                return sprintf('https://storage.googleapis.com/digiternak1/%s', $bcsImage->image_path);
            }, $model->bcsImages);
        };

        $fields['created_at'] = 'created_at';
        $fields['updated_at'] = 'updated_at';

        return $fields;
    }

    public function getBcsImages()
    {
        return $this->hasMany(BcsImage::class, ['bcs_id' => 'id']);
    }
    public function getLivestock()
    {
        return $this->hasOne(Livestock::class, ['id' => 'livestock_id']);
    }
    public function getUserId()
    {
        return $this->livestock ? $this->livestock->user_id : null;
    }

}
