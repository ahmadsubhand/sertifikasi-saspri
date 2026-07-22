<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Note extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%note}}';
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

    public function rules()
    {
        return [
            [['livestock_feed', 'costs', 'feed_weight'], 'required', 'message' => '{attribute} tidak boleh kosong.'],
            [['costs'], 'required'],
            [['livestock_name', 'livestock_id', 'livestock_vid', 'livestock_cage', 'location', 'created_at', 'updated_at'], 'safe'],
            [['costs', 'feed_weight'], 'number', 'min' => 0, 'message' => '{attribute} harus berupa angka positif.'],
            [['location', 'livestock_feed', 'vitamin'], 'match', 'pattern' => '/^[A-Za-z0-9\s]{3,255}$/', 'message' => '{attribute} harus terdiri dari 3 sampai 255 karakter dan hanya boleh berisi huruf, angka, dan spasi.'],
            [['details'], 'match', 'pattern' => '/^[A-Za-z0-9\s.,-]{3,255}$/', 'message' => '{attribute} harus terdiri dari 3 sampai 255 karakter dan hanya boleh berisi huruf, angka, spasi, dan tanda baca.'],
            [['documentation'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 10, 'extensions' => ['jpg', 'jpeg', 'png'] , 'maxSize' => 1024 * 1024 * 10, 'message' => 'File tidak valid. File harus berformat jpg, jpeg, atau png dan berukuran maksimal 10MB.'],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'livestock_id',
            'livestock_vid',
            'livestock_name',
            'livestock_cage',
            'location',
            'livestock_feed',
            'feed_weight',
            'vitamin',
            'costs',
            'details',
        ];

        $fields['note_images'] = function ($model) {
            return array_map(function ($noteImage) {
                return sprintf('https://storage.googleapis.com/digiternak1/%s', $noteImage->image_path);
            }, $model->noteImages);
        };

        $fields['created_at'] = 'created_at';
        $fields['updated_at'] = 'updated_at';

        return $fields;
    }

    public function attributeLabels()
    {
        return [
            'livestock_vid' => 'Visual ID',
            'livestock_name' => 'Nama Ternak',
            'livestock_cage' => 'Kandang',
            'location' => 'Lokasi',
            'livestock_feed' => 'Pakan Ternak',
            'feed_weight' => 'Berat Pakan',
            'vitamin' => 'Vitamin',
            'costs' => 'Biaya Pakan',
            'details' => 'Deskripsi',
            'documentation' => 'Dokumentasi',
            'created_at'=> 'Dibuat Pada',
            'updated_at'=> 'Diperbarui Pada',
        ];
    }

    public function validateCosts($attribute, $params)
    {
        $costs = Yii::$app->getRequest()->getBodyParams()['costs'];

        if (is_float($costs)) {
            $this->addError($attribute, 'Biaya harus berupa angka bulat positif.');
        } elseif (!preg_match('/^\d+$/', $this->$attribute)) {
            $this->addError($attribute, 'Biaya harus berupa angka bulat positif.');
        }
    }

    // Definisikan relasi dengan model NoteImage
    public function getNoteImages()
    {
        return $this->hasMany(NoteImage::class, ['note_id' => 'id']);
    }
    
    /**
     * Gets the related Livestock model.
     * @return \yii\db\ActiveQuery
     */
    public function getLivestock()
    {
        return $this->hasOne(Livestock::class, ['id' => 'livestock_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Get user_id from the currently logged in user
        $userId = Yii::$app->user->identity->id;

        // Save user_id
        $this->updateAttributes(['user_id' => $userId]);
    }
}
