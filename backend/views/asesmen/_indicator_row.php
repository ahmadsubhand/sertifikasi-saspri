<?php
/** @var common\models\Indicator $indicator */
/** @var common\models\Assessment $assessment */

use yii\bootstrap5\Html;

?>
<tr class="table-info">
    <td class="fw-bold"><?= Html::encode($indicator->code) ?></td>
    <td><?= Html::encode($indicator->label) ?></td>
    <td><?= $indicator->order ?></td>
    <td>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-primary" onclick='edit_indikator(<?= json_encode($indicator->attributes) ?>)'>Edit</button>
            <?= Html::a('Hapus', ['hapus-indikator', 'indicator_id' => $indicator->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Apakah Anda yakin ingin menghapus indikator ini?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </td>
</tr>
<tr>
    <td colspan="4" class="ps-5 py-2 border-bottom shadow-sm bg-white">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small fw-bold text-muted">Opsi Jawaban:</span>
            <button class="btn btn-outline-success btn-sm py-0" style="font-size: 0.75rem;" onclick="tambah_opsi(<?= $indicator->id ?>)">
                Tambah Opsi
            </button>
        </div>
        <table class="table table-sm table-bordered mb-0" style="font-size: 0.85rem;">
            <thead class="bg-light">
                <tr>
                    <th style="width: 15%">Kode</th>
                    <th>Label Opsi</th>
                    <th style="width: 15%">Bobot</th>
                    <th style="width: 15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($indicator->indicatorOptions as $option): ?>
                    <tr>
                        <td><?= Html::encode($option->code) ?></td>
                        <td><?= Html::encode($option->label) ?></td>
                        <td><?= $option->weight ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary py-0" onclick='edit_opsi(<?= json_encode($option->attributes) ?>)'>Edit</button>
                                <?= Html::a('Hapus', ['hapus-opsi', 'indicator_option_id' => $option->id], [
                                    'class' => 'btn btn-outline-danger py-0',
                                    'data' => [
                                        'confirm' => 'Yakin hapus opsi?',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($indicator->indicatorOptions)): ?>
                    <tr><td colspan="4" class="text-center text-muted small py-2">Belum ada opsi.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </td>
</tr>
