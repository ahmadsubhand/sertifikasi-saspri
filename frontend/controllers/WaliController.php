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
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\UnprocessableEntityHttpException;

class WaliController extends Controller
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
    $saspri_k = SaspriK::findOne(['coordinator_id' => Yii::$app->user->id]);
    if (!$saspri_k) {
      $message = 'Halaman ini hanya boleh diakses oleh Wali SASPRI-K';
      Yii::$app->session->setFlash('error', $message);
      throw new ForbiddenHttpException($message);
    }
    return $saspri_k;
  }

  private function getOnGoingCertification(): Certification | null
  {
    return Certification::find()
      ->where(['saspri_k_id' => $this->findSaspriK()->id]) // sertifikasi milik saspri-k saat ini
      ->andWhere(['!=', 'status', CertificationStatus::COMPLETED]) // yang belum selesai
      ->one();
  }

  private function requestNewCertification(): Certification
  {
    try {
      // tambahkan pengecekan apakah sudah masuk tanggal next_certification_due_date dari valid_certificate_id
      $certification = new Certification();
      $certification->setNewCertificationRequest($this->findSaspriK());
      return $certification;
    } catch (Exception $error) {
      $message = explode('|', $error->getMessage());
      if ($message[0] === 'not time yet') {
        throw new UnprocessableEntityHttpException('Sertifikasi baru bisa dilakukan setelah tanggal ' . $message[1]);
      }
      throw $error;
    }
  }

  public function actionIndex()
  {
    $saspri_k = $this->findSaspriK();

    return $this->render('index', [
      'saspri_k' => $saspri_k,
      'valid_certificate' => $saspri_k->validCertificate,
      'certifications' => $saspri_k->certifications,
      'users' => User::find()
        ->where(['saspri_k_id' => $saspri_k->id]) // anggota saspri-k saat ini
        ->andWhere(['!=', 'id', Yii::$app->user->id]) // selain wali saspri-k
        ->all(),
      'district' => $saspri_k->district,
    ]);
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
    $userIds = Yii::$app->request->post('user_ids');
    if (!empty($userIds)) {
      $parsed_user_ids = array_unique(array_filter(array_map('trim', explode(',', $userIds))));

      $valid_users = User::find()
        ->where(['id' => $parsed_user_ids]) // anggota yang ditambahkan
        ->andWhere(['saspri_k_id' => null]) // belum terdaftar di saspri-k manapun
        ->select('username')
        ->column();

      if (count($valid_users) !== count($parsed_user_ids)) {
        Yii::$app->session->setFlash('error', 'Beberapa user tidak valid atau sudah terdaftar di SASPRI-K lain');
        return $this->redirect(['index']);
      }

      $saspri_k = $this->findSaspriK();
      User::updateAll(
        ['saspri_k_id' => $saspri_k->id],
        ['id' => $parsed_user_ids]
      );

      Yii::$app->session->setFlash('success', implode(', ', $valid_users) .' berhasil ditambahkan ke SASPRI-K ' . $saspri_k->district->name);
    }
    return $this->redirect(['index']);
  }

  public function actionHapusAnggota(int $id)
  {
    $saspri_k = $this->findSaspriK();
    $district = $saspri_k->district;
    
    // Pastikan user yang ingin dikeluarkan memang anggota dari saspri-k ini
    $user = User::findOne(['id' => $id, 'saspri_k_id' => $saspri_k->id]);

    if (!$user) {
      Yii::$app->session->setFlash('error', 'User tidak ditemukan dalam SASPRI-K' . $district->name);
      return $this->redirect(['index']);
    }

    $user->saspri_k_id = null;
    $user->save(false);

    Yii::$app->session->setFlash('success', $user->username . ' berhasil dikeluarkan dari SASPRI-K ' . $district->name);
    return $this->redirect(['index']);
  }

  public function actionPengajuanSertifikasi()
  {
    try {
      $certification = $this->getOnGoingCertification() ?: $this->requestNewCertification();

      $self_team_members = SelfTeamMember::find()
        ->with('user')
        ->where(['certification_id' => $certification->id])
        ->all();

      return $this->render('pengajuanSertifikasi', [
        'certification' => $certification,
        'self_team_members' => $self_team_members,
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

    $saspri_k = $this->findSaspriK();

    $certification = $this->getOnGoingCertification();
    
    $existingMemberIds = $certification 
      ? SelfTeamMember::find()
        ->select('user_id')
        ->where(['certification_id' => $certification->id])
        ->column()
      : [];

    $users = User::find()
      ->select(['id', 'username'])
      ->where(['saspri_k_id' => $saspri_k->id]) // anggota saspri-k saat ini
      ->andWhere(['!=', 'id', $saspri_k->coordinator_id]) // bukan wali saspri-k
      ->andWhere(['not in', 'id', $existingMemberIds]) // belum tergabung dalam tim mandiri ini
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
        $saspri_k = $this->findSaspriK();

        $certification = $this->getOnGoingCertification() ?: $this->requestNewCertification();

        $parsed_user_ids = array_unique(array_filter(array_map('trim', explode(',', $userIds))));

        $existingMemberIds = SelfTeamMember::find()
          ->select('user_id')
          ->where(['certification_id' => $certification->id])
          ->column();

        $valid_users = User::find()
          ->where(['id' => $parsed_user_ids]) // anggota yang ditambahkan
          ->andWhere(['saspri_k_id' => $saspri_k->id]) // merupakan anggota saspri-k saat ini
          ->andWhere(['!=', 'id', $saspri_k->coordinator_id]) // bukan wali saspri-k
          ->andWhere(['not in', 'id', $existingMemberIds]) // belum tergabung dalam tim mandiri ini
          ->select('username')
          ->column();

        if (count($valid_users) !== count($parsed_user_ids)) {
          Yii::$app->session->setFlash('error', 'Beberapa user tidak valid atau sudah terdaftar di Tim Mandiri saat ini');
          return $this->redirect(['index']);
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
      if ($error instanceof UnprocessableEntityHttpException) {
        Yii::$app->session->setFlash('error', $error->getMessage());
        return $this->redirect(['index']);
      }
      throw $error;
    }
  }

  public function actionHapusAnggotaTimMandiri(int $id)
  {
    $certification = $this->getOnGoingCertification();

    /** @var SelfTeamMember|null $member */
    $member = SelfTeamMember::find()
      ->with('user')
      ->where(['id' => $id, 'certification_id' => $certification->id])
      ->one();

    if (!$member) {
      Yii::$app->session->setFlash('error', 'User tidak ditemukan atau bukan anggota Tim Mandiri');
      return $this->redirect(['index']);
    }
    $member->delete();

    Yii::$app->session->setFlash('success', $member->user->username . ' berhasil dikeluarkan dari Tim Mandiri');
    return $this->redirect(['pengajuan-sertifikasi']);
  }

  public function actionUbahPeranAnggotaTimMandiri(int $id)
  {
    $certification = $this->getOnGoingCertification();
    $role = Yii::$app->request->post('role');

    if (!in_array($role, TeamRole::values())) {
      Yii::$app->session->setFlash('error', 'Peran tidak valid');
      return $this->redirect(['pengajuan-sertifikasi']);
    }

    /** @var SelfTeamMember|null $member */
    $member = SelfTeamMember::find()
      ->with('user')
      ->where(['id' => $id, 'certification_id' => $certification->id])
      ->one();

    if (!$member) {
      Yii::$app->session->setFlash('error', 'User tidak ditemukan atau bukan anggota Tim Mandiri');
      return $this->redirect(['index']);
    }

    $member->role = $role;
    $member->save(false);

    Yii::$app->session->setFlash('success', 'Peran ' . $member->user->username . ' berhasil diubah menjadi ' . TeamRole::list()[$role]);
    return $this->redirect(['pengajuan-sertifikasi']);
  }

  public function actionAjukanSertifikasi()
  {
    $certification = $this->getOnGoingCertification();
    if (!$certification) {
      Yii::$app->session->setFlash('error', 'Tidak ada sertifikasi yang sedang berlangsung');
      return $this->redirect(['index']);
    }

    if ($certification->status !== CertificationStatus::PENDING_SELF_TEAM_FORMATION) {
      Yii::$app->session->setFlash('error', 'Sertifikasi sudah pernah diajukan');
      return $this->redirect(['pengajuan-sertifikasi']);
    }

    $members = SelfTeamMember::find()
      ->where(['certification_id' => $certification->id])
      ->all();

    $approvedMembers = array_filter($members, fn($m) => $m->status === ApprovalStatus::APPROVED);
    $approvedCount = count($approvedMembers);
    $leaderCount = count(array_filter($approvedMembers, fn($m) => $m->role === TeamRole::LEADER));
    $memberCount = count(array_filter($approvedMembers, fn($m) => $m->role === TeamRole::MEMBER));

    if ($approvedCount === 0 || $approvedCount % 3 !== 0) {
      Yii::$app->session->setFlash('error', 'Jumlah anggota Tim Mandiri yang setuju bergabung harus kelipatan 3');
      return $this->redirect(['pengajuan-sertifikasi']);
    }

    if ($leaderCount !== 1 || $memberCount < 2) {
      Yii::$app->session->setFlash('error', 'Tim Mandiri harus terdiri dari 1 ketua dan minimal 2 anggota lainnya');
      return $this->redirect(['pengajuan-sertifikasi']);
    }

    // Jika komposisi sudah terpenuhi namun masih ada yang pending, maka otomatis menjadi rejected
    SelfTeamMember::updateAll(
      ['status' => ApprovalStatus::REJECTED],
      ['certification_id' => $certification->id, 'status' => ApprovalStatus::PENDING]
    );

    $certification->submitForSelfReview();

    Yii::$app->session->setFlash('success', 'Sertifikasi berhasil diajukan');

    return $this->redirect(['pengajuan-sertifikasi']);
  }
}