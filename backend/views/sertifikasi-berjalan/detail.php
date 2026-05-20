<?php

use common\models\SelfTeamMember;
use common\enums\CertificateLevel;
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

$this->title = 'Detail Sertifikasi Berjalan';

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
  'Status',
];
$certIndex = [
  'level',
  'code',
  'submitted_at',
  'status',
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
    <a href="<?= Url::to(['index']) ?>" class=" text-decoration-none text-black fs-5 me-3">
      <i class="fa-solid fa-arrow-left"></i>
    </a>
    <p class="fw-bold mb-0 h3"><?= Html::encode($this->title) ?></p>
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
        <div class="px-3 text-center">
          <p class=" fw-bold h5">Informasi Sertifikasi</p>
          <?php foreach ($certIndex as $key => $dat) : ?>
            <?php
            $dataValue = $cert[$dat] ?? '-';
            if ($dat === 'level') {
              $dataValue = CertificateLevel::list()[$dataValue] ?? $dataValue;
            } elseif ($dat === 'status') {
              $dataValue = CertificationStatus::list()[$dataValue] ?? $dataValue;
            }
            ?>
            <?php echo $this->render('/component/_idline', [
              'label' => $certLabel[$key],
              'data' => $dataValue,
              'shingles' => ''
            ]); ?>
          <?php endforeach ?>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-6">
      <?= $this->render('/component/_team_table', [
        "model" => $self_team,
        "is_self" => true
      ]) ?>
    </div>
    <div class="col-sm-6">
      <?= $this->render('/component/_team_table', [
        "model" => $peer_team,
        "is_self" => false
      ]) ?>
    </div>
  </div>
</div>
