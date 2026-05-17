<?php

use common\enums\UserRole;
use yii\bootstrap5\Nav;
use yii\helpers\Html;
use yii\helpers\Url;

$currentRoute = Yii::$app->controller->getRoute();

?>

<aside class="flex-shrink-0 border-end " style="width: 4.5rem;">
  <div class="w-100 mx-auto d-flex p-3">
    <a href="<?php echo Url::to(Yii::$app->homeUrl) ?>" class=" text-decoration-none text-white mx-auto">
      <h1 class="mx-auto h4">
        <?php echo Html::img('@web/images/matasapi.svg', [
          'alt' => 'Matasapi Digdaya Logo',
          'class' => 'bg-white rounded-4 p-1',
          'style' => 'width: 50px;'
        ]); ?> Sertifikasi</h1>
    </a>
  </div>
  <div class="<?= str_contains($currentRoute, 'dashboard') ? 's-bg-sec' : 's-bg-side' ?> align-items-center">
    <a href="<?php echo Url::to('/dashboard') ?>" class="text-decoration-none text-white w-100 text-white h6">
      <div class=" py-2 mx-2 ">
        Dashboard
      </div>
    </a>
  </div>
  <div class="<?= str_contains($currentRoute, 'wali') ? 's-bg-sec' : 's-bg-side' ?> align-items-center">
    <a href="<?php echo Url::to('/wali') ?>" class="text-decoration-none text-white w-100 text-white h6 ">
      <div class=" py-2 mx-2 ">
        Verifikasi Wali
      </div>
    </a>
  </div>
  <div>
    <div class="s-bg-side align-items-center">
      <a class="text-decoration-none text-white h6 align-items-center" id="collapse-trig"
      href="#collapse-sidenav" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapse-sidenav">
        <div class="d-flex py-2 mx-2">
          <p class="mb-0">
            Sertifikasi
          </p>
          <div class="ms-2">
            <i class="fa-solid fa-chevron-up "></i>
          </div>
        </div>
      </a>
    </div>
    <div id="collapse-sidenav" class="collapse show">
      <a href="<?php echo Url::to('/penentuan-tim-sebaya') ?>" class="text-decoration-none text-white w-100 ">
        <div class=" py-2 px-4 <?= str_contains($currentRoute, 'penentuan') ? 's-bg-sec' : 's-bg-side' ?>">
          Penentuan Tim Sebaya
        </div>
      </a>
    </div>
    <div id="collapse-sidenav" class="collapse show">
      <a href="<?php echo Url::to('/sertifikasi-berjalan') ?>" class="text-decoration-none text-white w-100 ">
        <div class=" py-2 px-4 <?= str_contains($currentRoute, 'berjalan') ? 's-bg-sec' : 's-bg-side' ?>">
          Sertifikasi Berjalan
        </div>
      </a>
    </div>
    <div id="collapse-sidenav" class="collapse show">
      <a href="<?php echo Url::to('/penerbitan-sertifikasi') ?>" class="text-decoration-none text-white w-100 ">
        <div class=" py-2 px-4 <?= str_contains($currentRoute, 'erbit') ? 's-bg-sec' : 's-bg-side' ?>">
          Penerbitan Sertifikat
        </div>
      </a>
    </div>
    <div id="collapse-sidenav" class="collapse show">
      <a href="<?php echo Url::to('/kegiatan-tim-sebaya') ?>" class="text-decoration-none text-white w-100 ">
        <div class=" py-2 px-4 <?= str_contains($currentRoute, 'kegiatan') ? 's-bg-sec' : 's-bg-side' ?>">
          Kegiatan Tim Sebaya
        </div>
      </a>
    </div>
  </div>
</aside>

<?php
$this->registerJs(<<<JS

const collapseNav = document.getElementById('collapse-sidenav')
const collapseNavKey = 'collapsenav-state'

const localState = localStorage.getItem(collapseNavKey)
if (localState === 'true') {
  $(collapseNav).addClass('show')
  $('#collapse-trig').attr('aria-expanded', 'true')
} else if (localState === 'false'){
  $(collapseNav).removeClass('show')
  $('#collapse-trig').attr('aria-expanded', 'false')
}
collapseNav.addEventListener('shown.bs.collapse', function(){
  localStorage.setItem(collapseNavKey, true)
})
collapseNav.addEventListener('hidden.bs.collapse', function(){
  localStorage.setItem(collapseNavKey, false)
})
JS);
?>