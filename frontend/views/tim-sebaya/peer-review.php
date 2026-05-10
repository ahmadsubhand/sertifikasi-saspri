<?php

use common\enums\TeamRole;
use common\models\PeerTeamMember;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Certification $certification */
/** @var common\models\IndicatorGroup[] $rootGroups */
/** @var common\models\IndicatorGroup $currentRootGroup */
/** @var common\models\Indicator[] $indicators */
/** @var common\models\IndicatorScore[] $scores */
/** @var int $page */
/** @var int $totalPages */

$this->title = 'Peer Review SASPRI-K';

// Group indicators by subgroup ID
$indicatorsByGroup = [];
foreach ($indicators as $indicator) {
    $indicatorsByGroup[$indicator->indicator_group_id][] = $indicator;
}

// Fetch immediate children of the current root group
$subGroups = $currentRootGroup->getIndicatorGroups()->orderBy(['order' => SORT_ASC])->all();

// Check if user is Leader
$isLeader = PeerTeamMember::find()
    ->where([
        'certification_id' => $certification->id,
        'user_id' => \Yii::$app->user->id,
        'role' => TeamRole::LEADER
    ])
    ->exists();

// Score Calculation Logic (Temporary)
$groupTotalScore = 0;
$subGroupResults = [];

foreach ($subGroups as $subGroup) {
    $subGroupSum = 0;
    if (isset($indicatorsByGroup[$subGroup->id])) {
        foreach ($indicatorsByGroup[$subGroup->id] as $indicator) {
            $score = isset($scores[$indicator->id]) ? $scores[$indicator->id]->peer_team_score : 0;
            $subGroupSum += $score;
        }
    }

    $subGroupWeighted = $subGroupSum * ($subGroup->weight / 100);
    $subGroupResults[$subGroup->id] = [
        'sum' => $subGroupSum,
        'weighted' => $subGroupWeighted
    ];
    $groupTotalScore += $subGroupWeighted;
}

$finalGroupScore = $groupTotalScore * ($currentRootGroup->weight / 100);

?>

<div class="d-flex flex-column align-items-start gap-3">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="text-muted d-flex align-items-center gap-2 mb-2">
        <?php if ($isLeader): ?>
            <span class="badge bg-info">Ketua Tim Sebaya</span>
        <?php else: ?>
            <span class="badge bg-secondary">Anggota Tim Sebaya</span>
        <?php endif; ?>
        <div>
            Sertifikasi SASPRI-K <?= Html::encode($certification->saspriK->district->name) ?> tingkat <?= Html::encode(ucfirst($certification->level)) ?>
        </div>
    </div>

    <div class="card p-3 d-flex flex-column gap-2 w-100">
        <h2><?= Html::encode($currentRootGroup->code) ?>. <?= Html::encode($currentRootGroup->label) ?> (<?= Html::encode($currentRootGroup->weight) ?>%)</h2>
        
        <?php
            $saveAction = Url::to(['simpan-sementara-peer-review', 'id' => $certification->id, 'page' => $page]);
            $finalizeAction = Url::to(['finalisasi-peer-review', 'id' => $certification->id]);
        ?>
        <form id="peer-review-form" action="<?= $saveAction ?>" method="post">
            <?= Html::hiddenInput(\Yii::$app->request->csrfParam, \Yii::$app->request->csrfToken) ?>
            
            <table class="table align-middle">
                <thead>
                    <tr class="text-center">
                        <th scope="col" style="width: 50px;">No</th>
                        <th scope="col" class="text-start">Kriteria</th>
                        <th scope="col" style="width: 100px;">Skor Mandiri</th>
                        <th scope="col" style="width: 100px;">Bukti Mandiri</th>
                        <th scope="col" style="width: 250px;">Penilaian Sebaya</th>
                        <th scope="col" style="width: 150px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subGroups as $subGroup): ?>
                        <tr class="table-light">
                            <th scope="row" class="text-center"><?= Html::encode($subGroup->code) ?></th>
                            <td colspan="5" class="fw-bold">
                                <?= Html::encode($subGroup->label) ?> [<?= Html::encode($subGroup->weight) ?>%]
                            </td>
                        </tr>
                        
                        <?php if (isset($indicatorsByGroup[$subGroup->id])): ?>
                            <?php foreach ($indicatorsByGroup[$subGroup->id] as $index => $indicator): ?>
                                <?php 
                                    $selfScore = isset($scores[$indicator->id]) ? $scores[$indicator->id]->self_team_score : 0;
                                    $peerScore = isset($scores[$indicator->id]) ? $scores[$indicator->id]->peer_team_score : 0;
                                    $currentStatus = isset($scores[$indicator->id]) ? $scores[$indicator->id]->status : null;
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
                                        <?php if (isset($scores[$indicator->id]) && $scores[$indicator->id]->evidence_url): ?>
                                            <a href="<?= Url::to($scores[$indicator->id]->evidence_url) ?>" target="_blank" class="btn btn-sm btn-outline-info">Lihat</a>
                                        <?php else: ?>
                                            <span class="text-muted small">Tidak ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <select 
                                            name="IndicatorScore[<?= $indicator->id ?>][peer_team_score]" 
                                            class="form-select score-select" 
                                            data-subgroup-id="<?= $subGroup->id ?>"
                                            data-indicator-id="<?= $indicator->id ?>"
                                        >
                                            <option value="0">Pilih Penilaian</option>

                                            <?php foreach ($indicator->indicatorOptions as $option): ?>
                                                <?php
                                                    $selected = ($peerScore == $option->weight) ? 'selected' : '';
                                                ?>

                                                <option value="<?= $option->weight ?>" <?= $selected ?>>
                                                    <?= Html::encode($option->label) ?> (<?= $option->weight ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select 
                                            name="IndicatorScore[<?= $indicator->id ?>][status]" 
                                            class="form-select status-select" 
                                            data-indicator-id="<?= $indicator->id ?>"
                                        >
                                            <option value="">Pilih Status</option>
                                            <?php foreach (\common\enums\IndicatorStatus::list() as $val => $label): ?>
                                                <option value="<?= $val ?>" <?= ($currentStatus == $val) ? 'selected' : '' ?>>
                                                    <?= $label ?>
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
                        <th colspan="4" class="text-end">Nilai Total <?= Html::encode($currentRootGroup->code) ?> (<?= Html::encode($currentRootGroup->label) ?>)</th>
                        <th class="text-center text-success fs-5" id="group-total-score" data-root-weight="<?= $currentRootGroup->weight ?>"><?= number_format($finalGroupScore, 2) ?></th>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>

    <div class="d-flex justify-content-between w-100 mt-3">
        <button type="submit" id="btn-save-temp" form="peer-review-form" name="target_page" value="<?= $page ?>" class="btn btn-outline-primary">Simpan sementara</button>
        
        <div class="d-flex gap-2">
            <?php if ($page > 1): ?>
                <button type="submit" id="btn-prev" form="peer-review-form" name="target_page" value="<?= $page - 1 ?>" class="btn btn-secondary">Sebelumnya</button>
            <?php else: ?>
                <button class="btn btn-secondary" disabled>Sebelumnya</button>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
                <button type="submit" id="btn-next" form="peer-review-form" name="target_page" value="<?= $page + 1 ?>" class="btn btn-primary">Berikutnya</button>
            <?php else: ?>
                <button type="submit" id="btn-finish" form="peer-review-form" name="finish" value="1" class="btn btn-success">Selesai Review</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
    // Handle form action changes
    $('#btn-finish').on('click', function() {
        $('#peer-review-form').attr('action', '$finalizeAction');
    });

    $('#btn-save-temp, #btn-prev, #btn-next').on('click', function() {
        $('#peer-review-form').attr('action', '$saveAction');
    });

    function updateStatus(indicatorId) {
        var selfScore = parseFloat(
            $('.self-score-display[data-indicator-id="' + indicatorId + '"]')
                .text()
                .trim()
        ) || 0;

        var peerScoreSelect = $('.score-select[data-indicator-id="' + indicatorId + '"]');
        var peerScore = parseFloat(peerScoreSelect.val()) || 0;

        var statusSelect = $('.status-select[data-indicator-id="' + indicatorId + '"]');

        var hiddenInputClass = 'hidden-status-' + indicatorId;

        // cleanup hidden input lama
        $('.' + hiddenInputClass).remove();

        // Belum memilih skor
        if (peerScoreSelect.val() === '0') {
            statusSelect
                .prop('disabled', false)
                .val('');

            statusSelect
                .find('option[value="identical"]')
                .prop('disabled', false);

            return;
        }

        // Skor sama
        if (peerScore === selfScore) {
            statusSelect
                .val('identical')
                .prop('disabled', true);

            statusSelect.after(
                '<input type="hidden" class="' + hiddenInputClass + '" name="IndicatorScore[' + indicatorId + '][status]" value="identical">'
            );
        } else {
            statusSelect.prop('disabled', false);

            statusSelect
                .find('option[value="identical"]')
                .prop('disabled', true);

            // default hanya jika belum ada pilihan
            if (
                statusSelect.val() === '' ||
                statusSelect.val() === 'identical'
            ) {
                statusSelect.val('different');
            }
        }
    }

    // Initialize statuses on load
    $('.score-select').each(function() {
        updateStatus($(this).data('indicator-id'));
    });

    $('.score-select').on('change', function() {
        var indicatorId = $(this).data('indicator-id');
        updateStatus(indicatorId);

        var subgroupId = $(this).data('subgroup-id');
        var subGroupSum = 0;
        
        $('.score-select[data-subgroup-id="' + subgroupId + '"]').each(function() {
            subGroupSum += parseFloat($(this).val()) || 0;
        });
        
        var subGroupWeight = parseFloat($('#subgroup-weighted-' + subgroupId).data('weight')) || 0;
        var subGroupWeighted = subGroupSum * (subGroupWeight / 100);
        
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
JS
);
?>
