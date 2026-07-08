<?php

use common\enums\CertificateLevel;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\SaspriK $saspri_k */
/** @var common\models\SaspriKDocument[] $documents */
/** @var common\models\User $coordinator */
/** @var common\models\Certification $certification */
/** @var common\models\IndicatorGroup $current_root_group */
/** @var common\models\IndicatorGroup[] $current_child_groups */
/** @var int $page */
/** @var int $total_pages */

$this->title = 'Detail Permintaan Pendaftaran Wali';

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
$shingles = [
    'number_of_active_members' => 'Orang',
    'total_livestock_count' => 'Ekor',
    'breeding_livestock_count' => 'Ekor',
    'productive_heifer_count' => 'Ekor',
];

// Score Calculation Logic (from externalReview.php)
$groupTotalScore = 0;
$subGroupResults = [];

foreach ($current_child_groups as $subGroup) {
    $subGroupSum = 0;
    $indicatorCount = count($subGroup->indicators);

    foreach ($subGroup->indicators as $indicator) {
        $score = $indicator->indicatorScores[0]->final_score ?? 0;
        $subGroupSum += $score;
    }

    $subGroupAverage = $indicatorCount > 0 ? ($subGroupSum / $indicatorCount) : 0;
    $subGroupWeighted = $subGroupAverage * ($subGroup->weight / 100);
    $subGroupResults[$subGroup->id] = [
        'sum' => $subGroupSum,
        'weighted' => $subGroupWeighted
    ];
    $groupTotalScore += $subGroupWeighted;
}

$finalGroupScore = $groupTotalScore * ($current_root_group->weight / 100);

?>

<div class="page-cont w-100 h-100 p-md-3 d-flex flex-column gap-3">
    <div class="d-flex align-items-center">
        <a href="<?= Url::to(['index']) ?>" class="text-decoration-none text-black fs-5 me-3">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h3 class="fw-bold mb-0"><?= Html::encode($this->title) ?></h3>
    </div>

    <div class="row">
        <!-- Identitas SASPRI-K -->
        <div class="col-md-8">
            <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border h-100">
                <div class="px-md-4 px-2">
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

        <!-- Dokumen Terkait -->
        <div class="col-md-4">
            <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border h-100 mt-3 mt-md-0">
                <div class="px-md-3 px-1">
                    <p class="fw-bold h5 mb-3 border-bottom pb-2">Dokumen Terkait</p>
                    <?php if (!empty($documents)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($documents as $doc): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="small text-muted"><?= Html::encode($doc->type) ?></span>
                                    <a href="<?= Yii::$app->params['frontendUrl'] . Html::encode($doc->url) ?>"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-primary py-0">
                                        <i class="fa-solid fa-file-lines me-1"></i> Lihat
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">Tidak ada dokumen terlampir.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Penilaian Sertifikat -->

    <div class="bg-white px-3 py-4 rounded-2 shadow border-1 border d-flex flex-column gap-2 w-100 mt-3">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
            <h5 class="fw-bold mb-1">Penilaian Sertifikat SASPRI-K</h5>
            <div class="d-flex align-items-center gap-2 flex-column flex-md-row">
                <span class="badge bg-primary">Tingkat <?= Html::encode(CertificateLevel::list()[$certification->level] ?? '-') ?></span>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalGantiLevel">
                    <i class="fa-solid fa-pen-to-square"></i> Ganti
                </button>
            </div>
        </div>
        <h5 class="fw-bold mb-2"><?= Html::encode($current_root_group->code) ?>. <?= Html::encode($current_root_group->label) ?> (<?= Html::encode($current_root_group->weight) ?>%)</h5>

        <?php
        $saveAction = Url::to(['simpan-sementara-permintaan-pendaftaran', 'saspri_k_id' => $saspri_k->id, 'page' => $page]);
        $approveAction = Url::to(['daftarkan-wali', 'saspri_k_id' => $saspri_k->id]);
        ?>
        <div class="mobile-scroll">

            <form id="pendaftaran-wali-form" action="<?= $saveAction ?>" method="post">
                <?= Html::hiddenInput(\Yii::$app->request->csrfParam, \Yii::$app->request->csrfToken) ?>
                <input type="hidden" name="action" id="form-action-input" value="approve">

                <table class="table align-middle">
                    <thead>
                        <tr class="text-center">
                            <th scope="col" style="width: 50px;">No</th>
                            <th scope="col" class="text-start">Kriteria</th>
                            <th scope="col" style="width: 250px;">Penilaian Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($current_child_groups as $subGroup): ?>
                            <tr class="table-light">
                                <td scope="row" class="text-center"><?= Html::encode($subGroup->code) ?></th>
                                <td colspan="5" class="fw-bold">
                                    <?= Html::encode($subGroup->label) ?> [<?= Html::encode($subGroup->weight) ?>%]
                                </td>
                            </tr>

                            <?php if (isset($subGroup->indicators)): ?>
                                <?php foreach ($subGroup->indicators as $index => $indicator): ?>
                                    <?php
                                    $scoreModel = $indicator->indicatorScores[0] ?? null;
                                    $finalScore = $scoreModel->final_score ?? 0;
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $index + 1 ?></td>
                                        <td><?= Html::encode($indicator->label) ?></td>
                                        <td>
                                            <select
                                                name="indicator_scores[<?= $indicator->id ?>][final_score]"
                                                class="form-select score-select final-score-select"
                                                data-subgroup-id="<?= $subGroup->id ?>"
                                                data-indicator-id="<?= $indicator->id ?>">
                                                <option value="0">Pilih Penilaian</option>

                                                <?php foreach ($indicator->indicatorOptions as $option): ?>
                                                    <?php
                                                    $selected = ($finalScore == $option->weight) ? 'selected' : '';
                                                    ?>

                                                    <option value="<?= $option->weight ?>" <?= $selected ?>>
                                                        <?= Html::encode($option->label) ?> (<?= $option->weight ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <tr>
                                <td colspan="1"></td>
                                <td colspan="1" class="text-end fw-bold">Nilai Sub-total <?= Html::encode($subGroup->code) ?></td>
                                <td class="text-center fw-bold s-color-main subgroup-weighted-display" id="subgroup-weighted-<?= $subGroup->id ?>" data-weight="<?= $subGroup->weight ?>"><?= number_format($subGroupResults[$subGroup->id]['weighted'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <tr class="table-secondary">
                            <th scope="row"></th>
                            <th class="text-end">Nilai Total <?= Html::encode($current_root_group->code) ?> (<?= Html::encode($current_root_group->label) ?>)</th>
                            <th class="text-center text-success fs-5" id="group-total-score" data-root-weight="<?= $current_root_group->weight ?>"><?= number_format($finalGroupScore, 2) ?></th>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>

        <div class="d-flex justify-content-between w-100 mt-3 flex-column flex-md-row gap-2 gap-md-0">
            <div class="d-flex align-items-center gap-2 ms-auto ms-md-0">
                <button type="submit" id="btn-save-temp" form="pendaftaran-wali-form" name="target_page" value="<?= $page ?>" class="btn btn-sm s-btn-main py-2">Simpan sementara</button>
            </div>
            <div class="ms-auto ms-md-0">
                <nav aria-label="pagination">
                    <ul class="pagination mb-0">
                        <li class="page-item <?php echo $page > 1 ? '' : 'disabled' ?>">
                            <a class="page-link s-btn-sec">
                                <button type="submit" id="btn-prev" form="pendaftaran-wali-form" name="target_page" value="<?= $page - 1 ?>" class="btn-none">Sebelumnya</button>
                            </a>
                        </li>
                        <?php foreach (range(1, $total_pages) as $inPage): ?>
                            <li class="page-item">
                                <a class="page-link <?= $inPage == $page ? 'text-primary' : 'text-secondary' ?>">
                                    <button type="submit" id="btn-pagin" form="pendaftaran-wali-form" name="target_page" value="<?= $inPage ?>" class="btn-none"><?= $inPage ?></button>
                                </a>
                            </li>
                        <?php endforeach ?>
                        <li class="page-item <?php echo $page < $total_pages ? '' : 'disabled' ?>">
                            <a class="page-link s-btn-main <?php echo $page == $total_pages ? 'disabled' : '' ?>">
                                <button type="submit" id="btn-next" form="pendaftaran-wali-form" name="target_page" value="<?= $page + 1 ?>" class="btn-none">Berikutnya</button>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Final Actions -->
    <div class="bg-white px-3 py-4 rounded-2 shadow border-1 border mt-3 mb-5">
        <div class="d-flex justify-content-center gap-3 flex-column-reverse flex-md-row">
            <button type="button" class="btn btn-danger px-5 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="fa-solid fa-xmark me-2"></i> Tolak Pendaftaran
            </button>
            <?php if ($page == $total_pages): ?>
                <button type="submit" id="btn-approve" form="pendaftaran-wali-form" class="btn btn-success px-5 py-2 fw-bold" onclick="return confirm('Apakah Anda yakin ingin menyetujui pendaftaran wali ini?')">
                    <i class="fa-solid fa-check me-2"></i> Setujui Pendaftaran
                </button>
            <?php else: ?>
                <div class="text-muted small italic width-acc">Selesaikan penilaian pada seluruh kategori indikator untuk mengaktifkan tombol Setuju.</div>
            <?php endif; ?>


        </div>
    </div>
</div>

<!-- Modal Ganti Level -->
<div class="modal fade" id="modalGantiLevel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= Html::beginForm(['ganti-level-sertifikat', 'saspri_k_id' => $saspri_k->id], 'post') ?>
            <div class="modal-header">
                <h5 class="modal-title">Ganti Tingkat Sertifikat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih Tingkat Baru</label>
                    <?= Html::dropDownList('Tingkat', $certification->level, CertificateLevel::list(), ['class' => 'form-select']) ?>
                </div>
                <div class="alert alert-warning small">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Perhatian: Penilaian yang telah dilakukan pada Tingkat yang lama akan dihapus.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>


<div class="modal fade modal-lg" id="rejectModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <?= $this->render('/component/_reject_modal', [
            'step' => 'Pendaftaran Wali',
            'url' => ['daftarkan-wali', 'saspri_k_id' => $saspri_k->id]
            ]); ?>
        </div>
        </div>
    </div>
</div>

<?php
$this->registerJs(
    <<<JS
    // Handle form action changes
    $('#btn-approve').on('click', function() {
        $('#pendaftaran-wali-form').attr('action', '$approveAction');
        $('#form-action-input').val('approve');
    });

    $('#btn-save-temp, #btn-prev, #btn-next, #btn-pagin').on('click', function() {
        $('#pendaftaran-wali-form').attr('action', '$saveAction');
    });

    function setInitialDefaultScores() {
        $('.final-score-select').each(function() {
            var indicatorId = $(this).data('indicator-id');
            var currentVal = $(this).val();
            
            // Only set default if it's currently 0 (unselected)
            if (currentVal === '0') {
                var status = $('.status-display[data-indicator-id="' + indicatorId + '"]').data('status');
                var peerScore = $('.peer-score-display[data-indicator-id="' + indicatorId + '"]').text().trim();
                
                if (status === 'identical' || status === 'agreed') {
                    $(this).val(peerScore).trigger('change');
                }
            }
        });
    }

    $('.score-select').on('change', function() {
        var subgroupId = $(this).data('subgroup-id');
        var subGroupSum = 0;
        var scoreSelects = $('.score-select[data-subgroup-id="' + subgroupId + '"]');
        var indicatorCount = scoreSelects.length;
        
        scoreSelects.each(function() {
            subGroupSum += parseFloat($(this).val()) || 0;
        });
        
        var subGroupWeight = parseFloat($('#subgroup-weighted-' + subgroupId).data('weight')) || 0;
        var subGroupAverage = indicatorCount > 0 ? (subGroupSum / indicatorCount) : 0;
        var subGroupWeighted = subGroupAverage * (subGroupWeight / 100);
        
        $('#subgroup-weighted-' + subgroupId).text(subGroupWeighted.toFixed(2));
        
        // Calculate Group Total
        var groupTotalWeightedSum = 0;
        $('.subgroup-weighted-display').each(function() {
            groupTotalWeightedSum += parseFloat($(this).text()) || 0;
        });
        
        var rootGroupWeight = parseFloat($('#group-total-score').data('root-weight')) || 0;
        var finalGroupScore = groupTotalWeightedSum * (rootGroupWeight / 100);
        
        $('#group-total-score').text(finalGroupScore.toFixed(2));
    });

    // Initialize defaults on page load
    setInitialDefaultScores();
JS
);
?>