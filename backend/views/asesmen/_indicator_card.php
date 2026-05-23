<?php

use yii\helpers\Html;


/** @var common\models\Indicator $indicator */
/** @var common\models\Assessment $assessment */

?>

<div class="ind-card card rounded-2 my-3 d-flex flex-row s-border-main">
  <div class="s-bg-main d-block rounded-start-2" style="width: 1rem !important;"></div>
  <div class="py-3 ps-2 pe-4 w-100">
    <div class="d-flex justify-content-between">
      <a id="parInd<?= $indicator->id ?>" class="text-decoration-none" data-bs-toggle="collapse" href="#collapseInd<?= $indicator->id ?>" role="button" aria-expanded="false" aria-controls="collapseInd<?= $indicator->id ?>">
        <div class="d-flex gap-1 align-items-start justify-content-between w-100 flex-grow-1">
          <p class="mb-0 h6 text-secondary">Indikator [<?= Html::encode($indicator->code) ?>] </p>
          <p class="mb-0 h6 text-black fw-bold"> <?= Html::encode($indicator->label) ?></p>
          <i class="fa-solid fa-chevron-up text-black h-fit me-2 my-auto"></i>
        </div>
      </a>
      <div class="btn-group btn-group-sm h-fit khuvdvb">
        <button class="btn btn-sm btn-primary" onclick='edit_indikator(<?= json_encode($indicator->attributes) ?>)'><i class="fa-solid fa-pen-to-square"></i> Edit</button>
        <?= Html::a('<i class="fa-solid fa-trash-can"></i> Hapus', ['hapus-indikator', 'indicator_id' => $indicator->id], [
          'class' => 'btn btn-danger btn-sm',
          'data' => [
            'confirm' => 'Apakah Anda yakin ingin menghapus indikator ini?',
            'method' => 'delete',
          ],
        ]) ?>
      </div>

    </div>
    <div class="opt-all my-2 collapse" id="collapseInd<?= $indicator->id ?>" parent-link="parInd<?= $indicator->id ?>">
      <p class="mb-1">Opsi Jawaban:</p>
      <?php foreach ($indicator->indicatorOptions as $option): ?>
        <div class="d-flex justify-content-between border-bottom border-2">
          <div class="d-flex gap-2 py-2 mx-2">
            <p class="mb-0 s-color-main"><?= Html::encode($option->code) ?></p>
            <div>
              <p class="mb-0"><?= Html::encode($option->label) ?></p>
              <p class="opt-weight mb-0 fw-light text-black" data-weight="<?= Html::encode($option->weight) ?>">bobot: <?= Html::encode($option->weight) ?></p>
            </div>
          </div>
          <div class="d-flex flex-row align-items-center btn-group btn-group-sm">
            <button class="btn btn-outline-primary btn-sm" onclick='edit_opsi(<?= json_encode($option->attributes) ?>)'><i class="fa-solid fa-pen-to-square"></i> Edit</button>
            <?= Html::a('<i class="fa-solid fa-trash-can"></i> Hapus', ['hapus-opsi', 'indicator_option_id' => $option->id], [
              'class' => 'btn btn-outline-danger btn-sm',
              'data' => [
                'confirm' => 'Yakin hapus opsi?',
                'method' => 'delete',
              ],
            ]) ?>
          </div>
        </div>
        <?php endforeach ?>
        <?php if (empty($indicator->indicatorOptions)): ?>
        <div class="py-2">
          <p colspan="4" class="text-center text-muted small py-2">Belum ada opsi.</p>
        </div>
      <?php endif; ?>
      <div class="rounded rounded-2 d-flex align-items-center w-100 justify-content-center">
        <button class="btn btn-outline-light w-100 text-black py-2 mt-1 d-flex align-items-center justify-content-center gap-2" onclick="tambah_opsi(<?= $indicator->id ?>)">
          <i class="fa-solid fa-circle-plus fs-5 mb-0"></i> Tambah Opsi
        </button>
      </div>
    </div>
  </div>
</div>