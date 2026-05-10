<?php

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

$this->title = 'Self Review SASPRI-K';

// Group indicators by subgroup ID
$indicatorsByGroup = [];
foreach ($indicators as $indicator) {
    $indicatorsByGroup[$indicator->indicator_group_id][] = $indicator;
}

// Fetch immediate children of the current root group
$subGroups = $currentRootGroup->getIndicatorGroups()->orderBy(['order' => SORT_ASC])->all();

// Score Calculation Logic (Temporary)
$groupTotalScore = 0;
$subGroupResults = [];

foreach ($subGroups as $subGroup) {
    $subGroupSum = 0;
    // We need all indicators in this subgroup and its descendants
    // For simplicity, let's assume indicators are directly in A1, A2, etc.
    // If there's more nesting, we might need a recursive sum.
    if (isset($indicatorsByGroup[$subGroup->id])) {
        foreach ($indicatorsByGroup[$subGroup->id] as $indicator) {
            $score = isset($scores[$indicator->id]) ? $scores[$indicator->id]->self_team_score : 0;
            // self_team_score stores the weight of the selected option? 
            // Or the ID of the option? Let's check common/models/IndicatorScore.php again.
            // Actually, usually it's the weight.
            $subGroupSum += $score;
        }
    }
    
    // "indikator dalam sub grup di total dan dikalikan dengan weight sub groupnya"
    $subGroupWeighted = $subGroupSum * ($subGroup->weight / 100);
    $subGroupResults[$subGroup->id] = [
        'sum' => $subGroupSum,
        'weighted' => $subGroupWeighted
    ];
    $groupTotalScore += $subGroupWeighted;
}

// "setelahnya jumlah tersebut dijumlah lagi dan dikalikan dengan weight dalam grup"
$finalGroupScore = $groupTotalScore * ($currentRootGroup->weight / 100);

?>

<div class="d-flex flex-column align-items-start gap-3">
    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Sertifikasi: <?= Html::encode($certification->level) ?> - <?= Html::encode($certification->saspriK->cooperative_name) ?></p>

    <div class="card p-3 d-flex flex-column gap-2 w-100">
        <h2><?= Html::encode($currentRootGroup->code) ?>. <?= Html::encode($currentRootGroup->label) ?> (<?= Html::encode($currentRootGroup->weight) ?>%)</h2>
        
        <form id="self-review-form" action="<?= Url::to(['simpan-sementara-self-review', 'id' => $certification->id, 'page' => $page]) ?>" method="post" enctype="multipart/form-data">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
            
            <table class="table align-middle">
                <thead>
                    <tr class="text-center">
                        <th scope="col" style="width: 50px;">No</th>
                        <th scope="col" class="text-start">Kriteria</th>
                        <th scope="col">Penilaian</th>
                        <th scope="col" style="width: 250px;">Bukti</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subGroups as $subGroup): ?>
                        <tr class="table-light">
                            <th scope="row" class="text-center"><?= Html::encode($subGroup->code) ?></th>
                            <td colspan="3" class="fw-bold">
                                <?= Html::encode($subGroup->label) ?> [<?= Html::encode($subGroup->weight) ?>%]
                            </td>
                        </tr>
                        
                        <?php if (isset($indicatorsByGroup[$subGroup->id])): ?>
                            <?php foreach ($indicatorsByGroup[$subGroup->id] as $index => $indicator): ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td><?= Html::encode($indicator->label) ?></td>
                                    <td>
                                        <select name="IndicatorScore[<?= $indicator->id ?>][self_team_score]" class="form-select score-select" data-subgroup-id="<?= $subGroup->id ?>">
                                            <option value="0">Pilih Penilaian</option>
                                            <?php foreach ($indicator->indicatorOptions as $option): ?>
                                                <?php 
                                                    $selected = (isset($scores[$indicator->id]) && $scores[$indicator->id]->self_team_score == $option->weight) ? 'selected' : '';
                                                ?>
                                                <option value="<?= $option->weight ?>" <?= $selected ?>>
                                                    <?= Html::encode($option->label) ?> (<?= $option->weight ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <?php if (isset($scores[$indicator->id]) && $scores[$indicator->id]->evidence_url): ?>
                                            <div class="mb-1">
                                                <a href="<?= Url::to($scores[$indicator->id]->evidence_url) ?>" target="_blank" class="btn btn-sm btn-outline-info">Lihat Bukti</a>
                                            </div>
                                        <?php endif; ?>
                                        <input class="form-control form-control-sm" type="file" name="IndicatorScore[<?= $indicator->id ?>][evidence]">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <tr>
                            <td></td>
                            <td class="text-end fw-bold">Nilai Sub-total <?= Html::encode($subGroup->code) ?></td>
                            <td class="text-center fw-bold text-primary subgroup-weighted-display" id="subgroup-weighted-<?= $subGroup->id ?>" data-weight="<?= $subGroup->weight ?>"><?= number_format($subGroupResults[$subGroup->id]['weighted'], 2) ?></td>
                            <td></td>
                        </tr>
                    <?php endforeach; ?>

                    <tr class="table-secondary">
                        <th scope="row"></th>
                        <th class="text-end">Nilai Total <?= Html::encode($currentRootGroup->code) ?> (<?= Html::encode($currentRootGroup->label) ?>)</th>
                        <th class="text-center text-success fs-5" id="group-total-score" data-root-weight="<?= $currentRootGroup->weight ?>"><?= number_format($finalGroupScore, 2) ?></th>
                        <th></th>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>

    <div class="d-flex justify-content-between w-100 mt-3">
        <button type="submit" form="self-review-form" name="target_page" value="<?= $page ?>" class="btn btn-outline-primary">Simpan sementara</button>
        
        <div class="d-flex gap-2">
            <?php if ($page > 1): ?>
                <button type="submit" form="self-review-form" name="target_page" value="<?= $page - 1 ?>" class="btn btn-secondary">Sebelumnya</button>
            <?php else: ?>
                <button class="btn btn-secondary" disabled>Sebelumnya</button>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
                <button type="submit" form="self-review-form" name="target_page" value="<?= $page + 1 ?>" class="btn btn-primary">Berikutnya</button>
            <?php else: ?>
                <button type="submit" form="self-review-form" name="finish" value="1" class="btn btn-success">Selesai Review</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
    $('.score-select').on('change', function() {
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
