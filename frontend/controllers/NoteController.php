<?php

namespace frontend\controllers;

use common\enums\UserRole;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use common\models\Note;
use common\models\NoteImage;
use common\models\Livestock;
use common\models\Cage;
use DateInterval;
use DatePeriod;
use DateTime;
use yii\data\Pagination;
use yii\filters\AccessControl;
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
                'livestock-notes' => ['GET'],
                'get-note-by-livestock-id' => ['GET'],
                'upload-documentation' => ['POST'],
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

    public function actionLivestockNotes(int $id)
    {
        $userId = Yii::$app->user->id;

        $livestock = Livestock::find()
            ->where(['id' => $id, 'user_id' => $userId])
            ->with('notes')
            ->one();

        if ($livestock === null) {
            throw new NotFoundHttpException('Data sapi tidak ditemukan.');
        }

        $notes = $livestock->notes;

        $notesByDate = [];
        $earliestNoteDate = null;

        foreach ($notes as $note) {
            $dateString = $note->note_date ?? null;
            if (empty($dateString) && !empty($note->created_at)) {
                $dateString = substr($note->created_at, 0, 10);
            }

            if (empty($dateString)) {
                continue;
            }

            $dateKey = (new DateTime($dateString))->format('Y-m-d');
            $notesByDate[$dateKey][] = $note;

            if ($earliestNoteDate === null || $dateKey < $earliestNoteDate) {
                $earliestNoteDate = $dateKey;
            }
        }

        $today = new DateTime('today');
        $birthDate = !empty($livestock->birthdate) ? new DateTime($livestock->birthdate) : clone $today;
        $startReference = clone $birthDate;

        if ($earliestNoteDate !== null) {
            $earliestNote = new DateTime($earliestNoteDate);
            if ($earliestNote < $startReference) {
                $startReference = $earliestNote;
            }
        }

        if ($startReference > $today) {
            $startReference = clone $today;
        }

        $firstMonthStart = (clone $startReference)->modify('first day of this month');
        $currentMonthStart = (clone $today)->modify('first day of this month');

        $monthsSummary = [];
        $monthCursor = clone $firstMonthStart;

        while ($monthCursor <= $currentMonthStart) {
            $monthKey = $monthCursor->format('Y-m');
            $monthLabel = Yii::$app->formatter->asDate($monthCursor->format('Y-m-d'), 'php:F Y');

            $monthStart = clone $monthCursor;
            $monthEnd = (clone $monthCursor)->modify('last day of this month');

            if ($monthStart < $birthDate) {
                $monthStart = clone $birthDate;
            }

            if ($monthEnd > $today) {
                $monthEnd = clone $today;
            }

            $missingDays = [];
            $dayIteratorEnd = (clone $monthEnd)->modify('+1 day');
            $period = new DatePeriod($monthStart, new DateInterval('P1D'), $dayIteratorEnd);

            foreach ($period as $day) {
                $dayKey = $day->format('Y-m-d');
                if (empty($notesByDate[$dayKey])) {
                    $missingDays[] = $dayKey;
                }
            }

            $monthsSummary[$monthKey] = [
                'label' => $monthLabel,
                'year' => (int) $monthCursor->format('Y'),
                'month' => (int) $monthCursor->format('m'),
                'hasMissing' => !empty($missingDays),
                'missingDays' => $missingDays,
            ];

            $monthCursor->modify('+1 month');
        }

        $requestedMonth = Yii::$app->request->get('month');
        $requestedYear = Yii::$app->request->get('year');

        $selectedKey = null;
        if ($requestedMonth !== null && $requestedYear !== null) {
            $selectedKey = sprintf('%04d-%02d', (int) $requestedYear, (int) $requestedMonth);
        }

        if ($selectedKey === null || !isset($monthsSummary[$selectedKey])) {
            $selectedKey = $currentMonthStart->format('Y-m');
            if (!isset($monthsSummary[$selectedKey]) && !empty($monthsSummary)) {
                $selectedKey = array_key_last($monthsSummary);
            }
        }

        $selectedMonthData = $monthsSummary[$selectedKey] ?? reset($monthsSummary);

        if ($selectedMonthData === false) {
            $selectedMonthData = null;
        }

        $dailyEntries = [];
        if ($selectedMonthData !== null) {
            $monthStart = new DateTime(sprintf('%04d-%02d-01', $selectedMonthData['year'], $selectedMonthData['month']));
            $monthEnd = (clone $monthStart)->modify('last day of this month');

            if ($monthStart < $birthDate) {
                $monthStart = clone $birthDate;
            }

            if ($monthEnd > $today) {
                $monthEnd = clone $today;
            }

            $dayIteratorEnd = (clone $monthEnd)->modify('+1 day');
            $period = new DatePeriod($monthStart, new DateInterval('P1D'), $dayIteratorEnd);

            foreach ($period as $day) {
                $dayKey = $day->format('Y-m-d');
                $dailyEntries[] = [
                    'date' => $dayKey,
                    'notes' => $notesByDate[$dayKey] ?? [],
                    'isMissing' => empty($notesByDate[$dayKey]),
                ];
            }
        }

        return $this->render('livestock-notes', [
            'livestock' => $livestock,
            'monthsSummary' => $monthsSummary,
            'selectedKey' => $selectedKey,
            'dailyEntries' => $dailyEntries,
        ]);
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
            Yii::$app->session->setFlash('error', 'Gagal membuat catatan, livestock_id tidak ditemukan.');
            return $this->redirect(['index']);
        }

        // Cari ternak berdasarkan livestock_id
        $livestock = Livestock::findOne($livestock_id);

        if (!$livestock) {
            Yii::$app->response->statusCode = 400;
            Yii::$app->session->setFlash('error', 'Gagal membuat catatan, data ternak tidak ditemukan.');
            return $this->redirect(['index']);
        }

        // Cari kandang yang terkait dengan ternak
        $cage = Cage::findOne($livestock->cage_id);

        if (!$cage) {
            Yii::$app->response->statusCode = 400;
            Yii::$app->session->setFlash('error', 'Gagal membuat catatan, kandang tidak ditemukan.');
            return $this->redirect(['index']);
        }

        // Set atribut-atribut dari Note
        $model->livestock_id = $livestock->id;
        $model->livestock_vid = $livestock->vid;
        $model->livestock_name = $livestock->name;
        $model->livestock_cage = $cage->name;
        $model->location = $cage->location;

        // Muat data dari input POST
        if ($model->load(Yii::$app->request->post())) {
            // Cek duplikasi di tanggal yang sama (WIB)
            $noteDate = $model->note_date ?: (new \DateTime('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
            $existing = Note::find()
                ->where([
                    'livestock_id' => $model->livestock_id,
                    'note_date' => $noteDate,
                ])
                ->exists();

            if ($existing) {
                Yii::$app->session->setFlash('error', 'Catatan pada tanggal ' . Yii::$app->formatter->asDate($noteDate, 'php:d M Y') . ' telah dibuat.');
                return $this->redirect(['livestock-notes', 'id' => $livestock->id]);
            }

            if ($model->validate() && $model->save()) {
                Yii::$app->session->setFlash('success', 'Catatan berhasil dibuat.');
                return $this->redirect(['livestock-notes', 'id' => $livestock->id]);
            }
        }

        Yii::$app->response->statusCode = 400;
        Yii::$app->session->setFlash('error', 'Catatan gagal dibuat.');
        return $this->redirect(['livestock-notes', 'id' => $livestock->id]);
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
            return $this->redirect(['livestock-notes', 'id' => Yii::$app->request->get('livestock_id')]);
        }

        if ($model->load(Yii::$app->request->post())) {
            if (empty($model->note_date)) {
                $model->note_date = (new \DateTime('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
            }

            if ($model->validate() && $model->save()) {
                Yii::$app->session->setFlash('success', 'Catatan berhasil diperbarui.');
                return $this->redirect(['livestock-notes', 'id' => $model->livestock_id]);
            }

            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', implode('<br>', array_values($errors)));
            return $this->redirect(['update', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
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
        $userId = Yii::$app->user->identity->id;
        $livestock = Livestock::find()
            ->where(['user_id' => $userId])
            ->all();

        // Validasi cage_id berdasarkan user_id
        if (empty($livestock)) {
            Yii::$app->session->setFlash('error', 'Sapi tidak boleh kosong, mohon buat sapi terlebih dahulu.');
            return $this->redirect(['livestock/index']);
        }
        $model = new Note();

        $prefillLivestockId = Yii::$app->request->get('livestock_id');
        if (!empty($prefillLivestockId)) {
            $ownsLivestock = Livestock::find()
                ->where(['id' => $prefillLivestockId, 'user_id' => $userId])
                ->exists();
            if ($ownsLivestock) {
                $model->livestock_id = (int) $prefillLivestockId;
            }
        }

        $prefillDate = Yii::$app->request->get('date');
        if (!empty($prefillDate)) {
            $model->note_date = $prefillDate;
        }
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


    /**
     * Get note data by livestock_id.
     * @return mixed
     */
    public function actionGetNoteByLivestockId()
    {
        $livestock_id = Yii::$app->request->get('livestock_id'); // Retrieve the livestock_id from the GET request

        if (!$livestock_id) {
            Yii::$app->session->setFlash('error', 'Livestock ID tidak valid.');
            return $this->redirect(['index']);
        }

        $userId = Yii::$app->user->id;

        $query = Note::find()
            ->where([
                'livestock_id' => $livestock_id,
                'user_id' => $userId,
            ])
            ->orderBy(['created_at' => SORT_DESC]);

        if (!$query->exists()) {
            Yii::$app->session->setFlash('error', 'Catatan tidak ditemukan.');
            return $this->redirect(['index']);
        }

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $query->count(),
        ]);

        $notes = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $model = new Note();
        $model->livestock_id = (int) $livestock_id;

        return $this->render('index', [
            'notes' => $notes,
            'pagination' => $pagination,
            'model' => $model,
            'livestock_id' => $livestock_id,
        ]);
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
