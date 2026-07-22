<?php

namespace frontend\controllers;

use common\enums\UserRole;
use Yii;
use yii\web\Controller;
use common\models\Cage;
use common\models\Livestock;
use common\models\LivestockImage;
use common\models\Note;
use common\models\NoteImage;
use common\models\BodyCountScore;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class CageController extends Controller
{
    public $modelClass = 'common\models\Cage';

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
        // $behaviors['authenticator'] = [
        //     'class' => HttpBearerAuth::class,
        //     'except' => ['options'],
        // ];

        // Menambahkan VerbFilter untuk memastikan setiap action hanya menerima HTTP method yang sesuai
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'create' => ['POST'],
                'update' => ['GET', 'POST', 'PUT'],
                'delete' => ['DELETE'],
                'view' => ['GET'],
                'view-api' => ['GET'],
                'get-cages' => ['GET'],
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

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Since all actions require a token, directly verify the token without checking the action's id
        // $token = Yii::$app->request->getHeaders()->get('Authorization');
        // if ($token !== null && !User::verifyJwt($token)) {
        //     throw new \yii\web\UnauthorizedHttpException('Your token is invalid or expired.');
        //     return false;
        // }

        return true; // Proceed with the action since the token is valid
    }

    /**
     * Menampilkan data Cage.
     * @return mixed
     */
    public function actionIndex(?Cage $model = null)
    {
        $model ??= new Cage();
        $userId = Yii::$app->user->identity->id;
        $error = [];

        // Get the list of cages based on user_id
        $cage = Cage::find()
            ->where(['user_id' => $userId])
            ->all();
        return $this-> render('index',[
            'cage' => $cage,
            'model' => $model,
            'error' => $error,
        ]);
    }

    /**
     * Menampilkan data Cage berdasarkan ID.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = Cage::findOne($id);

        if (!$model) {
            Yii::$app->session->setFlash('error', 'Kandang tidak ditemukan.');
            return $this->redirect(['index']);
        }

        // Pastikan kandang milik user yang sedang login
        $userId = Yii::$app->user->identity->id;
        if ($model->user_id != $userId) {
            Yii::$app->session->setFlash('error', 'Anda tidak memiliki akses ke kandang ini.');
            return $this->redirect(['index']);
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Menampilkan data Cage dalam format JSON berdasarkan ID (untuk API).
     * @param integer $id
     * @return array
     */
    public function actionViewApi($id)
    {
        $cage = Cage::findOne($id);

        if ($cage) {
            Yii::$app->response->statusCode = 200;
            return [
                'message' => 'Data kandang berhasil ditemukan',
                'error' => false,
                'data' => $cage,
            ];
        } else {
            Yii::$app->response->statusCode = 404;
            return [
                'message' => "Kandang tidak ditemukan",
                'error' => true,
            ];
        }
    }

    /**
     * Mendapatkan daftar nama kandang berdasarkan pengguna yang sedang login.
     * @return array
     */
    public function actionGetCages()
    {
        // Get the ID of the currently logged in user
        $userId = Yii::$app->user->identity->id;

        // Get the list of cages based on user_id
        $cages = Cage::find()
            ->where(['user_id' => $userId])
            ->all();

        if (!empty($cages)) {
            // If the query was successful, return the list of cages, a success message, and an error status of false
            Yii::$app->response->statusCode = 200;
            return [
                'message' => 'Berhasil mendapatkan seluruh daftar kandang.',
                'error' => false,
                'data' => $cages,
            ];
        } else {
            // If the query failed, return an error message, an error status of true, and no cages found message
            Yii::$app->response->statusCode = 404;
            return [
                'message' => 'Tidak ada kandang yang ditemukan',
                'error' => true,
            ];
        }
    }

    /**
     * Membuat data Cage baru.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Cage();
        // $model->scenario = Cage::SCENARIO_CREATE;
        $model->user_id = Yii::$app->user->id;
        $params = Yii::$app->request->post('Cage', []);
        $model->load(['Cage' => $params]);
        if (!$model->validate()) {
            // Debug validation errors
            return $this->actionIndex($model);
        }else{
            $model->save();
            return $this->redirect(['index']);
        }
    }
    
    /**
     * Mengupdate data Cage berdasarkan ID.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        // Find the cage by ID
        $model = Cage::findOne($id);

        // Check if the cage exists
        if (!$model) {
            Yii::$app->session->setFlash('error', 'Kandang tidak ditemukan.');
            return $this->redirect(['index']);
        }

        // If the form is submitted
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // Save the changes
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Kandang berhasil diperbarui.');
                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('error', 'Gagal memperbarui data.');
            }
        }

        // Render the update view with the model data
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Menghapus data Cage berdasarkan ID.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
{
    $transaction = Yii::$app->db->beginTransaction();
    try {
        // Cari cage berdasarkan ID
        $cage = Cage::findOne($id);

        // Jika cage tidak ditemukan, tampilkan pesan error
        if ($cage === null) {
            Yii::$app->session->setFlash('error', 'Gagal menghapus data kandang. Data kandang tidak ditemukan.');
            return $this->redirect(['index']);
        }

        // Dapatkan semua livestock yang terkait dengan cage
        $livestockList = Livestock::find()->where(['cage_id' => $id])->all();

        foreach ($livestockList as $livestock) {
            // Hapus semua catatan yang terkait dengan livestock
            $notes = Note::find()->where(['livestock_id' => $livestock->id])->all();
            foreach ($notes as $note) {
                // Hapus gambar yang terkait dengan catatan terlebih dahulu
                NoteImage::deleteAll(['note_id' => $note->id]);

                // Hapus catatan
                $note->delete();
            }

            // Hapus semua BCS yang terkait dengan livestock
            BodyCountScore::deleteAll(['livestock_id' => $livestock->id]);

            // Hapus gambar livestock
            LivestockImage::deleteAll(['livestock_id' => $livestock->id]);

            // Hapus livestock
            $livestock->delete();
        }

        // Hapus cage
        $cage->delete();

        // Commit transaksi
        $transaction->commit();

        // Set flash message untuk sukses
        Yii::$app->session->setFlash('success', 'Data kandang dan semua ternak di dalamnya berhasil dihapus.');
    } catch (\Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        $transaction->rollBack();
        Yii::$app->session->setFlash('error', 'Gagal menghapus data kandang. Alasan: ' . $e->getMessage());
    }

    // Redirect ke halaman index setelah operasi selesai
    return $this->redirect(['index']);
}

}
