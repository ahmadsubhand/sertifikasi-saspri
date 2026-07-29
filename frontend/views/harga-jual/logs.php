<?php
/** @var yii\web\View $this */
/** @var common\models\HistoryChangeLog[] $logs */

use yii\helpers\Html;
use yii\helpers\Json;

$this->title = 'Log Perubahan Harga';
$this->params['breadcrumbs'][] = $this->title;

$formatter = \Yii::$app->formatter;
$formatCurrency = static function ($value) use ($formatter) {
    $numeric = is_numeric($value) ? (float) $value : 0;
    return $formatter->asCurrency($numeric, 'IDR');
};
$wibFormatter = clone \Yii::$app->formatter;
$wibFormatter->timeZone = 'Asia/Jakarta';

$totalLogs = count($logs);
$uniqueHistories = [];
$uniqueLivestock = [];
$changedEntries = 0;

foreach ($logs as $logItem) {
    $uniqueHistories[$logItem->history_id] = true;
    if ($logItem->history && $logItem->history->livestock_id) {
        $uniqueLivestock[$logItem->history->livestock_id] = true;
    }

    if ($logItem->previous_data !== $logItem->new_data) {
        $changedEntries++;
    }
}

$uniqueHistoryCount = count($uniqueHistories);
$uniqueLivestockCount = count($uniqueLivestock);

$this->registerCss(<<<CSS
.history-logs-table .log-row-stable,
.history-logs-table .log-row-updated {
    background-color: transparent;
}

.history-logs-table td,
.history-logs-table th {
    font-size: 0.95rem;
}

.history-logs-table .log-change-list {
    margin: 0;
    padding-left: 0;
    list-style: none;
    font-size: 0.95rem;
}

.history-logs-table .log-change-list li {
    margin-bottom: 0.35rem;
}

.history-logs-table .badge {
    font-size: 0.85rem;
}
CSS);
?>

<div class="container-fluid px-4 pt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="card-title mb-0">Log Perubahan Harga - Semua Riwayat</h5>
                    <small class="text-muted">Pantau perubahan biaya ternak Anda secara menyeluruh</small>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-primary"><i class="bi bi-collection me-1"></i><?= $totalLogs ?> Log</span>
                    <span class="badge bg-success"><i class="bi bi-hash me-1"></i><?= $uniqueHistoryCount ?> History</span>
                    <span class="badge bg-info text-dark"><i class="bi bi-cow me-1"></i><?= $uniqueLivestockCount ?> Sapi</span>
                    <span class="badge bg-warning text-dark"><i class="bi bi-arrow-repeat me-1"></i><?= $changedEntries ?> Update</span>
                </div>
            </div>
            <p class="text-muted">Menampilkan seluruh perubahan harga jual dan komponen biaya untuk seluruh history perhitungan milik Anda.</p>

            <?php if (empty($logs)): ?>
                <p class="text-muted mb-0">Belum ada log perubahan yang tersimpan.</p>
            <?php else: ?>
                <?php
                    $generalLabelMap = [
                        'pedet_price'     => 'Nilai Investasi Indukan',
                        'additional_cost' => 'Biaya Tambahan',
                        'cage_price'      => 'Investasi Kandang & Peralatan',
                        'hpp_price'       => 'Total HPP',
                        'sell_price'      => 'Harga Jual',
                    ];

                    $feedLabelMap = [
                        'forage'      => 'Pakan Hijauan',
                        'concentrate' => 'Konsentrat',
                        'additive'    => 'Feed Additive',
                    ];

                    $healthLabelMap = [
                        'insemination'    => 'Inseminasi',
                        'vaccine'         => 'Vaksin',
                        'vitamin'         => 'Vitamin',
                        'pregnancy_check' => 'Pemeriksaan Kebuntingan',
                        'antibiotics'     => 'Antibiotik',
                        'anthelmintic'    => 'Obat Cacing / Anthelmintic',
                    ];

                    $detailModals = [];
                ?>
                <div class="table-responsive history-logs-table">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Waktu Perubahan</th>
                                <th>History ID</th>
                                <th>Nama Sapi</th>
                                <th>Jenis Usaha</th>
                                <th>Perubahan</th>
                                <th>Aksi</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <?php
                                    $history = $log->history;
                                    $livestock = $history ? $history->livestock : null;
                                    $previous = Json::decode($log->previous_data) ?: [];
                                    $current  = Json::decode($log->new_data) ?: [];
                                    $isDeleted = isset($current['deleted']) && $current['deleted'];

                                    $detailRows = [];
                                    $addRow = static function ($label, $before, $after) use (&$detailRows) {
                                        $detailRows[] = [
                                            'label' => $label,
                                            'before' => $before ?? 0,
                                            'after'  => $after ?? 0,
                                        ];
                                    };

                                    if (!$isDeleted) {
                                        $addRow($generalLabelMap['pedet_price'], $previous['pedet_price'] ?? 0, $current['pedet_price'] ?? 0);
                                        $addRow($generalLabelMap['additional_cost'], $previous['additional_cost'] ?? 0, $current['additional_cost'] ?? 0);

                                        foreach (['feed' => 'Biaya Pakan', 'health' => 'Biaya Kesehatan'] as $key => $title) {
                                            $previousGroup = isset($previous[$key]) && is_array($previous[$key]) ? $previous[$key] : [];
                                            $currentGroup  = isset($current[$key]) && is_array($current[$key]) ? $current[$key] : [];
                                            $subKeys = array_unique(array_merge(array_keys($previousGroup), array_keys($currentGroup)));
                                            foreach ($subKeys as $subKey) {
                                                $label = $key === 'feed'
                                                    ? ($feedLabelMap[$subKey] ?? ucfirst(str_replace('_', ' ', $subKey)))
                                                    : ($healthLabelMap[$subKey] ?? ucfirst(str_replace('_', ' ', $subKey)));
                                                $addRow($label, $previousGroup[$subKey] ?? 0, $currentGroup[$subKey] ?? 0);
                                            }
                                        }

                                        $addRow($generalLabelMap['cage_price'], $previous['cage_price'] ?? 0, $current['cage_price'] ?? 0);
                                        $addRow($generalLabelMap['hpp_price'], $previous['hpp_price'] ?? 0, $current['hpp_price'] ?? 0);
                                        $addRow($generalLabelMap['sell_price'], $previous['sell_price'] ?? 0, $current['sell_price'] ?? 0);
                                    }

                                    $differences = array_filter($detailRows, static function ($row) {
                                        return (int) round($row['before']) !== (int) round($row['after']);
                                    });
                                ?>
                                <?php
                                    $rowClass = $isDeleted ? 'log-row-updated' : (empty($differences) ? 'log-row-stable' : 'log-row-updated');
                                    $historyId = $history->id ?? ($current['history_id'] ?? null);
                                    $businessTypeValue = $history->business_type ?? ($current['business_type'] ?? null);
                                    $businessTypeKey = $businessTypeValue ?? '';
                                    $businessType = $businessTypeValue ? ucfirst($businessTypeValue) : '-';
                                    switch ($businessTypeKey) {
                                        case 'penggemukan':
                                            $businessBadgeClass = 'bg-success';
                                            break;
                                        case 'breeding':
                                            $businessBadgeClass = 'bg-info text-dark';
                                            break;
                                        default:
                                            $businessBadgeClass = 'bg-secondary';
                                    }
                                    $displayLivestockName = $livestock->name ?? ($current['livestock_name'] ?? null);
                                    $displayLivestockVid = $livestock->vid ?? ($current['visual_id'] ?? null);
                                ?>
                                <tr class="<?= $rowClass ?> align-middle">
                                    <td><?= Html::encode($wibFormatter->asDatetime($log->changed_at, 'php:d M Y H:i:s')) ?> WIB</td>
                                    <td>
                                        <?php if ($historyId): ?>
                                            <span class="badge rounded-pill bg-dark">#<?= Html::encode($historyId) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                    <?php if ($displayLivestockName): ?>
                                        <div class="fw-semibold"><?= Html::encode($displayLivestockName) ?></div>
                                        <?php if ($displayLivestockVid): ?>
                                            <small class="text-muted">VID: <?= Html::encode($displayLivestockVid) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $businessBadgeClass ?>">
                                            <?= Html::encode($businessType) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($isDeleted): ?>
                                            <span class="badge bg-danger">History dihapus</span>
                                        <?php elseif (empty($differences)): ?>
                                            <span class="badge bg-success">Stabil</span>
                                        <?php else: ?>
                                            <ul class="log-change-list">
                                                <?php foreach ($differences as $row): ?>
                                                    <li class="mb-1">
                                                        <span class="badge bg-secondary text-white me-2"><?= Html::encode($row['label']) ?></span>
                                                        <span class="text-danger fw-semibold"><?= $formatCurrency($row['before']) ?></span>
                                                        <i class="bi bi-arrow-right-short mx-1 text-muted"></i>
                                                        <span class="text-success fw-semibold"><?= $formatCurrency($row['after']) ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($history && !$isDeleted): ?>
                                            <a href="<?= yii\helpers\Url::to(['history-log', 'id' => $history->id]) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>Detail History
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center" style="width: 1%; white-space: nowrap;">
                                        <?php if ($isDeleted): ?>
                                            <span class="text-muted">-</span>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#logAllDetail<?= $log->id ?>">
                                                <i class="bi bi-list-ul me-1"></i>Komponen
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php ob_start(); ?>
                                <div class="modal fade" id="logAllDetail<?= $log->id ?>" tabindex="-1" aria-labelledby="logAllDetailLabel<?= $log->id ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="logAllDetailLabel<?= $log->id ?>">Detail Komponen Harga</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Komponen</th>
                                                                <th class="text-end">Sebelum</th>
                                                                <th class="text-end">Sesudah</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($detailRows as $detail): ?>
                                                                <tr>
                                                                    <td><?= Html::encode($detail['label']) ?></td>
                                                                    <td class="text-end text-danger fw-semibold"><?= $formatCurrency($detail['before']) ?></td>
                                                                    <td class="text-end text-success fw-semibold"><?= $formatCurrency($detail['after']) ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php $detailModals[] = ob_get_clean(); ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?= implode('', $detailModals) ?>
            <?php endif; ?>
        </div>
    </div>
</div>
