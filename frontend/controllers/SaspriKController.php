<?php

namespace frontend\controllers;

use common\enums\ApprovalStatus;
use common\enums\CertificationStatus;
use common\enums\TeamRole;
use common\enums\UserRole;
use common\helpers\UserHelper;
use common\models\Certification;
use common\models\SaspriK;
use common\models\SelfTeamMember;
use common\models\User;
use Exception;
use yii\web\Controller;
use Yii;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnprocessableEntityHttpException;

class SaspriKController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [UserRole::COORDINATOR],
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'tambah-anggota' => ['post'],
                    'hapus-anggota' => ['delete'],
                    'tambah-anggota-tim-mandiri' => ['post'],
                    'hapus-anggota-tim-mandiri' => ['delete'],
                    'ubah-peran-anggota-tim-mandiri' => ['post'],
                    'ajukan-sertifikasi' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(
        int $user_limit = 10,
        int $user_offset = 0,
        int $certification_limit = 10,
        int $certification_offset = 0
    ) {
        try {
            $saspri_k = $this->findSaspriKAsCoordinator();

            return $this->render('index', [
                'saspri_k' => $saspri_k,
                'valid_certificate' => $saspri_k->validCertificate,
                'completed_certifications' => $saspri_k->getCertifications()
                    ->where(['status' => CertificationStatus::COMPLETED])
                    ->orderBy(['updated_at' => SORT_DESC]) // nanti bisa dicustom
                    ->limit($certification_limit)
                    ->offset($certification_offset)
                    ->all(),
                'saspri_k_members' => $saspri_k->getUsers()
                    ->where(['!=', 'id', Yii::$app->user->id])
                    ->orderBy(['updated_at' => SORT_DESC]) // nanti bisa dicustom
                    ->select(UserHelper::$basicSelect)
                    ->limit($user_limit)
                    ->offset($user_offset)
                    ->all(),
            ]);
        } catch (Exception $error) {
            if ($error instanceof ForbiddenHttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                return $this->goHome();
            }
            throw $error;
        }
    }

    public function actionCariUser(string $q)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $users = $this->getAvailableSaspriKMembersQuery()
            ->andWhere(['like', 'username', $q])
            ->select(['id', 'username'])
            ->limit(10)
            ->asArray()
            ->all();

        return $users;
    }

    public function actionTambahAnggota()
    {
        try {
            $user_ids = Yii::$app->request->post('user_ids');
            if (!empty($user_ids)) {
                $array_user_ids = UserHelper::convertUserIdsToArray($user_ids);

                $valid_users = $this->getAvailableSaspriKMembersQuery()
                    ->andWhere(['id' => $array_user_ids])
                    ->select('username')
                    ->column();

                if (count($valid_users) !== count($array_user_ids)) {
                    throw new BadRequestHttpException('Beberapa user tidak valid atau sudah terdaftar di SASPRI-K lain');
                }

                $saspri_k = $this->findSaspriKAsCoordinator();
                $saspri_k->addMembers($array_user_ids);

                Yii::$app->session->setFlash(
                    'success',
                    implode(', ', $valid_users) . ' berhasil ditambahkan ke SASPRI-K ' . $saspri_k->region_name
                );
                return $this->redirect(['index']);
            }
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                } elseif ($error instanceof BadRequestHttpException) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionHapusAnggota(int $user_id)
    {
        try {
            $saspri_k = $this->findSaspriKAsCoordinator();
            $user = $this->findAMemberOfSaspriK($user_id, $saspri_k);
            $user->removeUserFromSaspriK()->save(false);

            Yii::$app->session->setFlash(
                'success',
                $user->username . ' berhasil dikeluarkan dari SASPRI-K ' . $saspri_k->region_name
            );
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                } elseif ($error instanceof NotFoundHttpException) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionPengajuanSertifikasi()
    {
        try {
            $saspri_k = $this->findSaspriKAsCoordinator();
            $certification = $this->findOrCreateOnGoingCertification($saspri_k);

            return $this->render('pengajuanSertifikasi', [
                'saspri_k' => $saspri_k,
                'district' => $saspri_k->district,
                'certification' => $certification,
                'self_team_members' => $certification
                    ->getSelfTeamMembers()
                    ->with([
                        'user' => function (ActiveQuery $query) {
                            $query->select(['id', 'username']);
                        }
                    ])
                    ->all(),
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                } elseif ($error instanceof UnprocessableEntityHttpException) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionCariAnggotaTimMandiri(string $q)
    {
        try {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $saspri_k = $this->findSaspriKAsCoordinator();
            $certification = $saspri_k->onGoingCertification;
            $users = $this->getAvailableSelfTeamMembersQuery($saspri_k, $certification)
                ->andWhere(['like', 'username', $q])
                ->limit(10)
                ->asArray()
                ->all();

            return $users;
        } catch (Exception $error) {
            if ($error instanceof ForbiddenHttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                return $this->goHome();
            }
            throw $error;
        }
    }

    public function actionCariAnggotaSaspriK(string $q)
    {
        try {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $saspri_k = $this->findSaspriKAsCoordinator();
            $users = $saspri_k->getUsers()
                ->where(['!=', 'id', $saspri_k->coordinator_id])
                ->andWhere(['like', 'username', $q])
                ->select(['id', 'username'])
                ->limit(10)
                ->asArray()
                ->all();

            return $users;
        } catch (Exception $error) {
            if ($error instanceof ForbiddenHttpException) {
                return [];
            }
            throw $error;
        }
    }

    public function actionTambahAnggotaTimMandiri()
    {
        try {
            $user_ids = Yii::$app->request->post('user_ids');
            if (!empty($user_ids)) {
                $saspri_k = $this->findSaspriKAsCoordinator();
                $certification = $this->findOrCreateOnGoingCertification($saspri_k);
                $this->ensureSelfTeamCanBeModified($certification);

                $array_user_ids = UserHelper::convertUserIdsToArray($user_ids);
                $valid_users = $this->getAvailableSelfTeamMembersQuery($saspri_k, $certification)
                    ->andWhere(['id' => $array_user_ids])
                    ->select('username')
                    ->column();

                if (count($valid_users) !== count($array_user_ids)) {
                    throw new BadRequestHttpException('Beberapa user tidak valid atau sudah terdaftar di Tim Mandiri saat ini');
                }

                $certification->save(false); // untuk mendapatkan id jika sertifikasi baru diajukan
                $certification->addSelfTeamMembers($array_user_ids);

                Yii::$app->session->setFlash('success', implode(', ', $valid_users) . ' berhasil ditambahkan ke Tim Mandiri');
            }

            return $this->redirect(['pengajuan-sertifikasi']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                } elseif ($error instanceof UnprocessableEntityHttpException) {
                    if (str_contains($error->getMessage(), 'Tim Mandiri')) {
                        return $this->redirect(['pengajuan-sertifikasi']);
                    }
                    return $this->redirect(['index']);
                } elseif ($error instanceof BadRequestHttpException) {
                    return $this->redirect(['pengajuan-sertifikasi']);
                }
            }
            throw $error;
        }
    }

    public function actionHapusAnggotaTimMandiri(int $user_id)
    {
        try {
            $saspri_k = $this->findSaspriKAsCoordinator();
            $certification = $this->findOrCreateOnGoingCertification($saspri_k);
            $this->ensureSelfTeamCanBeModified($certification);

            $member = $this->findAMemberOfSelfTeam($user_id, $certification);
            $member->delete();

            Yii::$app->session->setFlash('success', $member->user->username . ' berhasil dikeluarkan dari Tim Mandiri');
            return $this->redirect(['pengajuan-sertifikasi']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                } elseif (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    Yii::$app->session->setFlash('error', $error->getMessage());
                    return $this->redirect(['pengajuan-sertifikasi']);
                }
            }
            throw $error;
        }
    }

    public function actionUbahPeranAnggotaTimMandiri(int $user_id)
    {
        try {
            $saspri_k = $this->findSaspriKAsCoordinator();
            $certification = $this->findOrCreateOnGoingCertification($saspri_k);
            $this->ensureSelfTeamCanBeModified($certification);

            $role = Yii::$app->request->post('role');
            $member = $this->findAMemberOfSelfTeam($user_id, $certification);
            $member->changeRole($role)->save(false);

            Yii::$app->session->setFlash('success', 'Peran ' . $member->user->username . ' berhasil diubah menjadi ' . TeamRole::list()[$role]);
            return $this->redirect(['pengajuan-sertifikasi']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                } elseif (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException ||
                    $error instanceof BadRequestHttpException
                ) {
                    return $this->redirect(['pengajuan-sertifikasi']);
                }
            }
            throw $error;
        }
    }

    public function actionAjukanSertifikasi()
    {
        try {
            $saspri_k = $this->findSaspriKAsCoordinator();
            $certification = $saspri_k->onGoingCertification;
            if (!$certification) {
                throw new NotFoundHttpException('Tidak ada sertifikasi yang sedang berlangsung');
            }
            if ($certification->status !== CertificationStatus::PENDING_SELF_TEAM_FORMATION) {
                throw new ConflictHttpException('Sertifikasi sudah pernah diajukan');
            }
            $certification->validateApprovedSelfTeamComposition();
            $certification->submitForSelfReview()->save(false);

            Yii::$app->session->setFlash('success', 'Sertifikasi berhasil diajukan');
            return $this->redirect(['pengajuan-sertifikasi']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                } elseif ($error instanceof NotFoundHttpException) {
                    return $this->redirect(['index']);
                } elseif (
                    $error instanceof ConflictHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['pengajuan-sertifikasi']);
                }
            }
            throw $error;
        }
    }

    public function actionDetail(int $case_id)
    {
        try {
            $sasprik = $this->findSaspriKAsCoordinator();
            $cert = $sasprik->getCertifications()->where(['id' => $case_id])->one();
            if (!$cert) {
                throw new NotFoundHttpException('Sertifikasi tidak ditemukan');
            }
            $selfTeam = $cert->getSelfTeamMembers()
                ->with([
                    'user' => function (ActiveQuery $query) {
                        $query->select(UserHelper::$basicSelect);
                    },
                ])
                ->all();
            $peerTeam = $cert->getPeerTeamMembers()
                ->with([
                    'user' => function (ActiveQuery $query) {
                        $query->select(UserHelper::$basicSelect);
                    },
                ])
                ->all();
            return $this->render('detail', [
                'id' => $case_id,
                'saspri' => $sasprik,
                'cert' => $cert,
                'selfTeam' => $selfTeam,
                'peerTeam' => $peerTeam,
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                } elseif ($error instanceof NotFoundHttpException) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionPergantianWali()
    {
        try {
            $saspri_k = $this->findSaspriKAsCoordinator();

            return $this->render('pergantianWali', [
                'saspri_k' => $saspri_k,
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                }
            }
            throw $error;
        }
    }

    public function actionAjukanPergantianWali()
    {
        try {
            $saspri_k = $this->findSaspriKAsCoordinator();
            if ($saspri_k->change_status === ApprovalStatus::PENDING) {
                throw new UnprocessableEntityHttpException(
                    'Pergantian wali sudah pernah diajukan dan masih dalam proses tinjauan SASPRI-Nasional'
                );
            }
            $new_coordinator = $this->findAMemberOfSaspriK(Yii::$app->request->post('new_coordinator_id'), $saspri_k);

            $saspri_k->new_coordinator_id = $new_coordinator->id;
            $saspri_k->change_request_reason = Yii::$app->request->post('change_request_reason');
            $saspri_k->change_status = ApprovalStatus::PENDING;
            $saspri_k->change_rejection_reason = null;
            $saspri_k->save(false);

            Yii::$app->session->setFlash('success', 'Pergantian wali berhasil diajukan');
            return $this->redirect(['pergantian-wali']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                } elseif (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['pergantian-wali']);
                }
            }
            throw $error;
        }
    }


    private function findSaspriKAsCoordinator()
    {
        $saspri_k = User::findOne(['id' => Yii::$app->user->id])
            ->saspriKAsCoordinator;
        if (!$saspri_k) {
            throw new ForbiddenHttpException('Hanya wali yang boleh mengakses halaman ini');
        }
        return $saspri_k;
    }

    private function getAvailableSaspriKMembersQuery()
    {
        return User::find()
            ->leftJoin('auth_assignment aa', 'aa.user_id = id')
            ->where(['!=', 'aa.item_name', UserRole::ADMIN])
            ->andWhere(['saspri_k_id' => null]);
    }

    private function findAMemberOfSaspriK(int $user_id, SaspriK $saspri_k): User
    {
        $user = $saspri_k->getUsers()->where(['id' => $user_id])->one();
        if (!$user) {
            throw new NotFoundHttpException('User tidak ditemukan dalam SASPRI-K' . $saspri_k->region_name);
        }
        return $user;
    }

    private function findOrCreateOnGoingCertification(SaspriK $saspri_k)
    {
        try {
            $certification = $saspri_k->onGoingCertification ?: $saspri_k->createNewCertificationRequest();
            return $certification;
        } catch (Exception $error) {
            if ($error instanceof UnprocessableEntityHttpException) {
                throw new UnprocessableEntityHttpException($error->getMessage());
            }
            throw $error;
        }
    }

    private function ensureSelfTeamCanBeModified(Certification $certification)
    {
        if ($certification->status !== CertificationStatus::PENDING_SELF_TEAM_FORMATION) {
            throw new UnprocessableEntityHttpException(
                'Tim Mandiri hanya dapat diubah sebelum proses sertifikasi dimulai'
            );
        }
    }

    private function getAvailableSelfTeamMembersQuery(SaspriK $saspri_k, ?Certification $certification)
    {
        $existing_member_ids = $certification
            ? $certification->getSelfTeamMembers()
            ->select('user_id')
            ->column()
            : [];

        return $saspri_k->getUsers()
            ->where(['!=', 'id', $saspri_k->coordinator_id])
            ->andWhere(['not in', 'id', $existing_member_ids]);
    }

    private function findAMemberOfSelfTeam(int $user_id, Certification $certification): SelfTeamMember
    {
        $member = $certification->getSelfTeamMembers()
            ->where(['user_id' => $user_id])
            ->one();
        if (!$member) {
            throw new NotFoundHttpException('User tidak ditemukan atau bukan anggota Tim Mandiri');
        }
        return $member;
    }
}
