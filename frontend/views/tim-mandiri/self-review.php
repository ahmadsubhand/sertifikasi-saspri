<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\SaspriK $saspri_k */
/** @var common\models\Certification $certification */
/** @var common\models\IndicatorGroup $current_root_group */
/** @var common\models\IndicatorGroup[] $current_child_group */
/** @var int $page */
/** @var int $total_pages */
/** @var bool $is_leader */

$this->title = 'Self Review SASPRI-K';

// Score Calculation Logic (Temporary)
$groupTotalScore = 0;
$subGroupResults = [];

foreach ($current_child_group as $subGroup) {
    $subGroupSum = 0;

    foreach ($subGroup->indicators as $indicator) {
        $score = $indicator->indicatorScores[0]->self_team_score ?? 0;
        $subGroupSum += $score;
    }

    $subGroupWeighted = $subGroupSum * ($subGroup->weight / 100);
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
        <?php if ($is_leader): ?>
            <span class="badge bg-info">Ketua Tim</span>
        <?php else: ?>
            <span class="badge bg-secondary">Anggota Tim</span>
        <?php endif; ?>
        <div>
            Sertifikasi SASPRI-K <?= Html::encode($saspri_k->region_name) ?> tingkat <?= Html::encode(ucfirst($certification->level)) ?>
        </div>
    </div>

    <div class="card p-3 d-flex flex-column gap-2 w-100">
        <h2><?= Html::encode($current_root_group->code) ?>. <?= Html::encode($current_root_group->label) ?> (<?= Html::encode($current_root_group->weight) ?>%)</h2>
        
        <?php
            // Default action is always save temporary
            $saveAction = Url::to(['simpan-sementara-self-review', 'certification_id' => $certification->id, 'page' => $page]);
            $finalizeAction = Url::to(['finalisasi-self-review', 'certification_id' => $certification->id]);
        ?>
        <form id="self-review-form" action="<?= $saveAction ?>" method="post" enctype="multipart/form-data">
            <?= Html::hiddenInput(\Yii::$app->request->csrfParam, \Yii::$app->request->csrfToken) ?>
            
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
                    <?php foreach ($current_child_group as $subGroup): ?>
                        <tr class="table-light">
                            <th scope="row" class="text-center"><?= Html::encode($subGroup->code) ?></th>
                            <td colspan="3" class="fw-bold">
                                <?= Html::encode($subGroup->label) ?> [<?= Html::encode($subGroup->weight) ?>%]
                            </td>
                        </tr>
                        
                        <?php if (isset($subGroup->indicators)): ?>
                            <?php foreach ($subGroup->indicators as $index => $indicator): ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td><?= Html::encode($indicator->label) ?></td>
                                    <td>
                                        <select 
                                            name="indicator_scores[<?= $indicator->id ?>][self_team_score]" 
                                            class="form-select score-select" 
                                            data-subgroup-id="<?= $subGroup->id ?>"
                                        >
                                            <option value="0">Pilih Penilaian</option>

                                            <?php foreach ($indicator->indicatorOptions as $option): ?>
                                                <?php
                                                    $selected = (
                                                        isset($indicator->indicatorScores[0]->self_team_score) &&
                                                        $indicator->indicatorScores[0]->self_team_score == $option->weight
                                                    ) ? 'selected' : '';
                                                ?>

                                                <option value="<?= $option->weight ?>" <?= $selected ?>>
                                                    <?= Html::encode($option->label) ?> (<?= $option->weight ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <?php if (isset($indicator->indicatorScores[0]) && $indicator->indicatorScores[0]->evidence_url): ?>
                                            <div class="mb-1">
                                                <a href="<?= Url::to($indicator->indicatorScores[0]->evidence_url) ?>" target="_blank" class="btn btn-sm btn-outline-info">Lihat Bukti</a>
                                            </div>
                                        <?php endif; ?>
                                        <input class="form-control form-control-sm" type="file" name="indicator_scores[<?= $indicator->id ?>][evidence]">
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
                        <th class="text-end">Nilai Total <?= Html::encode($current_root_group->code) ?> (<?= Html::encode($current_root_group->label) ?>)</th>
                        <th class="text-center text-success fs-5" id="group-total-score" data-root-weight="<?= $current_root_group->weight ?>"><?= number_format($finalGroupScore, 2) ?></th>
                        <th></th>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>

    <div class="d-flex justify-content-between w-100 mt-3">
        <button type="submit" id="btn-save-temp" form="self-review-form" name="target_page" value="<?= $page ?>" class="btn btn-outline-primary">Simpan sementara</button>
        
        <div class="d-flex gap-2">
            <?php if ($page > 1): ?>
                <button type="submit" id="btn-prev" form="self-review-form" name="target_page" value="<?= $page - 1 ?>" class="btn btn-secondary">Sebelumnya</button>
            <?php else: ?>
                <button class="btn btn-secondary" disabled>Sebelumnya</button>
            <?php endif; ?>

            <?php if ($page < $total_pages): ?>
                <button type="submit" id="btn-next" form="self-review-form" name="target_page" value="<?= $page + 1 ?>" class="btn btn-primary">Berikutnya</button>
            <?php else: ?>
                <button type="submit" id="btn-finish" form="self-review-form" name="finish" value="1" class="btn btn-success">Selesai Review</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
    // Handle form action changes
    $('#btn-finish').on('click', function() {
        $('#self-review-form').attr('action', '$finalizeAction');
    });

    $('#btn-save-temp, #btn-prev, #btn-next').on('click', function() {
        $('#self-review-form').attr('action', '$saveAction');
    });

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
