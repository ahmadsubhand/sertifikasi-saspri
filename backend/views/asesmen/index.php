<?php

/** @var \common\models\Assessment[] $assessments */

use common\enums\CertificateLevel;
use yii\helpers\Html;

$this->title = 'Asesmen Sertifikasi SASPRI-K'

?>

<div class="d-flex flex-column align-items-start gap-3">
    <div class="d-flex justify-content-between w-100 align-items-center">
        <h1><?= Html::encode($this->title) ?></h1>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal_buat_baru">
            Tambah Asesmen Baru
        </button>
    </div>

    <div class="card p-3 d-flex flex-column gap-2 w-100">
        <table class="table align-middle text-center">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Judul</th>
                    <th scope="col">Level</th>
                    <th scope="col">Status</th>
                    <th scope="col">Tanggal Rilis</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assessments as $index => $assessment): ?>
                <tr>
                    <th scope="row"><?= $index + 1 ?></th>
                    <td><?= Html::encode($assessment->title) ?></td>
                    <td><?= Html::encode(CertificateLevel::list()[$assessment->level]) ?></td>
                    <td>
                        <?php if ($assessment->level === $assessment->active_at_level): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td><?= 
                        $assessment->released_at
                        ? date('Y-m-d', strtotime($assessment->released_at)) 
                        : '-' 
                    ?></td>
                    <td>
                        <div class="btn-group">
                            <a href="<?= \yii\helpers\Url::to(['kelola', 'assessment_id' => $assessment->id]) ?>" class="btn btn-primary btn-sm">
                                Kelola
                            </a>
                            <a href="<?= \yii\helpers\Url::to(['buat', 'assessment_id' => $assessment->id]) ?>" 
                               class="btn btn-outline-primary btn-sm"
                               data-method="post"
                               data-confirm="Salin asesmen ini?">
                                Salin
                            </a>
                            <a href="<?= \yii\helpers\Url::to(['hapus', 'assessment_id' => $assessment->id]) ?>" 
                               class="btn btn-danger btn-sm"
                               data-method="delete"
                               data-confirm="Apakah Anda yakin ingin menghapus asesmen ini?">
                                Hapus
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach ?>
                <?php if (empty($assessments)): ?>
                <tr>
                    <td colspan="6" class="text-center">Tidak ada asesmen.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Buat Baru -->
<div class="modal fade" id="modal_buat_baru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= Html::beginForm(['buat', 'assessment_id' => 0], 'post') ?>
            <div class="modal-header">
                <h5 class="modal-title">Tambah Asesmen Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Judul Asesmen</label>
                    <?= Html::textInput('title', '', ['class' => 'form-control', 'required' => true]) ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Level Sertifikasi</label>
                    <?= Html::dropDownList('level', null, CertificateLevel::list(), ['class' => 'form-select', 'required' => true]) ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>