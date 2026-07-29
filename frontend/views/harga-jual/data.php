<?php
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\PriceList;

/** @var yii\web\View $this */
$this->title = 'Data Biaya Overhead';
$this->params['breadcrumbs'][] = $this->title;

// Fetch existing model for current user
$userId = \Yii::$app->user->identity->id;
$model = PriceList::findOne(['user_id' => $userId]);
// Determine form action
$action = $model === null
    ? Url::to(['price-list/create'])
    : Url::to(['price-list/update']);
?>

<div class="container-fluid px-4 pt-4">
    <form method="post" action="<?= Html::encode($action) ?>" class="price-list-form">
        <?= Html::hiddenInput(\Yii::$app->request->csrfParam, \Yii::$app->request->csrfToken) ?>

        <div class="card p-4 shadow-sm mb-4">
            <h6 class="fw-semibold">1. Biaya Tambahan (Pertahun)</h6>
            <p class="text-muted small mb-3">Isi nominal dalam Rupiah. Listrik & air dihitung per tahun, akan diprorata otomatis sesuai lama pemeliharaan saat simulasi harga jual.</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <?= Html::label('Biaya Listrik & Air', 'electricity_water') ?>
                    <?= Html::input('number', 'electricity_water', $model->electricity_water ?? '', ['class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                </div>
                <div class="col-md-4">
                    <?= Html::label('Biaya Lahan', 'land') ?>
                    <?= Html::input('number', 'land', $model->land ?? '', ['class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                </div>
            </div>
        </div>

        <div class="card p-4 shadow-sm mb-4">
            <h6 class="fw-semibold">2. Tenaga Kerja</h6>
            <p class="text-muted small mb-3">Total gaji tahunan dihitung dari gaji per pekerja × jumlah pekerja. Biaya tenaga kerja per ekor otomatis dihitung sebagai <strong>(Total Gaji / (Jumlah Pekerja × Jumlah Sapi per Pekerja))</strong> dan diprorata sesuai lama pemeliharaan.</p>
            <div class="row g-3">
                <div class="col-md-3">
                    <?= Html::label('Jumlah Tenaga Kerja', 'employee') ?>
                    <?= Html::input('number', 'employee', $model->employee ?? '', ['class' => 'form-control', 'placeholder' => '0 pekerja']) ?>
                </div>
                <div class="col-md-3">
                    <?= Html::label('Total Gaji Tenaga Kerja', 'wage') ?>
                    <?= Html::input('number', 'wage', $model->wage ?? '', ['class' => 'form-control no-spinner', 'placeholder' => 'Rp. 0']) ?>
                </div>
                <div class="col-md-3">
                    <?= Html::label('Jumlah Ternak per Tenaga Kerja', 'livestock_per_employee') ?>
                    <?= Html::input('number', 'livestock_per_employee', $model->livestock_per_employee ?? '', ['class' => 'form-control', 'placeholder' => '0 ekor']) ?>
                </div>
            </div>
        </div>

        <div class="card p-4 shadow-sm mb-4">
            <h6 class="fw-semibold">3. Margin & Inflasi</h6>
            <p class="text-muted small mb-3">Margin dan inflasi diinput dalam persen (%). Nilai ini menambah mark-up di atas Harga Pokok Produksi (HPP).</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <?= Html::label('Margin (Persen)', 'margin') ?>
                    <?= Html::input('number', 'margin', $model->margin ?? '', ['class' => 'form-control', 'placeholder' => '10 %']) ?>
                </div>
                <div class="col-md-4">
                    <?= Html::label('Inflasi (Persen)', 'inflation') ?>
                    <?= Html::input('number', 'inflation', $model->inflation ?? '', ['class' => 'form-control', 'placeholder' => '3 %']) ?>
               </div>
            </div>
        </div>

        <div class="d-flex mb-4">
            <?= Html::submitButton('Simpan', ['class' => 'btn btn-primary']) ?>
        </div>
    </form>
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
