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
  <div>
    <div class="s-bg-side align-items-center">
      <a class="text-decoration-none text-white h6 align-items-center" id="collapse-trig"
        href="#collapse-sidenav" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapse-sidenav">
        <div class="d-flex py-2 mx-2">
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
      <a href="<?php echo Url::to('/tim-sebaya') ?>" class="text-decoration-none text-white w-100 ">
        <div class=" py-2 px-4 <?= str_contains($currentRoute, 'tim-sebaya') ? 's-bg-sec' : 's-bg-side' ?>">
          Kegiatan Tim Sebaya
        </div>
      </a>
      <?php if (Yii::$app->user->can(UserRole::USER)) : ?>
        <a href="<?php echo Url::to('/tim-mandiri') ?>" class="text-decoration-none text-white w-100 ">
          <div class=" py-2 px-4 <?= str_contains($currentRoute, 'tim-mandiri') ? 's-bg-sec' : 's-bg-side' ?>">
            Kegiatan Tim Mandiri
          </div>
        </a>
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