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
            [['livestock_feed', 'forage_costs', 'forage_weight','consentrate_costs', 'consentrate_weight','additive_costs', 'additive_weight'], 'required', 'message' => '{attribute} tidak boleh kosong.'],
            [['forage_costs', 'forage_weight','consentrate_costs', 'consentrate_weight','additive_costs', 'additive_weight'], 'required'],
            [['livestock_name', 'livestock_id', 'livestock_vid', 'livestock_cage', 'location', 'note_date', 'created_at', 'updated_at'], 'safe'],
            [['note_date'], 'date', 'format' => 'php:Y-m-d'],
            [['forage_costs', 'forage_weight','consentrate_costs', 'consentrate_weight','additive_costs', 'additive_weight','vaccine','insemination','pregnancy_check','antibiotics','anthelmintic','vitamin'], 'number', 'min' => 0, 'message' => '{attribute} harus berupa angka positif.'],
            [['location', 'livestock_feed'], 'match', 'pattern' => '/^[A-Za-z0-9\s]{3,255}$/', 'message' => '{attribute} harus terdiri dari 3 sampai 255 karakter dan hanya boleh berisi huruf, angka, dan spasi.'],
            [['details'], 'match', 'pattern' => '/^[A-Za-z0-9\s.,-]{3,255}$/', 'message' => '{attribute} harus terdiri dari 3 sampai 255 karakter dan hanya boleh berisi huruf, angka, spasi, dan tanda baca.'],
            [['documentation'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 10, 'extensions' => ['jpg', 'jpeg', 'png'] , 'maxSize' => 1024 * 1024 * 10, 'message' => 'File tidak valid. File harus berformat jpg, jpeg, atau png dan berukuran maksimal 10MB.'],
        ];
    }

    /**
     * Normalisasi tanggal ke zona WIB dan format Y-m-d agar validasi tidak gagal
     * ketika nilai berasal dari kolom datetime (mis. "2025-12-03 00:00:00").
     */
    public function beforeValidate()
    {
        if (!empty($this->note_date)) {
            try {
                $dt = new \DateTime($this->note_date, new \DateTimeZone('Asia/Jakarta'));
                $this->note_date = $dt->format('Y-m-d');
            } catch (\Exception $e) {
                // biarkan validasi date menangani jika parsing gagal
            }
        } else {
            $this->note_date = (new \DateTime('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
        }

        return parent::beforeValidate();
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
            'note_date',
            'livestock_feed',
            'forage_weight',
            'forage_costs',
            'consentrate_weight',
            'consentrate_costs',
            'additive_weight',
            'additive_costs',
            'vaccine',
            'insemination',
            'pregnancy_check',
            'antibiotics',
            'anthelmintic',
            'vitamin',
            'details',
        ];

        $fields['note_images'] = function ($model) {
            return array_map(function ($noteImage) {
                return sprintf('https://storage.googleapis.com/digiternak1/%s', $noteImage->image_path);
            }, $model->noteImages);
        };

        $fields['created_at'] = 'created_at';
        $fields['updated_at'] = 'updated_at';
        $fields['note_date'] = 'note_date';

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
            'forage_weight'=> 'Berat Pakan Hijauan (kg)',
            'forage_costs'=> 'Harga Pakan Hijauan (per kg)',
            'consentrate_weight'=> 'Berat Pakan Konsentrat (kg)',
            'consentrate_costs'=> 'Harga Pakan Konsentrat (per kg)',
            'additive_weight'=> 'Berat Pakan additive (kg)',
            'additive_costs'=> 'Harga Pakan additive (per kg)',
            'vaccine'=> 'Harga Vaksin',
            'insemination'=> 'Harga Inseminasi Buatan',
            'pregnancy_check'=> 'Harga Cek Kebuntingan',
            'antibiotics'=> 'Harga Antibiotik',
            'anthelmintic'=> 'Harga Obat Cacing',
            'vitamin'=> 'Harga Vitamin',
            'details' => 'Deskripsi',
            'note_date' => 'Tanggal Catatan',
            'documentation' => 'Dokumentasi',
            'created_at'=> 'Dibuat Pada',
            'updated_at'=> 'Diperbarui Pada',
        ];
    }

    public function getCosts()
    {
        return (float) $this->forage_costs * (float) $this->forage_weight
            + (float) $this->consentrate_costs * (float) $this->consentrate_weight
            + (float) $this->additive_costs * (float) $this->additive_weight
            + (float) $this->vaccine
            + (float) $this->insemination
            + (float) $this->pregnancy_check
            + (float) $this->antibiotics
            + (float) $this->anthelmintic
            + (float) $this->vitamin;
    }

    public function validateCosts(string $attribute, $params)
    {
        $costs = Yii::$app->getRequest()->getBodyParams()['forage_costs'];

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

        if ($insert && Yii::$app->has('user') && !Yii::$app->user->isGuest) {
            $this->updateAttributes(['user_id' => Yii::$app->user->identity->id]);
        }

        $livestock = $this->livestock ?: Livestock::findOne($this->livestock_id);
        if ($livestock) {
            History::recalculateForLivestock($livestock);
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $livestock = $this->livestock ?: Livestock::findOne($this->livestock_id);
        if ($livestock) {
            History::recalculateForLivestock($livestock);
        }
    }
}
