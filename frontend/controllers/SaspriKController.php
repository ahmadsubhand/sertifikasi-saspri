<?php

namespace frontend\controllers;

use common\enums\ApprovalStatus;
use common\enums\CertificationStatus;
use common\enums\TeamRole;
use common\enums\UserRole;
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

    // private function findSaspriK(): SaspriK
    // {
    //     $saspri_k = User::findOne(['id' => Yii::$app->user->id])->getSaspriKAsCoordinator()->one();
    //     if (!$saspri_k) {
    //         throw new ForbiddenHttpException('Halaman ini hanya boleh diakses oleh Koordinator SASPRI-K');
    //     }
    //     return $saspri_k;
    // }

    // private function requestNewCertification(): Certification
    // {
    //     try {
    //         return $this->findSaspriK()->createNewCertificationRequest();
    //     } catch (Exception $error) {
    //         if ($error instanceof UnprocessableEntityHttpException) {
    //             throw new UnprocessableEntityHttpException($error->getMessage());
    //         }
    //         throw $error;
    //     }
    // }

    private function findAMemberOfSelfTeam(int $user_id, int|null $certification_id): SelfTeamMember
    {
        $member = SelfTeamMember::find()
            ->with('user')
            ->where(['id' => $user_id, 'certification_id' => $certification_id])
            ->one();
        if (!$member) {
            throw new NotFoundHttpException('User tidak ditemukan atau bukan anggota Tim Mandiri');
        }
        return $member;
    }

    public function actionIndex()
    {
        $saspri_k = User::findOne(['id' => Yii::$app->user->id])
            ->getSaspriKAsCoordinator()
            ->with([
                'validCertificate',
                'certifications' => function (ActiveQuery $query) {
                    $query->where(['status' => CertificationStatus::COMPLETED]);
                },
                'users' => function (ActiveQuery $query) {
                    $query->where(['!=', 'id', Yii::$app->user->id]);
                },
                'district'
            ])
            ->one();

        return $this->render('index', [
            'saspri_k' => $saspri_k,
        ]);
    }

    public function actionCariUser(string $q)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $users = User::find()
          ->select(['id', 'username'])
          ->where(['saspri_k_id' => null])
          ->andWhere(['like', 'username', $q])
          ->limit(10)
          ->asArray()
          ->all();

        return $users;
    }

    public function actionTambahAnggota()
    {
        try {
            $userIds = Yii::$app->request->post('user_ids');
            if (!empty($userIds)) {
                $parsed_user_ids = array_unique(array_filter(array_map('trim', explode(',', $userIds))));

                $valid_users = User::find()
                    ->where(['id' => $parsed_user_ids])
                    ->andWhere(['saspri_k_id' => null])
                    ->select('username')
                    ->column();

                if (count($valid_users) !== count($parsed_user_ids)) {
                    throw new BadRequestHttpException('Beberapa user tidak valid atau sudah terdaftar di SASPRI-K lain');
                }

                /** @var SaspriK $saspri_k */
                $saspri_k = User::findOne(['id' => Yii::$app->user->id])->getSaspriKAsCoordinator()->with('district')->one();
                User::updateAll(
                    ['saspri_k_id' => $saspri_k->id],
                    ['id' => $parsed_user_ids]
                );

                Yii::$app->session->setFlash(
                    'success',
                    implode(', ', $valid_users) .' berhasil ditambahkan ke SASPRI-K ' . $saspri_k->district->name
                );
                return $this->redirect(['index']);
            }
        } catch (Exception $error) {
            if ($error instanceof BadRequestHttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                return $this->redirect(['index']);
            }
            throw $error;
        }
    }

    public function actionHapusAnggota(int $id)
    {
        try {
            /** @var SaspriK $saspri_k */
            $saspri_k = User::findOne(['id' => Yii::$app->user->id])->getSaspriKAsCoordinator()->with('district')->one();

            // Pastikan user yang ingin dikeluarkan memang anggota dari saspri-k ini
            $user = User::findOne(['id' => $id, 'saspri_k_id' => $saspri_k->id]);
            if (!$user) {
                throw new NotFoundHttpException('User tidak ditemukan dalam SASPRI-K' . $saspri_k->district->name);
            }

            $user->saspri_k_id = null;
            $user->save(false);

            Yii::$app->session->setFlash(
                'success',
                $user->username . ' berhasil dikeluarkan dari SASPRI-K ' . $saspri_k->district->name
            );
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof NotFoundHttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                return $this->redirect(['index']);
            }
            throw $error;
        }
    }

    public function actionPengajuanSertifikasi()
    {
        try {
            $saspri_k = User::findOne(['id' => Yii::$app->user->id])->saspriKAsCoordinator;
            $certification = $saspri_k->getOnGoingCertification()->with('selfTeamMembers.user')->one()
                ?: $saspri_k->createNewCertificationRequest();

            return $this->render('pengajuanSertifikasi', [
              'certification' => $certification,
            ]);
        } catch (Exception $error) {
            if ($error instanceof UnprocessableEntityHttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                return $this->redirect(['index']);
            }
            throw $error;
        }
    }

    public function actionCariAnggotaTimMandiri(string $q)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $saspri_k = User::findOne(['id' => Yii::$app->user->id])->saspriKAsCoordinator;
        $certification = $saspri_k->onGoingCertification;
        $existing_member_ids = $certification
            ? $certification->getSelfTeamMembers()
                ->select('user_id')
                ->column()
                : [];

        $users = $saspri_k->getUsers()
            ->where(['!=', 'id', $saspri_k->coordinator_id])
            ->andWhere(['not in', 'id', $existing_member_ids])
            ->andWhere(['like', 'username', $q])
            ->limit(10)
            ->asArray()
            ->all();

        return $users;
    }

    public function actionTambahAnggotaTimMandiri()
    {
        try {
            $userIds = Yii::$app->request->post('user_ids');
            if (!empty($userIds)) {
                $saspri_k = User::findOne(['id' => Yii::$app->user->id])->saspriKAsCoordinator;
                $certification = $saspri_k->onGoingCertification ?: $saspri_k->createNewCertificationRequest();
                $existing_member_ids = $certification
                    ->getSelfTeamMembers()
                    ->select('user_id')
                    ->column();

                $parsed_user_ids = array_unique(array_filter(array_map('trim', explode(',', $userIds))));
                $valid_users = $saspri_k->getUsers()
                    ->where(['id' => $parsed_user_ids])
                    ->andWhere(['!=', 'id', $saspri_k->coordinator_id])
                    ->andWhere(['not in', 'id', $existing_member_ids])
                    ->select('username')
                    ->column();

                if (count($valid_users) !== count($parsed_user_ids)) {
                    throw new BadRequestHttpException('Beberapa user tidak valid atau sudah terdaftar di Tim Mandiri saat ini');
                }

                $certification->save(false); // untuk mendapatkan id jika sertifikasi baru diajukan

                foreach ($parsed_user_ids as $user_id) { // masih N + query
                    $member = new SelfTeamMember();
                    $member->user_id = $user_id;
                    $member->certification_id = $certification->id;
                    $member->save(false);
                }

                Yii::$app->session->setFlash('success', implode(', ', $valid_users) .' berhasil ditambahkan ke Tim Mandiri');
            }

            return $this->redirect(['pengajuan-sertifikasi']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof UnprocessableEntityHttpException) {
                    return $this->redirect(['index']);
                } elseif ($error instanceof BadRequestHttpException) {
                    return $this->redirect(['pengajuan-sertifikasi']);
                }
            }
            throw $error;
        }
    }

    public function actionHapusAnggotaTimMandiri(int $id)
    {
        try {
            $certification_id = User::findOne(['id' => Yii::$app->user->id])
                ->saspriKAsCoordinator
                ->onGoingCertification
                ->id;

            $member = $this->findAMemberOfSelfTeam($id, $certification_id);
            $member->delete();

            Yii::$app->session->setFlash('success', $member->user->username . ' berhasil dikeluarkan dari Tim Mandiri');
            return $this->redirect(['pengajuan-sertifikasi']);
        } catch (Exception $error) {
            if ($error instanceof NotFoundHttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                return $this->redirect(['pengajuan-sertifikasi']);
            }
            throw $error;
        }
    }

    public function actionUbahPeranAnggotaTimMandiri(int $id)
    {
        try {
            $role = Yii::$app->request->post('role');
            if (!in_array($role, TeamRole::values())) {
                throw new BadRequestHttpException('Peran tidak valid');
            }

            $certification_id = User::findOne(['id' => Yii::$app->user->id])
                ->saspriKAsCoordinator
                ->onGoingCertification
                ->id;

            $member = $this->findAMemberOfSelfTeam($id, $certification_id);
            $member->role = $role;
            $member->save(false);

            Yii::$app->session->setFlash('success', 'Peran ' . $member->user->username . ' berhasil diubah menjadi ' . TeamRole::list()[$role]);
            return $this->redirect(['pengajuan-sertifikasi']);
        } catch (Exception $error) {
            if ($error instanceof BadRequestHttpException || $error instanceof NotFoundHttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                return $this->redirect(['pengajuan-sertifikasi']);
            }
            throw $error;
        }
    }

    public function actionAjukanSertifikasi()
    {
        try {
            $certification = User::findOne(['id' => Yii::$app->user->id])
                ->saspriKAsCoordinator
                ->onGoingCertification;
            if (!$certification) {
                throw new NotFoundHttpException('Tidak ada sertifikasi yang sedang berlangsung');
            }
            if ($certification->status !== CertificationStatus::PENDING_SELF_TEAM_FORMATION) {
                throw new ConflictHttpException('Sertifikasi sudah pernah diajukan');
            }

            $members = $certification->selfTeamMembers;

            $approvedMembers = array_filter($members, fn ($m) => $m->status === ApprovalStatus::APPROVED);
            $approvedCount = count($approvedMembers);
            $leaderCount = count(array_filter($approvedMembers, fn ($m) => $m->role === TeamRole::LEADER));
            $memberCount = count(array_filter($approvedMembers, fn ($m) => $m->role === TeamRole::MEMBER));

            if ($approvedCount === 0 || $approvedCount % 3 !== 0) {
                throw new UnprocessableEntityHttpException('Jumlah anggota Tim Mandiri yang setuju bergabung harus kelipatan 3');
            }
            if ($leaderCount !== 1 || $memberCount < 2) {
                throw new UnprocessableEntityHttpException('Tim Mandiri harus terdiri dari 1 ketua dan minimal 2 anggota lainnya');
            }

            // Jika komposisi sudah terpenuhi namun masih ada yang pending, maka otomatis menjadi rejected
            SelfTeamMember::updateAll(
                ['status' => ApprovalStatus::REJECTED],
                ['certification_id' => $certification->id, 'status' => ApprovalStatus::PENDING]
            );

            $certification->submitForSelfReview()->save(false);

            Yii::$app->session->setFlash('success', 'Sertifikasi berhasil diajukan');
            return $this->redirect(['pengajuan-sertifikasi']);
        } catch (Exception $error) {
            if (
                $error instanceof NotFoundHttpException ||
                $error instanceof ConflictHttpException ||
                $error instanceof UnprocessableEntityHttpException
            ) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                return $this->redirect(['pengajuan-sertifikasi']);
            }
            throw $error;
        }
    }
}
