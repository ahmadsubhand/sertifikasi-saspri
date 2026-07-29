<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use common\models\Livestock;
use yii\helpers\Url;

class CowFamilyTree extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%cow_family_tree}}';
    }

    public function rules()
    {
        return [
            [['main_cow_id'], 'required'],

            [['main_cow_id', 'father_id', 'mother_id'], 'integer'],

            [['partners', 'created_at', 'updated_at'], 'safe'],
            [['partners'], 'validatePartners'],

            [['main_cow_id'], 'exist',
                'targetClass' => Livestock::class,
                'targetAttribute' => ['main_cow_id' => 'id'],
                'message' => 'Sapi tidak ditemukan.',
            ],

            [['father_id'], 'exist',
                'targetClass' => Livestock::class,
                'targetAttribute' => ['father_id' => 'id'],
                'skipOnEmpty' => true,
                'message' => 'Sapi pejantan tidak ditemukan.',
            ],

            [['mother_id'], 'exist',
                'targetClass' => Livestock::class,
                'targetAttribute' => ['mother_id' => 'id'],
                'skipOnEmpty' => true,
                'message' => 'Sapi induk tidak ditemukan.',
            ],

            [['main_cow_id'], 'unique',
                'targetAttribute' => 'main_cow_id',
                'message' => 'Sapi ini sudah memiliki silsilah yang terdaftar.',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'main_cow_id' => 'Sapi Utama',
            'father_id' => 'Ayah',
            'mother_id' => 'Ibu',
            'partners' => 'Pasangan',
            'created_at' => 'Dibuat Pada',
            'updated_at' => 'Diperbarui Pada',
        ];
    }

    public function beforeValidate()
    {
        $userId = Yii::$app->user->identity->id;

        $mainCowId = $this->main_cow_id;
        $fatherId  = $this->father_id;
        $motherId  = $this->mother_id;

        // Validasi: main_cow_id, father_id, mother_id harus dimiliki user
        $cowIds = [
            'main_cow_id' => $mainCowId,
            'father_id' => $fatherId,
            'mother_id' => $motherId,
        ];

        foreach ($cowIds as $field => $id) {
            if ($id !== null) {
                $exists = Livestock::find()
                    ->where(['id' => $id, 'user_id' => $userId])
                    ->exists();

                if (!$exists) {
                    $this->addError($field, "Sapi dengan ID {$id} tidak dimiliki oleh pengguna saat ini.");
                }
            }
        }

        // Validasi: sapi tidak bisa menjadi orang tua dari dirinya sendiri
        if ($fatherId && $fatherId == $mainCowId || $motherId && $motherId == $mainCowId) {
            $this->addError('main_cow_id', 'Sapi tidak bisa menjadi orang tua dari dirinya sendiri');
            return false;
        }

        // Validasi: sapi tidak bisa menjadi pasangan dirinya sendiri
        if ($this->partners) {
            $partners = json_decode($this->partners, true) ?: [];
            if (in_array($mainCowId, $partners, true)) {
                $this->addError('partners', 'Sapi tidak bisa menjadi pasangan dirinya sendiri.');
            }
        }

        // Validasi looping (sapi menjadi anak/cucunya sendiri)
        if ($this->createsCircularRelation($this->main_cow_id, $this->father_id) || $this->createsCircularRelation($this->main_cow_id, $this->mother_id)) {
            $this->addError('main_cow_id', 'Relasi ini menyebabkan siklus silsilah (infinite loop).');
            return false;
        }

        // Validasi: gender orang tua
        if ($fatherId) {
            $father = Livestock::findOne(['id' => $fatherId, 'user_id' => $userId]);
            if ($father && $father->gender !== 'Jantan') {
                $this->addError('father_id', 'Ayah harus merupakan sapi jantan.');
            }
        }

        if ($motherId) {
            $mother = Livestock::findOne(['id' => $motherId, 'user_id' => $userId]);
            if ($mother && $mother->gender !== 'Betina') {
                $this->addError('mother_id', 'Ibu harus merupakan sapi betina.');
            }
        }

        // Validasi gender pasangan
        $mainCow = Livestock::findOne(['id' => $this->main_cow_id, 'user_id' => $userId]);

        if ($this->partners) {
            $partners = json_decode($this->partners, true) ?: [];
            foreach ($partners as $partnerId) {
                $partner = Livestock::findOne(['id' => $partnerId, 'user_id' => $userId]);
                if ($partner && $partner->gender == $mainCow->gender) {
                    $this->addError('partners', 'Pasangan harus merupakan sapi dengan jenis kelamin yang berbeda.');
                }
            }
        }

        return parent::beforeValidate();
    }

    public function validatePartners(string $attribute, $params)
    {
        $userId = Yii::$app->user->id;

        // Decode JSON
        $partners = json_decode($this->$attribute, true);

        if (!is_array($partners)) {
            $this->addError($attribute, 'Format pasangan tidak valid. Harus berupa array JSON.');
            return;
        }

        // Cek bahwa semua item adalah integer dan milik user
        foreach ($partners as $partnerId) {
            if (!is_int($partnerId)) {
                $this->addError($attribute, "Semua ID pasangan harus berupa angka.");
                break;
            }

            $exists = Livestock::find()
                ->where(['id' => $partnerId, 'user_id' => $userId])
                ->exists();

            if (!$exists) {
                $this->addError($attribute, "Pasangan dengan ID {$partnerId} tidak ditemukan atau bukan milik Anda.");
            }
        }
    }


    /**
     * Mengecek apakah terjadi circular relationship.
     */
    protected function createsCircularRelation(int $mainCowId, int $potentialParentId)
    {
        // Hindari validasi jika parent tidak diset
        if (!$potentialParentId) {
            return false;
        }

        // Ambil semua ascendant (ayah, kakek, dst)
        $visited = [];
        $currentId = $potentialParentId;

        while ($currentId) {
            if (in_array($currentId, $visited)) {
                // Hindari infinite loop jika data rusak
                break;
            }
            if ($currentId == $mainCowId) {
                return true; // Sapi ditemukan di atasnya sendiri → circular
            }

            $visited[] = $currentId;

            $parentTree = self::findOne(['main_cow_id' => $currentId]);
            if (!$parentTree) break;

            // Cek salah satu parent di atas
            $currentId = $parentTree->father_id ?: $parentTree->mother_id;
        }

        return false;
    }
    

    public function getFather()
    {
        return $this->hasOne(Livestock::class, ['id' => 'father_id']);
    }

    public function getMother()
    {
        return $this->hasOne(Livestock::class, ['id' => 'mother_id']);
    }

    public function getPartners()
    {
        return $this->hasMany(Livestock::class, ['id' => 'partners']);
    }

    public static function getChildrenIds(int $cowId): array
    {
        /**
         * Mengambil daftar ID anak langsung dari seekor sapi.
         * Logika baru: setiap entri CowFamilyTree yang memiliki father_id atau mother_id = $cowId
         * dianggap anak langsung. Sebagai fallback, data di kolom `children` masih dipertimbangkan
         * untuk kompatibilitas lama.
         *
         * @param int $cowId ID sapi induk
         * @return int[] Daftar ID anak langsung
         */

        // 1. Cari berdasarkan kolom father_id / mother_id
        $entries = self::find()
            ->where(['father_id' => $cowId])
            ->orWhere(['mother_id' => $cowId])
            ->all();

        $ids = array_map(static fn(self $e) => (int)$e->main_cow_id, $entries);

        // 2. Fallback: gabungkan dengan data kolom children (jika masih ada)
        $legacyIds = [];
        $legacyEntry = self::find()->where(['main_cow_id' => $cowId])->one();
        if ($legacyEntry && !empty($legacyEntry->children)) {
            $decoded = is_string($legacyEntry->children)
                ? json_decode($legacyEntry->children, true)
                : (is_array($legacyEntry->children) ? $legacyEntry->children : []);
            if (is_array($decoded)) {
                $legacyIds = array_map('intval', $decoded);
            }
        }

        return array_values(array_unique(array_merge($ids, $legacyIds)));
    }


    public static function getFamilyData(int $livestockId): array
    {
        /**
         * Mengambil data keluarga (ayah, ibu, pasangan, anak, saudara) untuk suatu hewan ternak.
         * Logika ini sebelumnya didefinisikan di views dan dipindahkan ke model agar sesuai pola MVC.
         *
         * @param int $livestockId ID hewan ternak utama
         * @return array Struktur data keluarga
         */
        $family = [];

        // Cari sebagai main cow (induk utama)
        $mainTree = self::find()->where(['main_cow_id' => $livestockId])->one();
        if ($mainTree) {
            $family['father']   = $mainTree->father;
            $family['mother']   = $mainTree->mother;
            $family['partners'] = json_decode($mainTree->partners, true) ?: [];
            $family['children'] = self::getChildrenIds($livestockId);
        }

        // Cari sebagai anak untuk mendapatkan orang tua jika belum ada
        $asChild = self::find()
            ->where(['like', 'children', '"' . $livestockId . '"', false])
            ->all();

        foreach ($asChild as $tree) {
            $children = json_decode($tree->children, true) ?: [];
            if (in_array($livestockId, $children)) {
                if (!isset($family['father']) && $tree->father) {
                    $family['father'] = $tree->father;
                }
                if (!isset($family['mother']) && $tree->mother) {
                    $family['mother'] = $tree->mother;
                }
            }
        }

        // Ambil Kakek-Nenek dari sisi Ayah (Paternal Grandparents)
        if (!empty($family['father'])) {
            $fatherTree = self::find()->where(['main_cow_id' => $family['father']->id])->one();
            if ($fatherTree) {
                $family['paternal_grandfather'] = $fatherTree->father;
                $family['paternal_grandmother'] = $fatherTree->mother;
            }
        }

        // Ambil Kakek-Nenek dari sisi Ibu (Maternal Grandparents)
        if (!empty($family['mother'])) {
            $motherTree = self::find()->where(['main_cow_id' => $family['mother']->id])->one();
            if ($motherTree) {
                $family['maternal_grandfather'] = $motherTree->father;
                $family['maternal_grandmother'] = $motherTree->mother;
            }
        }

        // Ambil data cucu
        $family['grandchildren'] = [];
        if (!empty($family['children'])) {
            foreach ($family['children'] as $childId) {
                $grandchildrenIds = self::getChildrenIds((int)$childId);
                if (!empty($grandchildrenIds)) {
                    $family['grandchildren'][$childId] = $grandchildrenIds;
                }
            }
        }

        return $family;
    }

    /**
     * Mengonversi data family ke format yang sesuai untuk layout D3.js.
     *
     * @param \common\models\Livestock $model  Model hewan ternak utama
     * @param array                 $familyData Hasil dari getFamilyData()
     * @return array [nodeDataArray, linkDataArray]
     */
    public static function convertToD3Data(Livestock $model, array $familyData): array
    {
        $nodeDataArray = [];
        $linkDataArray = [];

        // Node utama
        $nodeDataArray[] = self::createNode($model, 'Utama', null, null, true);

        // Ayah
        if (!empty($familyData['father'])) {
            $father = $familyData['father'];
            $nodeDataArray[] = self::createNode($father, 'Ayah');
            $linkDataArray[] = self::createLink($father->id, $model->id, 'parent-child');
        }

        // Ibu
        if (!empty($familyData['mother'])) {
            $mother = $familyData['mother'];
            $nodeDataArray[] = self::createNode($mother, 'Ibu');
            $linkDataArray[] = self::createLink($mother->id, $model->id, 'parent-child');
        }

        // Link perkawinan Ayah & Ibu
        if (!empty($familyData['father']) && !empty($familyData['mother'])) {
            $linkDataArray[] = [
                'from'         => $familyData['father']->id,
                'to'           => $familyData['mother']->id,
                'relationship' => 'marriage',
            ];
        }

        // Kakek & Nenek dari Ayah
        if (!empty($familyData['paternal_grandfather'])) {
            $p_grandfather = $familyData['paternal_grandfather'];
            $nodeDataArray[] = self::createNode($p_grandfather, 'Kakek', 'paternal');
            $linkDataArray[] = self::createLink($p_grandfather->id, $familyData['father']->id, 'parent-child');
        }
        if (!empty($familyData['paternal_grandmother'])) {
            $p_grandmother = $familyData['paternal_grandmother'];
            $nodeDataArray[] = self::createNode($p_grandmother, 'Nenek', 'paternal');
            $linkDataArray[] = self::createLink($p_grandmother->id, $familyData['father']->id, 'parent-child');
        }
        if (!empty($familyData['paternal_grandfather']) && !empty($familyData['paternal_grandmother'])) {
             $linkDataArray[] = self::createLink($familyData['paternal_grandfather']->id, $familyData['paternal_grandmother']->id, 'marriage');
        }
        
        // Kakek & Nenek dari Ibu
        if (!empty($familyData['maternal_grandfather'])) {
            $m_grandfather = $familyData['maternal_grandfather'];
            $nodeDataArray[] = self::createNode($m_grandfather, 'Kakek', 'maternal');
            $linkDataArray[] = self::createLink($m_grandfather->id, $familyData['mother']->id, 'parent-child');
        }
        if (!empty($familyData['maternal_grandmother'])) {
            $m_grandmother = $familyData['maternal_grandmother'];
            $nodeDataArray[] = self::createNode($m_grandmother, 'Nenek', 'maternal');
            $linkDataArray[] = self::createLink($m_grandmother->id, $familyData['mother']->id, 'parent-child');
        }
        if (!empty($familyData['maternal_grandfather']) && !empty($familyData['maternal_grandmother'])) {
             $linkDataArray[] = self::createLink($familyData['maternal_grandfather']->id, $familyData['maternal_grandmother']->id, 'marriage');
        }

        // Pasangan
        if (!empty($familyData['partners'])) {
            foreach ($familyData['partners'] as $partnerId) {
                $partner = Livestock::findOne($partnerId);
                if (!$partner) {
                    continue;
                }

                $nodeDataArray[] = self::createNode($partner, 'Pasangan');

                $linkDataArray[] = [
                    'from'         => $model->id,
                    'to'           => $partner->id,
                    'relationship' => 'marriage',
                    'category'     => 'marriage',
                ];
            }
        }

        // Anak-anak
        if (!empty($familyData['children'])) {
            foreach ($familyData['children'] as $childId) {
                $child = Livestock::findOne($childId);
                if (!$child) {
                    continue;
                }

                $nodeDataArray[] = self::createNode($child, 'Anak');

                $linkDataArray[] = [
                    'from'         => $model->id,
                    'to'           => $child->id,
                    'relationship' => 'parent-child',
                ];
            }
        }

        // Cucu-cucu
        if (!empty($familyData['grandchildren'])) {
            foreach ($familyData['grandchildren'] as $childId => $grandchildrenIds) {
                foreach ($grandchildrenIds as $grandchildId) {
                    $grandchild = Livestock::findOne($grandchildId);
                    if (!$grandchild) continue;

                    $nodeDataArray[] = self::createNode($grandchild, 'Cucu', null, $childId);

                    $linkDataArray[] = [
                        'from'         => $childId,
                        'to'           => $grandchild->id,
                        'relationship' => 'parent-child',
                    ];
                }
            }
        }

        return [
            'nodeDataArray' => $nodeDataArray,
            'linkDataArray' => $linkDataArray,
        ];
    }

    /**
     * Mengembalikan daftar keturunan (anak, cucu, dst.) yang dikelompokkan per generasi.
     * Generasi 1 = anak langsung, Generasi 2 = cucu, dan seterusnya.
     *
     * @param int $cowId ID sapi utama
     * @return array<int, \common\models\Livestock[]> Array dengan key nomor generasi dan value array model Livestock
     */
    public static function getDescendantsGrouped(int $cowId): array
    {
        $result = [];
        $visited = [$cowId];
        self::collectDescendantsRecursive($cowId, 1, $result, $visited);
        return $result;
    }

    /**
     * Rekursif internal untuk membangun daftar keturunan per generasi.
     *
     * @param int $cowId ID sapi yang sedang diproses
     * @param int $generation Urutan generasi saat ini (anak = 1)
     * @param array $result Referensi array hasil akhir
     * @param array $visited Referensi array ID sapi yang sudah diproses untuk mencegah loop siklis
     * @return void
     */
    private static function collectDescendantsRecursive(int $cowId, int $generation, array &$result, array &$visited): void
    {
        $childrenIds = self::getChildrenIds($cowId);
        if (empty($childrenIds)) {
            return;
        }

        foreach ($childrenIds as $childId) {
            // Hindari loop tak hingga jika ada data siklis
            if (in_array($childId, $visited, true)) {
                continue;
            }
            $visited[] = $childId;
            $childModel = Livestock::findOne($childId);
            if ($childModel) {
                $result[$generation][] = $childModel;
            }
            // Proses keturunan berikutnya
            self::collectDescendantsRecursive($childId, $generation + 1, $result, $visited);
        }
    }

    /**
     * Mengambil daftar ID saudara kandung langsung dari seekor sapi.
     * Saudara kandung didefinisikan sebagai hewan ternak yang memiliki minimal
     * salah satu orang tua (father_id atau mother_id) yang sama dengan hewan utama
     * dan bukan dirinya sendiri.
     *
     * @param int $cowId ID sapi utama
     * @return int[] Daftar ID saudara kandung unik
     */
    public static function getSiblingIds(int $cowId): array
    {
        // Ambil entry family tree untuk sapi utama
        $entry = self::findOne(['main_cow_id' => $cowId]);
        if (!$entry) {
            return [];
        }

        $siblings = [];

        // Cari saudara dengan ayah yang sama (tidak termasuk diri sendiri)
        if ($entry->father_id) {
            $fatherSiblings = self::find()
                ->where(['father_id' => $entry->father_id])
                ->andWhere(['<>', 'main_cow_id', $cowId])
                ->all();
            
            foreach ($fatherSiblings as $sibling) {
                $siblings[] = (int) $sibling->main_cow_id;
            }
        }

        // Cari saudara dengan ibu yang sama (tidak termasuk diri sendiri)
        if ($entry->mother_id) {
            $motherSiblings = self::find()
                ->where(['mother_id' => $entry->mother_id])
                ->andWhere(['<>', 'main_cow_id', $cowId])
                ->all();
            
            foreach ($motherSiblings as $sibling) {
                $siblings[] = (int) $sibling->main_cow_id;
            }
        }

        // Hapus duplikat dan kembalikan array unik
        return array_values(array_unique($siblings));
    }

    private static function createNode(
        Livestock $livestock, 
        string $role, 
        ?string $lineage = null, 
        ?int $parentId = null, 
        bool $isMain = false
    ): array
    {
        $node = [
            'key'          => $livestock->id,
            'name'         => $livestock->name,
            'vid'          => $livestock->vid ?: 'N/A',
            'gender'       => $livestock->gender,
            'birthdate'    => $livestock->birthdate 
                                ? Yii::$app->formatter->asDate($livestock->birthdate, 'medium') 
                                : 'N/A',
            'role'         => $role,
            'url'          => Url::to(['silsilah/view', 'id' => $livestock->id]),
        ];

        if ($lineage) {
            $node['lineage'] = $lineage;
        }
        if ($parentId) {
            $node['parentId'] = $parentId;
        }
        if ($isMain) {
            $node['isMain'] = true;
        }

        return $node;
    }

    private static function createLink(int $from, int $to, string $relationship): array
    {
        return [
            'from'         => $from,
            'to'           => $to,
            'relationship' => $relationship,
        ];
    }
}
