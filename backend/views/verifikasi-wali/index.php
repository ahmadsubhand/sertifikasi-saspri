<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var \common\models\SaspriK[] $registration_requests */
/** @var \common\models\SaspriK[] $change_requests */
/** @var \common\models\SaspriK[] $verified_saspri */

$this->title = 'Verifikasi Wali SASPRI-K';
?>

<div class="page-cont w-100 h-100 p-3 d-flex flex-column gap-3">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- 1. Permintaan Pendaftaran Wali Baru -->
    <div>
        <div class="d-flex align-items-center mb-2">
            <p class="fw-bold h5">Permintaan Pendaftaran Wali Baru</p>
            <a href="#collapse-registration" class="text-decoration-none text-black fw-bolder h6 ms-2" data-bs-toggle="collapse" role="button" aria-expanded="true">
                <i class="fa-solid fa-chevron-up"></i>
            </a>
        </div>
        <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
            <div class="collapse show px-4" id="collapse-registration">
                <table class="table text-center align-middle">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama SASPRI-K</th>
                            <th scope="col">Kecamatan</th>
                            <th scope="col">Calon Wali</th>
                            <th scope="col">Koperasi</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registration_requests as $index => $saspri): ?>
                            <tr>
                                <td scope="row"><?= $index + 1 ?></th>
                                <td><?= Html::encode($saspri->region_name) ?></td>
                                <td><?= Html::encode($saspri->district->name) ?></td>
                                <td><?= Html::encode($saspri->coordinator->username) ?></td>
                                <td><?= Html::encode($saspri->cooperative_name) ?></td>
                                <td>
                                    <a href="<?= Url::to(['permintaan-pendaftaran-wali', 'saspri_k_id' => $saspri->id]) ?>" class="btn btn-sm s-btn-main" title="Detail Pendaftaran">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($registration_requests)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada permintaan pendaftaran baru.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. Permintaan Pergantian Wali -->
    <div>
        <div class="d-flex align-items-center mb-2">
            <p class="fw-bold h5">Permintaan Pergantian Wali</p>
            <a href="#collapse-change" class="text-decoration-none text-black fw-bolder h6 ms-2" data-bs-toggle="collapse" role="button" aria-expanded="true">
                <i class="fa-solid fa-chevron-up"></i>
            </a>
        </div>
        <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
            <div class="collapse show px-4" id="collapse-change">
                <table class="table text-center align-middle">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama SASPRI-K</th>
                            <th scope="col">Kecamatan</th>
                            <th scope="col">Wali Saat Ini</th>
                            <th scope="col">Wali Pengganti</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($change_requests as $index => $saspri): ?>
                            <tr>
                                <td scope="row"><?= $index + 1 ?></th>
                                <td><?= Html::encode($saspri->region_name) ?></td>
                                <td><?= Html::encode($saspri->district->name) ?></td>
                                <td><?= Html::encode($saspri->coordinator->username) ?></td>
                                <td><?= Html::encode($saspri->newCoordinator->username) ?></td>
                                <td>
                                    <a href="<?= Url::to(['permintaan-pergantian-wali', 'saspri_k_id' => $saspri->id]) ?>" class="btn btn-sm s-btn-main" title="Detail Pergantian">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($change_requests)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada permintaan pergantian wali.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
