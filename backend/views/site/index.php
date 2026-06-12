<?php

use common\enums\CertificateLevel;
use common\models\SaspriK;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var SaspriK[] $saspris
 * @var string|null $prev_link
 * @var string|null $next_link
 * @var int|null $offset
 * @var int|null $active_saspri
 * @var int|null $weania_plus
 * @var int|null $active_certifications_count
 */

$this->title = 'Dashboard Sertifikasi';
?>
<div class="page-cont w-100 p-md-3 d-flex flex-column gap-3 asesmen-kelola">
  <h1><?= Html::encode($this->title) ?></h1>
  <div class="row g-4 justify-content-center text-center">
    <div class="col-12 col-sm-6 col-lg-4 d-flex justify-content-center">
      <div class="bg-white py-4 px-4 shadow-sm border border-light-subtle rounded-3 w-100 d-flex flex-column align-items-center justify-content-center" style="max-width: 320px; transition: transform 0.2s;">
        <div class="text-primary opacity-85 mb-3">
          <i class="fa-solid fa-award" style="font-size: 4.5rem;"></i>
        </div>
        <h2 class="h6 text-muted text-uppercase tracking-wider mb-2" style="min-height: 40px; display: flex; align-items: center;">
          Sertifikasi Berjalan
        </h2>
        <p class="display-5 fw-bold text-dark mb-0">
          <?= Html::encode($active_certifications_count) ?>
        </p>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-4 d-flex justify-content-center">
      <div class="bg-white py-4 px-4 shadow-sm border border-light-subtle rounded-3 w-100 d-flex flex-column align-items-center justify-content-center" style="max-width: 320px; transition: transform 0.2s;">
        <div class="text-success opacity-85 mb-3">
          <i class="fa-solid fa-people-carry-box" style="font-size: 4.5rem;"></i>
        </div>
        <h2 class="h6 text-muted text-uppercase tracking-wider mb-2" style="min-height: 40px; display: flex; align-items: center;">
          SASPRI-K Aktif
        </h2>
        <p class="display-5 fw-bold text-dark mb-0">
          <?= Html::encode($active_saspri) ?>
        </p>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-4 d-flex justify-content-center">
      <div class="bg-white py-4 px-4 shadow-sm border border-light-subtle rounded-3 w-100 d-flex flex-column align-items-center justify-content-center" style="max-width: 320px; transition: transform 0.2s;">
        <div class="text-warning opacity-85 mb-3">
          <i class="fa-solid fa-medal" style="font-size: 4.5rem;"></i>
        </div>
        <h2 class="h6 text-muted text-uppercase tracking-wider mb-2" style="min-height: 40px; display: flex; align-items: center;">
          SASPRI-K di Atas Weania
        </h2>
        <p class="display-5 fw-bold text-dark mb-0">
          <?= Html::encode($weania_plus) ?>
        </p>
      </div>
    </div>
  </div>
  <div class="d-md-flex gap-4 justify-content-between align-items-center align-middle mt-5">
    <h2 class="mb-0 ms-2">SASPRI-K Aktif</h2>
    <div class="d-flex gap-0 gap-md-4 align-content-end w-fit flex-column flex-md-row ms-auto">
      <div>
        <label for="wilayah-search text-uppercase">WILAYAH</label>
        <input class="form-control border-dark-subtle" type="text" name="wilayah-search" id="wilayah-search" style="width: 12rem;" autocomplete="off">
      </div>
      <div>
        <label for="wali-search text-uppercase">WALI</label>
        <input class="form-control border-dark-subtle" type="text" name="wali-search" id="wali-search" style="width: 12rem;" autocomplete="off">
      </div>
      <div>
        <label for="level-search text-uppercase">TINGKATAN</label>
        <?= Html::dropDownList('level-search', null, CertificateLevel::list(), [
          'id' => 'level-search',
          'class' => 'form-select border-dark-subtle',
          'prompt' => '-',
          'style' => "width:12rem;"
        ]) ?>
      </div>
    </div>
  </div>
  <div class="bg-white px-2 py-4 rounded-2  border-dark-subtle shadow-sm border border-1">
    <?php Pjax::begin(['id' => 'pjax-saspri-table']) ?>
    <div id="saspri-table" class="mobile-scroll">
      <div class="px-md-4 px-2">
        <table class="table align-middle text-center">
          <thead>
            <tr class=" text-uppercase">
              <th scope="col">No</th>
              <th scope="col">Wilayah</th>
              <th scope="col">Tingkatan</th>
              <th scope="col">Nama Wali</th>
              <th scope="col">Alamat Sekretariat</th>
              <th scope="col">Detail</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($saspris as $index => $saspri): ?>
              <tr class="data-row" data-search="<?= $saspri->region_name . "-" . $saspri->validCertificate->level ?>">
                <td scope="row"><?= $index + 1 ?></th>
                <td><?= Html::encode(ucfirst($saspri->region_name)) ?></td>
                <td><?= Html::encode(CertificateLevel::list()[$saspri->validCertificate->level] ?? '-') ?></td>
                <td><?= Html::encode($saspri->coordinator->username) ?></td>
                <td><?= Html::encode(ucfirst($saspri->address)) ?></td>
                <td>
                  <a href="<?= Url::to(['saspri-k', 'saspri_id' => $saspri->id]) ?>" class="btn btn-sm s-btn-main">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach ?>
            <?php if (empty($saspris)): ?>
              <tr>
                <td colspan="6" class="text-center">Tidak ada sertifikasi yang sedang berjalan.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div aria-label="Member Pagination" class=" align-items-center justify-content-around d-flex mt-3 w-100">
      <a class="p-2 btn btn-sm s-btn-sec pager-btn <?= $prev_link === null ? 'disabled' : '' ?>" data-container="#running-table" href="<?= Url::to($prev_link) ?>"><i class="fa-solid fa-angles-left"></i> Sebelumnya</a>
      <a class="p-2 btn btn-sm s-btn-main pager-btn <?= $next_link === null ? 'disabled' : '' ?>" data-container="#running-table" href="<?= Url::to($next_link) ?>">Berikutnya <i class="fa-solid fa-angles-right"></i></a>
    </div>
    <?php Pjax::end() ?>
  </div>

</div>

<?php $this->registerJs(<<<JS
  let searchTimer
  function pSearch(){
    const wilayahq = $('#wilayah-search').val().trim()
    const waliq = $('#wali-search').val().trim()
    const levelq = $('#level-search').val()

    $.pjax.reload({
      container: '#pjax-saspri-table',
      type:'GET',
      data:{
        wilayah:wilayahq ?? '',
        wali:waliq ?? '',
        level:levelq ?? '',
        offset:0
      },
      timeout: 2000,
      replace: false
      });
  }

  $('#wilayah-search, #wali-search, #level-search').on('input', function(){
    clearTimeout(searchTimer);
    searchTimer = setTimeout(pSearch, 500);
  })
JS, \yii\web\View::POS_READY); ?>