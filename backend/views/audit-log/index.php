<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var \yii\web\View $this
 * @var \common\models\AuditLog[] $logs
 * @var array $filters
 * @var array $pagination
 */

$this->title = 'Sistem Audit Log';

// Helper function untuk merender diff JSON (be)
function renderDiff(?string $oldValues, ?string $newValues) {
    // 1. Cek tipe data terlebih dahulu untuk menghindari TypeError
    $oldStr = is_string($oldValues) ? $oldValues : '';
    $newStr = is_string($newValues) ? $newValues : '';

    // 2. Decode JSON (gunakan operator ternary untuk memastikan hasil akhir adalah array)
    $old = json_decode($oldStr, true);
    $new = json_decode($newStr, true);

    // 3. Fallback jika hasil decode bukan array (misal karena string kosong atau null)
    if (!is_array($old)) $old = [];
    if (!is_array($new)) $new = [];

    // 4. Gabungkan dan ambil kunci (kolom) unik dari kedua array
    $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));
    
    if (empty($allKeys)) return '<i>Tidak ada detail</i>';

    $html = '<table class="table table-sm table-bordered mb-0" style="font-size: 0.85rem;">';
    $html .= '<thead class="table-light"><tr><th>Kolom</th><th>Data Lama</th><th>Data Baru</th></tr></thead><tbody>';
    
    foreach ($allKeys as $key) {
        $valOld = isset($old[$key]) ? print_r($old[$key], true) : '<i>(kosong)</i>';
        $valNew = isset($new[$key]) ? print_r($new[$key], true) : '<i>(kosong)</i>';
        
        // Highlight warna jika berubah
        $rowClass = ($valOld !== $valNew) ? 'table-warning' : '';
        
        $html .= "<tr class='{$rowClass}'>";
        $html .= "<td><strong>" . Html::encode($key) . "</strong></td>";
        $html .= "<td><span class='text-danger'>" . Html::encode($valOld) . "</span></td>";
        $html .= "<td><span class='text-success'>" . Html::encode($valNew) . "</span></td>";
        $html .= "</tr>";
    }
    
    $html .= '</tbody></table>';
    return $html;
}
?>

<div class="audit-log-index p-md-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= Html::encode($this->title) ?></h2>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <?= Html::beginForm(['index'], 'get', ['class' => 'row g-3 align-items-end']) ?>
            
            <div class="col-md-3">
                <label class="form-label">Tabel / Entitas</label>
                <?= Html::textInput('table_name', $filters['table_name'], ['class' => 'form-control', 'placeholder' => 'Misal: saspri_k']) ?>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Aksi</label>
                <?= Html::dropDownList('action', $filters['action'], [
                    '' => 'Semua Aksi',
                    'CREATE' => 'CREATE',
                    'UPDATE' => 'UPDATE',
                    'DELETE' => 'DELETE',
                ], ['class' => 'form-select']) ?>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">ID User</label>
                <?= Html::textInput('user_id', $filters['user_id'], ['class' => 'form-control', 'placeholder' => 'ID User']) ?>
            </div>

            <div class="col-md-2">
                <label class="form-label">Urutan</label>
                <?= Html::dropDownList('sort', $filters['sort'], [
                    'desc' => 'Terbaru',
                    'asc' => 'Terlama',
                ], ['class' => 'form-select']) ?>
            </div>
            
            <div class="col-md-3">
                <?= Html::hiddenInput('offset', 0) ?>
                <button type="submit" class="btn s-btn-main w-100">Cari / Filter</button>
            </div>
            
            <?= Html::endForm() ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body px-md-4 px-2 table-responsive">
            <table class="table table-hover table-striped mb-0 text-black">
                <thead class="table-dark">
                    <tr >
                        <th class="text-black" style="min-width:150px;">Waktu</th>
                        <th class="text-black" style="min-width:100px;">Aksi</th>
                        <th class="text-black" style="min-width:150px;">Tabel</th>
                        <th class="text-black" style="min-width:100px;">ID Data</th>
                        <th class="text-black" style="min-width:150px;">User ID</th>
                        <th class="text-black" style="min-width:300px;">Detail Perubahan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Tidak ada data log yang ditemukan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <?= \Yii::$app->formatter->asDatetime($log->created_at, 'php:d M Y H:i:s') ?>
                                </td>
                                <td>
                                    <?php
                                        $badge = 'bg-secondary';
                                        if ($log->action === 'CREATE') $badge = 'bg-success';
                                        if ($log->action === 'UPDATE') $badge = 'bg-primary';
                                        if ($log->action === 'DELETE') $badge = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= Html::encode($log->action) ?></span>
                                </td>
                                <td><code class="text-dark"><?= Html::encode($log->table_name) ?></code></td>
                                <td><strong><?= Html::encode($log->model_id) ?></strong></td>
                                <td>
                                    <?= $log->user_id ? Html::encode($log->user_id) : '<i>Sistem/Guest</i>' ?>
                                </td>
                                <td>
                                    <?php if ($log->old_values || $log->new_values): ?>
                                        <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#diff-<?= $log->id ?>">
                                            Lihat Perubahan
                                        </button>
                                        <div class="collapse mt-2" id="diff-<?= $log->id ?>">
                                            <div class="card card-body p-1">
                                                <?= renderDiff($log->old_values, $log->new_values) ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Tidak ada payload</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer d-flex justify-content-between flex-column flex-md-row align-items-center">
            <span class="text-muted small mb-3 text-start ">
                Menampilkan halaman: <?= $pagination['currentOffset'] + 1 ?>
            </span>
            <div class="btn-group">
                <?php
                    // Helper untuk membuat URL dengan filter yang aktif (be)
                    $buildUrl = function($newOffset) use ($filters) {
                        $params = array_merge(['index', 'offset' => $newOffset], $filters);
                        return Url::to($params);
                    };
                ?>

                <?php if ($pagination['hasPrev']): ?>
                    <a href="<?= $buildUrl($pagination['prevOffset']) ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fa-solid fa-angles-left"></i> Sebelumnya
                    </a>
                <?php else: ?>
                    <button class="btn btn-outline-secondary" disabled><i class="fa-solid fa-angles-left"></i> Sebelumnya</button>
                <?php endif; ?>

                <?php if ($pagination['hasNext']): ?>
                    <a href="<?= $buildUrl($pagination['nextOffset']) ?>" class="btn btn-sm btn-outline-primary">
                        Selanjutnya <i class="fa-solid fa-angles-right"></i>
                    </a>
                <?php else: ?>
                    <button class="btn btn-outline-secondary" disabled>Selanjutnya <i class="fa-solid fa-angles-right"></i></button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>