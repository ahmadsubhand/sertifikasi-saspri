<?php

use common\enums\CertificateGrade;
use yii\helpers\Html;

/** @var \common\models\Certification[] $certifications */

$this->title = 'External Review';

?>

<div class="page-cont w-100 h-100 p-3 d-flex flex-column gap-3">
  <h1><?= Html::encode($this->title) ?></h1>

  <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border p-3 d-flex flex-column gap-2 w-100">
    <div class="px-4">
      <table class="table align-middle text-center">
        <thead>
          <tr>
            <th scope="col">No</th>
            <th scope="col">Wilayah</th>
            <th scope="col">Alamat Sekretaris</th>
            <th scope="col">Tenggat Waktu Penilaian</th>
            <th scope="col">Penilaian Sistem</th>
            <th scope="col">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($certifications as $index => $certification): ?>
            <tr>
              <td scope="row"><?= $index + 1 ?></th>
              <td><?= Html::encode(ucfirst($certification->saspriK->region_name)) ?></td>
              <td><?= Html::encode($certification->saspriK->address) ?></td>
              <td><?=
                  $certification->external_review_due_date
                    ? date('d-m-Y', strtotime($certification->external_review_due_date))
                    : '-'
                  ?></td>
              <td><?= Html::encode($certification->grade ? CertificateGrade::list()[$certification->grade] : '-') ?></td>
              <td>
                <a href="<?= \yii\helpers\Url::to(['detail', 'case_id' => $certification->id]) ?>" class="btn btn-sm s-btn-main">
                  <i class="fa-solid fa-pen-to-square"></i>
                </a>
              </td>
            </tr>
          <?php endforeach ?>
          <?php if (empty($certifications)): ?>
            <tr>
              <td colspan="6" class="text-center">Tidak ada permintaan penerbitan sertifikasi.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>