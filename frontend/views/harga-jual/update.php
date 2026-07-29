<?php

/** @var yii\web\View $this */
/** @var common\models\form\CattleCalculatorForm $model */
/** @var array|null $results */
/** @var array $namaSapiList */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Edit Perhitungan Harga Jual Sapi';

function formatRupiah(int $number) {
    return \Yii::$app->formatter->asCurrency($number, 'Rp.');
}

$js = <<<JS
function toggleBusinessType() {
    const businessType = $('#businessType').val();
    if (businessType === 'penggemukan') {
        $('#penggemukanSection').show();
        $('#breedingSection').hide();
    } else {
        $('#penggemukanSection').hide();
        $('#breedingSection').show();
    }
}

function updateLivestockDetails() {
    const option = $('#nama_sapi option:selected');
    const ras = option.data('ras') || '';
    const vid = option.data('vid') || '';
    $('#ras_sapi').val(ras);
    $('#visual_id').val(vid);
}

$(document).ready(function () {
    toggleBusinessType();

    $('#businessType').on('change', function () {
        const selectedType = $(this).val();
        $('#cattle-calculator-form').find('input, select').not('#businessType').val('');
        toggleBusinessType();
        window.location.href = window.location.pathname + '?type=' + selectedType;
    });

    $('#nama_sapi').on('change', function () {
        updateLivestockDetails();
    });

    updateLivestockDetails();
});
JS;

$this->registerJs($js);
?>

<div class="container-fluid px-4 pt-4">
    <div class="mb-3">
        <p class="text-muted mb-4">Edit hasil perhitungan harga jual untuk data sapi yang sudah ada.</p>
    </div>

    <?php $form = ActiveForm::begin(['id' => 'cattle-calculator-form']); ?>

    <!-- Jenis Usaha -->
    <div class="card p-4 shadow-sm mb-4">
        <h6 class="fw-semibold">Jenis Usaha</h6>
        <?= $form->field($model, 'businessType')->dropDownList([
            'penggemukan' => 'Penggemukan',
            'breeding' => 'Breeding/Pembiakan'
        ], ['id' => 'businessType'])->label('Pilih Jenis Usaha:') ?>
    </div>

    <!-- Data Sapi -->
    <div class="card p-4 shadow-sm mb-4">
        <h6 class="fw-semibold">Data Sapi</h6>
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'nama_sapi')->dropDownList(
                    \yii\helpers\ArrayHelper::map($namaSapiList, 'nama_sapi', 'nama_sapi'),
                    [
                        'id' => 'nama_sapi',
                        'class' => 'form-control',
                        'prompt' => 'Pilih Nama Sapi',
                        'options' => array_reduce($namaSapiList, function($carry, $sapi) {
                            $carry[$sapi['nama_sapi']] = [
                                'data-ras' => $sapi['ras_sapi'],
                                'data-vid' => $sapi['visual_id'],
                            ];
                            return $carry;
                        }, [])
                    ]
                ) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'ras_sapi')->textInput(['readonly' => true, 'id' => 'ras_sapi']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'visual_id')->textInput(['readonly' => true, 'id' => 'visual_id']) ?>
            </div>
        </div>
    </div>

    <!-- Tanggal Perhitungan -->
    <div class="card p-4 shadow-sm mb-4">
        <h6 class="fw-semibold">Waktu Perhitungan Harga Jual</h6>
        <div class="col-md-6">
            <?= $form->field($model, 'tanggalPerhitungan')->input('date', ['placeholder' => 'YYYY-MM-DD']) ?>
        </div>
    </div>

    <!-- 📦 Bagian Penggemukan -->
    <div id="penggemukanSection">
        <div class="card p-4 shadow-sm mb-4">
            <h6 class="fw-semibold">1. Harga Beli Bakalan</h6>
            <?= $form->field($model, 'hargaBakalan')->textInput(['placeholder' => 'Rp. 0']) ?>
        </div>

        <div class="card p-4 shadow-sm mb-4">
            <h6 class="fw-semibold">2. Biaya Pakan</h6>
            <div class="row">
                <div class="col-md-6"><?= $form->field($model, 'pakanHijauan')->textInput(['placeholder' => 'Rp. 0']) ?></div>
                <div class="col-md-6"><?= $form->field($model, 'konsentrat')->textInput(['placeholder' => 'Rp. 0']) ?></div>
                <div class="col-md-6"><?= $form->field($model, 'feedAdditive')->textInput(['placeholder' => 'Rp. 0']) ?></div>
                <div class="col-md-6"><?= $form->field($model, 'waktuPemeliharaan')->textInput(['placeholder' => '0 bulan']) ?></div>
            </div>
        </div>

        <div class="card p-4 shadow-sm mb-4">
            <h6 class="fw-semibold">3. Biaya Kesehatan</h6>
            <div class="row">
                <div class="col-md-4"><?= $form->field($model, 'obatCacing')->textInput(['placeholder' => 'Rp. 0']) ?></div>
                <div class="col-md-4"><?= $form->field($model, 'vitamin')->textInput(['placeholder' => 'Rp. 0']) ?></div>
                <div class="col-md-4"><?= $form->field($model, 'antibiotik')->textInput(['placeholder' => 'Rp. 0']) ?></div>
            </div>
        </div>
    </div>

    <!-- 🐮 Bagian Breeding -->
    <div id="breedingSection" style="display:none;">
        <div class="card p-4 shadow-sm mb-4">
            <h6 class="fw-semibold">1. Amortisasi Investasi Indukan</h6>
            <div class="row">
                <div class="col-md-6"><?= $form->field($model, 'nilaiIndukan')->textInput(['placeholder' => 'Rp. 0']) ?></div>
                <div class="col-md-6"><?= $form->field($model, 'umurProduktif')->textInput(['placeholder' => '0 tahun']) ?></div>
            </div>
        </div>

        <div class="card p-4 shadow-sm mb-4">
            <h6 class="fw-semibold">2. Biaya Pakan Indukan</h6>
            <div class="row">
                <div class="col-md-6"><?= $form->field($model, 'pakanIndukanHijauan')->textInput(['placeholder' => 'Rp. 0']) ?></div>
                <div class="col-md-6"><?= $form->field($model, 'pakanIndukanKonsentrat')->textInput(['placeholder' => 'Rp. 0']) ?></div>
                <div class="col-md-6"><?= $form->field($model, 'pakanIndukanfeedAdditive')->textInput(['placeholder' => 'Rp. 0']) ?></div>
                <div class="col-md-6"><?= $form->field($model, 'waktuPemeliharaanIndukan')->textInput(['placeholder' => '0 bulan']) ?></div>
            </div>
        </div>

        <div class="card p-4 shadow-sm mb-4">
            <h6 class="fw-semibold">3. Biaya Kesehatan (Breeding)</h6>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'biayaIB')->textInput(['placeholder' => 'Rp. 0']) ?>
                    <?= $form->field($model, 'vaksin')->textInput(['placeholder' => 'Rp. 0']) ?>
                    <?= $form->field($model, 'vitaminBreeding')->textInput(['placeholder' => 'Rp. 0']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'pemeriksaanKebuntingan')->textInput(['placeholder' => 'Rp. 0']) ?>
                    <?= $form->field($model, 'obatCacingBreeding')->textInput(['placeholder' => 'Rp. 0']) ?>
                    <?= $form->field($model, 'antibiotikBreeding')->textInput(['placeholder' => 'Rp. 0']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Biaya lainnya -->
    <div class="card p-4 shadow-sm mb-4">
        <h6 class="fw-semibold">4. Biaya Penyusutan</h6>
        <div class="row">
            <div class="col-md-6"><?= $form->field($model, 'investasiKandang')->textInput(['placeholder' => 'Rp. 0']) ?></div>
            <div class="col-md-6"><?= $form->field($model, 'umurEkonomis')->textInput(['placeholder' => '0 tahun']) ?></div>
        </div>
    </div>

    <div class="card p-4 shadow-sm mb-4">
        <h6 class="fw-semibold">5. Biaya Tenaga Kerja</h6>
        <div class="row">
            <div class="col-md-6"><?= $form->field($model, 'gajiPekerja')->textInput(['placeholder' => 'Rp. 0']) ?></div>
            <div class="col-md-6"><?= $form->field($model, 'jumlahSapi')->textInput(['placeholder' => '0 ekor']) ?></div>
        </div>
    </div>

    <div class="card p-4 shadow-sm mb-4">
        <h6 class="fw-semibold">6. Margin & Risiko</h6>
        <div class="row">
            <div class="col-md-6"><?= $form->field($model, 'marginKeuntungan')->textInput(['placeholder' => '0 %']) ?></div>
            <div class="col-md-6"><?= $form->field($model, 'inflasi')->textInput(['placeholder' => '0 %']) ?></div>
        </div>
    </div>

    

    <div class="d-flex mb-4">
        <div>
            <?= Html::submitButton('Hitung', [
                'class' => 'btn btn-primary',
                'name' => 'action',
                'value' => 'calculate'
            ]) ?>
        </div>
    </div>



    <!-- Hasil -->
    <div class="card p-4 shadow-sm bg-light mt-4">
        <h5 class="fw-bold mb-3">Hasil Perhitungan</h5>
        <div class="p-3 mb-3 bg-info-subtle border rounded text-center">
            <div class="text-muted fw-semibold">Harga Jual Rekomendasi</div>
            <div class="fs-4 fw-bold text-success"><?= formatRupiah($results['hargaJual'] ?? 0) ?></div>
        </div>

        <h6 class="fw-semibold mb-2">Rincian Biaya</h6>
        <ul class="list-unstyled small">
            <li>Harga Beli/Amortisasi: <span class="float-end"><?= formatRupiah($results['biayaBeli'] ?? 0) ?></span></li>
            <li>Biaya Pakan: <span class="float-end"><?= formatRupiah($results['biayaPakan'] ?? 0) ?></span></li>
            <li>Biaya Penyusutan: <span class="float-end"><?= formatRupiah($results['biayaPenyusutan'] ?? 0) ?></span></li>
            <li>Biaya Kesehatan: <span class="float-end"><?= formatRupiah($results['biayaKesehatan'] ?? 0) ?></span></li>
            <li>Biaya Tenaga Kerja: <span class="float-end"><?= formatRupiah($results['biayaTenagaKerja'] ?? 0) ?></span></li>
            <hr>
            <li>Total Harga Pokok Produksi (HPP): <span class="float-end fw-bold"><?= formatRupiah($results['totalHPP'] ?? 0) ?></span></li>
        </ul>
    </div>

    <div class="d-flex mt-4">
    <div>
        <?= Html::a('Batal', ['harga-jual/history'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('Simpan', [
            'class' => 'btn btn-success',
            'name' => 'action',
            'value' => 'save'
        ]) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>