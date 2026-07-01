<?php

namespace backend\controllers;

use common\enums\ApprovalStatus;
use common\enums\RequestResponse;
use common\enums\UserRole;
use common\helpers\ModelHelper;
use common\helpers\UserHelper;
use common\models\form\ChangeLevelForm;
use common\models\form\ExternalReviewForm;
use common\models\form\RequestResponseForm;
use common\models\SaspriK;
use common\services\SaspriKService;
use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\UnprocessableEntityHttpException;

class VerifikasiWaliController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [UserRole::ADMIN],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'ganti-wali' => ['post'],
                    'daftarkan-wali' => ['post'],
                    'simpan-skor-pendaftaran' => ['post'],
                    'ganti-level-sertifikat' => ['post'],
                ],
            ],
        ];
    }

    public function actionGantiLevelSertifikat(int $saspri_k_id)
    {
        try {
            $data = new ChangeLevelForm();
            ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->post());
            SaspriKService::changeRegistrationLevel($saspri_k_id, $data);

            Yii::$app->session->setFlash('success', 'Berhasil mengubah level sertifikat');
            return $this->redirect(['permintaan-pendaftaran-wali', 'saspri_k_id' => $saspri_k_id]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                } elseif ($error instanceof BadRequestHttpException) {
                    return $this->redirect(['permintaan-pendaftaran-wali', 'saspri_k_id' => $saspri_k_id]);
                }
            }
            throw $error;
        }
    }

    public function actionIndex(
        ?int $limit = 10,
        ?int $offset_registration = 0,
        ?int $offset_change = 0,
    ) {
        $registration_query = SaspriK::find()
            ->where(['request_status' => ApprovalStatus::PENDING]);

        $change_query = SaspriK::find()
            ->where(['change_status' => ApprovalStatus::PENDING]);

        $registrations = $registration_query
            ->with([
                'coordinator' => function (ActiveQuery $query) {
                    $query->select(UserHelper::basicSelect());
                },
                'district'
            ])
            ->orderBy(['updated_at' => SORT_ASC])
            ->limit($limit + 1)
            ->offset($offset_registration)
            ->all();
        $registration_has_next = count($registrations) > $limit;
        if ($registration_has_next) {
            array_pop($registrations);
        }

        $changes = $change_query
            ->with([
                'coordinator' => function (ActiveQuery $query) {
                    $query->select(UserHelper::basicSelect());
                },
                'newCoordinator' => function (ActiveQuery $query) {
                    $query->select(UserHelper::basicSelect());
                },
                'district'
            ])
            ->orderBy(['updated_at' => SORT_ASC])
            ->limit($limit + 1)
            ->offset($offset_change)
            ->all();
        $change_has_next = count($changes) > $limit;
        if ($change_has_next) {
            array_pop($changes);
        }

        return $this->render('index', [
            'registration_requests' => $registrations,
            'registration_prev_link' => $offset_registration > 0 ? Url::current(['offset_registration' => max(0, $offset_registration - $limit)]) : null,
            'registration_next_link' => $registration_has_next ? Url::current(['offset_registration' => $offset_registration + $limit]) : null,
            'offset_registration' => $offset_registration,

            'change_requests' => $changes,
            'change_prev_link' => $offset_change > 0 ? Url::current(['offset_change' => max(0, $offset_change - $limit)]) : null,
            'change_next_link' => $change_has_next ? Url::current(['offset_change' => $offset_change + $limit]) : null,
            'offset_change' => $offset_change,
            'limit' => $limit,
        ]);
    }

    public function actionPermintaanPergantianWali(int $saspri_k_id)
    {
        try {
            return $this->render(
                'permintaanPergantianWali',
                SaspriKService::coordinatorChangeDetail($saspri_k_id),
            );
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionGantiWali(int $saspri_k_id)
    {
        try {
            $data = new RequestResponseForm();
            ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->post());
            SaspriKService::coordinatorChangeResponse($saspri_k_id, $data);

            Yii::$app->session->setFlash(
                'success',
                'Berhasil ' . strtolower(RequestResponse::list()[$data->action]) . ' pergantian wali'
            );
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                } elseif ($error instanceof BadRequestHttpException) {
                    return $this->redirect(['permintaan-pergantian-wali']);
                }
            }
            throw $error;
        }
    }

    public function actionPermintaanPendaftaranWali(int $saspri_k_id, ?int $page = 1)
    {
        try {
            return $this->render(
                'permintaanPendaftaranWali',
                SaspriKService::coordinatorRegistrationDetail($saspri_k_id, $page)
            );
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionSimpanSementaraPermintaanPendaftaran(int $saspri_k_id, int $page = 1)
    {
        try {
            $data = new ExternalReviewForm();
            ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->post());
            SaspriKService::saveRegistration($saspri_k_id, $data);

            Yii::$app->session->setFlash('success', 'Perubahan berhasil disimpan sementara');
            $targetPage = Yii::$app->request->post('target_page', $page);
            return $this->redirect([
                'permintaan-pendaftaran-wali',
                'saspri_k_id' => $saspri_k_id,
                'page' => $targetPage,
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof BadRequestHttpException) {
                    return $this->redirect([
                        'permintaan-pendaftaran-wali',
                        'saspri_k_id' => $saspri_k_id,
                        'page' => $page
                    ]);
                } elseif (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionDaftarkanWali(int $saspri_k_id)
    {
        try {
            $request = Yii::$app->request->post();

            $data = new RequestResponseForm();
            ModelHelper::loadAndValidateOrFail($data, $request);

            if ($data->action === RequestResponse::APPROVE) {
                $scores = new ExternalReviewForm();
                ModelHelper::loadAndValidateOrFail($scores, $request);

                SaspriKService::saveRegistration($saspri_k_id, $scores);
            }

            SaspriKService::registrationRequestResponse($saspri_k_id, $data);

            Yii::$app->session->setFlash(
                'success',
                'Berhasil ' . strtolower(RequestResponse::list()[$data->action]) . ' pendaftaran wali'
            );
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof UnprocessableEntityHttpException) {
                    if (str_contains($error->getMessage(), 'tolak')) {
                        return $this->redirect([
                            'permintaan-pendaftaran-wali',
                            'saspri_k_id' => $saspri_k_id,
                        ]);
                    }
                    return $this->redirect(['index']);
                }
                if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                } elseif ($error instanceof BadRequestHttpException) {
                    return $this->redirect([
                        'permintaan-pendaftaran-wali',
                        'saspri_k_id' => $saspri_k_id,
                    ]);
                }
            }
            throw $error;
        }
    }
}
