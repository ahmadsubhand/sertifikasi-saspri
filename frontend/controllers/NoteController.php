<?php

namespace frontend\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use common\models\Note;
use common\models\NoteImage;
use common\models\Livestock;
use common\models\Cage;
use yii\data\Pagination;
use yii\web\Controller;

class NoteController extends Controller
{
    public $modelClass = 'app\models\Note';

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


        // Menambahkan VerbFilter untuk memastikan setiap action hanya menerima HTTP method yang sesuai
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'create' => ['POST'],
                'update' => ['PUT', 'PATCH'],
                'delete' => ['DELETE'],
                'view' => ['GET'],
                'index' => ['GET'],
                'get-note-by-livestock-id' => ['GET'],
                'upload-documentation' => ['POST'],
            ],
        ];

        return $behaviors;
    }

    /**
     * Menampilkan data Note berdasarkan ID.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException jika data Note tidak ditemukan
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($model !== null) {
            Yii::$app->response->statusCode = 200;
            return [
                'message' => 'Berhasil menemukan catatan.',
                'error' => false,
                'data' => $model,
            ];
        } else {
            Yii::$app->response->statusCode = 404;
            return [
                'message' => 'Catatan tidak ditemukan.',
                'error' => true,
            ];
        }
    }

    protected function findModel(int $id)
    {
        if (($model = Note::findOne($id)) !== null) {
            return $model;
        } else {
            return null;
        }
    }

    /**
     * Membuat data Note baru.
     * @return mixed
     * @throws BadRequestHttpException jika input tidak valid
     * @throws ServerErrorHttpException jika data Note tidak dapat disimpan
     */
    public function actionCreate()
{
    $model = new Note();

    // Ambil livestock_id dari input POST
    $livestock_id = Yii::$app->request->post('Note')['livestock_id'];

    // Validasi apakah livestock_id ada
    if (!$livestock_id) {
        Yii::$app->response->statusCode = 400;
        return $this->render('error', [
            'message' => 'Gagal membuat catatan, livestock_id tidak ditemukan.',
            'error' => true,
        ]);
    }

    // Cari ternak berdasarkan livestock_id
    $livestock = Livestock::findOne($livestock_id);

    if (!$livestock) {
        Yii::$app->response->statusCode = 400;
        return $this->render('error', [
            'message' => 'Gagal membuat catatan, data ternak tidak ditemukan.',
            'error' => true,
        ]);
    }

    // Cari kandang yang terkait dengan ternak
    $cage = Cage::findOne($livestock->cage_id);

    if (!$cage) {
        Yii::$app->response->statusCode = 400;
        return $this->render('error', [
            'message' => 'Gagal membuat catatan, kandang tidak ditemukan.',
            'error' => true,
        ]);
    }

    // Set atribut-atribut dari Note
    $model->livestock_id = $livestock->id;
    $model->livestock_vid = $livestock->vid;
    $model->livestock_name = $livestock->name;
    $model->livestock_cage = $cage->name;
    $model->location = $cage->location;

    // Muat data dari input POST
    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        // Simpan model
        if ($model->save()) {
            // Set flash message untuk sukses
            Yii::$app->session->setFlash('success', 'Catatan berhasil dibuat.');
    
            // Redirect ke halaman index
            return $this->redirect(['index']);
        }
    } else {
        Yii::$app->response->statusCode = 400;
        return $this->render('error', [
            'message' => 'Catatan gagal dibuat.',
            'error' => true,
            'details' => $this->getValidationErrors($model),
        ]);
    }
}



    /**
     * Mengupdate data Note berdasarkan ID.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException jika data Note tidak ditemukan
     * @throws BadRequestHttpException jika input tidak valid
     * @throws ServerErrorHttpException jika data Note tidak dapat disimpan
     */
    public function actionUpdate($id)
{
    $model = $this->findModel($id);

    if ($model === null) {
        Yii::$app->session->setFlash('error', 'Catatan tidak ditemukan.');
        return $this->redirect(['index']);
    }

    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        $model->save();
        Yii::$app->session->setFlash('success', 'Catatan berhasil diperbarui.');
        return $this->redirect('index'); // Assuming you have a view page for notes
    } else {
        Yii::$app->session->setFlash('error', 'Gagal memperbarui catatan.');
        return $this->render('update', [
            'model' => $model,
        ]);
    }
}


    /**
     * Deletes a Note model based on its primary key value.
     * If the deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ServerErrorHttpException if the model cannot be deleted
     */
    public function actionDelete($id)
{
    $transaction = Yii::$app->db->beginTransaction();
    try {
        $model = $this->findModel($id);

        if ($model === null) {
            Yii::$app->session->setFlash('error', 'Gagal menghapus catatan, catatan tidak ditemukan.');
            return $this->redirect(['index']);
        }

        // Delete note images first
        NoteImage::deleteAll(['note_id' => $id]);

        // Then delete the note
        $model->delete();

        $transaction->commit();

        Yii::$app->session->setFlash('success', 'Catatan berhasil dihapus.');
        return $this->redirect(['index']);
    } catch (\Exception $e) {
        $transaction->rollBack();

        Yii::$app->session->setFlash('error', 'Gagal menghapus catatan: ' . $e->getMessage());
        return $this->redirect(['index']);
    }
}


    /**
     * Returns all notes created by the current user.
     * @return array
     */
    public function actionIndex()
{
    if (Yii::$app->user->isGuest) {
        // Redirect to login page if user is a guest
        return $this->redirect(['user/login']);
    } else {
        $userId = Yii::$app->user->identity->id;
        $livestock = Livestock::find()
        ->where(['user_id' => $userId])
        ->all();

    // Validasi cage_id berdasarkan user_id
        if (empty($livestock)) {
            return $this->render('error', [
                'message' => 'Sapi tidak boleh kosong, mohon buat sapi terlebih dahulu.',
                'error' => true,
            ]);
        }
        $model = new Note();
        // First, get the query object
        $query = Note::find()->where(['user_id' => Yii::$app->user->id])->orderBy(['created_at' => SORT_DESC]);

        // Create a pagination object with a limit of 10 items per page
        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $query->count(),
        ]);

        // Apply pagination to the query
        $notes = $query->offset($pagination->offset)
                       ->limit($pagination->limit)
                       ->all();

        return $this->render('index', [
            'notes' => $notes,
            'pagination' => $pagination,
            'model' => $model,
        ]);
    }
}


    /**
     * Get note data by livestock_id.
     * @return mixed
     */
    public function actionGetNoteByLivestockId()
{
    $livestock_id = Yii::$app->request->get('livestock_id'); // Retrieve the livestock_id from the GET request

    if ($livestock_id) {
        $notes = Note::find()->where(['livestock_id' => $livestock_id])->all();

        if (!empty($notes)) {
            return $this->render('index', [
                'notes' => $notes,
                'livestock_id' => $livestock_id,
            ]);
        } else {
            Yii::$app->session->setFlash('error', 'Catatan tidak ditemukan.');
            return $this->redirect(['index']);
        }
    } else {
        Yii::$app->session->setFlash('error', 'Livestock ID tidak valid.');
        return $this->redirect(['index']);
    }
}


    /**
     * Mengunggah dokumentasi ke dalam catatan.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException jika data Note tidak ditemukan
     * @throws BadRequestHttpException jika tidak ada dokumentasi yang diunggah
     * @throws ServerErrorHttpException jika data Note tidak dapat disimpan
     */
    // public function actionUploadDocumentation($id)
    // {
    //     // Temukan model Note berdasarkan ID
    //     $model = $this->findModel($id);

    //     // Ambil gambar dari request
    //     $imageFiles = UploadedFile::getInstancesByName('documentation');

    //     if (!empty($imageFiles)) {
    //         // Ambil user_id dari pengguna yang sedang login
    //         $userId = Yii::$app->user->identity->id;

    //         // Buat path direktori berdasarkan user_id dan id Note
    //         $uploadPath = 'notes/' . $userId . '/' . $model->livestock_id . '/' . $model->id . '/';

    //         // Periksa apakah direktori sudah ada, jika tidak, buat direktori baru
    //         if (!is_dir($uploadPath)) {
    //             FileHelper::createDirectory($uploadPath);
    //         }

    //         $uploadedImages = [];

    //         // Initialize the Google Cloud Storage client
    //         $storage = new StorageClient([
    //             'keyFilePath' => Yii::getAlias('@app/config/sa.json')
    //         ]);
    //         $bucket = $storage->bucket('digiternak1');

    //         // Iterate through each uploaded file
    //         foreach ($imageFiles as $index => $imageFile) {
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

    //             // Save the image information to the note_images table
    //             $noteImage = new NoteImage();
    //             $noteImage->note_id = $model->id;
    //             $noteImage->image_path = $uploadPath . $imageName;
    //             if (!$noteImage->save()) {
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
}
