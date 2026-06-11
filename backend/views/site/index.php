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
<div class="page-cont w-100 p-3 d-flex flex-column gap-3 asesmen-kelola">
  <h1><?= Html::encode($this->title) ?></h1>
  <div class="d-md-flex flex-lg-row text-center justify-content-around">
    <div class="bg-white py-5 border-dark-subtle shadow-sm border border-1 px-5 w-fit rounded-2 ">
      <i class="fa-solid fa-award m-4" style="font-size:6rem;"></i>
      <h2 class="h5">Sertifikasi Berjalan <br></h2>
      <p class="h2"><?= Html::encode($active_certifications_count) ?></p>
    </div>
    <div class="bg-white py-5 border-dark-subtle shadow-sm border border-1 px-5 w-fit rounded-2 ">
      <i class="fa-solid fa-people-carry-box m-4" style="font-size:6rem;"></i>
      <h2 class="h5">SASPRI-K Aktif <br></h2>
      <p class="h2"><?= Html::encode($active_saspri) ?></p>
    </div>
    <div class="bg-white py-5 border-dark-subtle shadow-sm border border-1 px-5 w-fit rounded-2 ">
      <i class="fa-solid fa-medal m-4" style="font-size:6rem;"></i>
      <h2 class="h5">SASPRI-K di Atas <br> Weania</h2>
      <p class="h2"><?= Html::encode($weania_plus) ?></p>
    </div>
  </div>
  <div class="d-md-flex gap-4 justify-content-between align-items-center align-middle mt-5">
    <h2 class="mb-0 ms-2">SASPRI-K Aktif</h2>
    <div class="d-flex gap-4 align-content-end w-fit uppe">
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
    <div id="saspri-table">
      <div class="px-4">
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
        <div aria-label="Member Pagination" class=" align-items-center justify-content-around d-flex mt-3 w-100">
          <a class="p-2 btn btn-sm s-btn-sec pager-btn <?= $prev_link === null ? 'disabled' : '' ?>" data-container="#running-table" href="<?= Url::to($prev_link) ?>"><i class="fa-solid fa-angles-left"></i> Sebelumnya</a>
          <a class="p-2 btn btn-sm s-btn-main pager-btn <?= $next_link === null ? 'disabled' : '' ?>" data-container="#running-table" href="<?= Url::to($next_link) ?>">Berikutnya <i class="fa-solid fa-angles-right"></i></a>
        </div>
      </div>
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