<?php

/** @var yii\web\View $this */
/** @var common\models\Assessment $assessment */
/** @var common\models\IndicatorGroup[] $root_groups */
/** @var common\models\IndicatorGroup[] $root_groups_only */
/** @var common\models\IndicatorGroup[] $child_groups_only */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\ArrayHelper;

$this->title = $assessment->title;
// $this->params['breadcrumbs'][] = ['label' => 'Asesmen', 'url' => ['index']];
// $this->params['breadcrumbs'][] = $this->title;

$group_model = new \common\models\IndicatorGroup();
$indicator_model = new \common\models\Indicator();
$option_model = new \common\models\IndicatorOption();

$root_group_list = ArrayHelper::map($root_groups_only, 'id', function ($model) {
  return '[' . $model->code . '] ' . $model->label;
});

$child_group_list = ArrayHelper::map($child_groups_only, 'id', function ($model) {
  return '[' . $model->code . '] ' . $model->label;
});

?>




<div class="page-cont w-100 p-3 d-flex flex-column gap-3 asesmen-kelola">
  <div class="row align-items-center mb-4">
    <div class="col-md-11">
      <h1>Kelola Asesmen:</h1>
      <h1><?= Html::encode($this->title) ?></h1>
    </div>
    <div class="col-md-1">
      <a class="btn s-btn-sec" data-bs-toggle="collapse" href="#settingscollapse" role="button" aria-expanded="false" aria-controls="collapseSettings">
        <i class="fa-solid fa-gear"></i>
      </a>
    </div>

  </div>
  <div class="collapse" id="settingscollapse">
    <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border p-3 d-flex flex-column gap-2 w-100 mb-4">
      <div class="px-4 d-flex justify-content-between">
        <h5 class="mb-0">Pengaturan Utama Asesmen</h5>
        <a class="btn" data-bs-toggle="collapse" href="#settingscollapse" role="button" aria-expanded="false" aria-controls="collapseSettings">
          <i class="fa-solid fa-x"></i>
        </a>
      </div>
      <div class="px-4 ">
        <div class="row g-3 align-items-end ">
          <div class="col-md-5">
            <?= Html::beginForm(['ubah-judul', 'assessment_id' => $assessment->id], 'patch') ?>
            <label class="form-label fw-bold">Judul Asesmen</label>
            <div class="input-group">
              <?= Html::textInput('title', $assessment->title, ['class' => 'form-control', 'required' => true]) ?>
              <button class="btn s-btn-main" type="submit">Ubah Judul</button>
            </div>
            <?= Html::endForm() ?>
          </div>
          <div class="col-md-4">
            <?= Html::beginForm(['ganti-tingkat', 'assessment_id' => $assessment->id], 'post') ?>
            <label class="form-label fw-bold">Tingkat Sertifikasi</label>
            <div class="input-group">
              <?= Html::dropDownList('level', $assessment->level, \common\enums\CertificateLevel::list(), ['class' => 'form-select']) ?>
              <button class="btn btn-warning" type="submit" data-confirm="Ganti tingkat asesmen? Ini akan menonaktifkan asesmen jika sedang aktif.">Ganti Level</button>
            </div>
            <?= Html::endForm() ?>
          </div>
          <div class="col-md-3 ">
            <label class="form-label d-block fw-bold">Status Saat Ini</label>
            <?php if ($assessment->level === $assessment->active_at_level): ?>
              <div class="alert alert-success py-2 mb-0">
                <i class="bi bi-check-circle-fill"></i> Aktif di Level <?= \common\enums\CertificateLevel::list()[$assessment->level] ?>
              </div>
            <?php else: ?>
              <?= Html::a('Aktifkan Sekarang', ['aktifkan', 'assessment_id' => $assessment->id], [
                'class' => 'btn btn-outline-success w-100',
                'data-confirm' => 'Aktifkan asesmen ini untuk menggantikan asesmen aktif saat ini di level ' . \common\enums\CertificateLevel::list()[$assessment->level] . '?',
                'data-method' => 'post',
              ]) ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- groups -->
  <?php foreach ($root_groups as $root): ?>
    <div class="card bg-white px-2 py-4 rounded-2 shadow border-1 border d-flex flex-column gap-2 w-100 mb-4">
      <a id="parRoot<?= $root->id ?>" class="text-decoration-none" data-bs-toggle="collapse" href="#collapseRoot<?= $root->id ?>" role="button" aria-expanded="true" aria-controls="collapseRoot<?= $root->id ?>">
        <div class="card-header text-black d-flex bg-white justify-content-between align-items-center">
          <div class="d-flex gap-3">
            <h5 class="mb-0 text-black">Group [<?= Html::encode($root->code) ?>] <?= Html::encode($root->label) ?></h5>
            <i class="fa-solid fa-chevron-up text-black h-fit me-2 my-auto"></i>
          </div>
      </a>
      <div class="btn-group btn-group-sm">
        <button class="btn btn-primary" onclick='edit_group(<?= json_encode($root->attributes) ?>)'><i class="fa-solid fa-pen-to-square"></i> Edit</button>
        <?= Html::a('<i class="fa-solid fa-trash-can"></i> Hapus', ['hapus-grup', 'indicator_group_id' => $root->id], [
          'class' => 'btn btn-danger',
          'data' => [
            'confirm' => 'Apakah Anda yakin ingin menghapus group ini beserta seluruh isinya?',
            'method' => 'delete',
          ],
        ]) ?>
      </div>
    </div>
    <div class="card-body collapse show" id="collapseRoot<?= $root->id ?>" parent-link="parRoot<?= $root->id ?>">
      <!-- root indicators -->
      <?php if (!empty($root->indicators)): ?>
        <div class="mb-4 ms-4">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold">Indikator Group Utama</h6>
          </div>
          <div class="">
            <?php foreach ($root->indicators as $indicator): ?>
              <?= $this->render('_indicator_card', ['indicator' => $indicator, 'assessment' => $assessment]) ?>
            <?php endforeach; ?>
            <button class="btn s-btn-outline-main w-100 py-2 mt-1 d-flex align-items-center justify-content-center gap-2" onclick="tambah_indikator(<?= $root->id ?>)">
              <i class="fa-solid fa-circle-plus fs-5"></i> Tambah Indikator
            </button>
          </div>
        </div>
      <?php endif; ?>

      <!-- subgroup -->
      <?php foreach ($root->childGroups as $child): ?>
        <div class="card mb-3 s-border-main ms-4 shadow-sm">
          <div class="card-header s-bg-main text-white d-flex justify-content-between align-items-center">
            <span class="fw-bold">Subgroup [<?= Html::encode($child->code) ?>] <?= Html::encode($child->label) ?></span>
            <div class="btn-group btn-group-sm">
              <button class="btn btn-primary" onclick='edit_group(<?= json_encode($child->attributes) ?>)'><i class="fa-solid fa-pen-to-square"></i> Edit</button>
              <?= Html::a('<i class="fa-solid fa-trash-can"></i> Hapus', ['hapus-grup', 'indicator_group_id' => $child->id], [
                'class' => 'btn btn-danger',
                'data' => [
                  'confirm' => 'Apakah Anda yakin ingin menghapus subgroup ini beserta seluruh isinya?',
                  'method' => 'delete',
                ],
              ]) ?>
            </div>
          </div>
          <div class="card-body px-4 py-3">
            <!-- subgroup indicators -->
            <div class="p-2 border-bottom d-flex justify-content-between align-items-center bg-light">
              <span class="small fw-bold text-dark rounded rounded-2">Indikator</span>
            </div>
            <div>
              <?php foreach ($child->indicators as $indicator): ?>
                <?= $this->render('_indicator_card', ['indicator' => $indicator, 'assessment' => $assessment]) ?>
              <?php endforeach; ?>
              <?php if (empty($child->indicators)): ?>
                <div>
                  <p class="text-center text-muted small py-3">Belum ada indikator.</p>
                </div>
              <?php endif; ?>
              <button class="btn s-btn-outline-main w-100 py-2 mt-1 d-flex align-items-center justify-content-center gap-2" onclick="tambah_indikator(<?= $child->id ?>)">
                <i class="fa-solid fa-circle-plus fs-5"></i> Tambah Indikator
              </button>
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
      <div class="border border-1 border-black ms-4"></div>
      <div class="py-2 ms-4">
        <button class="btn s-btn-outline-main w-100 py-2 mt-1 d-flex align-items-center justify-content-center gap-2 onclick=" onClick="tambah_group(<?= $root->id ?>)">
          <i class="fa-solid fa-circle-plus fs-5"></i> Tambah Subgroup
        </button>
      </div>
    </div>
</div>
<?php endforeach ?>
<div class="card-header s-bg-main text-white d-flex justify-content-between align-items-center rounded rounded-2">
  <button class="btn s-btn-main  w-100 py-2 mt-1 d-flex align-items-center justify-content-center gap-2 " onclick="tambah_group(null)">
    <i class="fa-solid fa-circle-plus fs-3 mb-1"></i>
    <p class="mb-0">Tambah Group</p>
  </button>
</div>
</div>

<!-- Modal Group -->
<div class="modal fade" id="modal_group" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <?php $form = ActiveForm::begin(['id' => 'form_group', 'action' => ['simpan-grup', 'assessment_id' => $assessment->id]]); ?>
      <div class="modal-header">
        <h5 class="modal-title" id="modal_group_title">Tambah Group</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="parent_group_select_container">
          <?= $form->field($group_model, 'parent_group_id')->dropDownList($root_group_list, [
            'id' => 'group_parent_id',
            'prompt' => '-- Group Utama --'
          ]) ?>
        </div>
        <?= $form->field($group_model, 'code')->textInput(['id' => 'group_code']) ?>
        <?= $form->field($group_model, 'label')->textInput(['id' => 'group_label']) ?>
        <div class="row">
          <div class="col-6"><?= $form->field($group_model, 'order')->textInput(['type' => 'number', 'id' => 'group_order']) ?></div>
          <div class="col-6"><?= $form->field($group_model, 'weight')->textInput(['type' => 'number', 'id' => 'group_weight']) ?></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn s-btn-main">Simpan</button>
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
        <?= $form->field($indicator_model, 'indicator_group_id')->dropDownList($child_group_list, [
          'id' => 'indicator_group_id',
          'prompt' => '-- Pilih Subgrup --'
        ]) ?>
        <?= $form->field($indicator_model, 'code')->textInput(['id' => 'indicator_code']) ?>
        <?= $form->field($indicator_model, 'label')->textarea(['rows' => 3, 'id' => 'indicator_label']) ?>
        <?= $form->field($indicator_model, 'order')->textInput(['type' => 'number', 'id' => 'indicator_order']) ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn s-btn-main">Simpan</button>
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
        <button type="submit" class="btn s-btn-main">Simpan</button>
      </div>
      <?php ActiveForm::end(); ?>
    </div>
  </div>
</div>

<script>
  // be stuff (data collection)
  function tambah_group(parent_id) {
    const form = document.getElementById('form_group');
    form.action = '<?= \yii\helpers\Url::to(['simpan-grup', 'assessment_id' => $assessment->id]) ?>';
    document.getElementById('modal_group_title').innerText = parent_id ? 'Tambah Subgrup' : 'Tambah Grup';

    const container = document.getElementById('parent_group_select_container');
    container.style.display = 'block';

    document.getElementById('group_parent_id').value = parent_id || '';

    // Reset dropdown: show all options
    const dropdown = document.getElementById('group_parent_id');
    for (let i = 0; i < dropdown.options.length; i++) {
      dropdown.options[i].disabled = false;
      dropdown.options[i].style.display = 'block';
    }

    document.getElementById('group_code').value = '';
    document.getElementById('group_label').value = '';
    document.getElementById('group_order').value = '1';
    document.getElementById('group_weight').value = '0';
    new bootstrap.Modal(document.getElementById('modal_group')).show();
  }

  function edit_group(data) {
    const form = document.getElementById('form_group');
    form.action = '<?= \yii\helpers\Url::to(['simpan-grup', 'assessment_id' => $assessment->id]) ?>&indicator_group_id=' + data.id;
    document.getElementById('modal_group_title').innerText = 'Edit Group [' + data.code + '] ' + data.label;

    const dropdown = document.getElementById('group_parent_id');
    const container = document.getElementById('parent_group_select_container');

    // Root group (Level 1) has no parent (parent_group_id is null)
    // Locked: cannot move root group to another parent
    // COMMENTED FOR BACKEND TESTING
    // if (data.parent_group_id === null) {
    //     container.style.display = 'none';
    // } else {
    container.style.display = 'block';
    dropdown.value = data.parent_group_id || '';
    // }

    // Disable self to prevent circular reference
    for (let i = 0; i < dropdown.options.length; i++) {
      if (dropdown.options[i].value == data.id) {
        dropdown.options[i].disabled = true;
        dropdown.options[i].style.display = 'none';
      } else {
        dropdown.options[i].disabled = false;
        dropdown.options[i].style.display = 'block';
      }
    }

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

  // fe stuff (persistance)
  const collapseRootObj = document.querySelectorAll('[id^=collapse]')
  collapseRootKey = "rootState"

  const onLocal = JSON.parse(localStorage.getItem(collapseRootKey)) || {}

  collapseRootObj.forEach(function(e) {
    // console.log(e)
    if (onLocal[e.id] === true) {
      e.classList.add('show')
      const par = document.getElementById(e.getAttribute('parent-link'))
      par.setAttribute('aria-expanded', 'true')
    } else if (onLocal[e.id] === false) {
      e.classList.remove('show')
      const par = document.getElementById(e.getAttribute('parent-link'))
      par.setAttribute('aria-expanded', 'false')
    }
  })

  collapseRootObj.forEach(function(e) {

    e.addEventListener('shown.bs.collapse', function() {
      onLocal[e.id] = true
      localStorage.setItem(collapseRootKey, JSON.stringify(onLocal))
    })
    e.addEventListener('hidden.bs.collapse', function() {
      onLocal[e.id] = false
      localStorage.setItem(collapseRootKey, JSON.stringify(onLocal))
    })
  })
</script>