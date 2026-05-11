<?php

namespace backend\controllers;
use common\enums\ApprovalStatus;
use common\enums\CertificationStatus;
use common\enums\TeamRole;
use common\enums\UserRole;
use common\models\Certification;
use common\models\PeerTeamMember;
use common\models\User;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnprocessableEntityHttpException;

class SertifikasiController extends Controller
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
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $certifications = Certification::find()
            ->where(['status' => CertificationStatus::PENDING_PEER_TEAM_FORMATION])
            ->with(['saspriK.district'])
            ->all();
        return $this->render('index', [
            'certifications' => $certifications,
        ]);
    }

    public function actionPembentukanTimSebaya(int $certification_id)
    {
        try {
            $certification = $this->findAndCheckCertification($certification_id);
            return $this->render('pembentukanTimSebaya', [
                'certification' => $certification,
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                return $this->redirect(['index']);
            }
            throw $error;
        }
    }

    public function actionCariAnggotaTimSebaya(int $certification_id, string $q)
    {
        try {
            Yii::$app->response->format = Response::FORMAT_JSON;
            [ 
                'certification' => $certification, 
                'existing_member_ids' => $existing_member_ids,
            ] = $this->getCertificationAndExistingMemberIds($certification_id);
    
            return User::find()
                ->alias('u')
                ->select(['u.id', 'u.username'])
                ->leftJoin('auth_assignment aa', 'aa.user_id = u.id')
                ->where(['like', 'u.username', $q])
                ->andWhere(['not in', 'id', $existing_member_ids])
                ->andWhere([
                    'or',
                    ['aa.item_name' => UserRole::ADMIN],
                    [
                        'and',
                        ['not', ['u.saspri_k_id' => null]],
                        ['!=', 'u.saspri_k_id', $certification->saspri_k_id]
                    ]
                ])
                ->limit(10)
                ->asArray()
                ->all();
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                return $this->redirect(['index']);
            }
            throw $error;
        }
    }

    public function actionTambahAnggotaTimSebaya(int $certification_id)
    {
        try {
            $user_ids = Yii::$app->request->post('user_ids');
            if (!empty($user_ids)) {
                [ 
                    'certification' => $certification, 
                    'existing_member_ids' => $existing_member_ids,
                ] = $this->getCertificationAndExistingMemberIds($certification_id);

                $parsed_user_ids = array_unique(array_filter(array_map('trim', explode(',', $user_ids))));
                $valid_users = User::find()
                    ->alias('u')
                    ->leftJoin('auth_assignment aa', 'aa.user_id = u.id')
                    ->andWhere(['id' => $parsed_user_ids])
                    ->andWhere(['not in', 'id', $existing_member_ids])
                    ->andWhere([
                        'or',
                        ['aa.item_name' => UserRole::ADMIN],
                        [
                            'and',
                            ['not', ['u.saspri_k_id' => null]],
                            ['!=', 'u.saspri_k_id', $certification->saspri_k_id]
                        ]
                    ])
                    ->select('username')
                    ->column();
    
                if (count($valid_users) !== count($parsed_user_ids)) {
                    throw new BadRequestHttpException('Beberapa user tidak valid atau sudah terdaftar di Tim Sebaya saat ini');
                }
    
                foreach ($parsed_user_ids as $user_id) {
                    $member = new PeerTeamMember();
                    $member->certification_id = $certification->id;
                    $member->user_id = $user_id;
                    $member->status = ApprovalStatus::PENDING;
                    
                    // If member has admin role, set to facilitator
                    if ($this->isUserAnAdmin($user_id)) {
                        $member->role = TeamRole::FACILITATOR;
                    } else {
                        $member->role = TeamRole::MEMBER;
                    }
        
                    $member->save(false);
                }
        
                Yii::$app->session->setFlash('success', implode(', ', $valid_users) .' berhasil ditambahkan ke Tim Sebaya');
                return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
            }
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof NotFoundHttpException) {
                    return $this->redirect(['index']);
                } else {
                    return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
                }
            }
            throw $error;
        }
    }

    public function actionHapusAnggotaTimSebaya(int $user_id, int $certification_id)
    {
        try {
            $this->findAndCheckCertification($certification_id);
            $member = $this->findAMemberOfPeerTeam($user_id, $certification_id);
            $member->delete();
    
            Yii::$app->session->setFlash('success', $member->user->username . ' berhasil dikeluarkan dari Tim Sebaya');
            return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof NotFoundHttpException) {
                    if (str_contains($error->getMessage(), 'anggota')) {
                        return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
                    }
                    return $this->redirect(['index']);
                } else {
                    return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
                }
            }
            throw $error;
        }
    }

    public function actionUbahPeranAnggotaTimSebaya(int $user_id, int $certification_id)
    {
        try {
            $role = Yii::$app->request->post('role');
            if (!in_array($role, TeamRole::values())) {
                throw new BadRequestHttpException('Peran tidak valid');
            }
    
            $this->findAndCheckCertification($certification_id);
            $member = $this->findAMemberOfPeerTeam($user_id, $certification_id);
            if ($this->isUserAnAdmin($user_id) && $role !== TeamRole::FACILITATOR) {
                throw new BadRequestHttpException('Admin hanya boleh menjadi fasilitator dalam Tim Sebaya');
            } else if ($role === TeamRole::FACILITATOR) {
                throw new BadRequestHttpException('Hanya Admin yang boleh menjadi fasilitator dalam Tim Sebaya');
            }
            $member->status = ApprovalStatus::PENDING;
            $member->role = $role;
            $member->save(false);

            Yii::$app->session->setFlash('success', 'Peran ' . $member->user->username . ' berhasil diubah menjadi ' . TeamRole::list()[$role]);
            return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof NotFoundHttpException) {
                    if (str_contains($error->getMessage(), 'anggota')) {
                        return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
                    }
                    return $this->redirect(['index']);
                } else {
                    return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
                }
            }
            throw $error;
        }
    }

    public function actionAjukanPeerReview(int $certification_id)
    {
        try {
            $certification = $this->findAndCheckCertification($certification_id);
            /** @var PeerTeamMember[] $members */
            $members = $certification->getPeerTeamMembers()->where(['status' => ApprovalStatus::APPROVED])->all();
    
            // Validasi komposisi tim
            $facilitatorCount = 0;
            $leaderCount = 0;
            $memberCount = 0;
            $saspriKIds = [];
    
            foreach ($members as $member) {
                if ($member->role === TeamRole::FACILITATOR) {
                    $facilitatorCount++;
                } else {
                    $saspriKIds[] = $member->user->saspri_k_id;
                    if ($member->role === TeamRole::LEADER) {
                        $leaderCount++;
                    } else if ($member->role === TeamRole::MEMBER) {
                        $memberCount++;
                    }
                }
            }
    
            if ($facilitatorCount !== 1 || $leaderCount !== 1 || $memberCount < 1) {
                throw new UnprocessableEntityHttpException(
                    'Anggota yang menyetujui bergabung di Tim sebaya harus terdiri dari minimal 2 orang ' . 
                    '(salah sartu bertindak sebagai ketua) dari SASPRI-K lainnya dan 1 pendamping dari SASPRI-N'
                );
            }
            // Validasi tidak dari SASPRI-K yang sama
            if (count(array_unique($saspriKIds)) !== count($saspriKIds)) {
                throw new UnprocessableEntityHttpException(
                    'Masing-masing anggota harus dari SASPRI-K yang berbeda satu sama lain'
                );
            }
    
            PeerTeamMember::updateAll(
                ['status' => ApprovalStatus::REJECTED],
                ['certification_id' => $certification_id, 'status' => ApprovalStatus::PENDING]
            );
    
            $certification->submitForPeerReview()->save(false);
    
            Yii::$app->session->setFlash('success', 'Sertifikasi berhasil dilanjutkan ke tahap peer review');
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof NotFoundHttpException) {
                    return $this->redirect(['index']);
                } else {
                    return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
                }
            }
            throw $error;
        }
    }

    private function findAndCheckCertification(int $id)
    {
        $certification = Certification::findOne($id);
        if (!$certification) {
            throw new NotFoundHttpException('Sertifikasi tidak ditemukan');
        }
        if ($certification->status !== CertificationStatus::PENDING_PEER_TEAM_FORMATION) {
            throw new NotFoundHttpException('Sertifikasi sedang tidak dalam tahap pembentukan Tim Sebaya');
        }
        return $certification;
    }

    private function getCertificationAndExistingMemberIds(int $certification_id) {
        $certification = $this->findAndCheckCertification($certification_id);
        $existing_member_ids = $certification
            ->getPeerTeamMembers()
            ->select('user_id')
            ->column();
        return [
            'certification' => $certification,
            'existing_member_ids' => $existing_member_ids,
        ];
    }

    private function findAMemberOfPeerTeam(int $user_id, int $certification_id): PeerTeamMember
    {
        $member = PeerTeamMember::find()
            ->with('user')
            ->where(['user_id' => $user_id, 'certification_id' => $certification_id])
            ->one();
        if (!$member) {
            throw new NotFoundHttpException('User tidak ditemukan atau bukan anggota Tim Sebaya');
        }
        return $member;
    }

    private function isUserAnAdmin(int $user_id): bool
    {
        if (Yii::$app->authManager->getAssignment(UserRole::ADMIN, $user_id)) {
            return true;
        } else {
            return false;
        }
    }
}
