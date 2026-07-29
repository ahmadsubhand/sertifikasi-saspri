<?php

use common\enums\UserRole;
use yii\bootstrap5\Nav;
use yii\helpers\Html;
use yii\helpers\Url;

$currentRoute = Yii::$app->controller->getRoute();

?>

<aside class="flex-shrink-0 border-md-end overflow-y-auto s-bg-main" style="width: 4.5rem;">
  <div class="text-uppercase lh-sm mt-1">
    <!-- DASHBOARD -->
    <div class="<?= str_contains($currentRoute, 'site') ? 's-bg-sec' : 's-bg-side' ?> align-items-center">
        <a href="<?= Url::to('/site') ?>" class="text-decoration-none text-white w-100 text-white h6">
            <div class="py-3 mx-2">
                Dashboard
            </div>
        </a>
    </div>

    <!-- CATATAN PAKAN TERNAK -->
    <div class="<?= str_contains($currentRoute, 'note') ? 's-bg-sec' : 's-bg-side' ?> align-items-center">
        <a href="<?= Url::to('/note') ?>" class="text-decoration-none text-white w-100 text-white h6">
            <div class="py-3 mx-2">
                Buat Catatan Ternak
            </div>
        </a>
    </div>

    <!-- PENDAFTARAN TERNAK -->
    <div>
        <div class="s-bg-side align-items-center">
            <a
                id="collapse-pendaftaran-trig"
                class="text-decoration-none text-white h6 align-items-center"
                href="#collapse-pendaftaran"
                data-bs-toggle="collapse"
                role="button"
                aria-expanded="true"
                aria-controls="collapse-pendaftaran">

                <div class="d-flex py-3 mx-2">
                    <p class="mb-0">Pendaftaran</p>
                    <div class="ms-2">
                        <i class="fa-solid fa-chevron-up"></i>
                    </div>
                </div>
            </a>
        </div>

        <div id="collapse-pendaftaran" class="collapse show">
            <a href="<?= Url::to('/cage') ?>" class="text-decoration-none text-white w-100">
                <div class="py-2 px-4 <?= str_contains($currentRoute, 'cage') ? 's-bg-sec' : 's-bg-side' ?>">
                    Tambah Kandang
                </div>
            </a>

            <a href="<?= Url::to('/livestock') ?>" class="text-decoration-none text-white w-100">
                <div class="py-2 px-4 <?= str_contains($currentRoute, 'livestock') ? 's-bg-sec' : 's-bg-side' ?>">
                    Tambah Sapi
                </div>
            </a>

            <a href="<?= Url::to('/bcs') ?>" class="text-decoration-none text-white w-100">
                <div class="py-2 px-4 <?= str_contains($currentRoute, 'bcs') ? 's-bg-sec' : 's-bg-side' ?>">
                    Tambah BCS
                </div>
            </a>
        </div>
    </div>

    <!-- SIMULASI HARGA JUAL -->
    <div class="<?= str_contains($currentRoute, '/harga-jual/simulation') ? 's-bg-sec' : 's-bg-side' ?> align-items-center">
        <a href="<?= Url::to('/harga-jual/simulation') ?>" class="text-decoration-none text-white w-100 text-white h6">
            <div class="py-3 mx-2">
                Simulasi Harga Jual
            </div>
        </a>
    </div>

    <!-- PERHITUNGAN HARGA -->
    <div>
        <div class="s-bg-side align-items-center">
            <a
                id="collapse-harga-trig"
                class="text-decoration-none text-white h6 align-items-center"
                href="#collapse-harga"
                data-bs-toggle="collapse"
                role="button"
                aria-expanded="true"
                aria-controls="collapse-harga">

                <div class="d-flex py-3 mx-2">
                    <p class="mb-0">Perhitungan Harga</p>
                    <div class="ms-2">
                        <i class="fa-solid fa-chevron-up"></i>
                    </div>
                </div>
            </a>
        </div>

        <div id="collapse-harga" class="collapse show">
            <a href="<?= Url::to('/harga-jual/data') ?>" class="text-decoration-none text-white w-100">
                <div class="py-2 px-4 <?= str_contains($currentRoute, 'harga-jual/data') ? 's-bg-sec' : 's-bg-side' ?>">
                    Data Biaya Overhead
                </div>
            </a>
            <a href="<?= Url::to('/harga-jual/index') ?>" class="text-decoration-none text-white w-100">
                <div class="py-2 px-4 <?= str_contains($currentRoute, 'harga-jual/index') ? 's-bg-sec' : 's-bg-side' ?>">
                    Hitung Harga Jual
                </div>
            </a>

            <a href="<?= Url::to('/harga-jual/history') ?>" class="text-decoration-none text-white w-100">
                <div class="py-2 px-4 <?= str_contains($currentRoute, 'harga-jual/history') ? 's-bg-sec' : 's-bg-side' ?>">
                    History Perhitungan
                </div>
            </a>

            <a href="<?= Url::to('/harga-jual/history-logs') ?>" class="text-decoration-none text-white w-100">
                <div class="py-2 px-4 <?= str_contains($currentRoute, 'harga-jual/history-logs') ? 's-bg-sec' : 's-bg-side' ?>">
                    Log Perubahan Harga
                </div>
            </a>
        </div>
    </div>

    <!-- SILSILAH HEWAN TERNAK -->
    <div class="<?= str_contains($currentRoute, 'silsilah') ? 's-bg-sec' : 's-bg-side' ?> align-items-center">
        <a href="<?= Url::to('/silsilah') ?>" class="text-decoration-none text-white w-100 text-white h6">
            <div class="py-3 mx-2">
                Silsilah Hewan Ternak
            </div>
        </a>
    </div>

    <!-- SERTIFIKASI SASPRI -->
    <div>
        <div class="s-bg-side align-items-center">
          <a class="text-decoration-none text-white h6 align-items-center" id="collapse-trig"
            href="#collapse-sidenav" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapse-sidenav">
            <div class="d-flex py-3 mx-2">
              <p class="mb-0 ">
                Sertifikasi
              </p>
              <div class="ms-2">
                <i class="fa-solid fa-chevron-up "></i>
              </div>
            </div>
          </a>
        </div>
        <div id="collapse-sidenav" class=" collapse show">
          <?php if (Yii::$app->user->can(UserRole::COORDINATOR)) : ?>
            <a href="<?php echo Url::to('/saspri-k') ?>" class="text-decoration-none text-white w-100 ">
              <div class=" py-2 px-4 <?= str_contains($currentRoute, 'saspri-k') ? 's-bg-sec' : 's-bg-side' ?>">
                SASPRI-K
              </div>
            </a>
          <?php endif ?>
          <?php if (Yii::$app->user->can(UserRole::USER)) : ?>
            <a href="<?php echo Url::to('/tim-mandiri') ?>" class="text-decoration-none text-white w-100 ">
              <div class=" py-2 px-4 <?= str_contains($currentRoute, 'tim-mandiri') ? 's-bg-sec' : 's-bg-side' ?>">
                Kegiatan Tim Mandiri
              </div>
            </a>
          <?php endif ?>
          <a href="<?php echo Url::to('/tim-sebaya') ?>" class="text-decoration-none text-white w-100 ">
            <div class=" py-2 px-4 <?= str_contains($currentRoute, 'tim-sebaya') ? 's-bg-sec' : 's-bg-side' ?>">
              Kegiatan Tim Sebaya
            </div>
          </a>
          <?php if (Yii::$app->user->can(UserRole::USER)) : ?>
            <div class="p-2">
              <div class="dropdown-divider border border-1"></div>
            </div>
            <a href="<?php echo Url::to('/daftar-wali') ?>" class="text-decoration-none text-white w-100 ">
              <div class=" py-2 px-4 <?= str_contains($currentRoute, 'wali') ? 's-bg-sec' : 's-bg-side' ?>">
                Daftar Sebagai Wali SASPRI-K
              </div>
            </a>
          <?php endif ?>
        </div>
    </div>
  </div>
</aside>

<?php
$this->registerJs(<<<JS

const collapseNav = document.getElementById('collapse-sidenav')
const collapseNavKey = 'collapsenav-state'

const localState = localStorage.getItem(collapseNavKey)
if (localState === 'true') {
  $('#collapse-trig').attr('aria-expanded', 'true')
  $(collapseNav).addClass('show')
} else if (localState === 'false'){
  $('#collapse-trig').attr('aria-expanded', 'false')
  $(collapseNav).removeClass('show')
}
collapseNav.addEventListener('shown.bs.collapse', function(){
  localStorage.setItem(collapseNavKey, true)
})
collapseNav.addEventListener('hidden.bs.collapse', function(){
  localStorage.setItem(collapseNavKey, false)
})
JS);
?>