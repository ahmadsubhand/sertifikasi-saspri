<?php


use common\enums\ApprovalStatus;
use common\enums\CertificateGrade;
use common\enums\CertificateLevel;
use common\enums\CertificationStatus;
use common\enums\TeamRole;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var \common\models\SelfTeamMember[] $self_team_member_request */
/** @var \common\models\SelfTeamMember[] $self_team_member_uncompleted */
/** @var \common\models\SelfTeamMember[] $self_team_member_completed */
?>

<div class="page-cont w-100 h-100 p-3 d-flex flex-column gap-3">
  <div class="">
    <h3 class="fw-bold">Kegiatan Tim Mandiri</h3>
  </div>

  <div>
    <div class="d-flex align-items-center mb-2">
      <p class=" fw-bold h5">Permintaan Partisipasi Tim Mandiri </p>
      <a href="#collapse-party" class=" text-decoration-none text-black fw-bolder h6 ms-2" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapse-party">
        <i class="fa-solid fa-chevron-up"></i>
      </a>
    </div>
    <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
      <div class="collapse show px-4" id="collapse-party">
        <table class="table self-request text-center">
          <thead>
            <tr>
              <th scope="col">No</th>
              <th scope="col">Nama SASPRI-K</th>
              <th scope="col">Alamat Sekretariat</th>
              <th scope="col">Tingkatan</th>
              <th scope="col">Peran</th>
              <th scope="col">Status</th>
              <th scope="col">Tenggat Waktu <br>Konfirmasi</th>
              <th scope="col">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($self_team_member_request as $key => $member) : ?>
              <tr>
                <td scope="row"><?php echo (int)$key + 1 ?></th>
                <td><?= Html::encode(ucfirst($member->certification->saspriK->region_name)) ?></td>
                <td><?= Html::encode($member->certification->saspriK->address) ?></td>
                <td><?= Html::encode(CertificateLevel::list()[$member->certification->level]) ?></td>
                <td><?= Html::encode(TeamRole::list()[$member->role]) ?></td>
                <td><?= Html::encode(ApprovalStatus::list()[$member->status]) ?></td>
                <td><?= Html::encode($member->certification->self_team_due_date
                      ? date('Y-m-d', strtotime($member->certification->self_team_due_date))
                      : '-') ?>
                </td>
                <td>
                  <?= $this->render("/component/_btn_opt", [
                    'yes' => ['setuju', 'self_team_member_id' => $member->id],
                    'no' => ['tolak', 'self_team_member_id' => $member->id],
                    'look' => ['/tim-mandiri/detail', 'case_id' => $member->certification->id],
                  ]); ?>
                </td>
              </tr>
            <?php endforeach ?>
            <?php if (empty($self_team_member_request)): ?>
            <tr>
              <td colspan="8" class="text-center">Tidak ada sertifikasi yang menunggu pembentukan tim mandiri.</td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <br>
  <div>
    <div class="d-flex align-items-center mb-2">
      <p class=" fw-bold h5">Sertifikasi Berjalan </p>
      <a href="#collapse-running" class=" text-decoration-none text-black fw-bolder h6 ms-2" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapse-party">
        <i class="fa-solid fa-chevron-up"></i>
      </a>
    </div>
    <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
      <div class="collapse show px-4" id="collapse-running">
        <table class="table self-request text-center">
          <thead>
            <tr>
              <th scope="col">No</th>
              <th scope="col">Nama SASPRI-K</th>
              <th scope="col">Alamat Sekretariat</th>
              <th scope="col">Tingkatan</th>
              <th scope="col">Tenggat Waktu <br>Penilaian</th>
              <th scope="col">Tahapan</th>
              <th scope="col">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($self_team_member_uncompleted as $key => $member) : ?>
              <tr>
                <td scope="row"><?php echo (int)$key + 1 ?></th>
                <td><?= Html::encode(ucfirst($member->certification->saspriK->region_name)) ?></td>
                <td><?= Html::encode($member->certification->saspriK->address) ?></td>
                <td><?= Html::encode(CertificateLevel::list()[$member->certification->level]) ?></td>
                <td><?= $this->render('/component/_date_comparator', [
                  'cert' => $member->certification
                ]); ?>
                </td>
                <td><?= Html::encode(CertificationStatus::list()[$member->certification->status]) ?></td>
                <td>
                  <?php if (str_contains(strtolower(CertificationStatus::list()[$member->certification->status]), 'self')) : ?>
                    <a href="<?php echo Url::to(['tim-mandiri/detail', 'case_id' => $member->certification->id]) ?>" class="s-btn-main btn btn-sm"><i class="fa-solid fa-edit"></i></a>
                  <?php else:  ?>
                    <a href="<?php echo Url::to(['tim-mandiri/detail', 'case_id' => $member->certification->id]) ?>" class="s-btn-main btn btn-sm"><i class="fa-solid fa-magnifying-glass"></i></a>
                  <?php endif  ?>
                </td>
              </tr>
            <?php endforeach ?>
            <?php if (empty($self_team_member_uncompleted)): ?>
            <tr>
              <td colspan="7" class="text-center">Tidak ada sertifikasi yang sedang berjalan.</td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <br>
  <div>
    <div class="d-flex align-items-center mb-2">
      <p class=" fw-bold h5">Riwayat Sertifikasi</p>
      <a href="#collapse-history" class=" text-decoration-none text-black fw-bolder h6 ms-2" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapse-party">
        <i class="fa-solid fa-chevron-up"></i>
      </a>
    </div>
    <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
      <div class="collapse show px-4" id="collapse-history">
        <table class="table self-request text-center">
          <thead>
            <tr>
              <th scope="col">No</th>
              <th scope="col">Nama SASPRI-K</th>
              <th scope="col">Alamat Sekretariat</th>
              <th scope="col">Tingkatan</th>
              <th scope="col">Tanggal Pengajuan</th>
              <th scope="col">Tanggal Penerbitan</th>
              <th scope="col">Predikat</th>
              <th scope="col">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($self_team_member_completed as $key => $member) : ?>
              <tr>
                <td scope="row"><?php echo (int)$key + 1 ?></th>
                <td><?= Html::encode(ucfirst($member->certification->saspriK->region_name)) ?></td>
                <td><?= Html::encode($member->certification->saspriK->address) ?></td>
                <td><?= Html::encode(CertificateLevel::list()[$member->certification->level]) ?></td>
                <td><?= Html::encode($member->certification->created_at
                      ? date('Y-m-d', $member->certification->created_at)
                      : '-') ?>
                </td>
                <td><?= Html::encode($member->certification->issued_at
                      ? date('Y-m-d', strtotime($member->certification->issued_at))
                      : '-') ?>
                </td>
                <td><?= CertificateGrade::list()[$member->certification->grade] ?: '-' ?></td>
                <td>
                  <a href="<?php echo Url::to(['tim-mandiri/detail', 'case_id' => $member->certification->id]) ?>" class="s-btn-main btn btn-sm"><i class="fa-solid fa-magnifying-glass"></i></a>
                </td>
              </tr>
            <?php endforeach ?>
            <?php if (empty($self_team_member_completed)): ?>
            <tr>
              <td colspan="8" class="text-center">Anda belum pernah berperan sebagai tim mandiri.</td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>