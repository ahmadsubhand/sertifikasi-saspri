<?php

use common\enums\CertificateLevel;
use common\models\SaspriK;
use common\models\Livestock;
use common\models\Cage;
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
 * @var int|null $sapi
 * @var int|null $cage
 * @var Livestock[] $livestocks
 * @var Cage[] $cages
 */

$this->title = 'Informasi Sertifikasi SASPRI Kawasan';
?>
<div class="page-cont w-100 p-md-3 d-flex flex-column gap-3 asesmen-kelola">
<?php if (!\Yii::$app->user->isGuest): ?>
  <h1>Dashboard Peternakan</h1>
  <div class="page-content"> 
    <section class="row">
        <div class="col-12 col-lg-12">
            <div class="row">
                <div class="col-6 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-end ">
                                    <div class="stats-icon purple mb-2">
                                        <i class="iconly-boldHome"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Kandang Terdaftar</h6>
                                    <h6 class="font-extrabold mb-0"><?= Html::decode($cage)?></h6>
                                </div>

                            </div> 
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-6 col-md-6">
                    <div class="card"> 
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-end ">
                                    <div class="stats-icon blue mb-2">
                                        <i class="iconly-boldHeart"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Sapi Terdaftar</h6>
                                    <h6 class="font-extrabold mb-0"><?= Html::encode("{$sapi}") ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>Kandang Saya</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    <?php if (!empty($cages)): ?>
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Nama Kandang</th>
                                                <th>Lokasi</th>
                                                <th>Kapasitas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $counter = 1; ?>
                                            <?php foreach ($cages as $cage): ?>
                                            <tr>
                                                <td class="text-bold-500"><?= $counter++ ?></td> <!-- Menambahkan kolom nomor -->
                                                <td class="text-bold-500 post"><?= $cage->name ?></td>
                                                <td><?= $cage->location ?></td>
                                                <td><?= $cage->getLivestockCount() . '/' . $cage->capacity ?></td>
                                                <!-- <td><div class="comment-actions">
                                                    <button class="btn icon icon-left btn-primary me-2 text-nowrap" data-bs-toggle="modal" data-bs-target="#border-less"><i class="bi bi-eye-fill"></i> Show</button>
                                                    <button class="btn icon icon-left btn-warning me-2 text-nowrap"><i class="bi bi-pencil-square"></i> Edit</button>
                                                    <button class="btn icon icon-left btn-danger me-2 text-nowrap"><i class="bi bi-x-circle"></i> Remove</button>
                                                </div></td> -->
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    <?php else: ?>
                                        <p>Anda belum memiliki kandang.</p>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 href="<?= Url::toRoute(['/cage/index']) ?>">Sapi Saya</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                <?php if (!empty($livestocks)): ?> 
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>VID</th>
                                    <th>Nama</th>
                                    <th>Kandang</th>
                                    <th>Umur</th>
                                    <th>Kesehatan</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $counter = 1; ?>
                            <?php foreach ($livestocks as $livestock): ?>
                                <tr>
                                    <td class="text-bold-500"><?= $counter++ ?></td> <!-- Menambahkan kolom nomor -->
                                    <td class="text-bold-500"><?= $livestock->vid?></td>
                                    <td class="text-bold-500"><?= $livestock->name ?></td>
                                    <td class="text-bold-500"><?= $livestock->cage->name ?></td>
                                    <td class="text-bold-500"><?= $livestock->age ?> tahun</td>
                                    <td class="text-bold-500"><?= $livestock->health ?></td>
                                </tr>
                                <div class="modal fade" id="modalView<?= $livestock->id ?>" tabindex="-1" aria-labelledby="modalViewLabel<?= $livestock->id ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalViewLabel<?= $livestock->id ?>">Detail Sapi</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                        <?php foreach ($livestock->attributes as $attribute => $value): ?>
                                                            <strong><?= ucfirst(str_replace('_', ' ', $attribute)) ?>:</strong> <?= $value ?><br>
                                                        <?php endforeach; ?>

                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                            <?php endforeach; ?>
                            </tbody>
                            <?php else: ?>
                               <p>Anda belum memiliki Sapi.</p>
                            <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="row">
                <div class="col-12 col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>Profile Visit</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-7">
                                    <div class="d-flex align-items-center">
                                        <svg class="bi text-primary" width="32" height="32" fill="blue"
                                            style="width:10px">
                                            <use
                                                xlink:href="assets/static/images/bootstrap-icons.svg#circle-fill" />
                                        </svg>
                                        <h5 class="mb-0 ms-3">Europe</h5>
                                    </div>
                                </div>
                                <div class="col-5">
                                    <h5 class="mb-0 text-end">862</h5>
                                </div>
                                <div class="col-12">
                                    <div id="chart-europe"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-7">
                                    <div class="d-flex align-items-center">
                                        <svg class="bi text-success" width="32" height="32" fill="blue"
                                            style="width:10px">
                                            <use
                                                xlink:href="assets/static/images/bootstrap-icons.svg#circle-fill" />
                                        </svg>
                                        <h5 class="mb-0 ms-3">America</h5>
                                    </div>
                                </div>
                                <div class="col-5">
                                    <h5 class="mb-0 text-end">375</h5>
                                </div>
                                <div class="col-12">
                                    <div id="chart-america"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-7">
                                    <div class="d-flex align-items-center">
                                        <svg class="bi text-success" width="32" height="32" fill="blue"
                                            style="width:10px">
                                            <use
                                                xlink:href="assets/static/images/bootstrap-icons.svg#circle-fill" />
                                        </svg>
                                        <h5 class="mb-0 ms-3">India</h5>
                                    </div>
                                </div>
                                <div class="col-5">
                                    <h5 class="mb-0 text-end">625</h5>
                                </div>
                                <div class="col-12">
                                    <div id="chart-india"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-7">
                                    <div class="d-flex align-items-center">
                                        <svg class="bi text-danger" width="32" height="32" fill="blue"
                                            style="width:10px">
                                            <use
                                                xlink:href="assets/static/images/bootstrap-icons.svg#circle-fill" />
                                        </svg>
                                        <h5 class="mb-0 ms-3">Indonesia</h5>
                                    </div>
                                </div>
                                <div class="col-5">
                                    <h5 class="mb-0 text-end">1025</h5>
                                </div>
                                <div class="col-12">
                                    <div id="chart-indonesia"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Latest Comments</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-lg">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Comment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="col-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-md">
                                                        <img src="./assets/compiled/jpg/5.jpg">
                                                    </div>
                                                    <p class="font-bold ms-3 mb-0">Si Cantik</p>
                                                </div>
                                            </td>
                                            <td class="col-auto">
                                                <p class=" mb-0">Congratulations on your graduation!</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="col-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-md">
                                                        <img src="./assets/compiled/jpg/2.jpg">
                                                    </div>
                                                    <p class="font-bold ms-3 mb-0">Si Ganteng</p>
                                                </div>
                                            </td>
                                            <td class="col-auto">
                                                <p class=" mb-0">Wow amazing design! Can you make another tutorial for
                                                    this design?</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="col-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-md">
                                                        <img src="./assets/compiled/jpg/8.jpg">
                                                    </div>
                                                    <p class="font-bold ms-3 mb-0">Singh Eknoor</p>
                                                </div>
                                            </td>
                                            <td class="col-auto">
                                                <p class=" mb-0">What a stunning design! You are so talented and creative!</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="col-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-md">
                                                        <img src="./assets/compiled/jpg/3.jpg">
                                                    </div>
                                                    <p class="font-bold ms-3 mb-0">Rani Jhadav</p>
                                                </div>
                                            </td>
                                            <td class="col-auto">
                                                <p class=" mb-0">I love your design! It's so beautiful and unique! How did you learn to do this?</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </section>
  </div>
<?php endif; ?>
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
  <div class="d-md-flex gap-4 justify-content-between align-items-center align-middle mt-5 w-100">
    <h2 class="mb-0 ms-2">SASPRI-K Aktif</h2>
    <div class="d-flex flex-column flex-md-row gap-md-4 align-content-end uppercase justify-content-end w-fit ms-auto">
      <div class="w-fit">
        <label for="wilayah-search text-uppercase">WILAYAH</label>
        <input class="form-control border-dark-subtle" type="text" name="wilayah-search" id="wilayah-search" style="width: 12rem;" autocomplete="off">
      </div>
      <div class="w-fit">
        <label for="wali-search text-uppercase">WALI</label>
        <input class="form-control border-dark-subtle" type="text" name="wali-search" id="wali-search" style="width: 12rem;" autocomplete="off">
      </div>
      <div class="w-fit">
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
      <div class="px-md-4 mobile-scroll">
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
                <td><?= Html::encode($saspri->coordinator->full_name) ?></td>
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
      <div aria-label="Member Pagination" class=" align-items-center justify-content-around d-flex mt-3 w-100">
        <a class="p-2 btn btn-sm s-btn-sec pager-btn <?= $prev_link === null ? 'disabled' : '' ?>" data-container="#running-table" href="<?= Url::to($prev_link) ?>"><i class="fa-solid fa-angles-left"></i> Sebelumnya</a>
        <a class="p-2 btn btn-sm s-btn-main pager-btn <?= $next_link === null ? 'disabled' : '' ?>" data-container="#running-table" href="<?= Url::to($next_link) ?>">Berikutnya <i class="fa-solid fa-angles-right"></i></a>
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