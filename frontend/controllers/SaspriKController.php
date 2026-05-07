<?php

namespace frontend\controllers;

use common\enums\ApprovalStatus;
use common\enums\CertificationStatus;
use common\enums\TeamRole;
use common\enums\UserRole;
use common\models\Certification;
use common\models\SaspriK;
use common\models\SelfTeamMember;
use common\models\User;
use Exception;
use yii\web\Controller;
use Yii;
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

    private function findSaspriK(): SaspriK
    {
        $saspri_k = User::findOne(['id' => Yii::$app->user->id])->getSaspriKAsCoordinator()->one();
        if (!$saspri_k) {
            throw new ForbiddenHttpException('Halaman ini hanya boleh diakses oleh Koordinator SASPRI-K');
        }
        return $saspri_k;
    }

    private function requestNewCertification(): Certification
    {
        try {
            return $this->findSaspriK()->createNewCertificationRequest();
        } catch (Exception $error) {
            if ($error instanceof UnprocessableEntityHttpException) {
                throw new UnprocessableEntityHttpException($error->getMessage());
            }
            throw $error;
        }
    }

    private function findAMemberOfSelfTeam(int $user_id, int $certification_id): SelfTeamMember
    {
        $member = SelfTeamMember::find()
            ->with('user')
            ->where(['id' => $user_id, 'certification_id' => $certification_id])
            ->one();
        if (!$member)
            throw new NotFoundHttpException('User tidak ditemukan atau bukan anggota Tim Mandiri');
        return $member;
    }

    public function actionIndex()
    {
        try {
            $saspri_k = $this->findSaspriK();

            return $this->render('index', [
              'saspri_k' => $saspri_k,
              'valid_certificate' => $saspri_k->validCertificate,
              'certifications' => $saspri_k->certifications,
              'users' => User::find()
                ->where(['saspri_k_id' => $saspri_k->id]) // anggota saspri-k saat ini
                ->andWhere(['!=', 'id', Yii::$app->user->id]) // selain koordinator SASPRI-K
                ->all(),
              'district' => $saspri_k->district,
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                }
                return $this->goHome();
            }
            throw $error;
        }
    }

    public function actionCariUser(string $q)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $users = User::find()
          ->select(['id', 'username'])
          ->where(['saspri_k_id' => null]) // yang belum terdaftar di saspri-k manapun
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
                  ->where(['id' => $parsed_user_ids]) // anggota yang ditambahkan
                  ->andWhere(['saspri_k_id' => null]) // belum terdaftar di saspri-k manapun
                  ->select('username')
                  ->column();

                if (count($valid_users) !== count($parsed_user_ids)) {
                    throw new BadRequestHttpException('Beberapa user tidak valid atau sudah terdaftar di SASPRI-K lain');
                }

                $saspri_k = $this->findSaspriK();
                User::updateAll(
                    ['saspri_k_id' => $saspri_k->id],
                    ['id' => $parsed_user_ids]
                );

                Yii::$app->session->setFlash('success', implode(', ', $valid_users) .' berhasil ditambahkan ke SASPRI-K ' . $saspri_k->district->name);
                return $this->redirect(['index']);
            }
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());

                if ($error instanceof BadRequestHttpException) {
                    return $this->redirect(['index']);
                } elseif ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                }

                return $this->goHome();
            }
            throw $error;
        }
    }

    public function actionHapusAnggota(int $id)
    {
        try {
            $saspri_k = $this->findSaspriK();
            $district = $saspri_k->district;

            // Pastikan user yang ingin dikeluarkan memang anggota dari saspri-k ini
            $user = User::findOne(['id' => $id, 'saspri_k_id' => $saspri_k->id]);

            if (!$user) {
                throw new NotFoundHttpException('User tidak ditemukan dalam SASPRI-K' . $district->name);
            }

            $user->saspri_k_id = null;
            $user->save(false);

            Yii::$app->session->setFlash('success', $user->username . ' berhasil dikeluarkan dari SASPRI-K ' . $district->name);
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());

                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                } elseif ($error instanceof NotFoundHttpException) {
                    return $this->redirect(['index']);
                }

                return $this->goHome();
            }
            throw $error;
        }
    }

    public function actionPengajuanSertifikasi()
    {
        try {
            $certification = $this->findSaspriK()->getOnGoingCertification() ?: $this->requestNewCertification();

            $self_team_members = SelfTeamMember::find()
              ->with('user')
              ->where(['certification_id' => $certification->id])
              ->all();

            return $this->render('pengajuanSertifikasi', [
              'certification' => $certification,
              'self_team_members' => $self_team_members,
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());

                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                } elseif ($error instanceof UnprocessableEntityHttpException) {
                    return $this->redirect(['index']);
                }

                return $this->goHome();
            }
            throw $error;
        }
    }

    public function actionCariAnggotaTimMandiri(string $q)
    {
        try {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $saspri_k = $this->findSaspriK();

            $certification = $saspri_k->getOnGoingCertification();

            $existingMemberIds = $certification
              ? SelfTeamMember::find()
                ->select('user_id')
                ->where(['certification_id' => $certification->id])
                ->column()
              : [];

            $users = User::find()
              ->select(['id', 'username'])
              ->where(['saspri_k_id' => $saspri_k->id]) // anggota saspri-k saat ini
              ->andWhere(['!=', 'id', $saspri_k->coordinator_id]) // bukan koordinator SASPRI-K
              ->andWhere(['not in', 'id', $existingMemberIds]) // belum tergabung dalam tim mandiri ini
              ->andWhere(['like', 'username', $q])
              ->limit(10)
              ->asArray()
              ->all();

            return $users;
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                }
                return $this->goHome();
            }
            throw $error;
        }
    }

    public function actionTambahAnggotaTimMandiri()
    {
        try {
            $userIds = Yii::$app->request->post('user_ids');
            if (!empty($userIds)) {
                $saspri_k = $this->findSaspriK();

                $certification = $saspri_k->getOnGoingCertification() ?: $this->requestNewCertification();

                $parsed_user_ids = array_unique(array_filter(array_map('trim', explode(',', $userIds))));

                $existingMemberIds = SelfTeamMember::find()
                  ->select('user_id')
                  ->where(['certification_id' => $certification->id])
                  ->column();

                $valid_users = User::find()
                  ->where(['id' => $parsed_user_ids]) // anggota yang ditambahkan
                  ->andWhere(['saspri_k_id' => $saspri_k->id]) // merupakan anggota saspri-k saat ini
                  ->andWhere(['!=', 'id', $saspri_k->coordinator_id]) // bukan koordinator SASPRI-K
                  ->andWhere(['not in', 'id', $existingMemberIds]) // belum tergabung dalam tim mandiri ini
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

                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                } elseif ($error instanceof UnprocessableEntityHttpException) {
                    return $this->redirect(['index']);
                } elseif ($error instanceof BadRequestHttpException) {
                    return $this->redirect(['pengajuan-sertifikasi']);
                }

                return $this->goHome();
            }
            throw $error;
        }
    }

    public function actionHapusAnggotaTimMandiri(int $id)
    {
        try {
            $certification = $this->findSaspriK()->getOnGoingCertification();
            $member = $this->findAMemberOfSelfTeam($id, $certification->id);
            $member->delete();
            Yii::$app->session->setFlash('success', $member->user->username . ' berhasil dikeluarkan dari Tim Mandiri');
            return $this->redirect(['pengajuan-sertifikasi']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());

                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                } elseif ($error instanceof NotFoundHttpException) {
                    return $this->redirect(['pengajuan-sertifikasi']);
                }

                return $this->goHome();
            }
            throw $error;
        }
    }

    public function actionUbahPeranAnggotaTimMandiri(int $id)
    {
        try {
            $certification = $this->findSaspriK()->getOnGoingCertification();
            $role = Yii::$app->request->post('role');

            if (!in_array($role, TeamRole::values())) {
                throw new BadRequestHttpException('Peran tidak valid');
            }

            $member = $this->findAMemberOfSelfTeam($id, $certification->id);
            $member->role = $role;
            $member->save(false);

            Yii::$app->session->setFlash('success', 'Peran ' . $member->user->username . ' berhasil diubah menjadi ' . TeamRole::list()[$role]);
            return $this->redirect(['pengajuan-sertifikasi']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());

                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                } elseif (
                    $error instanceof BadRequestHttpException ||
                    $error instanceof NotFoundHttpException
                ) {
                    return $this->redirect(['pengajuan-sertifikasi']);
                }

                return $this->goHome();
            }
            throw $error;
        }
    }

    public function actionAjukanSertifikasi()
    {
        try {
            $certification = $this->findSaspriK()->getOnGoingCertification();
            if (!$certification) {
                throw new NotFoundHttpException('Tidak ada sertifikasi yang sedang berlangsung');
            }

            if ($certification->status !== CertificationStatus::PENDING_SELF_TEAM_FORMATION) {
                throw new ConflictHttpException('Sertifikasi sudah pernah diajukan');
            }

            $members = SelfTeamMember::find()
              ->where(['certification_id' => $certification->id])
              ->all();

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

            $certification->submitForSelfReview();

            Yii::$app->session->setFlash('success', 'Sertifikasi berhasil diajukan');

            return $this->redirect(['pengajuan-sertifikasi']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());

                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                } elseif (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof ConflictHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['pengajuan-sertifikasi']);
                }

                return $this->goHome();
            }
            throw $error;
        }
    }
}
