<?php

use common\models\SelfTeamMember;
use common\enums\CertificateLevel;
use common\enums\CertificationPurpose;
use common\enums\CertificationStatus;
use common\models\PeerTeamMember;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var int $id
 *  @var common\models\SaspriK $saspri
 *  @var common\models\Certification $cert
 * @var SelfTeamMember[] $self_team
 * @var PeerTeamMember[] $peer_team
 */

$member_id = $self_team[array_search(Yii::$app->user->id, array_column($self_team, 'user_id'))]->id;
$label = [
  'SASPRI-K',
  'SASPRI-KK',
  'SASPRI-P',
  'Alamat Sekretariat',
  'Nama unit usaha (koperasi)',
  'Nama wali SASPRI',
  'Jumlah kelompok yang dibina',
  'Jumlah anggota aktif dalam kelompok yang dibina',
  'Ternak yang diusahakan',
  'Jumlah total ternak milik anggota aktif',
  'Jumlah ternak indukan (pernah beranak)',
  'Jumlah ternak dara produktif (siap dikawinkan)',
];
$index = [
  'district_id',
  'district_id',
  'district_id',
  'address',
  'cooperative_name',
  'coordinator_id',
  'number_of_groups',
  'number_of_active_members',
  'livestock_type',
  'total_livestock_count',
  'breeding_livestock_count',
  'productive_heifer_count',

];
$certLabel = [
  'Level Sertifikat',
  'No. Sertifikat',
  'Tanggal Pengajuan',
  'Tanggal Penerbitan',
  'Predikat',
];
$certIndex = [
  'level',
  'code',
  'created_at',
  'issued_at',
  'grade',
];
$shingles = [
  'number_of_active_members' => 'Orang',
  'total_livestock_count' => 'Ekor',
  'breeding_livestock_count' => 'Ekor',
  'productive_heifer_count' => 'Ekor',
];

?>

<div class="page-cont w-100 h-100 p-3 d-flex flex-column gap-3">
  <div class="d-flex align-items-center text-center">
    <a href="<?= Url::to(['/tim-mandiri']) ?>" class=" text-decoration-none text-black fs-5 me-3">
      <i class="fa-solid fa-arrow-left"></i>
    </a>
    <?php if (str_contains(strtolower($cert['status']), 'pending')) : ?>
      <p class="fw-bold mb-0 h3">Permintaan Partisipasi Tim Mandiri</p>
    <?php else: ?>
      <p class="fw-bold mb-0 h3">Detail Sertifikasi</p>
    <?php endif ?>
  </div>
  <div class="row">
    <div class="col-sm-8">
      <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
        <div class=" px-4">
          <p class=" fw-bold">Identitas </p>
          <?php foreach ($index as $key => $dat) : ?>
            <?php echo $this->render('/component/_idline', [
              'label' => $label[$key],
              'data' => $saspri[$dat],
              'shingles' => $shingles[$dat] ?? ''
            ]); ?>
          <?php endforeach ?>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
        <?php if ($cert->status != CertificationStatus::COMPLETED) : ?>
          <div class="px-3 text-center">
            <p class=" fw-bold h5">
              Sertifikasi
              <?=
              ($cert['purpose'] === CertificationPurpose::LEVEL_UP ? CertificateLevel::list()[$cert['level']] : CertificateLevel::prev()[$cert['level']]) .
                ' ke ' .
                CertificateLevel::list()[$cert['level']]
              ?>
            </p>
            <br>
            <p class="h6 mb-2">Proses <?= (string)CertificationStatus::list()[$cert['status']] ?></p>
            <?php if ($cert->status === CertificationStatus::SELF_REVIEW) : ?>
              <p class="h6">Sebelum tanggal <?= $this->render('/component/_date_comparator', [
                                              'cert' => $cert
                                            ]) ?>
              </p>
            <?php endif ?>
          </div>
          <?php if (in_array(Yii::$app->user->id, array_column($self_team, 'user_id')) && $cert->status == CertificationStatus::PENDING_SELF_TEAM_FORMATION) : ?>
            <div class="px-3">
              <?= Html::a('Setuju', ['setuju', 'self_team_member_id' => $member_id], [
                'class' => 'btn s-btn-green me-2 w-100 mt-3',
                'data-method' => 'post',
              ]) ?>
              <?= Html::a('Tolak', ['tolak', 'self_team_member_id' => $member_id], [
                'class' => 'btn s-btn-red me-2 w-100 mt-3',
                'data-method' => 'post',
                'data-confirm' => 'Apakah Anda yakin ingin menolak permintaan bergabung Tim Mandiri ini?',
              ]) ?>
            </div>
          <?php endif ?>
          <?php if (in_array(Yii::$app->user->id, array_column($self_team, 'user_id')) && $cert->status == CertificationStatus::SELF_REVIEW) : ?>
            <div>
              <?= Html::a('Mulai Self Review', ['/tim-mandiri/self-review', 'certification_id' => $cert['id']], [
                'class' => 'btn s-btn-main me-2 w-100 mt-3',
              ]) ?>
            </div>
          <?php endif ?>
        <?php else : ?>
          <div class="px-3 text-center">
            <p class=" fw-bold h5">Sertifikat</p>
            <?php foreach ($certIndex as $key => $dat) : ?>
              <?php echo $this->render('/component/_idline', [
                'label' => $certLabel[$key],
                'data' => $cert[$dat] ?? '-',
                'shingles' => ''
              ]); ?>
            <?php endforeach ?>
            <a href="<?= Url::to(['#', 'certificate_id' => $cert['id']]) ?>" class=" btn s-btn-main me-2 w-100 mt-3">Unduh Sertifikat <i class="fa-solid fa-download"></i></a>
          </div>
        <?php endif ?>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-6">
      <?= $this->render('/component/_team_table', [
        "model" => $self_team,
        'is_self' => 1
      ]) ?>
    </div>
    <div class="col-sm-6">
      <?= $this->render('/component/_team_table', [
        "model" => $peer_team,
        'is_self' => 0
      ]) ?>
    </div>
  </div>
</div>