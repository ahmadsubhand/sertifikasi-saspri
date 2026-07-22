<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use common\models\Livestock;
use common\models\CowFamilyTree;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;

/**
 * SilsilahController mengelola halaman silsilah hewan ternak.
 * Controller ini menyediakan daftar semua hewan ternak yang dimiliki pengguna
 * dengan fitur edit dan lihat silsilah untuk setiap ternak.
 */
class SilsilahController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'detail-ajax', 'add-relation', 'update'],
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'detail-ajax'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['add-relation', 'update'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Menampilkan halaman utama silsilah dengan daftar semua hewan ternak.
     * Halaman ini berisi tabel dengan identitas singkat hewan ternak
     * dan tombol edit serta lihat silsilah untuk setiap ternak.
     *
     * @return string|\yii\web\Response Hasil render halaman atau redirect jika user guest
     */
    public function actionIndex()
    {
        // Ambil ID pengguna jika sudah login
        $userId = Yii::$app->user->isGuest ? null : Yii::$app->user->identity->id;

        // Buat query untuk mengambil data hewan ternak
        $query = Livestock::find()->orderBy(['created_at' => SORT_DESC]);
        
        // Filter berdasarkan pengguna jika tidak guest
        if ($userId) {
            $query->where(['user_id' => $userId]);
        }

        // Ambil semua livestock untuk dropdown dan JS validation
        $livestockQuery = Livestock::find();
        if ($userId) {
            $livestockQuery->where(['user_id' => $userId]);
        }
        $livestocks = $livestockQuery->all();

        $livestockOptions = ArrayHelper::map(
            $livestocks,
            'id',
            static function ($model) {
                return $model->name . ' (' . ($model->vid ?: 'No VID') . ') - ' . $model->gender;
            }
        );

        // Data untuk JS
        $livestockDataForJs = [];
        foreach ($livestocks as $livestock) {
            $familyTree = CowFamilyTree::find()->where(['main_cow_id' => $livestock->id])->one();

            $livestockDataForJs[$livestock->id] = [
                'gender'    => $livestock->gender,
                'name'      => $livestock->name . ' (' . ($livestock->vid ?: 'No VID') . ') - ' . $livestock->gender,
                'father_id' => $familyTree ? $familyTree->father_id : null,
                'mother_id' => $familyTree ? $familyTree->mother_id : null,
            ];
        }

        // Tambahkan filter pencarian berdasarkan VID atau Nama
        $searchQuery = Yii::$app->request->get('search_query');
        if (!empty($searchQuery)) {
            $query->andWhere([
                'or',
                ['like', 'vid', $searchQuery],
                ['like', 'name', $searchQuery]
            ]);
        }

        // Tambahkan filter Jenis Kelamin
        $filterGender = Yii::$app->request->get('filter_gender');
        if (!empty($filterGender)) {
            $query->andWhere(['gender' => $filterGender]);
        }

        // Tambahkan filter Jenis Ternak
        $filterType = Yii::$app->request->get('filter_type');
        if (!empty($filterType)) {
            $query->andWhere(['type_of_livestock' => $filterType]);
        }

        // Tambahkan filter Ras
        $filterBreed = Yii::$app->request->get('filter_breed');
        if (!empty($filterBreed)) {
            $query->andWhere(['breed_of_livestock' => $filterBreed]);
        }

        // Tambahkan filter Kesehatan
        $filterHealth = Yii::$app->request->get('filter_health');
        if (!empty($filterHealth)) {
            $query->andWhere(['health' => $filterHealth]);
        }

        // Tambahkan filter Usia (berdasarkan tanggal lahir)
        $filterAge = Yii::$app->request->get('filter_age');
        if (!empty($filterAge)) {
            $currentDate = new \DateTime();
            
            switch ($filterAge) {
                case '0-6':
                    // 0-6 bulan: lahir dalam 6 bulan terakhir
                    $sixMonthsAgo = clone $currentDate;
                    $sixMonthsAgo->modify('-6 months');
                    $query->andWhere(['>=', 'birthdate', $sixMonthsAgo->format('Y-m-d')]);
                    break;
                    
                case '7-12':
                    // 7-12 bulan: lahir antara 12 bulan dan 7 bulan yang lalu
                    $twelveMonthsAgo = clone $currentDate;
                    $twelveMonthsAgo->modify('-12 months');
                    $sevenMonthsAgo = clone $currentDate;
                    $sevenMonthsAgo->modify('-7 months');
                    $query->andWhere(['between', 'birthdate', $twelveMonthsAgo->format('Y-m-d'), $sevenMonthsAgo->format('Y-m-d')]);
                    break;
                    
                case '13-24':
                    // 1-2 tahun: lahir antara 2 tahun dan 1 tahun yang lalu
                    $twoYearsAgo = clone $currentDate;
                    $twoYearsAgo->modify('-2 years');
                    $oneYearAgo = clone $currentDate;
                    $oneYearAgo->modify('-1 year');
                    $query->andWhere(['between', 'birthdate', $twoYearsAgo->format('Y-m-d'), $oneYearAgo->format('Y-m-d')]);
                    break;
                    
                case '25-36':
                    // 2-3 tahun: lahir antara 3 tahun dan 2 tahun yang lalu
                    $threeYearsAgo = clone $currentDate;
                    $threeYearsAgo->modify('-3 years');
                    $twoYearsAgo = clone $currentDate;
                    $twoYearsAgo->modify('-2 years');
                    $query->andWhere(['between', 'birthdate', $threeYearsAgo->format('Y-m-d'), $twoYearsAgo->format('Y-m-d')]);
                    break;
                    
                case '37+':
                    // > 3 tahun: lahir lebih dari 3 tahun yang lalu
                    $threeYearsAgo = clone $currentDate;
                    $threeYearsAgo->modify('-3 years');
                    $query->andWhere(['<', 'birthdate', $threeYearsAgo->format('Y-m-d')]);
                    break;
            }
        }

        // Buat data provider untuk pagination
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 15,
                'params' => Yii::$app->request->queryParams, // Mempertahankan parameter filter saat pagination
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'livestockOptions' => $livestockOptions,
            'livestockDataForJs' => $livestockDataForJs,
        ]);
    }

    /**
     * Menampilkan halaman edit untuk hewan ternak tertentu.
     * Redirect ke halaman edit livestock yang sudah ada.
     *
     * @param integer $id ID hewan ternak yang akan diedit
     * @return \yii\web\Response Redirect ke halaman edit livestock
     * @throws NotFoundHttpException jika ternak tidak ditemukan
     */
    // public function actionEdit($id)
    // {
    //     $model = $this->findModel($id);
    //     return $this->redirect(['livestock/update', 'id' => $id]);
    // }

    /**
     * Menampilkan halaman detail silsilah untuk hewan ternak tertentu.
     * Halaman ini akan menampilkan pohon keluarga atau informasi silsilah ternak.
     *
     * @param integer $id ID hewan ternak yang akan dilihat silsilahnya
     * @return string Hasil render halaman detail silsilah
     * @throws NotFoundHttpException jika ternak tidak ditemukan
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Menampilkan halaman detail lengkap untuk hewan ternak tertentu via AJAX.
     * Halaman ini berisi informasi lengkap ternak.
     *
     * @param integer $id ID hewan ternak yang akan dilihat detailnya
     * @return string Hasil render halaman detail lengkap
     * @throws NotFoundHttpException jika ternak tidak ditemukan
     */
    public function actionDetailAjax($id)
    {
        $model = $this->findModel($id);

        return $this->renderAjax('detail', [
            'model' => $model,
            'isAjax' => true,
        ]);
    }

    /**
     * Menambah relasi baru antar hewan ternak (orang tua, pasangan, anak).
     * Fungsi ini menangani logika penambahan relasi berdasarkan jenis yang dipilih.
     *
     * @return \yii\web\Response Redirect kembali ke halaman index dengan pesan status
     */
    public function actionAddRelation()
    {
        $userId = Yii::$app->user->identity->id;
        
        // Ambil data dari form
        $mainCowId = Yii::$app->request->post('main_cow_id');
        $relatedCowId = Yii::$app->request->post('related_cow_id');
        $relationType = Yii::$app->request->post('relation_type');
        $parentType = Yii::$app->request->post('parent_type');

        // Validasi input dasar
        if (!$mainCowId || !$relatedCowId || !$relationType) {
            Yii::$app->session->setFlash('error', 'Semua field harus diisi dengan lengkap.');
            return $this->redirect(['index']);
        }

        // Validasi tidak boleh sama
        if ($mainCowId == $relatedCowId) {
            Yii::$app->session->setFlash('error', 'Hewan utama dan hewan relasi tidak boleh sama.');
            return $this->redirect(['index']);
        }

        // Pastikan kedua hewan ternak milik user yang sedang login
        $mainCow = Livestock::find()->where(['id' => $mainCowId, 'user_id' => $userId])->one();
        $relatedCow = Livestock::find()->where(['id' => $relatedCowId, 'user_id' => $userId])->one();

        if (!$mainCow || !$relatedCow) {
            Yii::$app->session->setFlash('error', 'Hewan ternak yang dipilih tidak valid atau bukan milik Anda.');
            return $this->redirect(['index']);
        }

        // Validasi tambahan untuk parent type
        if ($relationType === 'parent' && !$parentType) {
            Yii::$app->session->setFlash('error', 'Tipe orang tua harus dipilih.');
            return $this->redirect(['index']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            switch ($relationType) {
                case 'parent':
                    $this->addParentRelation($mainCowId, $relatedCowId, $parentType);
                    break;
                case 'partner':
                    $this->addPartnerRelation($mainCowId, $relatedCowId);
                    break;
                case 'child':
                    $this->addChildRelation($mainCowId, $relatedCowId);
                    break;
                default:
                    throw new \Exception('Jenis relasi tidak valid.');
            }
            
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Relasi silsilah berhasil ditambahkan.');
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Gagal menambahkan relasi: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Menambahkan relasi orang tua ke silsilah.
     * 
     * @param integer $mainCowId ID hewan utama (anak)
     * @param integer $parentId ID orang tua
     * @param string $parentType Tipe orang tua (father/mother)
     * @throws \Exception jika validasi gagal
     */
    private function addParentRelation($mainCowId, $parentId, $parentType)
    {
        if (!in_array($parentType, ['father', 'mother'])) {
            throw new \Exception('Tipe orang tua harus dipilih (Ayah atau Ibu).');
        }

        // Validasi gender untuk parent type
        $parent = Livestock::findOne($parentId);
        if (!$parent) {
            throw new \Exception('Hewan orang tua tidak ditemukan.');
        }

        if ($parentType === 'father' && $parent->gender !== 'Jantan') {
            throw new \Exception('Ayah harus berjenis kelamin jantan.');
        }
        
        if ($parentType === 'mother' && $parent->gender !== 'Betina') {
            throw new \Exception('Ibu harus berjenis kelamin betina.');
        }

        // Cek apakah sudah ada parent dengan tipe yang sama
        $existingRelation = CowFamilyTree::find()
            ->where(['main_cow_id' => $mainCowId])
            ->one();

        if ($existingRelation) {
            if ($parentType === 'father' && $existingRelation->father_id && $existingRelation->father_id != $parentId) {
                throw new \Exception('Hewan ini sudah memiliki ayah yang berbeda.');
            }
            if ($parentType === 'mother' && $existingRelation->mother_id && $existingRelation->mother_id != $parentId) {
                throw new \Exception('Hewan ini sudah memiliki ibu yang berbeda.');
            }
        }

        // Cari atau buat record family tree untuk main cow (anak)
        $familyTree = CowFamilyTree::find()->where(['main_cow_id' => $mainCowId])->one();
        
        if (!$familyTree) {
            $familyTree = new CowFamilyTree();
            $familyTree->main_cow_id = $mainCowId;
            $familyTree->children = json_encode([]);
            $familyTree->partners = json_encode([]);
        }

        // Set parent berdasarkan tipe
        if ($parentType === 'father') {
            $familyTree->father_id = $parentId;
        } else {
            $familyTree->mother_id = $parentId;
        }

        if (!$familyTree->save()) {
            throw new \Exception('Gagal menyimpan relasi orang tua: ' . implode(', ', $familyTree->getFirstErrors()));
        }

        // Tambahkan main cow sebagai anak di record parent
        $this->addChildToParent($parentId, $mainCowId);
    }

    /**
     * Menambahkan relasi pasangan ke silsilah.
     * 
     * @param integer $mainCowId ID hewan utama
     * @param integer $partnerId ID pasangan
     * @throws \Exception jika validasi gagal
     */
    private function addPartnerRelation($mainCowId, $partnerId)
    {
        // Validasi gender - pasangan harus berbeda jenis kelamin
        $mainCow = Livestock::findOne($mainCowId);
        $partner = Livestock::findOne($partnerId);
        
        if (!$mainCow || !$partner) {
            throw new \Exception('Hewan tidak ditemukan.');
        }

        if ($mainCow->gender === $partner->gender) {
            throw new \Exception('Pasangan harus berbeda jenis kelamin.');
        }

        // Cari atau buat record family tree untuk main cow
        $familyTree = CowFamilyTree::find()->where(['main_cow_id' => $mainCowId])->one();
        
        if (!$familyTree) {
            $familyTree = new CowFamilyTree();
            $familyTree->main_cow_id = $mainCowId;
            $familyTree->partners = json_encode([]);
            $familyTree->children = json_encode([]);
        }

        $partners = json_decode($familyTree->partners ?: '[]', true);
        
        // Cek apakah sudah ada relasi ini
        if (in_array($partnerId, $partners)) {
            throw new \Exception('Relasi pasangan sudah ada.');
        }

        $partners[] = (int)$partnerId;
        $familyTree->partners = json_encode($partners);
        
        if (!$familyTree->save()) {
            throw new \Exception('Gagal menyimpan relasi pasangan: ' . implode(', ', $familyTree->getFirstErrors()));
        }

        // Tambahkan main cow sebagai pasangan di record partner juga
        $partnerFamilyTree = CowFamilyTree::find()->where(['main_cow_id' => $partnerId])->one();
        
        if (!$partnerFamilyTree) {
            $partnerFamilyTree = new CowFamilyTree();
            $partnerFamilyTree->main_cow_id = $partnerId;
            $partnerFamilyTree->partners = json_encode([]);
            $partnerFamilyTree->children = json_encode([]);
        }

        $partnerPartners = json_decode($partnerFamilyTree->partners ?: '[]', true);
        
        if (!in_array($mainCowId, $partnerPartners)) {
            $partnerPartners[] = (int)$mainCowId;
            $partnerFamilyTree->partners = json_encode($partnerPartners);
            
            if (!$partnerFamilyTree->save()) {
                throw new \Exception('Gagal menyimpan relasi pasangan balik: ' . implode(', ', $partnerFamilyTree->getFirstErrors()));
            }
        }
    }

    /**
     * Menambahkan relasi anak ke silsilah.
     * 
     * @param integer $mainCowId ID hewan utama (parent)
     * @param integer $childId ID anak
     * @throws \Exception jika validasi gagal
     */
    private function addChildRelation($mainCowId, $childId)
    {
        // Cek apakah anak sudah memiliki parent dengan gender yang sama
        $mainCow = Livestock::findOne($mainCowId);
        if (!$mainCow) {
            throw new \Exception('Hewan parent tidak ditemukan.');
        }

        $childFamilyTree = CowFamilyTree::find()->where(['main_cow_id' => $childId])->one();
        
        if ($childFamilyTree) {
            if ($mainCow->gender === 'Jantan' && $childFamilyTree->father_id && $childFamilyTree->father_id != $mainCowId) {
                throw new \Exception('Anak ini sudah memiliki ayah yang berbeda.');
            }
            if ($mainCow->gender === 'Betina' && $childFamilyTree->mother_id && $childFamilyTree->mother_id != $mainCowId) {
                throw new \Exception('Anak ini sudah memiliki ibu yang berbeda.');
            }
        }

        // Cari atau buat record family tree untuk main cow (parent)
        $familyTree = CowFamilyTree::find()->where(['main_cow_id' => $mainCowId])->one();
        
        if (!$familyTree) {
            $familyTree = new CowFamilyTree();
            $familyTree->main_cow_id = $mainCowId;
            $familyTree->children = json_encode([]);
            $familyTree->partners = json_encode([]);
        }

        $children = json_decode($familyTree->children ?: '[]', true);
        
        // Cek apakah sudah ada relasi ini
        if (in_array($childId, $children)) {
            throw new \Exception('Relasi anak sudah ada.');
        }

        $children[] = (int)$childId;
        $familyTree->children = json_encode($children);
        
        if (!$familyTree->save()) {
            throw new \Exception('Gagal menyimpan relasi anak: ' . implode(', ', $familyTree->getFirstErrors()));
        }

        // Set main cow sebagai parent di record anak
        $this->setParentForChild($childId, $mainCowId);
    }

    /**
     * Menambahkan anak ke record orang tua.
     * 
     * @param integer $parentId ID orang tua
     * @param integer $childId ID anak
     */
    private function addChildToParent($parentId, $childId)
    {
        $parentFamilyTree = CowFamilyTree::find()->where(['main_cow_id' => $parentId])->one();
        
        if (!$parentFamilyTree) {
            $parentFamilyTree = new CowFamilyTree();
            $parentFamilyTree->main_cow_id = $parentId;
            $parentFamilyTree->children = json_encode([]);
            $parentFamilyTree->partners = json_encode([]);
        }

        $children = json_decode($parentFamilyTree->children ?: '[]', true);
        
        if (!in_array($childId, $children)) {
            $children[] = (int)$childId;
            $parentFamilyTree->children = json_encode($children);
            $parentFamilyTree->save();
        }
    }

    /**
     * Menetapkan orang tua untuk anak.
     * 
     * @param integer $childId ID anak
     * @param integer $parentId ID orang tua
     */
    private function setParentForChild($childId, $parentId)
    {
        $childFamilyTree = CowFamilyTree::find()->where(['main_cow_id' => $childId])->one();
        
        if (!$childFamilyTree) {
            $childFamilyTree = new CowFamilyTree();
            $childFamilyTree->main_cow_id = $childId;
            $childFamilyTree->children = json_encode([]);
            $childFamilyTree->partners = json_encode([]);
        }

        // Tentukan gender parent untuk menentukan father atau mother
        $parent = Livestock::findOne($parentId);
        if ($parent) {
            if ($parent->gender === 'Jantan') {
                $childFamilyTree->father_id = $parentId;
            } else {
                $childFamilyTree->mother_id = $parentId;
            }
            $childFamilyTree->save();
        }
    }

    /**
     * Mencari model hewan ternak berdasarkan ID dan memastikan
     * hewan ternak tersebut milik pengguna yang sedang login.
     *
     * @param integer $id ID hewan ternak yang dicari
     * @return Livestock Model hewan ternak yang ditemukan
     * @throws NotFoundHttpException jika ternak tidak ditemukan atau bukan milik user
     */
    protected function findModel($id)
    {
        if (Yii::$app->user->isGuest) {
            // Untuk tamu, cari hanya berdasarkan ID
            $model = Livestock::findOne($id);
        } else {
            // Untuk pengguna yang login, pastikan ternak milik mereka
            $userId = Yii::$app->user->identity->id;
            $model = Livestock::findOne(['id' => $id, 'user_id' => $userId]);
        }

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Halaman yang Anda cari tidak ditemukan.');
    }

    /**
     * Memperbarui silsilah hewan ternak.
     * Mengelola form untuk update ayah, ibu, dan pasangan.
     *
     * @param int $id ID dari hewan ternak utama
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $userId = Yii::$app->user->identity->id;

        $familyTree = CowFamilyTree::findOne(['main_cow_id' => $id]) ?? new CowFamilyTree(['main_cow_id' => $id]);

        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();
            
            // Simpan ayah, ibu
            $familyTree->father_id = $postData['father_id'] ?? null;
            $familyTree->mother_id = $postData['mother_id'] ?? null;
            
            // Simpan pasangan
            $partnerIds = array_filter($postData['partner_ids'] ?? []);
            $familyTree->partners = !empty($partnerIds) ? json_encode(array_values($partnerIds)) : null;

            if ($familyTree->save()) {
                Yii::$app->session->setFlash('success', 'Silsilah berhasil diperbarui.');
                return $this->redirect(['view', 'id' => $id]);
            } else {
                Yii::$app->session->setFlash('error', 'Gagal menyimpan silsilah.');
            }
        }

        // Helper function untuk format label
        $formatLabel = function ($livestock) {
            $vidPart = $livestock->vid ? ' (' . $livestock->vid . ')' : '';
            return $livestock->name . $vidPart . ' - ' . $livestock->gender;
        };

        // Opsi Ayah: Semua ternak jantan, kecuali ternak itu sendiri
        $fatherOptions = ArrayHelper::map(
            Livestock::find()->where(['user_id' => $userId, 'gender' => 'Jantan'])->andWhere(['!=', 'id', $id])->all(),
            'id',
            $formatLabel
        );

        // Opsi Ibu: Semua ternak betina, kecuali ternak itu sendiri
        $motherOptions = ArrayHelper::map(
            Livestock::find()->where(['user_id' => $userId, 'gender' => 'Betina'])->andWhere(['!=', 'id', $id])->all(),
            'id',
            $formatLabel
        );

        // Tentukan jenis kelamin yang berlawanan untuk pasangan
        $oppositeGender = ($model->gender === 'Jantan') ? 'Betina' : 'Jantan';

        // Opsi Pasangan: Semua ternak dengan jenis kelamin berlawanan, kecuali ternak itu sendiri
        $partnerOptions = ArrayHelper::map(
            Livestock::find()
                ->where(['user_id' => $userId, 'gender' => $oppositeGender])
                ->andWhere(['!=', 'id', $id])
                ->all(),
            'id',
            $formatLabel
        );

        return $this->render('update', [
            'model' => $model,
            'currentFather' => $familyTree->father_id,
            'currentMother' => $familyTree->mother_id,
            'currentPartners' => $familyTree->partners ? json_decode($familyTree->partners, true) : [],
            'fatherOptions' => $fatherOptions,
            'motherOptions' => $motherOptions,
            'partnerOptions' => $partnerOptions,
        ]);
    }
}
