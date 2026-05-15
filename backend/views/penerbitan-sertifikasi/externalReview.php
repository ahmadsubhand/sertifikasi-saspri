<?php

use common\enums\CertificateLevel;
use common\enums\IndicatorStatus;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Certification $certification */
/** @var common\models\IndicatorGroup $current_root_group */
/** @var common\models\IndicatorGroup[] $current_child_groups */
/** @var int $page */
/** @var int $total_pages */

$this->title = 'Penerbitan Sertifikasi - Penilaian Final';

// Score Calculation Logic (Temporary)
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

<div class="d-flex flex-column align-items-start gap-3">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="text-muted d-flex align-items-center gap-2 mb-2">
        <span class="badge bg-primary">Admin Nasional</span>
        <div>
            Sertifikasi SASPRI-K <?= Html::encode($certification->saspriK->region_name) ?> tingkat <?= Html::encode(CertificateLevel::list()[$certification->level] ?? '-') ?>
        </div>
    </div>

    <div class="card p-3 d-flex flex-column gap-2 w-100">
        <h2><?= Html::encode($current_root_group->code) ?>. <?= Html::encode($current_root_group->label) ?> (<?= Html::encode($current_root_group->weight) ?>%)</h2>
        
        <?php
            $saveAction = Url::to(['simpan-sementara-penerbitan-sertifikasi', 'certification_id' => $certification->id, 'page' => $page]);
            $finalizeAction = Url::to(['finalisasi-penerbitan-sertifikasi', 'certification_id' => $certification->id]);
        ?>
        <form id="penerbitan-sertifikasi-form" action="<?= $saveAction ?>" method="post">
            <?= Html::hiddenInput(\Yii::$app->request->csrfParam, \Yii::$app->request->csrfToken) ?>
            
            <table class="table align-middle">
                <thead>
                    <tr class="text-center">
                        <th scope="col" style="width: 50px;">No</th>
                        <th scope="col" class="text-start">Kriteria</th>
                        <th scope="col" style="width: 100px;">Skor Mandiri</th>
                        <th scope="col" style="width: 100px;">Skor Sebaya</th>
                        <th scope="col" style="width: 100px;">Status</th>
                        <th scope="col" style="width: 250px;">Penilaian Final</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($current_child_groups as $subGroup): ?>
                        <tr class="table-light">
                            <th scope="row" class="text-center"><?= Html::encode($subGroup->code) ?></th>
                            <td colspan="5" class="fw-bold">
                                <?= Html::encode($subGroup->label) ?> [<?= Html::encode($subGroup->weight) ?>%]
                            </td>
                        </tr>
                        
                        <?php if (isset($subGroup->indicators)): ?>
                            <?php foreach ($subGroup->indicators as $index => $indicator): ?>
                                <?php 
                                    $scoreModel = $indicator->indicatorScores[0] ?? null;
                                    $selfScore = $scoreModel->self_team_score ?? 0;
                                    $peerScore = $scoreModel->peer_team_score ?? 0;
                                    $finalScore = $scoreModel->final_score ?? 0;
                                    $currentStatus = $scoreModel->status ?? null;
                                    $statusLabel = IndicatorStatus::list()[$currentStatus] ?? '-';
                                ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td><?= Html::encode($indicator->label) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark self-score-display" data-indicator-id="<?= $indicator->id ?>">
                                            <?= $selfScore ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark peer-score-display" data-indicator-id="<?= $indicator->id ?>">
                                            <?= $peerScore ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $currentStatus === 'identical' ? 'bg-success' : ($currentStatus === 'agreed' ? 'bg-info' : 'bg-warning') ?> status-display" 
                                              data-indicator-id="<?= $indicator->id ?>" 
                                              data-status="<?= $currentStatus ?>">
                                            <?= Html::encode($statusLabel) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <select 
                                            name="indicator_scores[<?= $indicator->id ?>][final_score]" 
                                            class="form-select score-select final-score-select" 
                                            data-subgroup-id="<?= $subGroup->id ?>"
                                            data-indicator-id="<?= $indicator->id ?>"
                                        >
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
                            <td colspan="2"></td>
                            <td colspan="3" class="text-end fw-bold">Nilai Sub-total <?= Html::encode($subGroup->code) ?></td>
                            <td class="text-center fw-bold text-primary subgroup-weighted-display" id="subgroup-weighted-<?= $subGroup->id ?>" data-weight="<?= $subGroup->weight ?>"><?= number_format($subGroupResults[$subGroup->id]['weighted'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <tr class="table-secondary">
                        <th scope="row"></th>
                        <th colspan="4" class="text-end">Nilai Total <?= Html::encode($current_root_group->code) ?> (<?= Html::encode($current_root_group->label) ?>)</th>
                        <th class="text-center text-success fs-5" id="group-total-score" data-root-weight="<?= $current_root_group->weight ?>"><?= number_format($finalGroupScore, 2) ?></th>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>

    <div class="d-flex justify-content-between w-100 mt-3">
        <button type="submit" id="btn-save-temp" form="penerbitan-sertifikasi-form" name="target_page" value="<?= $page ?>" class="btn btn-outline-primary">Simpan sementara</button>
        
        <div class="d-flex gap-2">
            <?php if ($page > 1): ?>
                <button type="submit" id="btn-prev" form="penerbitan-sertifikasi-form" name="target_page" value="<?= $page - 1 ?>" class="btn btn-secondary">Sebelumnya</button>
            <?php else: ?>
                <button class="btn btn-secondary" disabled>Sebelumnya</button>
            <?php endif; ?>

            <?php if ($page < $total_pages): ?>
                <button type="submit" id="btn-next" form="penerbitan-sertifikasi-form" name="target_page" value="<?= $page + 1 ?>" class="btn btn-primary">Berikutnya</button>
            <?php else: ?>
                <button type="submit" id="btn-finish" form="penerbitan-sertifikasi-form" name="finish" value="1" class="btn btn-success">Selesai Review</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
    // Handle form action changes
    $('#btn-finish').on('click', function() {
        $('#penerbitan-sertifikasi-form').attr('action', '$finalizeAction');
    });

    $('#btn-save-temp, #btn-prev, #btn-next').on('click', function() {
        $('#penerbitan-sertifikasi-form').attr('action', '$saveAction');
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
