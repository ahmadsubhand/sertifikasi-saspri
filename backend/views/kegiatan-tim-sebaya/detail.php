<?php

use common\models\SelfTeamMember;
use common\enums\CertificateLevel;
use common\enums\CertificationPurpose;
use common\enums\CertificationStatus;
use common\enums\RequestResponse;
use common\enums\TeamRole;
use common\models\PeerTeamMember;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/** @var int $id
 * @var int $member_id
 * @var bool $has_responded
 *  @var common\models\SaspriK $saspri
 *  @var common\models\Certification $cert
 * @var SelfTeamMember[] $self_team
 * @var PeerTeamMember[] $peer_team
 */

$user_ids = ArrayHelper::getColumn($peer_team, 'user_id');
$this->title = (string)'Detail Sertifikasi SASPRI-K ' . $saspri->region_name;

$current_user_id = Yii::$app->user->id;
$curr_role = null;

foreach ($peer_team as $member) {
    if ($member->user_id == $current_user_id) {
        $curr_role = $member->role;
        break;
    }
}

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

<div class="page-cont w-100 h-100 p-md-3 d-flex flex-column gap-3">
  <div class="d-flex align-items-center text-center">
    <a href="<?= \yii\helpers\Url::to(['index']) ?>" class=" text-decoration-none text-black fs-5 me-3">
      <i class="fa-solid fa-arrow-left"></i>
    </a>
    <?php if ($cert['status'] === CertificationStatus::PENDING_PEER_TEAM_FORMATION) : ?>
      <p class="fw-bold mb-0 h3">Permintaan Partisipasi Tim Sebaya</p>
    <?php else: ?>
      <p class="fw-bold mb-0 h3">Detail Sertifikasi</p>
    <?php endif ?>
  </div>

  <div class="row">
    <div class="col-sm-8">
      <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
        <div class=" px-md-4 px-2">
          <p class=" fw-bold h5 mb-3 border-bottom pb-2">Identitas SASPRI-K</p>
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
      <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border mt-3 mt-md-0">
        <?php if ($cert['status'] != CertificationStatus::COMPLETED) : ?>
          <div class="px-md-3 px-1 text-center">
            <p class=" fw-bold h5">
              Sertifikasi
              <?=
              ($cert['purpose'] === CertificationPurpose::LEVEL_UP ? CertificateLevel::prev()[$cert['level']] : CertificateLevel::list()[$cert['level']]) .
                ' ke ' .
                CertificateLevel::list()[$cert['level']]
              ?>
            </p>
            <br>
            <p class="h6 mb-2">Proses <?= (string)CertificationStatus::list()[$cert['status']] ?></p>
            <?php if ($cert->status === CertificationStatus::PEER_REVIEW ) : ?>
            <p class="h6">Sebelum tanggal <?= $this->render('/component/_date_comparator', [
                                              'cert' => $cert
                                            ]); ?>
            </p>
            <?php endif ?>
          </div>
          <div class="px-md-3 px-1 text-center">
              <small>Anda diminta menjadi <strong><?= TeamRole::list()[$curr_role] ?></strong> tim Sebaya</small>
              <?php if ($has_responded) : ?>
                <div class="d-flex align-items-center gap-3 w-100 mb-2 mt-3">
                  <hr class="flex-grow-1 m-0 text-success-tight opacity-25">
                  <small class="text-center mx-auto mb-0 text-success">anda sudah merespon</small>
                  <hr class="flex-grow-1 m-0 text-success-tight opacity-25">
                </div>
              <?php endif ?>
            <?php if ($cert->status == CertificationStatus::PENDING_PEER_TEAM_FORMATION) : ?>
              <?= Html::a('Setuju', ['tanggapi-permintaan-bergabung', 'peer_team_member_id' => $member_id], [
                'class' => 'btn s-btn-green me-2 w-100 mt-3 ' . ($has_responded == true ? 'disabled' : ''),
                'data-method' => 'post',
                'data-params' => [
                  'action' => RequestResponse::APPROVE,
                ],
              ]) ?>
              <?= Html::a('Tolak', ['tanggapi-permintaan-bergabung', 'peer_team_member_id' => $member_id], [
                'class' => 'btn s-btn-red me-2 w-100 mt-3 ' . ($has_responded == true ? 'disabled' : ''),
                'data-method' => 'post',
                'data-params' => [
                  'action' => RequestResponse::REJECT,
                ],
                'data-confirm' => 'Apakah Anda yakin ingin menolak permintaan bergabung Tim Sebaya ini?',
              ]) ?>
            <?php endif ?>
            <?php if ($cert->status == CertificationStatus::PEER_REVIEW) : ?>
              <div>
                <?= Html::a('Mulai Peer Review', ['peer-review', 'certification_id' => $cert['id']], [
                  'class' => 'btn s-btn-main me-2 w-100 mt-3',
                ]) ?>
              </div>
            <?php endif ?>
          </div>
        <?php else : ?>
          <div class="px-md-3 px-1 text-center">
            <p class=" fw-bold h5">Sertifikat</p>
            <?php foreach ($certIndex as $key => $dat) : ?>
              <?php echo $this->render('/component/_idline', [
                'label' => $certLabel[$key],
                'data' => $cert[$dat] ?? '-',
                'shingles' => ''
              ]); ?>
            <?php endforeach ?>
          </div>
        <?php endif ?>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-6 mt-3 mt-md-0">
      <?= $this->render('/component/_team_table', [
        "model" => $self_team,
        'is_self' => 1
      ]) ?>
    </div>
    <div class="col-sm-6 mt-3 mt-md-0">
      <?= $this->render('/component/_team_table', [
        "model" => $peer_team,
        'is_self' => 0
      ]) ?>
    </div>
  </div>
</div>