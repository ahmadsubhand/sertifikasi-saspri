<?php

namespace backend\controllers;

use common\enums\CertificationStatus;
use common\enums\TeamRole;
use common\enums\UserRole;
use common\helpers\ModelHelper;
use common\models\Certification;
use common\models\form\AddMembersForm;
use common\models\form\ChangeMemberRoleForm;
use common\models\form\RejectCertificationForm;
use common\models\User;
use common\services\CertificationService;
use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnprocessableEntityHttpException;

class PenentuanTimSebayaController extends Controller
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
                    'tambah-anggota-tim-sebaya' => ['post'],
                    'hapus-anggota-tim-sebaya' => ['delete'],
                    'ubah-peran-anggota-tim-sebaya' => ['post'],
                    'ajukan-peer-review' => ['post'],
                    'tolak-sertifikasi' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(?int $limit = 20, ?int $offset = 0)
    {
        $certs = Certification::find()
            ->where(['status' => CertificationStatus::PENDING_PEER_TEAM_FORMATION])
            ->orderBy(['peer_team_due_date' => SORT_ASC])
            ->with(['saspriK'])
            ->limit($limit + 1)
            ->offset($offset)
            ->all();

        $has_next = count($certs) > $limit;
        if ($has_next) array_pop($certs);

        return $this->render('index', [
            'certifications' => $certs,
            'prev_link' => $offset > 0 ? Url::current(['offset' => max(0, $offset - $limit)]) : null,
            'next_link' => $has_next ? Url::current(['offset' => $offset + $limit]) : null,
            'offset' => $offset,
        ]);
    }

    public function actionPembentukanTimSebaya(int $certification_id)
    {
        try {
            $certification = CertificationService::findOrFail($certification_id)
                ->validateCertificationStatus(CertificationStatus::PENDING_PEER_TEAM_FORMATION);

            return $this->render('pembentukanTimSebaya', [
                'saspri_k' => $certification->saspriK,
                'district' => $certification->saspriK->district,
                'certification' => $certification,
                'valid_certificate' => $certification->saspriK->validCertificate,
                'peer_team_members' => $certification
                    ->getFullPeerTeamMembers()
                    ->with([
                        'user' => function (ActiveQuery $query) {
                            $query->alias('u')
                                ->with('saspriK')
                                ->select(['u.id', 'u.username', 'u.saspri_k_id', 'u.full_name']);
                        }
                    ])
                    ->all(),
            ]);
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

    public function actionCariAnggotaTimSebaya(int $certification_id, string $q)
    {
        try {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $certification = CertificationService::findOrFail($certification_id)
                ->validateCertificationStatus(CertificationStatus::PENDING_PEER_TEAM_FORMATION);

            $users = User::find()->availableForPeerTeam($certification)
                ->andWhere(['like', 'username', $q])
                ->select(['id', 'username'])
                ->limit(10)
                ->asArray()
                ->all();

            return $users;
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

    public function actionTambahAnggotaTimSebaya(int $certification_id)
    {
        try {
            $data = new AddMembersForm();
            ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->post());
            $username_users = CertificationService::addPeerTeamMembers($certification_id, $data);

            Yii::$app->session->setFlash('success', implode(', ', $username_users) . ' berhasil ditambahkan ke Tim Sebaya');
            return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                } elseif (
                    $error instanceof BadRequestHttpException ||
                    $error instanceof ConflictHttpException
                ) {
                    return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
                }
            }
            throw $error;
        }
    }

    public function actionHapusAnggotaTimSebaya(int $user_id, int $certification_id)
    {
        try {
            $member = CertificationService::removePeerTeamMember($certification_id, $user_id);
            Yii::$app->session->setFlash(
                'success', $member['user']['username'] . ' berhasil dikeluarkan dari Tim Sebaya'
            );
            return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof NotFoundHttpException &&
                    str_contains($error->getMessage(), 'anggota')
                ) {
                    return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
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

    public function actionUbahPeranAnggotaTimSebaya(int $user_id, int $certification_id)
    {
        try {
            $data = new ChangeMemberRoleForm();
            ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->post());
            $member = CertificationService::changePeerTeamMemberRole($certification_id, $user_id, $data);

            Yii::$app->session->setFlash(
                'success', 
                'Peran ' . $member['user']['username'] . ' berhasil diubah menjadi ' . strtolower(TeamRole::list()[$data->role])
            );
            return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof BadRequestHttpException ||
                    (
                        $error instanceof NotFoundHttpException &&
                        str_contains($error->getMessage(), 'anggota')
                    )
                ) {
                    return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
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

    public function actionAjukanPeerReview(int $certification_id)
    {
        try {
            CertificationService::submitForPeerReview($certification_id);
            Yii::$app->session->setFlash('success', 'Sertifikasi berhasil dilanjutkan ke tahap peer review');
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof UnprocessableEntityHttpException &&
                    str_contains($error->getMessage(), 'minimal')
                ) {
                    return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
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

    public function actionTolakSertifikasi(int $certification_id)
    {
        try {
            $data = new RejectCertificationForm();
            ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->post());
            $certification = CertificationService::rejectPeerTeamFormationRequest($certification_id, $data);
            
            Yii::$app->session->setFlash(
                'success', 
                'Sertifikasi SASPRI-K ' . $certification->saspriK->district->name . ' berhasil ditolak',
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
                }
            }
            throw $error;
        }
    }
}
