<?php

use common\enums\CertificateGrade;
use common\enums\CertificateLevel;
use common\enums\CertificationStatus;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var \common\models\SaspriK $saspri_k 
 * @var \common\models\Certification $valid_certificate
 * @var \common\models\Certification[] $completed_certifications
 * @var \common\models\User[] $saspri_k_members 
 * @var string|null $cert_prev_link
 * @var string|null $cert_next_link
 * @var int $certification_offset
 * @var string|null $user_prev_link
 * @var string|null $user_next_link
 * @var int $user_offset
 */

// dd($saspri_k_members);

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
  <div class="">
    <h3 class="fw-bold">SASPRI Kawasan</h3>
  </div>
  <div class="row">
    <div class="col-sm-8">
      <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
        <div class=" px-md-4 px-2">
          <p class=" fw-bold h5 mb-3 border-bottom pb-2">Identitas SASPRI-K</p>
          <?php foreach ($index as $key => $dat) : ?>
            <?php echo $this->render('/component/_idline', [
              'label' => $label[$key],
              'data' => $saspri_k[$dat],
              'shingles' => $shingles[$dat] ?? ''
            ]); ?>
          <?php endforeach ?>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border mt-3 mt-md-0">
        <div class="px-md-3 px-1">
          <p class=" fw-bold">Sertifikat </p>

          <?php foreach ($certIndex as $key => $dat) : ?>
            <?php echo $this->render('/component/_idline', [
              'label' => $certLabel[$key],
              'data' => $valid_certificate[$dat],
              'shingles' => ''
            ]); ?>
          <?php endforeach ?>
        </div>
        <?= Html::a(
            'Unduh Sertifikat <i class="fa-solid fa-download"></i>',
            ['/sertifikat/download-transcript', 'certification_id' => $valid_certificate->id],
            ['class' => 'btn s-btn-main me-2 w-100 mt-3', 'target' => '_blank', 'data-pjax' => '0']
        ) ?>
      </div>
    </div>
  </div>
  <div>
    <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
      <div class="px-md-4">
        <p class=" fw-bold">Riwayat Sertifikasi </p>
        <?php Pjax::begin() ?>
        <div id="sapri-cert-hist-card" class="mobile-scroll">
          <table class="table text-center">
            <thead>
              <tr>
                <th scope="col">No</th>
                <th scope="col">Tingkatan</th>
                <th scope="col">Tanggal Pengajuan</th>
                <th scope="col">Tanggal Penerbitan</th>
                <th scope="col">Status</th>
                <th scope="col">Predikat</th>
                <th scope="col">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($completed_certifications as $key => $value) : ?>
                <tr>
                  <td scope="row"><?php echo $certification_offset + (int)$key + 1 ?></th>
                  <td><?= Html::encode(CertificateLevel::list()[$value->level]) ?></td>
                  <td><?= Html::encode($value->created_at ? date('d-m-Y', $value->created_at)
                        : '-')  ?></td>
                  <td><?= Html::encode($value->issued_at ? date('d-m-Y', strtotime($value->issued_at))
                        : '-')  ?></td>
                  <td><?= Html::encode(CertificationStatus::list()[$value->status]) ?></td>
                  <td><?= Html::encode(CertificateGrade::list()[$value->grade] ?? '-') ?></td>
                  <td>
                    <div class="d-flex gap-2">
                      <a href="<?php echo Url::to(['detail', 'case_id' => $value->id]) ?>" class="s-btn-main btn btn-sm"><i class="fa-solid fa-magnifying-glass"></i></a>

                      <?php if (str_contains(strtolower($value->status), 'comp')): ?>
                        <a href="<?php echo Url::to(['#', 'id' => $value->id]) ?>" class="s-btn-main btn btn-sm"><i class="fa-solid fa-download"></i></a>
                      <?php endif ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach ?>
              <?php if (empty($completed_certifications)): ?>
                <tr>
                  <td colspan="5" class="text-center">Belum ada Riwayat Sertifikasi.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div aria-label="Certification History Pagination" class="aalign-items-center justify-content-around d-flex mt-3 w-100">
          <a class="page-link btn-sm btn s-btn-sec pager-btn <?= $cert_prev_link === null ? 'disabled' : '' ?>"
            href="<?= $cert_prev_link ?>"
            data-container="sapri-cert-hist-card">
            <i class="fa-solid fa-angles-left"></i> Sebelumnya
          </a>
          <a class="page-link btn-sm btn s-btn-sec pager-btn <?= $cert_next_link === null ? 'disabled' : '' ?>"
            href="<?= $cert_next_link ?>"
            data-container="sapri-cert-hist-card">
            Berikutnya <i class="fa-solid fa-angles-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
  <div class="mt-3">
    <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
      <div class="px-md-4">
        <p class=" fw-bold">Anggota Kawasan</p>
      
        <?php Pjax::begin() ?>
        <div id="sapri-member-card" class="mobile-scroll">
          <table class="table text-center">
            <thead>
              <tr>
                <th scope="col">No</th>
                <th scope="col">Nama Anggota</th>
                <th scope="col">Nomor Telpon</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($saspri_k_members as $key => $member) : ?>
                <tr class="member-container" data-page-index="<?= $key ?>">
                  <td scope="row"><?php echo $user_offset + (int)$key + 1 ?></th>
                  <td><?= Html::encode($member->username) ?></td>
                  <td><?= Html::encode($member->phone_number) ?></td>
                </tr>
              <?php endforeach ?>
              <?php if (empty($saspri_k_members)): ?>
                <tr>
                  <td colspan="5" class="text-center">Belum ada anggota SASPRI-K.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div aria-label="Member Pagination" class=" align-items-center justify-content-around d-flex mt-3 w-100">
          <a class="p-2 btn-sm btn s-btn-sec pager-btn <?= $user_prev_link === null ? 'disabled' : '' ?>"
            data-container="#saspri-member-card"
            href="<?= Url::to($user_prev_link) ?>">
            <i class="fa-solid fa-angles-left"></i> Sebelumnya
          </a>
          <a class="p-2 btn-sm btn s-btn-main pager-btn <?= $user_next_link === null ? 'disabled' : '' ?>"
            data-container="#saspri-member-card" href="<?= Url::to($user_next_link) ?>">
            Berikutnya <i class="fa-solid fa-angles-right"></i>
          </a>

        </div>
        <?php Pjax::end() ?>
      </div>
    </div>
  </div>
</div>
<script>

</script>