<?php

use common\enums\CertificateLevel;
use common\enums\CertificationStatus;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;


/** @var \common\models\Certification[] $certifications 
 * @var string|null $prev_link
 * @var string|null $next_link
 */

$this->title = 'Sertifikasi Berjalan';
// dd($certifications)
?>


<div class="page-cont w-100 h-100 p-3 d-flex flex-column gap-3">
  <div class=" d-md-flex justify-content-between">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="d-md-flex gap-4 align-self-end">
      <div>
        <label for="wilayah-search">Wilayah</label>
        <input class="form-control border-black" type="text" name="wilayah-search" id="wilayah-search" style="width: 12rem;" autocomplete="off">
      </div>
      <div>
        <label for="level-search">Tingkatan</label>
        <?= Html::dropDownList('level-search', null, CertificateLevel::list(), [
          'id' => 'level-search',
          'class' => 'form-select border-black',
          'prompt' => '-',
          'style' => "width:12rem;"
        ]) ?>
      </div>
    </div>
  </div>

  <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
    <?php Pjax::begin(['id' => 'pjax-running-table']) ?>
    <div id="running-table">
      <div class="px-4">
        <table class="table align-middle text-center">
          <thead>
            <tr>
              <th scope="col">No</th>
              <th scope="col">Wilayah</th>
              <th scope="col">Alamat Sekretaris</th>
              <th scope="col">Tingkatan</th>
              <th scope="col">Status</th>
              <th scope="col">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($certifications as $index => $certification): ?>
              <tr class="data-row" data-search="<?= $certification->saspriK->region_name . "-" . $certification->level ?>">
                <td scope="row"><?= $index + 1 ?></th>
                <td><?= Html::encode(ucfirst($certification->saspriK->region_name)) ?></td>
                <td><?= Html::encode($certification->saspriK->address) ?></td>
                <td><?= Html::encode(CertificateLevel::list()[$certification->level] ?? '-') ?></td>
                <td><?= Html::encode(CertificationStatus::list()[$certification->status] ?? '-') ?></td>
                <td>
                  <a href="<?= Url::to(['detail', 'case_id' => $certification->id]) ?>" class="btn btn-sm s-btn-main">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach ?>
            <?php if (empty($certifications)): ?>
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
    const levelq = $('#level-search').val()

    $.pjax.reload({
      container: '#pjax-running-table',
      type:'GET',
      data:{
        wilayah:wilayahq ?? '',
        level:levelq ?? '',
        offset:0
      },
      timeout: 2000,
      replace: false
      });
  }

  $('#wilayah-search, #level-search').on('input', function(){
    clearTimeout(searchTimer);
    searchTimer = setTimeout(pSearch, 500);
  })
JS, \yii\web\View::POS_READY); ?>