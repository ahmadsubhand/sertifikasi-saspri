<?php

namespace frontend\controllers;

use common\enums\UserRole;
use Yii;
use common\models\BodyCountScore;
use common\models\Livestock;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;

class BcsController extends Controller
{
    public $modelClass = 'common\models\BodyCountScore';

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

        // Menambahkan authenticator untuk otentikasi menggunakan access token

        // Menambahkan VerbFilter untuk memastikan setiap action hanya menerima HTTP method yang sesuai
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'create' => ['POST'],
                'update' => ['PUT', 'PATCH'],
                'delete' => ['DELETE'],
                'view' => ['GET'],
                'get-bcs-by-livestock-id' => ['GET'],
                'upload-bcs' => ['POST'],
                'index' => ['GET'],
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
    public function actionIndex()
    {
        $userId = Yii::$app->user->identity->id;
        $livestock = Livestock::find()
            ->where(['user_id' => $userId])
            ->all();

        // Validasi cage_id berdasarkan user_id
        if (empty($livestock)) {
            Yii::$app->session->setFlash('error', 'Sapi tidak boleh kosong, mohon buat sapi terlebih dahulu.');
            return $this->redirect(['livestock/index']);
        }
        $bcs = BodyCountScore::find()
            ->alias('bcs')
            ->joinWith('livestock')
            ->orderBy(['bcs.created_at' => SORT_DESC])
            ->where(['livestock.user_id' => $userId])
            ->all();

        return $this->render('index', [
            'bcs' => $bcs,
            'model' => new BodyCountScore(),
        ]);
    }



    // public function actionCreate($livestock_id)
    // {
    //     $model = new BodyCountScore();
    //     $model->livestock_id = $livestock_id;
    
    //     if ($model->load(Yii::$app->request->post(), '') && $model->validate()) {
    //         // Update the Livestock model
    //         $livestock = Livestock::findOne($model->livestock_id);
    //         if ($livestock) {
    //             $model->save();
    //             $livestock->body_weight = $model->body_weight;
    //             $livestock->chest_size = $model->chest_size;
    //             $livestock->save(false);
    
    //             Yii::$app->response->setStatusCode(201);
    //             return [
    //                 'message' => 'BCS berhasil dibuat.',
    //                 'error' => false,
    //                 'data' => $model,
    //             ];
    //         } else {
    //             Yii::$app->response->setStatusCode(400);
    //             return [
    //                 'message' => 'Gagal membuat pembaruan BCS. Ternak tidak ditemukan',
    //                 'error' => true,
    //             ];
    //         }
    //     } else {
    //         Yii::$app->response->setStatusCode(400);
    //         return [
    //             'message' => 'Gagal membuat BCS. Data tidak valid.',
    //             'error' => true,
    //             'details' => $this->getValidationErrors($model),
    //         ];
    //     }
    // }
    public function actionCreate()
{
    $model = new BodyCountScore(); // Menggunakan model BCS

    // Jika request adalah POST
    if (Yii::$app->request->isPost) {
        $requestData = Yii::$app->request->post();

        // Cek apakah livestock_id ada di POST data
        if (isset($requestData['BodyCountScore']['livestock_id'])) {
            $livestockId = $requestData['BodyCountScore']['livestock_id'];

            // Cari data ternak berdasarkan livestock_id
            $livestock = Livestock::findOne($livestockId);

            // Jika ternak ditemukan, ambil data body_weight dan chest_size
            if ($livestock !== null) {
                $model->body_weight = $livestock->body_weight;
                $model->chest_size = $livestock->chest_size;
            }
        }

        // Muat data POST ke dalam model BCS
        $model->load($requestData);

        // Simpan data jika validasi berhasil
        if ($model->save()) {
            Yii::$app->session->setFlash('bcs_success', 'Data BCS berhasil dibuat.');
            return $this->redirect(['index']); // Redirect ke halaman index setelah berhasil
        }
    }

    // Render form create
    return $this->render('create', [
        'model' => $model,
    ]);
}

public function actionUpdate(int $id)
{
    $model = BodyCountScore::findOne($id);

    // Check if the cage exists
    if (!$model) {
        Yii::$app->session->setFlash('bcs_error', 'Data BCS tidak ditemukan.');
        return $this->redirect(['index']);
    }

    // If the form is submitted
    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        // Save the changes
        if ($model->save()) {
            Yii::$app->session->setFlash('bcs_success', 'Data BCS berhasil diperbarui.');
            return $this->redirect(['index']);
        } else {
            Yii::$app->session->setFlash('bcs_error', 'Gagal memperbarui data BCS.');
        }
    }

    // Render the update view with the model data
    return $this->render('update', [
        'model' => $model,
    ]);
}


    public function actionDelete(int $id)
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; // Set response format to JSON

    // Mulai transaksi untuk menjaga konsistensi data
    $transaction = Yii::$app->db->beginTransaction();

    try {
        $model = BodyCountScore::findOne($id);

        if ($model !== null) {
            // Hapus model dan komit transaksi
            if ($model->delete() !== false) {
                $transaction->commit();
                return $this->redirect(['index']);
            } else {
                // Rollback jika penghapusan gagal
                $transaction->rollBack();
                return [
                    'message' => 'Gagal menghapus data BCS.',
                    'error' => true,
                ];
            }
        } else {
            // Jika model tidak ditemukan, rollback dan kembalikan pesan error
            $transaction->rollBack();
            return [
                'message' => 'Gagal menghapus data BCS, data tidak ditemukan.',
                'error' => true,
            ];
        }
    } catch (\Exception $e) {
        // Jika terjadi exception, rollback transaksi dan kembalikan pesan error
        $transaction->rollBack();
        Yii::$app->session->setFlash('error', 'Gagal menghapus data BCS. Alasan: ' . $e->getMessage());
        return $this->redirect(['index']);
    }
}


    public function actionView(int $id)
    {
        $model = BodyCountScore::findOne($id);
        if ($model) {
            $response = Yii::$app->response;
            $response->setStatusCode(200);
            $response->data = [
                'message' => 'Data BCS berhasil ditemukan.',
                'error' => false,
                'data' => $model,
            ];
            return $response;
        } else {
            $response = Yii::$app->response;
            $response->setStatusCode(404);
            $response->data = [
                'message' => 'Data BCS tidak ditemukan.',
                'error' => true,
            ];
            return $response;
        }
    }

    public function actionGetBcsByLivestockId(int $livestock_id)
    {
        $models = BodyCountScore::find()->where(['livestock_id' => $livestock_id])->all();
        if ($models) {
            $response = Yii::$app->response;
            $response->setStatusCode(200);
            $response->data = [
                'message' => 'Data BCS berhasil ditemukan.',
                'error' => false,
                'data' => $models,
            ];
            return $response;
        } else {
            $response = Yii::$app->response;
            $response->setStatusCode(404);
            $response->data = [
                'message' => 'Data BCS tidak ditemukan.',
                'error' => true,
            ];
            return $response;
        }
    }

    // public function actionUploadBcs($id)
    // {
    //     // Find the Bcs model based on ID
    //     $model = $this->findModel($id);

    //     // Get the image from the request
    //     $imageFiles = UploadedFile::getInstancesByName('bcs_image');

    //     if (!empty($imageFiles)) {
    //         // Get the user_id of the currently logged in user
    //         $userId = Yii::$app->user->identity->id;

    //         // Create a directory path based on user_id and Livestock id
    //         $uploadPath = 'bcs/' . $userId . '/' . $model->livestock_id . '/' . $model->id . '/';

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

    //             // Save the image information to the bcs_images table
    //             $bcsImage = new BcsImage();
    //             $bcsImage->bcs_id = $model->id;
    //             $bcsImage->image_path = $uploadPath . $imageName;
    //             if (!$bcsImage->save()) {
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

    protected function findModel(int $id)
    {
        if (($model = BodyCountScore::findOne($id)) !== null) {
            return $model;
        } else {
            return null;
        }
    }

}