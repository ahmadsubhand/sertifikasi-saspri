<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

class Livestock extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%livestock}}';
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
     * Ukuran pinggul (atribut virtual).
     * Properti ini tidak disimpan di tabel `livestock`, namun diperlukan
     * untuk menerima input form dan kemudian diteruskan ke tabel BCS.
     * @var float|null
     */
    public $hips;

    public $livestock_image;

    /**
     * Override daftar atribut agar ActiveRecord mengenali atribut virtual
     * tambahan (hips). Tanpa ini, Yii akan menganggap hips sebagai properti
     * tak dikenal dan melempar UnknownPropertyException ketika diakses di
     * view.
     *
     * @return string[] Daftar nama atribut yang valid untuk model ini.
     */
    public function attributes()
    {
        // Gabungkan atribut bawaan (kolom tabel) dengan atribut virtual
        return array_merge(parent::attributes(), ['hips']);
    }

    public function rules()
    {
        return [
            [['name', 'birthdate', 'type_of_livestock', 'breed_of_livestock', 'purpose', 'maintenance', 'source', 'ownership_status', 'reproduction', 'gender', 'chest_size', 'body_weight', 'health'], 'required', 'message' => '{attribute} tidak boleh kosong.'],
            [['birthdate'], 'required', 'message' => 'Masukkan tanggal lahir ternak.'],
            [['user_id', 'cage_id', 'age'], 'integer'],
            ['name', 'validateLivestockName'],
            [['body_weight', 'chest_size', 'hips'], 'number', 'min' => 0, 'tooSmall' => '{attribute} harus bernilai positif.', 'message' => '{attribute} harus berupa angka.', 'skipOnEmpty' => true],
            ['name', 'string', 'max' => 255],
            [['livestock_image'], 'string'],
            [['eid', 'vid'], 'unique', 'message' => '{attribute} sudah digunakan oleh ternak lain.'],
            [['name'], 'match', 'pattern' => '/^[A-Za-z0-9\s]{3,255}$/', 'message' => 'Nama harus terdiri dari 3 sampai 255 karakter dan hanya boleh berisi huruf, angka, dan spasi.'],
            ['eid', 'string', 'max' => 19, 'message' => 'EID tidak boleh lebih dari 19 digit.'],
            ['eid', 'match', 'pattern' => '/^\d+$/', 'message' => 'EID hanya boleh berisi angka.'],
            [['vid'], 'string', 'max' => 10],
            [['vid'], 'match', 'pattern' => '/^[A-Z]{3}[0-9]{4}$/', 'message' => 'Visual ID harus mengikuti pola tiga huruf besar diikuti empat digit.', 'on' => 'create'],
            [['created_at', 'updated_at', 'birthdate'], 'safe'],
            [['birthdate'], 'date', 'format' => 'php:Y-m-d', 'message' => 'Format tanggal tidak valid. Tolong gunakan format YYYY-MM-DD.'],
            [['birthdate'], 'validateBirthdate'],
            [['livestock_image'], 'file', 'extensions' => ['png', 'jpg', 'jpeg'], 'maxSize' => 1024 * 1024 * 5, 'maxFiles' => 5, 'message' => 'Format file tidak valid atau ukuran file terlalu besar (maksimal 5 MB).'],
            [['livestock_image'], 'file', 'extensions' => 'jpg, png', 'maxFiles' => 5, 'maxSize' => 1024 * 1024 * 5, 'maxFiles' => 5, 'message' => 'Format file tidak valid atau ukuran file terlalu besar (maksimal 5 MB).'],


            // Enum validation rules
            ['gender', 'in', 'range' => ['Jantan', 'Betina']],
            ['type_of_livestock', 'in', 'range' => ['Kambing', 'Sapi']],
            ['breed_of_livestock', 'in', 'range' => ['Madura', 'Bali', 'Limousin', 'Brahman']],
            ['purpose', 'in', 'range' => ['Indukan', 'Penggemukan', 'Tabungan', 'Belum Tahu']],
            ['maintenance', 'in', 'range' => ['Kandang', 'Gembala', 'Campuran']],
            ['source', 'in', 'range' => ['Sejak Lahir', 'Bantuan Pemerintah', 'Beli', 'Beli dari Luar Kelompok', 'Beli dari Dalam Kelompok', 'Inseminasi Buatan', 'Kawin Alam', 'Tidak Tahu']],
            ['ownership_status', 'in', 'range' => ['Sendiri', 'Kelompok', 'Titipan']],
            ['reproduction', 'in', 'range' => ['Tidak Bunting', 'Bunting < 1 bulan', 'Bunting 1 bulan', 'Bunting 2 bulan', 'Bunting 3 bulan', 'Bunting 4 bulan', 'Bunting 5 bulan', 'Bunting 6 bulan', 'Bunting 7 bulan', 'Bunting 8 bulan', 'Bunting 9 bulan', 'Bunting 10 bulan', 'Bunting 11 bulan', 'Bunting > 11 bulan']],
            ['health', 'in', 'range' => ['Sehat', 'Sakit']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'eid' => 'EID',
            'vid' => 'Visual ID',
            'name' => 'Nama',
            'birthdate' => 'Tanggal Lahir',
            'cage_id' => 'Kandang',
            'type_of_livestock' => 'Jenis Ternak',
            'breed_of_livestock' => 'Ras Ternak',
            'purpose' => 'Tujuan Pemeliharaan',
            'maintenance' => 'Pola Pemeliharaan',
            'source' => 'Asal Ternak',
            'ownership_status' => 'Status Kepemilikan',
            'reproduction' => 'Kondisi Reproduksi',
            'gender' => 'Jenis Kelamin',
            'age' => 'Usia',
            'chest_size' => 'Lingkar Dada Pertama',
            'body_weight' => 'Berat Sapi Pertama',
            'hips' => 'Ukuran Pinggul Pertama',
            'health' => 'Kesehatan Ternak',
            'livestock_image' => 'Foto Ternak',
            'created_at'=> 'Dibuat Pada',
            'updated_at'=> 'Diperbarui Pada',
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'user_id',
            'eid',
            'vid',
            'name',
            'birthdate',
            'gender',
            'age',
            'chest_size',
            'body_weight',
            'health',
            'cage',
            'type_of_livestock',
            'breed_of_livestock',
            'purpose',
            'maintenance',
            'source',
            'ownership_status',
            'reproduction',
        ];

        $fields['cage'] = function ($model) {
            return [
                'id' => $model->cage_id,
                'name' => $model->cage->name,
            ];
        };

        // $fields['chest_size'] = function ($model) {
        //     return $model->chest_size . ' cm';
        // };

        // $fields['body_weight'] = function ($model) {
        //     return $model->body_weight . ' kg';
        // };

        $fields['livestock_images'] = function ($model) {
            return array_map(function ($livestockImage) {
                return sprintf('https://storage.googleapis.com/digiternak1/%s', $livestockImage->image_path);
            }, $model->livestockImages);
        };

        $fields['created_at'] = 'created_at';
        $fields['updated_at'] = 'updated_at';

        return $fields;
    }

    public function actionBcsData($id)
    {
    $bcsData = BodyCountScore::find()->where(['livestock_id' => $id])->all();

    $data = [];
    foreach ($bcsData as $bcs) {
        $data[] = [
            'date' => $bcs->date, // Ganti dengan nama atribut tanggal jika berbeda
            'chest_size' => $bcs->chest_size,
            'hips' => $bcs->hips,
            'body_weight' => $bcs->body_weight,
        ];
    }

    return $this->asJson($data);
    }

    public function validateBirthdate($attribute, $params)
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $birthdate = \DateTime::createFromFormat('Y-m-d', $this->$attribute);
    
        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');
    
        if ($birthdate >= $tomorrow) {
            $this->addError($attribute, 'Tanggal lahir ternak tidak boleh lebih dari hari ini.');
        }
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (!empty($this->birthdate)) {
                $this->age = $this->calculateAge($this->birthdate);
            }
            if ($this->isNewRecord && Yii::$app->user && !Yii::$app->user->isGuest) {
                $this->user_id = Yii::$app->user->identity->id;
            }
            if ($this->isNewRecord) {
                // Jika pengguna tidak memasukkan EID, buat otomatis 32 digit unik
                if (empty($this->eid)) {
                    $this->eid = $this->generateEid();
                }
                $this->vid = $this->generateVid();
            }
            return true;
        }
        return false;
    }

    /**
     * Membuat kode EID unik 32 digit.
     * Melakukan loop sampai menemukan nilai yang belum dipakai agar
     * terhindar dari pelanggaran unique constraint.
     *
     * @return string 32 digit EID
     */
    private function generateEid(): string
    {
        do {
            // Generate a random 19-digit number string, which fits in a signed BIGINT
            $part1 = (string)random_int(100000000, 999999999); // 9 digits
            $part2 = (string)random_int(1000000000, 9999999999); // 10 digits
            $eid = $part1 . $part2;
        } while (self::find()->where(['eid' => $eid])->exists());

        return $eid;
    }

    private function generateVid()
    {
        // Generate 3 random uppercase letters
        $letters = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3);

        // Generate a 4 digit random number
        $numbers = sprintf('%04d', mt_rand(0, 9999));

        // Combine the letters and numbers to form the VID
        $vid = $letters . $numbers;

        return $vid;
    }

    public function validateLivestockName($attribute, $params)
    {
        if (!$this->isNewRecord && !$this->isAttributeChanged($attribute)) {
            return;
        }
        $userId = Yii::$app->user->identity->id;
        $existingLivestock = Livestock::find()
            ->where(['name' => $this->$attribute, 'user_id' => $userId])
            ->one();

        if ($existingLivestock) {
            $this->addError($attribute, 'Anda sudah memiliki ternak dengan nama yang sama. Silakan gunakan nama yang berbeda.');
        }
    }

    // Definisikan relasi dengan model LivestockImage
    public function getLivestockImages()
    {
        return $this->hasMany(LivestockImage::class, ['livestock_id' => 'id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    // Definisikan relasi dengan model Cage
    public function getCage()
    {
        return $this->hasOne(Cage::class, ['id' => 'cage_id']);
    }

    /**
     * Mendefinisikan relasi one-to-many antara ternak dan catatan BCS-nya.
     *
     * @return \yii\db\ActiveQuery Daftar catatan BCS untuk ternak ini.
     */
    public function getBcsScores()
    {
        return $this->hasMany(BodyCountScore::class, ['livestock_id' => 'id']);
    }

    /**
     * Mendapatkan satu catatan BCS terbaru berdasarkan kolom created_at.
     *
     * @return \yii\db\ActiveQuery|BodyCountScore|null Catatan BCS terbaru atau null jika belum ada.
     */
    public function getLatestBcs()
    {
        return $this->hasOne(BodyCountScore::class, ['livestock_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Mengambil ukuran pinggul (hips) dari catatan BCS terbaru.
     * Helper ini memudahkan layer view untuk menampilkan data tanpa query tambahan.
     *
     * @return float|null Ukuran pinggul dalam sentimeter, atau null jika belum ada data.
     */
    public function getLatestHips()
    {
        $latestBcs = $this->latestBcs;
        return $latestBcs ? $latestBcs->hips : null;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $result = parent::toArray($fields, $expand, $recursive);
        
        if ($this === null) {
            return [];
        }

        return $result;
    }

    /**
     * Menghitung usia ternak dalam satuan bulan.
     *
     * @param string $birthdate Tanggal lahir dalam format Y-m-d
     * @return int Usia ternak (bulan). Selalu >= 0
     */
    private function calculateAge($birthdate)
    {
        try {
            $birthDate = new \DateTime($birthdate);
        } catch (\Exception $e) {
            return 0;
        }

        $today = new \DateTime('today');
        // Jika tanggal lahir di masa depan, kembalikan 0
        if ($birthDate > $today) {
            return 0;
        }

        $diff   = $today->diff($birthDate);
        $months = ($diff->y * 12) + $diff->m;

        return $months;
    }

    /**
     * Setelah record diambil dari DB, hitung ulang usia agar selalu up-to-date.
     */
    public function afterFind()
    {
        parent::afterFind();

        if (!empty($this->birthdate)) {
            $this->age = $this->calculateAge($this->birthdate);
        }
    }

    public function uploadImage()
    {
        if ($this->validate()) {
            $fileName = $this->id . '_' . $this->livestock_image->baseName . '.' . $this->livestock_image->extension;
            $filePath = Yii::getAlias('@webroot') . '/uploads/' . $fileName;

            if ($this->livestock_image->saveAs($filePath)) {
                $this->image = file_get_contents($filePath); // Menyimpan konten gambar ke atribut 'image'
                return true;
            }
        }
        return false;
    }



    const SCENARIO_UPDATE = 'update';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_UPDATE] = ['name', 'birthdate', 'cage_id', 'type_of_livestock', 'breed_of_livestock', 'maintenance', 'source', 'ownership_status', 'reproduction', 'gender', 'age', 'chest_size', 'body_weight'];
        return $scenarios;
    }

    /**
     * Mengembalikan usia ternak dalam format "X tahun Y bulan" atau "N bulan".
     *
     * @return string
     */
    public function getAgeLabel(): string
    {
        if ($this->age === null) {
            return '-';
        }

        // Jika usia kurang dari 12 bulan tampilkan langsung "n bulan"
        if ($this->age < 12) {
            return $this->age . ' bulan';
        }

        $years  = intdiv($this->age, 12);
        $months = $this->age % 12;

        if ($months === 0) {
            return $years . ' tahun';
        }

        return $years . ' tahun ' . $months . ' bulan';
    }

    /**
     * Mengembalikan data usia ternak dalam bentuk array untuk format tampilan yang fleksibel.
     *
     * @return array|null Array dengan keys 'years' dan 'months', atau null jika tidak ada data
     */
    public function getAgeData(): ?array
    {
        if ($this->age === null) {
            return null;
        }

        $years  = intdiv($this->age, 12);
        $months = $this->age % 12;

        return [
            'years' => $years,
            'months' => $months,
            'total_months' => $this->age
        ];
    }
}
