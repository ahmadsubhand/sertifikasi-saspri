<?php

use yii\helpers\Html;

/** @var string $step */
?>

<div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
  <div class="px-4">
    <h2 class="h4">Penolakan Sertifikasi</h2>
    <p class="text-muted small">
      Tolak sertifikasi jika ada kekurangan dari SASPRI-K
    </p>
    <?= Html::beginForm() ?>
    <label for="deny-reason" class="mb-0">Alasan:</label>
    <input type="text" id="deny-reason" placeholder="Tulis alasan penolakan sertifikasi"
      class="form-control border border-1 shadow-sm" autocomplete="off">

    <div class="form-check mt-3">
      <input class="form-check-input border-black" type="checkbox" id="check-consent" required>
      <label class="form-check-label" for="check-consent">
        Saya secara sadar menolak sertifikasi ini dengan alasan diatas
      </label>
    </div>

    <button type="submit" class="btn s-btn-red me-2 w-100 my-3"
      onclick="return confirm('Apakah Anda yakin ingin menolak sertifikasi SASPRI-K?')">
      Tolak Sertifikasi
    </button>
    <?= Html::endForm() ?>
  </div>
</div>