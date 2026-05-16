<?php

namespace backend\controllers;

use common\enums\CertificationStatus;
use common\enums\TeamRole;
use common\enums\UserRole;
use common\helpers\UserHelper;
use common\models\Certification;
use common\models\PeerTeamMember;
use common\models\User;
use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
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
            ],
          ],
        ];
    }

    public function actionIndex()
    {
        $certifications = Certification::find()
          ->where(['status' => CertificationStatus::PENDING_PEER_TEAM_FORMATION])
          ->with(['saspriK'])
          ->all();
        return $this->render('index', [
          'certifications' => $certifications,
        ]);
    }

    public function actionPembentukanTimSebaya(int $certification_id)
    {
        try {
            $certification = $this->findCertificationOrFail($certification_id);
            return $this->render('pembentukanTimSebaya', [
              'saspri_k' => $certification->saspriK,
              'district' => $certification->saspriK->district,
              'certification' => $certification,
              'valid_certificate' => $certification->saspriK->validCertificate,
              'peer_team_members' => $certification
                ->getPeerTeamMembers()
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

            $certification = $this->findCertificationOrFail($certification_id);
            $users = $this->getAvailablePeerTeamMembersQuery($certification)
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
            $user_ids = Yii::$app->request->post('user_ids');
            if (!empty($user_ids)) {
                $certification = $this->findCertificationOrFail($certification_id);

                $array_user_ids = UserHelper::convertUserIdsToArray($user_ids);
                $valid_users = $this->getAvailablePeerTeamMembersQuery($certification)
                  ->andWhere(['id' => $array_user_ids])
                  ->select('username')
                  ->column();

                if (count($valid_users) !== count($array_user_ids)) {
                    throw new BadRequestHttpException('Beberapa user tidak valid atau sudah terdaftar di Tim Sebaya saat ini');
                }

                $certification->addPeerTeamMembers($array_user_ids);

                Yii::$app->session->setFlash('success', implode(', ', $valid_users) . ' berhasil ditambahkan ke Tim Sebaya');
                return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
            }
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                } elseif ($error instanceof BadRequestHttpException) {
                    return $this->redirect(['pembentukan-tim-sebaya', 'certification_id' => $certification_id]);
                }
            }
            throw $error;
        }
    }

    public function actionHapusAnggotaTimSebaya(int $user_id, int $certification_id)
    {
        try {
            $certification = $this->findCertificationOrFail($certification_id);
            $member = $this->findAMemberOfPeerTeam($user_id, $certification);
            $member->delete();

            Yii::$app->session->setFlash('success', $member->user->username . ' berhasil dikeluarkan dari Tim Sebaya');
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
            $certification = $this->findCertificationOrFail($certification_id);

            $role = Yii::$app->request->post('role');
            $member = $this->findAMemberOfPeerTeam($user_id, $certification);
            $member->changeRole($role)->save(false);

            Yii::$app->session->setFlash('success', 'Peran ' . $member->user->username . ' berhasil diubah menjadi ' . TeamRole::list()[$role]);
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
            $certification = $this->findCertificationOrFail($certification_id);
            $certification->validateApprovedPeerTeamComposition();
            $certification->submitForPeerReview()->save(false);

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

    private function findCertificationOrFail(int $id): Certification
    {
        $certification = Certification::find()->andWhere(['id' => $id])->one();
        if (!$certification) {
            throw new NotFoundHttpException('Sertifikasi tidak ditemukan');
        }
        if ($certification->status !== CertificationStatus::PENDING_PEER_TEAM_FORMATION) {
            throw new UnprocessableEntityHttpException('Sertifikasi sedang tidak dalam tahap pembentukan Tim Sebaya');
        }
        return $certification;
    }

    private function getAvailablePeerTeamMembersQuery(Certification $certification)
    {
        $existing_member_ids = $certification
          ->getPeerTeamMembers()
          ->select('user_id')
          ->column();

        return User::find()
          ->leftJoin('auth_assignment aa', 'aa.user_id = id')
          ->andWhere(['not in', 'id', $existing_member_ids])
          ->andWhere([
            'or',
            ['aa.item_name' => UserRole::ADMIN],
            [
              'and',
              ['not', ['saspri_k_id' => null]],
              ['!=', 'saspri_k_id', $certification->saspri_k_id]
            ]
          ]);
    }

    private function findAMemberOfPeerTeam(int $user_id, Certification $certification): PeerTeamMember
    {
        $member = $certification->getPeerTeamMembers()
          ->where(['user_id' => $user_id])
          ->one();
        if (!$member) {
            throw new NotFoundHttpException('User tidak ditemukan atau bukan anggota Tim Sebaya');
        }
        return $member;
    }
}
