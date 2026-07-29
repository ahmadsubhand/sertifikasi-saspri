<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/** @var common\models\Cage $model */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Update Kandang: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Daftar Kandang', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<p class="text-subtitle text-muted">Edit informasi kandang <?= Html::encode($model->name) ?></p>

<div class="page-content">
    <section class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Kandang</h4>
                </div>
                <div class="card-content">
                    <div class="card-body cage-form">
                        <?php $form = ActiveForm::begin([
                            'id' => 'update-form',
                            'method' => 'post',
                        ]); ?>
                        <div class="form-body">
                            <?= $form->field($model, 'name')->textInput([
                                'maxlength' => true, 
                                'placeholder' => 'Masukkan nama kandang'
                            ]) ?>
                            <?= $form->field($model, 'location')->textInput([
                                'maxlength' => true, 
                                'placeholder' => 'Masukkan lokasi kandang'
                            ]) ?>
                            <?= $form->field($model, 'capacity')->input('number', [
                                'placeholder' => 'Masukkan kapasitas kandang',
                                'min' => 1
                            ]) ?>
                            <?= $form->field($model, 'investasi_kandang')->input('number', [
                                        'min' => 0,
                                        'placeholder' => 'Total investasi kandang & peralatan (Rp)',
                                        'class' => 'form-control no-spinner'
                            ]) ?>
                            <?= $form->field($model, 'umur_ekonomis')->input('number', [
                                'min' => 0,
                                'step' => '0.1',
                                'placeholder' => 'Umur ekonomis (tahun)'
                            ]) ?>
                            <?= $form->field($model, 'description')->textarea([
                                'rows' => 6,
                                'placeholder' => 'Masukkan deskripsi kandang'
                            ]) ?>
                        </div>
                        <div class="form-actions d-flex justify-content-end mt-3">
                            <?= Html::a(
                                '<i class="bi bi-arrow-left"></i> Batal', 
                                ['index'], 
                                ['class' => 'btn btn-secondary me-2']
                            ) ?>
                            <?= Html::submitButton(
                                '<i class="bi bi-check"></i> Simpan Perubahan', 
                                ['class' => 'btn btn-success']
                            ) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Info Sidebar -->
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informasi Kandang</h4>
                </div>
                <div class="card-body">
                    <p><strong>Nama Saat Ini:</strong> <?= Html::encode($model->name) ?></p>
                    <p><strong>Lokasi Saat Ini:</strong> <?= Html::encode($model->location) ?></p>
                    <p><strong>Kapasitas Saat Ini:</strong> <?= Html::encode($model->capacity) ?> ekor</p>
                    <p><strong>Investasi Kandang:</strong> <?= \Yii::$app->formatter->asCurrency($model->investasi_kandang ?? 0, 'IDR') ?></p>
                    <p><strong>Umur Ekonomis:</strong> <?= Html::encode($model->umur_ekonomis ?? '-') ?> tahun</p>
                    <p><strong>Jumlah Ternak:</strong> <?= $model->getLivestockCount() ?> ekor</p>
                    <hr>
                    <div class="d-grid">
                        <?= Html::a(
                            '<i class="bi bi-eye"></i> Lihat Detail',
                            ['view', 'id' => $model->id],
                            ['class' => 'btn btn-outline-primary btn-sm']
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
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