<?php

namespace frontend\controllers;

use common\enums\UserRole;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\Controller;
use common\models\Livestock;
use common\models\LivestockImage;
use common\models\Cage;
use common\models\Note;
use common\models\NoteImage;
use common\models\BodyCountScore;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\data\Pagination;
use yii\data\ActiveDataProvider;
use common\models\CowFamilyTree;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;




class LivestockController extends Controller
{
    public $modelClass = 'app\models\Livestock';

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();

        // Disable default CRUD actions
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);

        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // VerbFilter untuk memastikan setiap action hanya menerima HTTP method yang sesuai
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'create' => ['POST'],
                'update' => ['GET', 'POST', 'PUT', 'PATCH'],
                'delete' => ['DELETE'],
                'view' => ['GET'],
                'search' => ['GET'],
                'get-livestocks' => ['GET'],
                'upload-image' => ['POST'],
                'bcs-data' => ['GET'],
            ],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => [UserRole::USER, UserRole::COORDINATOR],
                ]
            ]
        ];

        return $behaviors;
    }

    public function actionIndex(?Livestock $model = null)
    {
        $model ??= new Livestock();
        $userId = Yii::$app->user->identity->id;
        $cages = Cage::find()
            ->where(['user_id' => $userId])
            ->all();

        // Validasi cage_id berdasarkan user_id
        if (empty($cages)) {
            Yii::$app->session->setFlash('error', 'Kandang tidak boleh kosong, mohon buat kandang terlebih dahulu.');
            return $this->redirect(['cage/index']);
        }

        // Load Livestock data
        $requestData = Yii::$app->getRequest()->getBodyParams();
        if ($model->load($requestData, '') && $model->save()) {
            $imageFiles = UploadedFile::getInstancesByName('livestock_image');
            $uploadedImages = [];

            if (!empty($imageFiles)) {
                foreach ($imageFiles as $index => $imageFile) {
                    $imageName = Yii::$app->security->generateRandomString(12) . $index . '.' . $imageFile->getExtension();
                    $uploadPath = Yii::getAlias('@webroot/uploads/livestock/' . $model->id . '/');
                    
                    // Create directory if it doesn't exist
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }

                    // Save the image file
                    $imageFile->saveAs($uploadPath . $imageName);

                    // Save the image information to the database
                    $livestockImage = new LivestockImage();
                    $livestockImage->livestock_id = $model->id;
                    $livestockImage->image_path = '/uploads/livestock/' . $model->id . '/' . $imageName;
                    
                    if ($livestockImage->save()) {
                        $uploadedImages[] = $livestockImage->image_path;
                    }
                }
            }
        }

        // Pagination and fetching livestock data
        $query = Livestock::find()->where(['user_id' => $userId])->orderBy(['created_at' => SORT_DESC]);

        // Mapping cage options for dropdown (digunakan di view, logika dipindahkan dari view)
        $cageOptions = ArrayHelper::map($cages, 'id', 'name');

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $query->count(),
        ]);

        $livestock = $query->offset($pagination->offset)
                        ->limit($pagination->limit)
                        ->all();

        return $this->render('index', [
            'livestock' => $livestock,
            'model' => $model,
            'pagination' => $pagination,
            'cageOptions' => $cageOptions,
        ]);
    }

    public function actionCreate(){
        $model = new Livestock();
        $model->user_id = Yii::$app->user->id;

        /*
        * Cukup gunakan `$model->load(Yii::$app->request->post())`.
        * Yii akan otomatis mencari array dengan key sesuai `formName()` ("Livestock")
        * sehingga semua field akan ter‐mapping tanpa manipulasi manual.
        */
        $model->load(Yii::$app->request->post());
        if (!$model->validate()) {
            // Catat error validasi ke log untuk debugging
            Yii::error('Livestock validation error: '.json_encode($model->getErrors(), JSON_PRETTY_PRINT), __METHOD__);
            // Debug validation errors di layar (sementara)
            return $this->actionIndex($model);
        }else {
            // Simpan data Livestock
            if ($model->save()) {
                // Jika berhasil menyimpan data Livestock, simpan juga data BCS
                $bcs = new BodyCountScore();
                $bcs->livestock_id = $model->id;
                $bcs->chest_size = $model->chest_size;
                $bcs->hips = $model->hips;
                $bcs->body_weight = $model->body_weight;

                // Simpan data BCS
                if (!$bcs->save()) {
                    // Jika gagal menyimpan BCS, tampilkan pesan error
                    Yii::$app->session->setFlash('bcs_error', 'Gagal menyimpan data BCS.');
                    return $this->actionIndex($model);
                }

                // Redirect ke halaman index jika semua berhasil
                Yii::$app->session->setFlash('success', 'Data ternak berhasil ditambahkan.');
                return $this->redirect(['index']);
            } else {
                // Catat error simpan ke log
                Yii::error('Livestock save error: '.json_encode($model->getErrors(), JSON_PRETTY_PRINT), __METHOD__);
                // Jika gagal menyimpan Livestock, tampilkan pesan error
                Yii::$app->session->setFlash('error', 'Gagal menyimpan data Livestock.');
                return $this->actionIndex($model);
            }
        }
    }
  
    public function actionBcsData(int $id)
    {
        $query = BodyCountScore::find()->where(['livestock_id' => $id]);
        $model = $this->findLivestockModel($id);

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10, // Set page size as required
                ],
            ]);

            return $this->render('bcs', [
                'dataProvider' => $dataProvider,
                'model' => $model,
            ]);
    }

    protected function findLivestockModel(int $id)
    {
        if (($model = Livestock::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested livestock does not exist.');
    }

    protected function uploadImages($model, $imageFiles)
    {
        $userId = Yii::$app->user->identity->id;
        $uploadPath = Yii::getAlias('@webroot/livestock/' . $userId . '/' . $model->id . '/');
    
        // Membuat direktori jika belum ada
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
    
        $uploadedImages = [];
    
        foreach ($imageFiles as $index => $imageFile) {
            $imageName = Yii::$app->security->generateRandomString(12) . $index . '.' . $imageFile->getExtension();
            $filePath = $uploadPath . $imageName;
    
            // Menyimpan gambar ke direktori
            if ($imageFile->saveAs($filePath)) {
                // Simpan informasi gambar ke tabel livestock_images
                $livestockImage = new LivestockImage();
                $livestockImage->livestock_id = $model->id;
                $livestockImage->image_path = '/livestock/' . $userId . '/' . $model->id . '/' . $imageName;
                if (!$livestockImage->save()) {
                    Yii::$app->session->setFlash('error', 'Gagal menyimpan data gambar ke database.');
                } else {
                    $uploadedImages[] = $livestockImage->image_path;
                }
            } else {
                Yii::$app->session->setFlash('error', 'Gagal mengunggah gambar.');
            }
        }
    
        return $uploadedImages;
    }

    /**
     * Mengupdate data Livestock berdasarkan ID.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException jika data Livestock tidak ditemukan
     * @throws ServerErrorHttpException jika data Livestock tidak dapat diupdate
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->livestock_image = UploadedFile::getInstance($model, 'livestock_image');
            if (!$model->validate()) {
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', implode('<br>', array_values($errors)));
                return $this->render('update', [
                    'model' => $model,
                ]);
            }

            $dirtySizeFields = $model->getDirtyAttributes(['chest_size', 'body_weight', 'hips']);

            if ($model->save(false)) {
                // Simpan atau perbarui relasi orang tua dan pasangan
                $this->saveFamilyRelations($model->id);

                // Set pesan sukses terlebih dahulu
                Yii::$app->session->setFlash('success', 'Data ternak berhasil diperbarui.');

                // Jika ada perubahan ukuran tubuh, catat sebagai BCS terbaru
                if (!empty($dirtySizeFields)) {
                    $bcs = new BodyCountScore();
                    $bcs->livestock_id = $model->id;
                    $bcs->chest_size = $model->chest_size;
                    $bcs->hips = $model->hips;
                    $bcs->body_weight = $model->body_weight;
                    $bcs->save(false);
                }

                if ($model->livestock_image) {
                    if ($model->uploadImage()) {
                        return $this->redirect(['index']);
                    } else {
                        // Kesalahan dalam mengunggah gambar
                        Yii::$app->session->setFlash('error', 'Gagal mengunggah gambar.');
                        return $this->redirect(['update','id'=> $model->id]);
                    }
                }

                return $this->redirect(['index']);
            }

            Yii::$app->session->setFlash('error', 'Gagal menyimpan perubahan data ternak.');
            return $this->render('update', [
                'model' => $model,
            ]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    private function uploadImage()
    {
        if ($this->validate()) {
            // Nama file unik untuk menghindari duplikasi
            $fileName = $this->id . '_' . uniqid() . '_' . $this->livestock_image->baseName . '.' . $this->livestock_image->extension;
            
            // Tentukan direktori penyimpanan
            $uploadDir = Yii::getAlias('@webroot/uploads');
            
            // Pastikan direktori ada, jika tidak maka buat direktori tersebut
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Gabungkan direktori dan nama file untuk membuat file path absolut
            $filePath = $uploadDir . '/' . $fileName;

            // Simpan file dan periksa apakah berhasil
            if ($this->livestock_image->saveAs($filePath)) {
                // Simpan konten gambar ke atribut 'image' di database
                $this->image = file_get_contents($filePath);
                return true;
            }
        }
        return false;
    }


    /**
     * Menampilkan data Livestock berdasarkan ID.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException jika data Livestock tidak ditemukan
     */
    public function actionView($id)
    {
        $livestock = $this->findModel($id);

        if ($livestock) {
            Yii::$app->response->statusCode = 200;
            return $this-> render ('create-sapi', [
                'message' => 'Data ternak berhasil ditemukan.',
                'error' => false,
                'data' => $livestock,
            ]);
        } else {
            Yii::$app->response->statusCode = 404;
            return $this->render('create-sapi',[
                'message' => "Ternak tidak ditemukan",
                'error' => true,
            ]);
        }
    }

    /**
     * Deletes a Livestock model based on its primary key value.
     * If the deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Cari ternak berdasarkan ID
            $livestock = $this->findModel($id);

            // Jika ternak tidak ditemukan, tampilkan pesan error
            if ($livestock === null) {
                Yii::$app->session->setFlash('error', 'Gagal menghapus data ternak. Data ternak tidak ditemukan.');
                return $this->redirect(['index']);
            }

            // Dapatkan semua catatan yang terkait dengan ternak
            $notes = Note::find()->where(['livestock_id' => $id])->all();

            foreach ($notes as $note) {
                // Hapus gambar yang terkait dengan catatan terlebih dahulu
                NoteImage::deleteAll(['note_id' => $note->id]);

                // Hapus catatan
                $note->delete();
            }
            BodyCountScore::deleteAll(['livestock_id' => $id]);


            // Hapus gambar ternak
            LivestockImage::deleteAll(['livestock_id' => $id]);

            // Hapus ternak
            $livestock->delete();

            // Commit transaksi
            $transaction->commit();

            // Set flash message untuk sukses
            Yii::$app->session->setFlash('success', 'Data ternak berhasil dihapus.');
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Gagal menghapus data ternak. Alasan: ' . $e->getMessage());
        }

        // Redirect ke halaman index setelah operasi selesai
        return $this->redirect(['index']);
    }

    /**
     * Mencari data Livestock berdasarkan VID.
     * @param string $vid
     * @return array
     */
    public function actionSearch($vid)
    {
        // Validasi pola VID
        if (!preg_match('/^[A-Z]{3}\d{4}$/', $vid)) {
            return [
                'message' => 'Format Visual ID tidak valid. Gunakan format tiga huruf kapital diikuti empat angka. Contoh: ABC1234.',
                'error' => true,
            ];
        }

        $userId = Yii::$app->user->identity->id;
        $livestock = Livestock::find()->where(['vid' => $vid, 'user_id' => $userId])->all();

        if ($livestock) {
            Yii::$app->getResponse()->setStatusCode(200); // OK
            return [
                'message' => 'Data ternak berhasil ditemukan.',
                'error' => false,
                'data' => $livestock,
            ];
        } else {
            Yii::$app->getResponse()->setStatusCode(404); // Not Found
            return [
                'message' => 'Data ternak tidak ditemukan.',
                'error' => true,
            ];
        }
    }

    /**
     * Retrieves livestock data by user_id.
     * @param integer $user_id
     * @return mixed
     */
    public function actionGetLivestocks($user_id)
    {
        $livestocks = Livestock::find()->where(['user_id' => $user_id])->all();

        if (!empty($livestocks)) {
            Yii::$app->getResponse()->setStatusCode(200); // OK
            return [
                'message' => 'Data ternak berhasil ditemukan.',
                'error' => false,
                'data' => $livestocks,
            ];
        } else {
            Yii::$app->getResponse()->setStatusCode(404); // Not Found
            return [
                'message' => 'Data ternak tidak ditemukan.',
                'error' => true,
            ];
        }
    }

    /**
     * Mengunggah gambar untuk Livestock berdasarkan ID.
     * @param integer $id
     * @return mixed
     * @throws ServerErrorHttpException jika gambar tidak dapat disimpan
     */
    // public function actionUploadImage($id)
    // {
    //     // Find the Livestock model based on ID
    //     $model = $this->findModel($id);

    //     // Get the image from the request
    //     $imageFiles = UploadedFile::getInstancesByName('livestock_image');

    //     if (!empty($imageFiles)) {
    //         // Get the user_id of the currently logged in user
    //         $userId = Yii::$app->user->identity->id;

    //         // Create a directory path based on user_id and Livestock id
    //         $uploadPath = 'livestock/' . $userId . '/' . $model->id . '/';

    //         $uploadedImages = [];

    //         // Initialize the Google Cloud Storage client
    //         $storage = new StorageClient([
    //             'keyFilePath' => Yii::getAlias('@app/config/sa.json')
    //         ]);
    //         $bucket = $storage->bucket('digiternak1');

    //         // Iterate through each uploaded file
    //         foreach ($imageFiles as $index => $imageFile) {
    //             // Check if the temporary file path is set
    //             if (empty($imageFile->tempName)) {
    //                 Yii::$app->response->statusCode = 400;
    //                 return [
    //                     'message' => 'Gagal mengunggah gambar. Silakan coba lagi.',
    //                     'error' => true,
    //                 ];
    //             }

    //             // Generate a unique file name
    //             $imageName = Yii::$app->security->generateRandomString(12) . $index . '.' . $imageFile->getExtension();
            
    //             // Save the file to the directory
    //             $object = $bucket->upload(
    //                 file_get_contents($imageFile->tempName),
    //                 ['name' => $uploadPath . $imageName]
    //             );

    //             // Make the object publicly accessible
    //             $object->update(['acl' => []], ['predefinedAcl' => 'publicRead']);
            
    //             // Get the public URL of the object
    //             $publicUrl = sprintf('https://storage.googleapis.com/%s/%s', $bucket->name(), $uploadPath . $imageName);

    //             // Save the image information to the livestock_images table
    //             $livestockImage = new LivestockImage();
    //             $livestockImage->livestock_id = $model->id;
    //             $livestockImage->image_path = $uploadPath . $imageName;
    //             if (!$livestockImage->save()) {
    //                 Yii::$app->response->statusCode = 400;
    //                 return [
    //                     'message' => 'Gagal menyimpan data gambar ke database.',
    //                     'error' => true,
    //                 ];
    //             }
            
    //             // Save the public URL to the array
    //             $uploadedImages[] = $publicUrl;
    //         }

    //         // If the model saving is successful
    //         Yii::$app->response->statusCode = 201;
    //         return [
    //             'message' => 'Gambar berhasil diunggah.',
    //             'error' => false,
    //             'data' => [
    //                 'livestock_images' => $uploadedImages,
    //             ],
    //         ];
    //     } else {
    //         Yii::$app->response->statusCode = 400;
    //         return [
    //             'message' => 'Tidak ada gambar yang diunggah.',
    //             'error' => true,
    //         ];
    //     }
    // }

    /**
     * Menemukan model Livestock berdasarkan ID.
     * @param integer $id
     * @return Livestock the loaded model
     * @throws NotFoundHttpException jika data Livestock tidak ditemukan
     */
    protected function findModel($id)
    {
        if (($model = Livestock::findOne($id)) !== null) {
            return $model;
        } else {
            return null;
        }
    }

    /**
     * Menyimpan relasi silsilah (ayah, ibu, dan pasangan) untuk ternak.
     *
     * Fungsi ini mengambil data POST father_id, mother_id, dan partner_ids
     * kemudian membuat atau memperbarui entri pada tabel cow_family_tree.
     * Selain itu, fungsi juga memastikan bahwa relasi pasangan bersifat dua arah
     * (mutual) dengan menambahkan ID ternak utama ke daftar pasangan milik
     * ternak pasangan.
     *
     * @param int $mainCowId ID ternak utama yang sedang diperbarui.
     * @return void
     */
    protected function saveFamilyRelations(int $mainCowId): void
    {
        $post = Yii::$app->request->post();

        // Ambil data POST; gunakan null coalescing untuk nilai default
        $fatherId    = $post['father_id']   ?? null;
        $motherId    = $post['mother_id']   ?? null;
        // Ambil pasangan (bisa single 'partner_id' atau array 'partner_ids')
        if (isset($post['partner_id']) && $post['partner_id'] !== '') {
            $partnerIds = [(int)$post['partner_id']];
        } else {
            $partnerIds = $post['partner_ids'] ?? [];
            if (!is_array($partnerIds)) {
                $partnerIds = [$partnerIds];
            }
        }

        // Bersihkan: buang nilai kosong, duplikat, dan ID sama dengan ternak utama
        $partnerIds = array_values(array_unique(array_filter(array_map('intval', $partnerIds), fn($id) => $id > 0 && $id !== $mainCowId)));

        // Cari atau buat record family tree untuk ternak utama
        $familyTree = CowFamilyTree::find()->where(['main_cow_id' => $mainCowId])->one();
        if (!$familyTree) {
            $familyTree = new CowFamilyTree();
            $familyTree->main_cow_id = $mainCowId;
        }

        // Set orang tua
        $familyTree->father_id = $fatherId ?: null;
        $familyTree->mother_id = $motherId ?: null;

        // Simpan pasangan (dalam bentuk JSON array)
        $familyTree->partners = json_encode(array_map('intval', $partnerIds));

        if (!$familyTree->save()) {
            // Jika gagal, tampilkan error agar user mengetahuinya
            Yii::$app->session->setFlash('error', 'Gagal menyimpan relasi silsilah: ' . implode(', ', $familyTree->getFirstErrors()));
        }

        // Pastikan relasi pasangan dua arah
        foreach ($partnerIds as $partnerId) {
            $partnerTree = CowFamilyTree::find()->where(['main_cow_id' => $partnerId])->one();
            if (!$partnerTree) {
                $partnerTree = new CowFamilyTree();
                $partnerTree->main_cow_id = (int)$partnerId;
            }

            $existingPartners = json_decode($partnerTree->partners, true) ?: [];
            if (!in_array($mainCowId, $existingPartners)) {
                $existingPartners[] = (int)$mainCowId;
                $partnerTree->partners = json_encode($existingPartners);
                $partnerTree->save(false);
            }
        }
    }
}

// public function actionUploadImage($id)
    // {
    //     // Temukan model Livestock berdasarkan ID
    //     $model = $this->findModel($id);

    //     // Ambil gambar dari request
    //     $imageFiles = UploadedFile::getInstancesByName('livestock_image');

    //     if (!empty($imageFiles)) {
    //         // Ambil user_id dari pengguna yang sedang login
    //         $userId = Yii::$app->user->identity->id;

    //         // Buat path direktori berdasarkan user_id dan id Livestock
    //         $uploadPath = 'uploads/livestock/' . $userId . '/' . $model->id . '/';

    //         // Periksa apakah direktori sudah ada, jika tidak, buat direktori baru
    //         if (!is_dir($uploadPath)) {
    //             FileHelper::createDirectory($uploadPath);
    //         }

    //         $uploadedImages = [];

    //         // Iterasi melalui setiap file yang diunggah
    //         foreach ($imageFiles as $index => $imageFile) {
    //             // Generate nama file yang unik
    //             $imageName = Yii::$app->security->generateRandomString(12) . $index . '.' . $imageFile->getExtension();
            
    //             // Simpan file ke direktori
    //             $imageFile->saveAs($uploadPath . $imageName);
            
    //             // Save image information to the livestock_images table
    //             $livestockImage = new LivestockImage();
    //             $livestockImage->livestock_id = $model->id;
    //             $livestockImage->image_path = $uploadPath . $imageName;
    //             if (!$livestockImage->save()) {
    //                 Yii::$app->response->statusCode = 400;
    //                 return [
    //                     'message' => 'Gagal menyimpan data gambar ke database.',
    //                     'error' => true,
    //                 ];
    //             }
            
    //             // Simpan nama file ke dalam array
    //             $uploadedImages[] = $uploadPath . $imageName;
    //         }

    //         // Jika penyimpanan model berhasil
    //         Yii::$app->response->statusCode = 201;
    //         return [
    //             'message' => 'Gambar berhasil diunggah.',
    //             'error' => false,
    //             'data' => [
    //                 'livestock_images' => $uploadedImages,
    //             ],
    //         ];
    //     } else {
    //         Yii::$app->response->statusCode = 400;
    //         return [
    //             'message' => 'Tidak ada gambar yang diunggah.',
    //             'error' => true,
    //         ];
    //     }
    // }

    /**
     * Mengembalikan semua data Livestock.
     * @return array
     */
    // public function actionIndex()
    // {
    //     $livestocks = Livestock::find()->all();
        
    //     if (!empty($livestocks)) {
    //         Yii::$app->response->statusCode = 200;
    //         return $livestocks;
    //     } else {
    //         Yii::$app->getResponse()->setStatusCode(404); // Not Found
    //         return [
    //             'message' => 'Ternak tidak ditemukan.',
    //             'error' => true,
    //         ];
    //     }
    // }
