<?php

use common\enums\CertificateLevel;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var \common\models\Certification[] $certifications */

$this->title = 'Permintaan Pembentukan Tim Sebaya';
?>

<div class="page-cont w-100 h-100 p-3 d-flex flex-column gap-3">
  <h1><?= Html::encode($this->title) ?></h1>

  <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
    <div class="PX-4">
      <table class="table align-middle text-center self-request">
        <thead>
          <tr>
            <th scope="col">No</th>
            <th scope="col">Wilayah</th>
            <th scope="col">Alamat Sekretaris</th>
            <th scope="col">Tingkatan</th>
            <th scope="col">Tenggat Waktu Pembentukan</th>
            <th scope="col">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($certifications as $index => $certification): ?>
            <tr>
              <td scope="row"><?= $index + 1 ?></th>
              <td><?= Html::encode(ucfirst($certification->saspriK->region_name)) ?></td>
              <td><?= Html::encode($certification->saspriK->address) ?></td>
              <td><?= Html::encode(CertificateLevel::list()[$certification->level] ?? '-') ?></td>
              <td><?=
                  $certification->peer_team_due_date
                    ? date('d-m-Y', strtotime($certification->peer_team_due_date))
                    : '-'
                  ?></td>
              <td>
                <a href="<?= Url::to(['pembentukan-tim-sebaya', 'certification_id' => $certification->id]) ?>" class="btn s-btn-main">
                  <i class="fa-solid fa-edit"></i>
                </a>
              </td>
            </tr>
          <?php endforeach ?>
          <?php if (empty($certifications)): ?>
            <tr>
              <td colspan="6" class="text-center">Tidak ada sertifikasi yang menunggu pembentukan tim sebaya.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>