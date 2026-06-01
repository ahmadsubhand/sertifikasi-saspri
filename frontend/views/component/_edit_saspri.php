<?php

use common\models\District;
use common\models\Province;
use common\models\Regency;
use common\models\SaspriK;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var SaspriK $model */
$provinces = Province::find()->all();
$regencies = [];
$districts = [];
if ($model && $model->district_id) {
  $district = $model->district;
  if ($district) {
    $regency_id = $district->regency_id;
    $province_id = $district->regency->province_id;

    $regencies = Regency::find()->where(['province_id' => $province_id])->all();
    $districts = District::find()->where(['regency_id' => $regency_id])->all();
  }
}

?>

<div class="bg-white px-3 py-4 rounded-2 shadow border-1 border d-flex flex-column gap-2">
  <!-- Nanti ini dibungkus dalam !$is_pending atau semua field di disable biar user gk ngajuin ulang klo lg pending -->
  <?php $form = ActiveForm::begin([
    'id' => 'form-edit-saspri',
    'action' => ['#'],
    'options' => ['enctype' => 'multipart/form-data']
  ]) ?>

  <div class="row">
    <div class="col-sm-6 d-flex flex-column gap-2">
      <?= $form->field($model, 'region_name')->textInput(['class' => 'form-control border-black', 'placeholder' => 'Nama SASPRI-K'])->label('Nama SASPRI-K') ?>
      <?= $form->field($model, 'cooperative_name')->textInput(['class' => 'form-control border-black', 'placeholder' => 'Nama Koperasi'])->label('Nama Koperasi') ?>
      <?= $form->field($model, 'address')->textInput(['class' => 'form-control border-black', 'placeholder' => 'Alamat Sekretariat'])->label('Alamat Sekretariat') ?>

      <div class="">
        <label class="form-label mb-0">Provinsi</label>
        <?= Html::dropDownList('province_id', $province_id, ArrayHelper::map($provinces, 'id', 'name'), [
          'id' => 'province-id',
          'prompt' => 'Pilih Provinsi',
          'class' => 'form-select border-black',

        ]) ?>
      </div>

      <div class="">
        <label class="form-label mb-0">Kabupaten/Kota</label>
        <?= Html::dropDownList('regency_id', $regency_id, ArrayHelper::map($regencies, 'id', 'name'), [
          'id' => 'regency-id',
          'prompt' => 'Pilih Kabupaten/Kota',
          'class' => 'form-select border-black',

        ]) ?>
      </div>

      <?= $form->field($model, 'district_id')->dropDownList(ArrayHelper::map($districts, 'id', 'name'), [
        'id' => 'district-id',
        'prompt' => 'Pilih Kecamatan',
        'class' => 'form-select border-black',

      ])->label('Kecamatan') ?>

    </div>

    <div class="col-sm-6 d-flex flex-column gap-2">
      <?= $form->field($model, 'number_of_groups')->textInput(['type' => 'number', 'class' => 'form-control border-black'])->label('Jumlah Kelompok Yang Dibina') ?>
      <?= $form->field($model, 'number_of_active_members')->textInput(['type' => 'number', 'class' => 'form-control border-black'])->label('Jumlah Anggota Aktif') ?>
      <?= $form->field($model, 'total_livestock_count')->textInput(['type' => 'number', 'class' => 'form-control border-black'])->label('Jumlah Total Ternak Anggota Aktif') ?>
      <?= $form->field($model, 'productive_heifer_count')->textInput(['type' => 'number', 'class' => 'form-control border-black'])->label('Jumlah Total Ternak dara Produktif (Siap Kawin)') ?>
      <?= $form->field($model, 'livestock_type')->textInput(['class' => 'form-control border-black'])->label('Ternak Yang Diusahakan') ?>
      <?= $form->field($model, 'breeding_livestock_count')->textInput(['type' => 'number', 'class' => 'form-control border-black'])->label('Jumlah Ternak Indukan (Pernah Beranak)') ?>
    </div>

    <div class="w-100 my-4">
      <?= Html::submitButton('Simpan', ['class' => 'btn w-100 py-2 fw-bold s-btn-main']) ?>
    </div>
  </div>

  <?php ActiveForm::end() ?>
  </div>
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
});
JS;
$this->registerJs($js);
?>