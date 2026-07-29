<?php
/** @var yii\web\View $this */
/** @var common\models\Livestock $livestock */
/** @var array $monthsSummary */
/** @var string $selectedKey */
/** @var array $dailyEntries */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Catatan ' . Html::encode($livestock->name);
$this->params['breadcrumbs'][] = ['label' => 'Tambah Sapi', 'url' => ['livestock/index']];
$this->params['breadcrumbs'][] = $this->title;

$selectedMonth = null;
$selectedYear = null;
if (!empty($selectedKey) && strpos($selectedKey, '-') !== false) {
    [$selectedYear, $selectedMonth] = explode('-', $selectedKey);
}

$formatWib = function ($value, $format = 'd M Y H:i') {
    if (empty($value)) {
        return '-';
    }
    $tzWib = new DateTimeZone('Asia/Jakarta');
    // If value is date-only, keep the date (no shift) but format nicely.
    if (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', trim($value))) {
        try {
            $dt = new DateTime($value, $tzWib);
            return $dt->format($format);
        } catch (\Exception $e) {
            return $value;
        }
    }
    // Treat stored datetime as UTC then convert to WIB
    try {
        $dt = new DateTime($value, new DateTimeZone('UTC'));
        $dt->setTimezone($tzWib);
    } catch (\Exception $e) {
        try {
            $dt = new DateTime($value);
            $dt->setTimezone($tzWib);
        } catch (\Exception $e) {
            return $value;
        }
    }
    return $dt->format($format) . ' WIB';
};

$this->registerCss(<<<CSS
.missing-note {
    border: 1px solid #dc3545 !important;
}
.missing-note-title {
    color: #dc3545;
}
CSS);
?>

<div class="page-content">
    <div class="row g-3 align-items-center mb-4">
        <div class="col-auto">
            <h3 class="mb-0">Catatan Harian</h3>
            <small class="text-muted">Sapi: <?= Html::encode($livestock->name) ?> (VID: <?= Html::encode($livestock->vid) ?>)</small>
        </div>
        <?php if (!empty($monthsSummary)): ?>
            <?php
                $monthLabels = [
                    1 => 'Januari',
                    2 => 'Februari',
                    3 => 'Maret',
                    4 => 'April',
                    5 => 'Mei',
                    6 => 'Juni',
                    7 => 'Juli',
                    8 => 'Agustus',
                    9 => 'September',
                    10 => 'Oktober',
                    11 => 'November',
                    12 => 'Desember',
                ];
                $availableYears = [];
                foreach ($monthsSummary as $summary) {
                    $availableYears[$summary['year']] = $summary['year'];
                }
                ksort($availableYears);
            ?>
            <div class="col-auto ms-auto">
                <form method="get" action="<?= Url::to(['note/livestock-notes']) ?>" class="row g-2 align-items-center">
                    <?= Html::hiddenInput('id', $livestock->id) ?>
                    <div class="col-auto">
                        <label class="form-label mb-0 small">Bulan</label>
                        <?= Html::dropDownList(
                            'month',
                            $selectedMonth ? (int) $selectedMonth : null,
                            $monthLabels,
                            [
                                'class' => 'form-select',
                                'prompt' => '--'
                            ]
                        ) ?>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-0 small">Tahun</label>
                        <?= Html::dropDownList(
                            'year',
                            $selectedYear ? (int) $selectedYear : null,
                            $availableYears,
                            [
                                'class' => 'form-select',
                                'prompt' => '--'
                            ]
                        ) ?>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary mt-4">Tampilkan</button>
                    </div>

                </form>
            </div>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">Ringkasan Bulan</h6>
                </div>
                <div class="list-group list-group-flush">
                    <?php if (empty($monthsSummary)): ?>
                        <div class="list-group-item text-muted small">Belum ada catatan.</div>
                    <?php else: ?>
                        <?php foreach ($monthsSummary as $key => $summary): ?>
                            <?php
                                $isActive = $key === $selectedKey;
                                $hasMissing = !empty($summary['hasMissing']);
                                $label = Html::encode($summary['label']);
                                $url = Url::to(['note/livestock-notes', 'id' => $livestock->id, 'month' => $summary['month'], 'year' => $summary['year']]);
                            ?>
                            <a href="<?= $url ?>"
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center<?= $isActive ? ' active' : '' ?>">
                                <span><?= $label ?></span>
                                <?php if ($hasMissing): ?>
                                    <span class="badge bg-danger">!</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <?= $selectedKey && isset($monthsSummary[$selectedKey])
                            ? Html::encode($monthsSummary[$selectedKey]['label'])
                            : 'Tidak ada catatan' ?>
                    </h6>
                    <?php if ($selectedKey && isset($monthsSummary[$selectedKey]) && !empty($monthsSummary[$selectedKey]['hasMissing'])): ?>
                        <span class="badge bg-danger">Ada hari tanpa catatan</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($dailyEntries)): ?>
                        <p class="text-muted mb-0">Belum ada catatan pada rentang waktu ini.</p>
                    <?php else: ?>
                        <?php $noteModals = []; ?>
                        <div class="list-group">
                            <?php foreach ($dailyEntries as $entry): ?>
                                <?php
                                    $dateLabel = \Yii::$app->formatter->asDate($entry['date'], 'php:d M Y');
                                    $notes = $entry['notes'];
                                    $isMissing = $entry['isMissing'];
                                    $createUrl = Url::to([
                                        'note/index',
                                        'livestock_id' => $livestock->id,
                                        'date' => $entry['date'],
                                    ]);
                                ?>
                                <div class="list-group-item<?= $isMissing ? ' missing-note' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-fill me-3">
                                            <h6 class="mb-1<?= $isMissing ? ' missing-note-title' : '' ?>"><?= Html::encode($dateLabel) ?></h6>
                                            <?php if ($isMissing): ?>
                                                <p class="text-muted mb-2 small">Belum ada catatan pada hari ini.</p>
                                            <?php else: ?>
                                                <div class="d-flex flex-column gap-3">
                                                    <?php foreach ($notes as $note): ?>
                                                        <?php
                                                            $modalId = 'noteDetail' . $note->id;
                                                            ob_start();
                                                        ?>
                                                        <div class="note-detail-modal">
                                                            <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>Label" aria-hidden="true">
                                                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="<?= $modalId ?>Label">Detail Catatan</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <?php foreach ($note->attributes as $attribute => $value): ?>
                                                                                <?php foreach ($note->attributeLabels() as $attrKey => $attrLabel): ?>
                                                                                    <?php if ($attrKey === $attribute): ?>
                                                                                        <?php
                                                                                            $label = $attrLabel;
                                                                                            if ($attrKey === 'livestock_id') {
                                                                                                $label = 'Nama Sapi';
                                                                                                $value = $note->livestock->name ?? '-';
                                                                                            } elseif (in_array($attrKey, ['note_date', 'created_at', 'updated_at'], true)) {
                                                                                                if ($attrKey === 'note_date') {
                                                                                                    $label = 'Tanggal Catatan';
                                                                                                    $value = $formatWib($value, 'd M Y');
                                                                                                } elseif ($attrKey === 'created_at') {
                                                                                                    $label = 'Dibuat Pada';
                                                                                                    $value = $formatWib($value);
                                                                                                } elseif ($attrKey === 'updated_at') {
                                                                                                    $label = 'Diperbarui Pada';
                                                                                                    $value = $formatWib($value);
                                                                                                }
                                                                                            } elseif (in_array($attrKey, ['costs', 'forage_costs', 'consentrate_costs', 'additive_costs', 'insemination', 'vaccine', 'vitamin', 'pregnancy_check', 'antibiotics', 'anthelmintic'], true)) {
                                                                                                $value = 'Rp. ' . number_format((float) $value, 0, ',', '.');
                                                                                            } elseif (in_array($attrKey, ['feed_weight', 'forage_weight', 'consentrate_weight', 'additive_weight'], true)) {
                                                                                                $value = $value . ' kg';
                                                                                            }
                                                                                        ?>
                                                                                <div class="mb-2">
                                                                                    <strong><?= Html::encode($label) ?>:</strong>
                                                                                    <span><?= Html::encode($value) ?></span>
                                                                                </div>
                                                                                    <?php endif; ?>
                                                                                <?php endforeach; ?>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <?= Html::a('Edit', ['note/update', 'id' => $note->id], [
                                                                                'class' => 'btn btn-primary',
                                                                            ]) ?>
                                                                            <?= Html::a('Hapus', ['note/delete', 'id' => $note->id], [
                                                                                'class' => 'btn btn-danger',
                                                                                'data' => [
                                                                                    'method' => 'delete',
                                                                                    'confirm' => 'Yakin ingin menghapus catatan ini?',
                                                                                ],
                                                                            ]) ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php $noteModals[] = ob_get_clean(); ?>

                                                        <div class="d-flex justify-content-between align-items-center note-entry">
                                                            <div class="me-3">
                                                                <div class="fw-semibold mb-1"><?= Html::encode($note->details ?? 'Catatan tanpa deskripsi') ?></div>
                                                                <small class="text-muted">Dibuat: <?= Html::encode($formatWib($note->created_at)) ?></small>
                                                            </div>
                                                                <button type="button"
                                                                        class="btn btn-sm btn-outline-primary"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#<?= $modalId ?>"
                                                                        style="margin-right: -6px;">
                                                                    Lihat Detail
                                                                </button>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-end">
                                            <?php if ($isMissing): ?>
                                                <a href="<?= $createUrl ?>" class="btn btn-sm btn-outline-danger">Buat Catatan</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?= implode('', $noteModals) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
