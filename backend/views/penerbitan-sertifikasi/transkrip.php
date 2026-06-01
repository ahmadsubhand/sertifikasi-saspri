<?php

use common\enums\CertificateGrade;
use common\enums\CertificateLevel;
use common\models\Certification;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var Certification $certification
 * @var array[] $transcripts
 */
// dd($certification);
// dd($transcripts);

$this->title = 'Penerbitan Sertifikasi - Transkrip';

$label = [
  'a' => 'Tersertifikasi dengan pujian tertinggi commendable',
  'ab' => 'Tersertifikasi di atas standard',
  'b' => 'Tersertifikasi sesuai standard',
  'bc' => 'Proses sertifikasi diulang dalam satu tahun setelah dilakukan usaha perbaikan',
  'c' => 'Proses sertifikasi diulang kembali dalam 2 tahun',
];

$passed = $certification->grade != CertificateGrade::BC && $certification->grade != CertificateGrade::C

?>

<div class="page-cont w-100 p-3 d-flex flex-column gap-3 asesmen-kelola">
  <h1><?= Html::encode($this->title) ?></h1>
  <div class="text-muted d-flex align-items-center gap-2 mb-2">
    <span class="badge bg-primary">Admin Nasional</span>
    <div>
      Sertifikasi SASPRI-K <?= Html::encode($certification->saspriK->region_name) ?> tingkat <?= Html::encode(CertificateLevel::list()[$certification->level] ?? '-') ?>
    </div>
  </div>
  <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border p-3 d-flex flex-column gap-2 w-100">
    <table class="table align-middle px-4">
      <thead>
        <tr class="text-center">
          <th scope="col" style="width: 50px;">Kode</th>
          <th scope="col" class="text-start">Kriteria</th>
          <th scope="col">Bobot</th>
          <th scope="col">Nilai Terbobot</th>
          <th scope="col">Nilai Akhir</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($transcripts as $key => $group): ?>
          <tr class="text-center">
            <td class="fw-bold"><?= Html::encode($group['code']) ?></td>
            <td class="fw-bold text-start"><?= Html::encode($group['label']) ?> [<?= Html::encode($group['weight'] * 100) ?>%]</td>
            <td class="fw-bold" colspan="3"></td>
          </tr>

          <?php foreach ($group['indicator_group'] as $index => $indicator) : ?>
            <tr class="text-center">
              <td class=""><?= Html::encode($indicator['code']) ?></td>
              <td class=" text-start"><?= Html::encode($indicator['label']) ?></td>
              <td class=""><?= Html::encode($indicator['weight'] * 100) ?>%</td>
              <td class=""><?= Html::encode($indicator['weighted_score']) ?></td>
              <td class=""><?= Html::encode($indicator['score']) ?></td>
            </tr>
          <?php endforeach ?>
          <tr class="fw-bold text-center">
            <td></td>
            <td class="text-start">Nilai Total [<?= Html::encode($group['code']) ?>]</td>
            <td colspan="2"></td>
            <td class=""><?= Html::encode($group['score']) ?></td>
          </tr>
        <?php endforeach ?>
        <tr class="fw-bold text-center">
          <td></td>
          <td class="text-start" colspan="3">Nilai Total Sertifikasi</td>

          <td class=""><?= Html::encode($certification->total_score) ?></td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="card border <?= $passed ? 'border-success-subtle' : 'border-danger-subtle' ?> shadow-sm rounded-2 bg-white w-100">
    <div class="card-header <?= $passed ? 'bg-success-subtle' : 'bg-danger-subtle' ?> text-danger-emphasis fw-bold py-3 px-4">
      <h5 class="mb-0 fw-bold"><i class="fa-solid fa-gavel me-2"></i> Hasil Sertifikasi</h5>
    </div>
    <div class="card-body p-4">
      <div class="row g-4 align-items-center">
        <div class="col-md-4 text-center border-end border-light-subtle py-2">
          <div class="mb-3">
            <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Nilai Akhir</span>
            <span class="display-4 fw-bold text-dark"><?= Html::encode($certification->total_score) ?></span>
          </div>
          <div>
            <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Huruf Mutu</span>
            <span class="display-3 fw-black <?= $passed ? 'text-success' : 'text-danger' ?> text-uppercase"><?= Html::encode($certification->grade) ?></span>
          </div>
        </div>

        <div class="col-md-8 ps-md-4">
          <div class="<?= $passed ? 'bg-success-subtle' : 'bg-danger-subtle'  ?> alert d-flex align-items-start gap-3 mb-3 border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-xmark fs-4 mt-1"></i>
            <div>
              <span class="small text-uppercase fw-bold d-block <?= $passed ? 'text-success-emphasis' : 'text-danger-emphasis' ?>">Predikat</span>
              <h5 class="alert-heading fw-bold mb-0"><?= Html::encode($label[$certification->grade]) ?></h5>
            </div>
          </div>

          <div class="p-3 bg-light rounded-2 border border-light-subtle mb-3">
            <span class="small text-secondary fw-bold text-uppercase d-block mb-1">Apresiasi & Ketentuan</span>
            <p class="mb-0 text-dark-emphasis fw-medium">
              <i class="fa-solid fa-clock-rotate-left me-2 text-secondary"></i>
              Sertifikasi diulang paling cepat setelah <?= Html::encode(date('d-m-Y', strtotime($certification->next_certification_due_date))) ?>.
            </p>
          </div>

          <div class="d-flex justify-content-between">
            <button type="button" class=" btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#rejectModal">Tolak Sertifikasi</button>
            <?= Html::a('Terbitkan Sertifikasi', ['finalisasi-penerbitan-sertifikasi', 'certification_id' => $certification->id], [
              'class' => 'btn btn-success me-2',
              'data' => [
                'method' => 'post',
                'confirm' => 'Apakah Anda yakin ingin memfinalisasi dan menerbitkan sertifikasi ini?', // Optional: Adds a native confirmation alert prompt before submitting
              ],
            ]) ?>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- modal -->
  <div class="modal fade modal-lg" id="rejectModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="rejectModalLabel">Penolakan Sertifikasi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?= $this->render('/component/_reject_modal', ['step' => 'Transcript']); ?>
        </div>
      </div>
    </div>
  </div>