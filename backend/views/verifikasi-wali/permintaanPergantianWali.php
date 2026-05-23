<?php

use common\enums\CertificateLevel;
use common\enums\CertificateGrade;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var \common\models\SaspriK $saspri_k */
/** @var \common\models\Certification $valid_certificate */

$this->title = 'Detail Permintaan Pergantian Wali';

$label = [
    'SASPRI-K',
    'SASPRI-KK',
    'SASPRI-P',
    'Alamat Sekretariat',
    'Nama unit usaha (koperasi)',
    'Nama wali SASPRI',
    'Jumlah kelompok yang dibina',
    'Jumlah anggota aktif dalam kelompok yang dibina',
    'Ternak yang diusahakan',
    'Jumlah total ternak milik anggota aktif',
    'Jumlah ternak indukan (pernah beranak)',
    'Jumlah ternak dara produktif (siap dikawinkan)',
];
$index = [
    'district_id',
    'district_id',
    'district_id',
    'address',
    'cooperative_name',
    'coordinator_id',
    'number_of_groups',
    'number_of_active_members',
    'livestock_type',
    'total_livestock_count',
    'breeding_livestock_count',
    'productive_heifer_count',
];
$certLabel = [
    'Level Sertifikat',
    'No. Sertifikat',
    'Tanggal Pengajuan',
    'Tanggal Penerbitan',
    'Predikat',
];
$certIndex = [
    'level',
    'code',
    'created_at',
    'issued_at',
    'grade',
];
$shingles = [
    'number_of_active_members' => 'Orang',
    'total_livestock_count' => 'Ekor',
    'breeding_livestock_count' => 'Ekor',
    'productive_heifer_count' => 'Ekor',
];

?>

<div class="page-cont w-100 h-100 p-3 d-flex flex-column gap-3">
    <div class="d-flex align-items-center mb-4">
        <a href="<?= Url::to(['index']) ?>" class="text-decoration-none text-black fs-5 me-3">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h3 class="fw-bold mb-0"><?= Html::encode($this->title) ?></h3>
    </div>

    <div class="d-flex mx-auto w-100 justify-content-evenly">
        <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border mb-3 h-fit" style="width: 20rem;">
            <div class="text-center px-3 py-2">
                <p class="text-muted mb-1">Wali Saat Ini</p>
                <h4 class="fw-bold text-danger"><?= Html::encode($saspri_k->coordinator->username) ?></h4>
                <p class="small text-muted mb-0"><?= Html::encode($saspri_k->coordinator->phone_number) ?></p>
            </div>
        </div>
        <div class=" align-items-sm-center text-center my-auto">
            <h4 class="fw-bold mb-4 text-center h6">Menjadi</h4>
            <i class="fa-solid fa-angles-right fs-1 mx-auto"></i>
        </div>
        <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border mb-3 h-fit" style="width: 20rem;">
            <div class="text-center px-3 py-2">
                <p class="text-muted mb-1">Calon Wali Pengganti</p>
                <h4 class="fw-bold text-success"><?= Html::encode($saspri_k->newCoordinator->username) ?></h4>
                <p class="small text-muted mb-0"><?= Html::encode($saspri_k->newCoordinator->phone_number) ?></p>
            </div>
        </div>
    </div>

    <!-- Konfirmasi Pergantian Wali -->
    <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border mb-3">
        <div class="px-4">
            <div class="alert alert-warning border-0 shadow-sm mb-4">
                <p class="mb-0 fw-bold">Alasan Pergantian:</p>
                <p class="mb-0 italic">"<?= Html::encode($saspri_k->change_request_reason) ?: 'Tidak disebutkan' ?>"</p>
            </div>

            <div class="d-flex justify-content-center gap-3">
                <button type="button" class="btn btn-danger px-4 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#modalTolak">
                    <i class="fa-solid fa-xmark me-2"></i> Tolak Pergantian
                </button>
                <?= Html::beginForm(['ganti-wali', 'saspri_k_id' => $saspri_k->id], 'post', ['class' => 'd-inline']) ?>
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="btn btn-success px-4 py-2 fw-bold" onclick="return confirm('Apakah Anda yakin ingin menyetujui pergantian wali ini?')">
                    <i class="fa-solid fa-check me-2"></i> Setujui Pergantian
                </button>
                <?= Html::endForm() ?>

            </div>
        </div>
    </div>

    <!-- Identitas SASPRI-K -->
    <div class="row">
        <div class="col-sm-8">
            <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
                <div class="px-4">
                    <p class="fw-bold h5 mb-3 border-bottom pb-2">Identitas SASPRI-K</p>
                    <?php foreach ($index as $key => $dat) : ?>
                        <?php echo $this->render('/component/_idline', [
                            'label' => $label[$key],
                            'data' => $saspri_k[$dat],
                            'shingles' => $shingles[$dat] ?? ''
                        ]); ?>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
                <div class="px-3 text-center">
                    <p class="fw-bold h5 mb-3 border-bottom pb-2">Sertifikasi Saat Ini</p>
                    <?php if ($valid_certificate): ?>
                        <?php foreach ($certIndex as $key => $dat) : ?>
                            <?php
                            $val = $valid_certificate[$dat];
                            if ($dat === 'level') $val = CertificateLevel::list()[$val] ?? $val;
                            if ($dat === 'grade') $val = CertificateGrade::list()[$val] ?? '-';
                            if ($dat === 'issued_at') {
                                $val = $val ? date('d-m-Y', is_numeric($val) ? $val : strtotime($val)) : '-';
                            }
                            ?>

                            <?= $this->render('/component/_idline', [
                                'label' => $certLabel[$key],
                                'data' => $val ?? '-',
                                'shingles' => ''
                            ]); ?>
                            <!-- <span class="small text-muted"><?= $certLabel[$key] ?>:</span>
                                <span class="small fw-bold"><?= Html::encode($val) ?></span> -->
                        <?php endforeach ?>
                    <?php else: ?>
                        <p class="text-muted py-4">Belum memiliki sertifikasi valid.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tolak -->
<div class="modal fade" id="modalTolak" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= Html::beginForm(['ganti-wali', 'saspri_k_id' => $saspri_k->id], 'post') ?>
            <input type="hidden" name="action" value="reject">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Pergantian Wali</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Alasan Penolakan</label>
                    <textarea name="change_rejection_reason" class="form-control" rows="4" placeholder="Masukkan alasan penolakan agar wali dapat memperbaikinya..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Tolak Pengajuan</button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>