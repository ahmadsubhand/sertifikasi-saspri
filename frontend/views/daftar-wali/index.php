<?php

use common\enums\ApprovalStatus;
use common\models\Province;
use common\models\Regency;
use common\models\District;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\SaspriK $saspri_k */
/** @var common\models\SaspriKDocument[] $documents */

$is_pending = $saspri_k && $saspri_k->request_status === ApprovalStatus::PENDING;
$is_rejected = $saspri_k && $saspri_k->request_status === ApprovalStatus::REJECTED;

$provinces = Province::find()->all();
$province_id = null;
$regency_id = null;
$regencies = [];
$districts = [];

if ($saspri_k && $saspri_k->district_id) {
    $district = $saspri_k->district;
    if ($district) {
        $regency_id = $district->regency_id;
        $province_id = $district->regency->province_id;

        $regencies = Regency::find()->where(['province_id' => $province_id])->all();
        $districts = District::find()->where(['regency_id' => $regency_id])->all();
    }
}

$model = $saspri_k ?: new \common\models\SaspriK();

?>

<div class="page-cont w-100 h-100 p-3 d-flex flex-column gap-3">
    <div class="">
        <h3 class="fw-bold">Daftar Sebagai Wali SASPRI</h3>
    </div>

    <?php if ($is_pending): ?>
        <div class="alert alert-info">
            Pendaftaran Anda sedang dalam proses verifikasi. Status: <strong>Menunggu (Pending)</strong>
        </div>
    <?php endif; ?>

    <?php if ($is_rejected): ?>
        <div class="alert alert-danger">
            <strong>Pendaftaran Anda ditolak.</strong><br>
            Alasan: <?= Html::encode($saspri_k->request_rejection_reason) ?><br>
            Silakan perbaiki data di bawah ini dan ajukan ulang.
        </div>
    <?php endif; ?>

    <!-- Nanti ini dibungkus dalam !$is_pending atau semua field di disable biar user gk ngajuin ulang klo lg pending -->
    <?php $form = ActiveForm::begin([
        'id' => 'form-daftar-saspri',
        'action' => ['daftar-saspri-k'],
        'options' => ['enctype' => 'multipart/form-data', 'disabled' => $is_pending]
    ]) ?>

    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'region_name')->textInput(['class' => 'form-control border-black', 'placeholder' => 'Nama SASPRI-K', 'disabled' => $is_pending])->label('Nama SASPRI-K') ?>
            <?= $form->field($model, 'cooperative_name')->textInput(['class' => 'form-control border-black', 'placeholder' => 'Nama Koperasi', 'disabled' => $is_pending])->label('Nama Koperasi') ?>
            <?= $form->field($model, 'address')->textInput(['class' => 'form-control border-black', 'placeholder' => 'Alamat Sekretariat', 'disabled' => $is_pending])->label('Alamat Sekretariat') ?>

            <div class="mb-3">
                <label class="form-label">Provinsi</label>
                <?= Html::dropDownList('province_id', $province_id, ArrayHelper::map($provinces, 'id', 'name'), [
                    'id' => 'province-id',
                    'prompt' => 'Pilih Provinsi',
                    'class' => 'form-select border-black',
                    'disabled' => $is_pending
                ]) ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Kabupaten/Kota</label>
                <?= Html::dropDownList('regency_id', $regency_id, ArrayHelper::map($regencies, 'id', 'name'), [
                    'id' => 'regency-id',
                    'prompt' => 'Pilih Kabupaten/Kota',
                    'class' => 'form-select border-black',
                    'disabled' => $is_pending
                ]) ?>
            </div>

            <?= $form->field($model, 'district_id')->dropDownList(ArrayHelper::map($districts, 'id', 'name'), [
                'id' => 'district-id',
                'prompt' => 'Pilih Kecamatan',
                'class' => 'form-select border-black',
                'disabled' => $is_pending
            ])->label('Kecamatan') ?>

        </div>

        <div class="col-sm-6">

            <?= $form->field($model, 'number_of_groups')->textInput(['type' => 'number', 'class' => 'form-control border-black', 'disabled' => $is_pending])->label('Jumlah Kelompok Yang Dibina') ?>
            <?= $form->field($model, 'number_of_active_members')->textInput(['type' => 'number', 'class' => 'form-control border-black', 'disabled' => $is_pending])->label('Jumlah Anggota Aktif') ?>
            <?= $form->field($model, 'total_livestock_count')->textInput(['type' => 'number', 'class' => 'form-control border-black', 'disabled' => $is_pending])->label('Jumlah Total Ternak Anggota Aktif') ?>
            <?= $form->field($model, 'productive_heifer_count')->textInput(['type' => 'number', 'class' => 'form-control border-black', 'disabled' => $is_pending])->label('Jumlah Total Ternak dara Produktif (Siap Kawin)') ?>
            <?= $form->field($model, 'livestock_type')->textInput(['class' => 'form-control border-black', 'disabled' => $is_pending])->label('Ternak Yang Diusahakan') ?>
            <?= $form->field($model, 'breeding_livestock_count')->textInput(['type' => 'number', 'class' => 'form-control border-black', 'disabled' => $is_pending])->label('Jumlah Ternak Indukan (Pernah Beranak)') ?>
        </div>

        <div class="col-sm-12">
            <div class="d-flex justify-content-between mt-4">
                <p class="fw-bold">Dokumen Pendukung</p>
                <button type="button" id="add-row" class="btn btn-sm text-white" <?= $ispending ?? 'disabled' ?> style="background-color: #6B78B9;">
                    <i class="fa-solid fa-plus"></i> Tambah Dokumen
                </button>
            </div>
            <div id="doc-container">
                <?php if (empty($documents)): ?>
                    <div class="doc-row row mb-3">
                        <div class="col-sm-6">
                            <label class="form-label">Kategori / Nama Dokumen</label>
                            <input type="text" class="form-control border-black" name="SaspriK[saspri_k_documents][]" <?= $ispending ?? 'disabled' ?> placeholder="Contoh: Sertifikat SPR" required>
                        </div>
                        <div class="col-sm-5">
                            <label class="form-label">Unggah Dokumen</label>
                            <input class="form-control border-black" type="file" name="saspri_k_documents[]" <?= $ispending ?? 'disabled' ?> required>
                        </div>
                        <div class="col-sm-1 d-flex align-items-end" <?= $ispending ?? 'disabled' ?>>
                            <button type="button" class="rem-row btn btn-danger w-100">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($documents as $doc): ?>
                        <div class="doc-row row mt-3">
                            <div class="col-sm-6">
                                <label class="form-label">Kategori / Nama Dokumen</label>
                                <input type="text" class="form-control border-black" name="SaspriK[saspri_k_documents][]" <?= $ispending ?? 'disabled' ?> value="<?= Html::encode($doc->type) ?>" required>
                            </div>
                            <div class="col-sm-5">
                                <label class="form-label">Ganti Dokumen</label>
                                <input class="form-control border-black" type="file" <?= $ispending ?? 'disabled' ?>  name="saspri_k_documents[]" required>

                            </div>
                            <div class="col-sm-1 d-flex align-items-end">
                                <button type="button" class="rem-row btn btn-danger w-100"<?= $ispending ?? 'disabled' ?>>
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mt-1 existing-file-info">
                                    <small class="text-muted">File sebelumnya: <a href="<?= Url::to($doc->url) ?>" target="_blank" class="text-decoration-none"><i class="fa-solid fa-file-lines"></i> Lihat Dokumen</a></small>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <small class="text-info lh-1">
                                    * Pengajuan ulang memerlukan unggah ulang dokumen. File lama akan otomatis dihapus.
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="w-100 my-4">
            <?= Html::submitButton($is_rejected ? 'Ajukan Ulang' : 'Daftar', ['class' => 'btn w-100 py-2 fw-bold s-btn-main', 'disabled' => $is_pending]) ?>
        </div>
    </div>

    <?php ActiveForm::end() ?>
</div>

<?php
$kabupatenUrl = Url::to(['wilayah/kabupaten-kota']);
$kecamatanUrl = Url::to(['wilayah/kecamatan']);

$js = <<<JS
$(document).ready(function() {
    $('#province-id').on('change', function() {
        var provinceId = $(this).val();
        var \$regency = $('#regency-id');
        var \$district = $('#district-id');
        
        \$regency.empty().append('<option value="">Pilih Kabupaten/Kota</option>');
        \$district.empty().append('<option value="">Pilih Kecamatan</option>');
        
        if (provinceId) {
            $.ajax({
                url: '$kabupatenUrl',
                data: {province_id: provinceId},
                success: function(data) {
                    $.each(data, function(i, item) {
                        \$regency.append($('<option>', {
                            value: item.id,
                            text: item.name
                        }));
                    });
                }
            });
        }
    });

    $('#regency-id').on('change', function() {
        var regencyId = $(this).val();
        var \$district = $('#district-id');
        
        \$district.empty().append('<option value="">Pilih Kecamatan</option>');
        
        if (regencyId) {
            $.ajax({
                url: '$kecamatanUrl',
                data: {regency_id: regencyId},
                success: function(data) {
                    $.each(data, function(i, item) {
                        \$district.append($('<option>', {
                            value: item.id,
                            text: item.name
                        }));
                    });
                }
            });
        }
    });

    $('#add-row').on('click', function() {
        var \$firstRow = $('.doc-row').first();
        var \$newRow = \$firstRow.clone();
        
        \$newRow.find('input').val('');
        \$newRow.find('.existing-file-info').remove();
        \$newRow.find('.text-info').remove();
        \$newRow.find('label').last().text('Unggah Dokumen');
        
        $('#doc-container').append(\$newRow);
    });

    $(document).on('click', '.rem-row', function() {
        if ($('.doc-row').length > 1) {
            $(this).closest('.doc-row').remove();
        } else {
            alert('Minimal harus ada satu dokumen pendukung.');
        }
    });
});
JS;
$this->registerJs($js);
?>