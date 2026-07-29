<?php
/** @var yii\web\View $this */
/** @var common\models\form\CattleSimulationForm $model */
/** @var array|null $results */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Simulasi Harga Jual Sapi';
$formatter = \Yii::$app->formatter;

$formatRupiah = static function ($value) use ($formatter) {
    return $formatter->asCurrency($value ?? 0, 'IDR');
};

$this->registerCss(<<<CSS
.sim-title { font-size: clamp(22px, 4vw, 28px); font-weight: 700; }
.sim-subtitle { font-size: clamp(14px, 3vw, 16px); color: #6c757d; }
.sim-card-title { font-size: 18px; font-weight: 600; }
.sim-section-label { font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; }
CSS);
?>

<div class="container-fluid px-4 pt-4">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center">
                <div>
                    <h3 class="mb-1 sim-title"><?= Html::encode($this->title) ?></h3>
                    <p class="mb-0 sim-subtitle">Hitung secara mandiri tanpa perlu login.</p>
                </div>
                <div class="alert alert-info mb-0 mt-3 mt-lg-0 py-2 px-3">
                    <small class="mb-0 d-block">Masukkan semua komponen biaya. Hasil tidak disimpan.</small>
                </div>
            </div>
        </div>
    </div>
    <div class="row gy-4">
        <div class="col-lg-7 col-xl-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div>
                        <h5 class="mb-0 sim-card-title">Input Data Simulasi</h5>
                        <small class="sim-subtitle">Masukkan komponen biaya yang ingin Anda uji</small>
                    </div>
                    <div class="d-none d-md-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border">Penggemukan</span>
                        <span class="badge bg-light text-dark border">Breeding</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'simulation-form',
                        'options' => ['class' => 'row g-3'],
                    ]); ?>

                    <div class="col-12 col-md-6">
                        <?= $form->field($model, 'namaSimulasi')->textInput(['placeholder' => 'Contoh: Simulasi Sapi Bali']) ?>
                    </div>
                    <div class="col-12 col-md-6">
                        <?= $form->field($model, 'businessType')->dropDownList([
                            'penggemukan' => 'Penggemukan',
                            'breeding'    => 'Breeding',
                        ], ['prompt' => 'Pilih jenis usaha']) ?>
                    </div>

                    <div class="col-12">
                        <h6 class="fw-semibold mt-3 mb-0 sim-section-label">Komponen Utama</h6>
                    </div>
                    <div class="col-12 col-md-6">
                        <?= $form->field($model, 'hargaPedet')->input('number', ['class' => 'form-control no-spinner', 'min' => 0, 'step' => '0.01', 'placeholder' => 'Rp. 0']) ?>
                    </div>
                    <div class="col-12 col-md-6">
                        <?= $form->field($model, 'biayaTambahan')->input('number', ['min' => 0, 'step' => '0.01', 'value' => $model->biayaTambahan ?? 0, 'class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                    </div>

                    <div class="col-12">
                        <h6 class="fw-semibold mb-0 sim-section-label">Biaya Pakan</h6>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'pakanHijauan')->input('number', ['min' => 0, 'step' => '0.01', 'class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'pakanKonsentrat')->input('number', ['min' => 0, 'step' => '0.01', 'class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'feedAdditive')->input('number', ['min' => 0, 'step' => '0.01', 'class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                    </div>

                    <div class="col-12">
                        <h6 class="fw-semibold mb-0 sim-section-label">Biaya Kesehatan</h6>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'insemination')->input('number', ['min' => 0, 'step' => '0.01', 'class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'vaksin')->input('number', ['min' => 0, 'step' => '0.01', 'class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'vitamin')->input('number', ['min' => 0, 'step' => '0.01', 'class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'pemeriksaanKebuntingan')->input('number', ['min' => 0, 'step' => '0.01', 'class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'antibiotik')->input('number', ['min' => 0, 'step' => '0.01', 'class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'anthelmintic')->input('number', ['min' => 0, 'step' => '0.01', 'class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                    </div>

                    <div class="col-12">
                        <h6 class="fw-semibold mb-0 sim-section-label">Penyusutan & Tenaga Kerja</h6>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'investasiKandang')->input('number', ['min' => 0, 'step' => '0.01', 'class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'tenagaKerja')->input('number', ['min' => 0, 'step' => '0.01', 'class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'listrik')->input('number', ['min' => 0, 'step' => '0.01', 'class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'lahan')->input('number', ['min' => 0, 'step' => '0.01', 'class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                    </div>

                    <div class="col-12">
                        <h6 class="fw-semibold mb-0 sim-section-label">Parameter Perhitungan</h6>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'marginKeuntungan')->input('number', ['min' => 0, 'step' => '0.01', 'placeholder' => '0%']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'inflasi')->input('number', ['min' => 0, 'step' => '0.01', 'placeholder' => '0%']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'maintenanceMonths')->input('number', ['min' => 0, 'step' => '0.01', 'placeholder' => 'Misal: 6']) ?>
                    </div>

                    <div class="col-12 text-end mt-3">
                        <?= Html::submitButton('Hitung Simulasi', ['class' => 'btn btn-primary']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5 col-xl-4">
            <?php if ($results): ?>
                <?php
                    $components = $results['components'] ?? [];
                    $maintenanceMonths = $results['maintenance']['months'] ?? 0;
                ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0">Ringkasan Simulasi</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-2">Jenis Usaha: <strong><?= Html::encode(($results['businessType'] ?? '') === 'breeding' ? 'Breeding' : 'Penggemukan') ?></strong></p>
                        <?php if (!empty($results['namaSimulasi'])): ?>
                            <p class="text-muted mb-2">Nama Simulasi: <strong><?= Html::encode($results['namaSimulasi']) ?></strong></p>
                        <?php endif; ?>
                        <div class="p-3 mb-3 border rounded text-center">
                            <div class="text-muted fw-semibold">Harga Jual Rekomendasi</div>
                            <div class="fs-4 fw-bold text-success"><?= $formatRupiah($results['hargaJual'] ?? 0) ?></div>
                        </div>

                        <h6 class="fw-semibold mb-2">Rincian Komponen</h6>
                        <ul class="list-unstyled small mb-0">
                            <li><?= ($results['businessType'] ?? '') === 'breeding' ? 'Investasi Indukan' : 'Harga Pedet' ?> <span class="float-end fw-semibold"><?= $formatRupiah($components['hargaPedet'] ?? 0) ?></span></li>
                            <li>Biaya Pakan <span class="float-end fw-semibold"><?= $formatRupiah($components['hargaPakan'] ?? 0) ?></span></li>
                            <li>Biaya Kesehatan <span class="float-end fw-semibold"><?= $formatRupiah($components['hargaKesehatan'] ?? 0) ?></span></li>
                            <li>Investasi Kandang & Peralatan <span class="float-end fw-semibold"><?= $formatRupiah($components['hargaKandang'] ?? 0) ?></span></li>
                            <li>Gaji Tenaga Kerja <span class="float-end fw-semibold"><?= $formatRupiah($components['hargaTenagaKerja'] ?? 0) ?></span></li>
                            <li>Biaya Listrik & Air <span class="float-end fw-semibold"><?= $formatRupiah($components['hargaListrik'] ?? 0) ?></span></li>
                            <li>Biaya Lahan <span class="float-end fw-semibold"><?= $formatRupiah($components['hargaLahan'] ?? 0) ?></span></li>
                            <li>Biaya Tambahan <span class="float-end fw-semibold"><?= $formatRupiah($components['biayaTambahan'] ?? 0) ?></span></li>
                            <hr>
                            <li>Total HPP <span class="float-end fw-bold"><?= $formatRupiah($results['totalHPP'] ?? 0) ?></span></li>
                            <li>Margin + Inflasi <span class="float-end"><?= $formatRupiah($results['hargaMarginInflasi'] ?? 0) ?> (Margin <?= $results['margin'] ?? 0 ?>% &amp; Inflasi <?= $results['inflasi'] ?? 0 ?>%)</span></li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0 fw-semibold">Detail Tambahan</h6>
                    </div>
                    <div class="card-body small">
                        <div class="mb-3">
                            <h6 class="fw-semibold">Rincian Pakan</h6>
                            <ul class="list-unstyled mb-0">
                                <li>Pakan Hijauan <span class="float-end"><?= $formatRupiah($results['feedBreakdown']['forage'] ?? 0) ?></span></li>
                                <li>Konsentrat <span class="float-end"><?= $formatRupiah($results['feedBreakdown']['concentrate'] ?? 0) ?></span></li>
                                <li>Feed Additive <span class="float-end"><?= $formatRupiah($results['feedBreakdown']['additive'] ?? 0) ?></span></li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-semibold">Rincian Kesehatan</h6>
                            <ul class="list-unstyled mb-0">
                                <li>Inseminasi <span class="float-end"><?= $formatRupiah($results['healthBreakdown']['insemination'] ?? 0) ?></span></li>
                                <li>Vaksin <span class="float-end"><?= $formatRupiah($results['healthBreakdown']['vaccine'] ?? 0) ?></span></li>
                                <li>Vitamin <span class="float-end"><?= $formatRupiah($results['healthBreakdown']['vitamin'] ?? 0) ?></span></li>
                                <li>Pemeriksaan Kebuntingan <span class="float-end"><?= $formatRupiah($results['healthBreakdown']['pregnancy_check'] ?? 0) ?></span></li>
                                <li>Antibiotik <span class="float-end"><?= $formatRupiah($results['healthBreakdown']['antibiotics'] ?? 0) ?></span></li>
                                <li>Obat Cacing <span class="float-end"><?= $formatRupiah($results['healthBreakdown']['anthelmintic'] ?? 0) ?></span></li>
                            </ul>
                        </div>

                        <div>
                            <h6 class="fw-semibold">Durasi Pemeliharaan</h6>
                            <ul class="list-unstyled mb-0">
                                <li>Bulan: <span class="float-end"><?= Html::encode($maintenanceMonths) ?></span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div class="text-center text-muted">
                            <div class="mb-2"><i class="bi bi-calculator" style="font-size: 2rem;"></i></div>
                            <p class="mb-0">Masukkan data simulasi pada formulir di samping, lalu tekan <strong>Hitung Simulasi</strong> untuk melihat hasilnya.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$this->registerCss("
    /* Hilangkan spinner Chrome, Edge, Safari */
    input.no-spinner::-webkit-outer-spin-button,
    input.no-spinner::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }




    /* Hilangkan spinner Firefox */
    input.no-spinner[type=number] {
        -moz-appearance: textfield;
    }
    
    
    /* Samakan tinggi semua label agar input sejajar rapi */
    .form-group label,
    .form-label {
        min-height: 40px;
        line-height: 1.2;
        white-space: normal;
    }
        
");
?>
