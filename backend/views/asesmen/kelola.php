<?php

/** @var yii\web\View $this */
/** @var common\models\Assessment $assessment */
/** @var common\models\IndicatorGroup[] $root_groups */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Kelola Asesmen: ' . $assessment->title;
$this->params['breadcrumbs'][] = ['label' => 'Asesmen', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$group_model = new \common\models\IndicatorGroup();
$indicator_model = new \common\models\Indicator();
$option_model = new \common\models\IndicatorOption();

?>

<div class="asesmen-kelola">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <button class="btn btn-success" onclick="tambah_group(null)">
            Tambah Grup
        </button>
    </div>

    <?php foreach ($root_groups as $root): ?>
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">[<?= Html::encode($root->code) ?>] <?= Html::encode($root->label) ?></h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-light" onclick='edit_group(<?= json_encode($root->attributes) ?>)'>Edit</button>
                    <?= Html::a('Hapus', ['hapus-group', 'indicator_group_id' => $root->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Apakah Anda yakin ingin menghapus group ini beserta seluruh isinya?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            </div>
            <div class="card-body">
                <!-- Root Group Indicators -->
                <?php if (!empty($root->indicators)): ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold">Indikator Group Utama</h6>
                            <button class="btn btn-success btn-sm" onclick="tambah_indikator(<?= $root->id ?>)">
                                Tambah Indikator
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover border">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 10%">Kode</th>
                                        <th>Label Indikator</th>
                                        <th style="width: 10%">Order</th>
                                        <th style="width: 20%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($root->indicators as $indicator): ?>
                                        <?= $this->render('_indicator_row', ['indicator' => $indicator, 'assessment' => $assessment]) ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-muted fw-bold">Subgrup</h6>
                    <button class="btn btn-outline-primary btn-sm" onclick="tambah_group(<?= $root->id ?>)">
                        Tambah Subgrup
                    </button>
                </div>

                <?php foreach ($root->childGroups as $child): ?>
                    <div class="card mb-3 border-secondary ms-4 shadow-sm">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <span class="fw-bold">[<?= Html::encode($child->code) ?>] <?= Html::encode($child->label) ?></span>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-light" onclick='edit_group(<?= json_encode($child->attributes) ?>)'>Edit</button>
                                <?= Html::a('Hapus', ['hapus-group', 'indicator_group_id' => $child->id], [
                                    'class' => 'btn btn-danger',
                                    'data' => [
                                        'confirm' => 'Apakah Anda yakin ingin menghapus child group ini beserta seluruh isinya?',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="p-2 border-bottom d-flex justify-content-between align-items-center bg-light">
                                <span class="small fw-bold text-dark">Daftar Indikator</span>
                                <button class="btn btn-success btn-sm py-0" style="font-size: 0.8rem;" onclick="tambah_indikator(<?= $child->id ?>)">
                                    Tambah Indikator
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light small">
                                        <tr>
                                            <th style="width: 10%">Kode</th>
                                            <th>Label Indikator</th>
                                            <th style="width: 10%">Order</th>
                                            <th style="width: 20%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($child->indicators as $indicator): ?>
                                            <?= $this->render('_indicator_row', ['indicator' => $indicator, 'assessment' => $assessment]) ?>
                                        <?php endforeach; ?>
                                        <?php if (empty($child->indicators)): ?>
                                            <tr><td colspan="4" class="text-center text-muted small py-3">Belum ada indikator.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($root->childGroups) && empty($root->indicators)): ?>
                    <div class="text-center py-4 bg-light rounded">
                        <p class="text-muted mb-2">Group ini masih kosong.</p>
                        <button class="btn btn-sm btn-outline-primary" onclick="tambah_indikator(<?= $root->id ?>)">Tambah Indikator Pertama</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($root_groups)): ?>
        <div class="card p-5 text-center shadow-sm">
            <h5 class="text-muted">Asesmen ini belum memiliki struktur data.</h5>
            <div class="mt-3">
                <button class="btn btn-primary" onclick="tambah_group(null)">Mulai dengan Tambah Grup</button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Group -->
<div class="modal fade" id="modal_group" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php $form = ActiveForm::begin(['id' => 'form_group', 'action' => ['simpan-group', 'assessment_id' => $assessment->id]]); ?>
            <div class="modal-header">
                <h5 class="modal-title" id="modal_group_title">Tambah Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?= $form->field($group_model, 'parent_group_id')->hiddenInput(['id' => 'group_parent_id'])->label(false) ?>
                <?= $form->field($group_model, 'code')->textInput(['id' => 'group_code']) ?>
                <?= $form->field($group_model, 'label')->textInput(['id' => 'group_label']) ?>
                <div class="row">
                    <div class="col-6"><?= $form->field($group_model, 'order')->textInput(['type' => 'number', 'id' => 'group_order']) ?></div>
                    <div class="col-6"><?= $form->field($group_model, 'weight')->textInput(['type' => 'number', 'id' => 'group_weight']) ?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<!-- Modal Indikator -->
<div class="modal fade" id="modal_indikator" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php $form = ActiveForm::begin(['id' => 'form_indikator', 'action' => ['simpan-indikator', 'assessment_id' => $assessment->id]]); ?>
            <div class="modal-header">
                <h5 class="modal-title" id="modal_indikator_title">Tambah Indikator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?= $form->field($indicator_model, 'indicator_group_id')->hiddenInput(['id' => 'indicator_group_id'])->label(false) ?>
                <?= $form->field($indicator_model, 'code')->textInput(['id' => 'indicator_code']) ?>
                <?= $form->field($indicator_model, 'label')->textarea(['rows' => 3, 'id' => 'indicator_label']) ?>
                <?= $form->field($indicator_model, 'order')->textInput(['type' => 'number', 'id' => 'indicator_order']) ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<!-- Modal Opsi -->
<div class="modal fade" id="modal_opsi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php $form = ActiveForm::begin(['id' => 'form_opsi', 'action' => ['simpan-opsi', 'assessment_id' => $assessment->id]]); ?>
            <div class="modal-header">
                <h5 class="modal-title" id="modal_opsi_title">Tambah Opsi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?= $form->field($option_model, 'indicator_id')->hiddenInput(['id' => 'option_indicator_id'])->label(false) ?>
                <?= $form->field($option_model, 'code')->textInput(['id' => 'option_code']) ?>
                <?= $form->field($option_model, 'label')->textInput(['id' => 'option_label']) ?>
                <div class="row">
                    <div class="col-6"><?= $form->field($option_model, 'order')->textInput(['type' => 'number', 'id' => 'option_order']) ?></div>
                    <div class="col-6"><?= $form->field($option_model, 'weight')->textInput(['type' => 'number', 'id' => 'option_weight']) ?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<script>
function tambah_group(parent_id) {
    const form = document.getElementById('form_group');
    form.action = '<?= \yii\helpers\Url::to(['simpan-group', 'assessment_id' => $assessment->id]) ?>';
    document.getElementById('modal_group_title').innerText = parent_id ? 'Tambah Subgrup' : 'Tambah Grup';
    document.getElementById('group_parent_id').value = parent_id || '';
    document.getElementById('group_code').value = '';
    document.getElementById('group_label').value = '';
    document.getElementById('group_order').value = '1';
    document.getElementById('group_weight').value = '0';
    new bootstrap.Modal(document.getElementById('modal_group')).show();
}

function edit_group(data) {
    const form = document.getElementById('form_group');
    form.action = '<?= \yii\helpers\Url::to(['simpan-group', 'assessment_id' => $assessment->id]) ?>&indicator_group_id=' + data.id;
    document.getElementById('modal_group_title').innerText = 'Edit Group';
    document.getElementById('group_parent_id').value = data.parent_group_id || '';
    document.getElementById('group_code').value = data.code;
    document.getElementById('group_label').value = data.label;
    document.getElementById('group_order').value = data.order;
    document.getElementById('group_weight').value = data.weight;
    new bootstrap.Modal(document.getElementById('modal_group')).show();
}

function tambah_indikator(group_id) {
    const form = document.getElementById('form_indikator');
    form.action = '<?= \yii\helpers\Url::to(['simpan-indikator', 'assessment_id' => $assessment->id]) ?>';
    document.getElementById('modal_indikator_title').innerText = 'Tambah Indikator';
    document.getElementById('indicator_group_id').value = group_id;
    document.getElementById('indicator_code').value = '';
    document.getElementById('indicator_label').value = '';
    document.getElementById('indicator_order').value = '1';
    new bootstrap.Modal(document.getElementById('modal_indikator')).show();
}

function edit_indikator(data) {
    const form = document.getElementById('form_indikator');
    form.action = '<?= \yii\helpers\Url::to(['simpan-indikator', 'assessment_id' => $assessment->id]) ?>&indicator_id=' + data.id;
    document.getElementById('modal_indikator_title').innerText = 'Edit Indikator';
    document.getElementById('indicator_group_id').value = data.indicator_group_id;
    document.getElementById('indicator_code').value = data.code;
    document.getElementById('indicator_label').value = data.label;
    document.getElementById('indicator_order').value = data.order;
    new bootstrap.Modal(document.getElementById('modal_indikator')).show();
}

function tambah_opsi(indicator_id) {
    const form = document.getElementById('form_opsi');
    form.action = '<?= \yii\helpers\Url::to(['simpan-opsi', 'assessment_id' => $assessment->id]) ?>';
    document.getElementById('modal_opsi_title').innerText = 'Tambah Opsi';
    document.getElementById('option_indicator_id').value = indicator_id;
    document.getElementById('option_code').value = '';
    document.getElementById('option_label').value = '';
    document.getElementById('option_order').value = '1';
    document.getElementById('option_weight').value = '0';
    new bootstrap.Modal(document.getElementById('modal_opsi')).show();
}

function edit_opsi(data) {
    const form = document.getElementById('form_opsi');
    form.action = '<?= \yii\helpers\Url::to(['simpan-opsi', 'assessment_id' => $assessment->id]) ?>&indicator_option_id=' + data.id;
    document.getElementById('modal_opsi_title').innerText = 'Edit Opsi';
    document.getElementById('option_indicator_id').value = data.indicator_id;
    document.getElementById('option_code').value = data.code;
    document.getElementById('option_label').value = data.label;
    document.getElementById('option_order').value = data.order;
    document.getElementById('option_weight').value = data.weight;
    new bootstrap.Modal(document.getElementById('modal_opsi')).show();
}
</script>
