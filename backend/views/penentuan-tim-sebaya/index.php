<?php

use common\enums\CertificateLevel;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var \common\models\Certification[] $certifications
 * @var string|null $prev_link
 * @var string|null $next_link
 */

$this->title = 'Permintaan Pembentukan Tim Sebaya';
?>

<div class="page-cont w-100 h-100 p-3 d-flex flex-column gap-3">
  <h1><?= Html::encode($this->title) ?></h1>

  <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
    <?php Pjax::begin() ?>
    <div id="cert-table-peer">
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
        <div aria-label="Member Pagination" class=" align-items-center justify-content-around d-flex mt-3 w-100">
          <a class="p-2 btn btn-sm s-btn-sec pager-btn <?= $prev_link === null ? 'disabled' : '' ?>" data-container="#cert-table-peer" href="<?= Url::to($prev_link) ?>"><i class="fa-solid fa-angles-left"></i> Sebelumnya</a>
          <a class="p-2 btn btn-sm s-btn-main pager-btn <?= $next_link === null ? 'disabled' : '' ?>" data-container="#cert-table-peer" href="<?= Url::to($next_link) ?>">Berikutnya <i class="fa-solid fa-angles-right"></i></a>
        </div>
      </div>
    </div>
    <?php Pjax::end() ?>
  </div>
</div>