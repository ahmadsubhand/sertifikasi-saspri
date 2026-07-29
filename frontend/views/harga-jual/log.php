<?php
/** @var yii\web\View $this */
/** @var common\models\HistoryChangeLog[] $logs */
/** @var common\models\History $history */

use yii\helpers\Html;
use yii\helpers\Json;

$this->title = 'Log Perubahan Harga Jual';
$this->params['breadcrumbs'][] = $this->title;

$formatter = \Yii::$app->formatter;
$formatCurrency = static function ($value) use ($formatter) {
    $numeric = is_numeric($value) ? (float) $value : 0;
    return $formatter->asCurrency($numeric, 'IDR');
};
$wibFormatter = clone \Yii::$app->formatter;
$wibFormatter->timeZone = 'Asia/Jakarta';

$this->registerCss(<<<CSS
.history-log-table .log-change-list {
    margin: 0;
    padding-left: 0;
    list-style: none;
}

.history-log-table .log-change-list li {
    margin-bottom: 0.35rem;
}

.history-log-table .badge {
    font-size: 0.85rem;
}
CSS);
?>

<div class="container-fluid px-4 pt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Log Perubahan untuk: <?= Html::encode($history->livestock->name ?? '-') ?></h5>
                <div>
                    <span class="badge bg-secondary">Jenis: <?= Html::encode(ucfirst($history->business_type)) ?></span>
                    <span class="badge bg-info text-dark">ID Riwayat: <?= Html::encode($history->id) ?></span>
                </div>
            </div>
            <p class="text-muted">Menampilkan perubahan komponen biaya pada history ini sejak pertama kali dibuat.</p>
<?php $formatter = \Yii::$app->formatter; ?>

<?php if (empty($logs)): ?>
                <p class="text-muted">Belum ada perubahan yang terekam.</p>
            <?php else: ?>
                <div class="table-responsive history-log-table">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Waktu Perubahan</th>
                                <th>Nama Sapi</th>
                                <th>Jenis Usaha</th>
                                <th>Perubahan</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
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
                            <?php foreach ($logs as $log): ?>
                                <?php
                                    $history = $log->history;
                                    $livestock = $history ? $history->livestock : null;
                                    $previous = Json::decode($log->previous_data) ?: [];
                                    $current  = Json::decode($log->new_data) ?: [];

                                    $detailRows = [];

                                    $addRow = static function ($label, $before, $after) use (&$detailRows) {
                                        $detailRows[] = [
                                            'label' => $label,
                                            'before' => $before ?? 0,
                                            'after'  => $after ?? 0,
                                        ];
                                    };

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

                                    $rows = $detailRows;

                                    $hasDifference = array_filter($rows, static function ($row) {
                                        return (int) round($row['before']) !== (int) round($row['after']);
                                    });
                                ?>
                                <tr>
                                    <td><?= Html::encode($wibFormatter->asDatetime($log->changed_at, 'php:d M Y H:i:s')) ?> WIB</td>
                                    <td><?= Html::encode($livestock ? $livestock->name : '-') ?></td>
                                    <td><?= Html::encode($history ? ucfirst($history->business_type) : '-') ?></td>
                                    <td>
                                        <?php if (empty($hasDifference)): ?>
                                            <span class="text-muted">Tidak ada perubahan nilai.</span>
                                        <?php else: ?>
                                            <ul class="log-change-list">
                                                <?php foreach ($rows as $row): ?>
                                                    <?php if ((int) round($row['before']) === (int) round($row['after'])) continue; ?>
                                                    <li>
                                                        <span class="badge bg-secondary text-white me-2"><?= Html::encode($row['label']) ?></span>
                                                        <span class="text-danger fw-semibold"><?= $formatCurrency($row['before']) ?></span>
                                                        <i class="bi bi-arrow-right-short mx-1 text-muted"></i>
                                                        <span class="text-success fw-semibold"><?= $formatCurrency($row['after']) ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center" style="width: 1%; white-space: nowrap;">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#logDetail<?= $log->id ?>">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                                <?php
                                    ob_start();
                                ?>
                                <div class="modal fade" id="logDetail<?= $log->id ?>" tabindex="-1" aria-labelledby="logDetailLabel<?= $log->id ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="logDetailLabel<?= $log->id ?>">Detail Komponen Harga</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
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
                                <?php
                                    $detailModals[] = ob_get_clean();
                                ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?= implode('', $detailModals) ?>
            <?php endif; ?>
        </div>
    </div>
</div>
