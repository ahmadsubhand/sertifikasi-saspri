<?php
/** @var yii\web\View $this */
/** @var common\models\form\CattleCalculatorForm $model */
/** @var array|null $results */
/** @var array $dropdownData */
/** @var common\models\PriceList|null $priceList */
/** @var array $statsMap */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Perhitungan Harga Jual Sapi';
$formatter = \Yii::$app->formatter;

$formatRupiah = static function ($value) use ($formatter) {
    return $formatter->asCurrency($value ?? 0, 'IDR');
};

\Yii::$app->view->registerJsVar('livestockStats', $statsMap);

$js = <<<JS
(function() {
  const statsMap = window.livestockStats || {};
  const rupiahFormatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  });

  function formatCurrency(value) {
    const numeric = parseFloat(value);
    if (isNaN(numeric)) {
      return rupiahFormatter.format(0);
    }
    return rupiahFormatter.format(numeric);
  }

  function setInputValue(selector, value) {
    const el = document.querySelector(selector);
    if (!el) return;
    el.value = formatCurrency(value);
  }

  function setText(selector, value) {
    const el = document.querySelector(selector);
    if (!el) return;
    el.textContent = value;
  }

  const businessTypeInput = document.getElementById('businessType');
  const businessTypeDisplay = document.getElementById('display-business-type');
  const tambahanInput = document.getElementById('cattlecalculatorform-biayatambahan');

  function formatBusinessType(type) {
    if (type === 'breeding') {
      return 'Breeding';
    }
    if (type === 'penggemukan') {
      return 'Penggemukan';
    }
    return '';
  }

  function updateDetails() {
    const livestockId = document.getElementById('nama_sapi').value;
    const stats = statsMap[livestockId] || null;
    const businessType = stats && stats.businessType ? stats.businessType : '';

    if (businessTypeInput) {
      businessTypeInput.value = businessType;
    }
    if (businessTypeDisplay) {
      businessTypeDisplay.value = formatBusinessType(businessType);
    }

    const pedetLabel = document.getElementById('label-harga-pedet');
    if (pedetLabel) {
      pedetLabel.textContent = businessType === 'breeding' ? 'Harga Investasi Indukan' : 'Harga Pedet';
    }

    const rasInput = document.getElementById('display-ras');
    const vidInput = document.getElementById('display-vid');

    if (!stats) {
      if (rasInput) rasInput.value = '';
      if (vidInput) vidInput.value = '';
      setInputValue('#display-harga-pedet', 0);
      setInputValue('#display-harga-pakan', 0);
      setInputValue('#display-harga-kandang', 0);
      setInputValue('#display-harga-kesehatan', 0);
      setInputValue('#display-harga-tenaga-kerja', 0);
      setInputValue('#display-harga-listrik', 0);
      setInputValue('#display-harga-lahan', 0);
      setInputValue('#display-biaya-tambahan', tambahanInput ? tambahanInput.value : 0);
      setText('#display-total-hari', '0');
      setText('#display-total-bulan', '0');
      setText('#display-total-tahun', '0');
      return;
    }

    if (rasInput) rasInput.value = stats.breed || '';
    if (vidInput) vidInput.value = stats.vid || '';

    const base = businessType && stats.base && stats.base[businessType] ? stats.base[businessType] : null;
    const shared = stats.base ? stats.base.shared : null;

    if (base) {
      setInputValue('#display-harga-pedet', base.harga_pedet || 0);
      setInputValue('#display-harga-pakan', base.harga_pakan || 0);
      setInputValue('#display-harga-kandang', base.harga_kandang || 0);
      setInputValue('#display-harga-kesehatan', base.harga_kesehatan || 0);
      setInputValue('#display-harga-tenaga-kerja', base.harga_tenaga_kerja || 0);
      setInputValue('#display-harga-listrik', base.harga_listrik || 0);
      setInputValue('#display-harga-lahan', base.harga_lahan || 0);
    } else {
      setInputValue('#display-harga-pedet', 0);
      setInputValue('#display-harga-pakan', 0);
      setInputValue('#display-harga-kandang', 0);
      setInputValue('#display-harga-kesehatan', 0);
      setInputValue('#display-harga-tenaga-kerja', 0);
      setInputValue('#display-harga-listrik', 0);
      setInputValue('#display-harga-lahan', 0);
    }

    setInputValue('#display-biaya-tambahan', tambahanInput ? tambahanInput.value : 0);

    if (shared) {
      setText('#display-total-hari', shared.maintenance_days ?? '0');
      setText('#display-total-bulan', shared.maintenance_months ?? '0');
      const years = parseFloat(shared.maintenance_years ?? 0);
      setText('#display-total-tahun', years.toFixed(2));
    } else {
      setText('#display-total-hari', '0');
      setText('#display-total-bulan', '0');
      setText('#display-total-tahun', '0');
    }
  }

 document.addEventListener('change', function (event) {
    if (!event.target) return;
    if (event.target.id === 'nama_sapi' || event.target.id === 'cattlecalculatorform-biayatambahan') {
      updateDetails();
    }
  });

  document.addEventListener('input', function (event) {
    if (event.target && event.target.id === 'cattlecalculatorform-biayatambahan') {
      updateDetails();
    }
  });

  document.addEventListener('DOMContentLoaded', updateDetails);
})();
JS;

$this->registerJs($js);
?>

<div class="container-fluid px-4 pt-4">
    <?php $form = ActiveForm::begin([
        'id' => 'cattle-calculator-form',
        'action' => ['/harga-jual/index'],
        'method' => 'post',
    ]); ?>

    <div class="card mb-4 p-4">
        <div class="row g-3">
            <div class="col-md-4">
                <?= $form->field($model, 'nama_sapi')->dropDownList($dropdownData, [
                    'prompt' => 'Pilih Sapi',
                    'id'     => 'nama_sapi',
                    'style'  => 'margin-top:8px;',
                ]); ?>
            </div>
            <div class="col-md-4">
                <label class="form-label">Jenis Usaha</label>
                <input type="text" id="display-business-type" class="form-control" readonly>
                <?= $form->field($model, 'businessType')->hiddenInput(['id' => 'businessType'])->label(false); ?>
            </div>
        </div>
    </div>

    <div class="card mb-4 p-4">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Ras Sapi</label>
                <input type="text" id="display-ras" class="form-control" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Visual ID</label>
                <input type="text" id="display-vid" class="form-control" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Total Hari Pemeliharaan</label>
                <div class="form-control-plaintext"><span id="display-total-hari">0</span> hari</div>
                <div class="form-control-plaintext"><span id="display-total-bulan">0</span> bulan</div>
                <div class="form-control-plaintext"><span id="display-total-tahun">0</span> tahun</div>
            </div>
        </div>
    </div>

    <div class="card mb-4 p-4">
        <h6 class="fw-semibold mb-3">Biaya Tambahan</h6>
        <p class="text-muted small mb-3">Biaya ekstra per sapi (misalnya biaya penyembuhan sapi atau biaya tak terduga)</p>
        <div class="row g-3">
            <div class="col-md-4">
                <?= $form->field($model, 'biayaTambahan')->input('number', [
                    'min'  => 0,
                    'step' => '0.01',
                    'placeholder' => 'Rp. 0',
                    'class'       => 'form-control no-spinner',
                ]); ?>
            </div>
        </div>
    </div>

    <div class="card mb-4 p-4">
        <h6 class="fw-semibold mb-3">Komponen Biaya (auto)</h6>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label" id="label-harga-pedet">Harga Pedet</label>
                <input type="text" id="display-harga-pedet" class="form-control" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Harga Pakan</label>
                <input type="text" id="display-harga-pakan" class="form-control" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Harga Kandang</label>
                <input type="text" id="display-harga-kandang" class="form-control" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Harga Kesehatan</label>
                <input type="text" id="display-harga-kesehatan" class="form-control" readonly>
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-3">
                <label class="form-label">Harga Tenaga Kerja</label>
                <input type="text" id="display-harga-tenaga-kerja" class="form-control" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Biaya Listrik & Air</label>
                <input type="text" id="display-harga-listrik" class="form-control" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Biaya Lahan</label>
                <input type="text" id="display-harga-lahan" class="form-control" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Biaya Tambahan</label>
                <input type="text" id="display-biaya-tambahan" class="form-control" readonly>
            </div>
        </div>
    </div>

    <div class="card mb-4 p-4">
        <div class="row g-3">
            <div class="col-md-4">
                <?= $form->field($model, 'marginKeuntungan')->input('number', [
                    'step' => '0.01',
                    'min'  => 0,
                ]); ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'inflasi')->input('number', [
                    'step' => '0.01',
                    'min'  => 0,
                ]); ?>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <?= Html::submitButton('Hitung', ['class' => 'btn btn-primary']) ?>
            <?php if ($results): ?>
                <?= Html::submitButton('Simpan ke History', ['class' => 'btn btn-success', 'name' => 'save', 'value' => '1']) ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($results): ?>
        <?php $components = $results['components'] ?? []; ?>
        <div class="card p-4 shadow-sm bg-light mb-4">
            <h5 class="fw-bold mb-3">Hasil Perhitungan Sementara</h5>
            <div class="mb-3 text-muted">Jenis Usaha: <strong><?= Html::encode(($results['businessType'] ?? '') === 'breeding' ? 'Breeding' : 'Penggemukan') ?></strong></div>
            <div class="p-3 mb-3 bg-info-subtle border rounded text-center">
                <div class="text-muted fw-semibold">Harga Jual Rekomendasi</div>
                <div class="fs-4 fw-bold text-success"><?= $formatRupiah($results['hargaJual'] ?? 0) ?></div>
            </div>

            <h6 class="fw-semibold mb-2">Rincian Komponen</h6>
            <ul class="list-unstyled small mb-0">
                <?php if (($results['businessType'] ?? '') === 'breeding'): ?>
                    <li>Harga Investasi Indukan: <span class="float-end"><?= $formatRupiah($components['hargaPedet'] ?? 0) ?></span></li>
                <?php else: ?>
                    <li>Harga Pedet: <span class="float-end"><?= $formatRupiah($components['hargaPedet'] ?? 0) ?></span></li>
                <?php endif; ?>
                <li>Harga Pakan: <span class="float-end"><?= $formatRupiah($components['hargaPakan'] ?? 0) ?></span></li>
                <li>Harga Kandang: <span class="float-end"><?= $formatRupiah($components['hargaKandang'] ?? 0) ?></span></li>
                <li>Harga Kesehatan: <span class="float-end"><?= $formatRupiah($components['hargaKesehatan'] ?? 0) ?></span></li>
                <li>Harga Tenaga Kerja: <span class="float-end"><?= $formatRupiah($components['hargaTenagaKerja'] ?? 0) ?></span></li>
                <li>Biaya Listrik & Air: <span class="float-end"><?= $formatRupiah($components['hargaListrik'] ?? 0) ?></span></li>
                <li>Biaya Lahan: <span class="float-end"><?= $formatRupiah($components['hargaLahan'] ?? 0) ?></span></li>
                <li>Biaya Tambahan: <span class="float-end"><?= $formatRupiah($components['biayaTambahan'] ?? 0) ?></span></li>
                <hr>
                <li>Total Harga Pokok Produksi (HPP): <span class="float-end fw-bold"><?= $formatRupiah($results['totalHPP'] ?? 0) ?></span></li>
                <li>Margin + Inflasi: <span class="float-end"><?= $formatRupiah($results['hargaMarginInflasi'] ?? 0) ?> (Margin <?= $results['margin'] ?? 0 ?>% &amp; Inflasi <?= $results['inflasi'] ?? 0 ?>%)</span></li>
            </ul>
        </div>
    <?php endif; ?>

    <?php ActiveForm::end(); ?>
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
");
?>
