<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->title = 'History Perhitungan Harga Jual';

/**
 * @var yii\web\View $this 
 * @var yii\widgets\ActiveForm $form 
 * @var common\models\HargaJual $model 
 */
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card p-4 shadow-sm">
                <?php $form = ActiveForm::begin(); ?>

                <?= $form->field($model, 'nama_sapi')->dropDownList(
                    ['' => 'Pilih Sapi', 'Sapi A' => 'Sapi A', 'Sapi B' => 'Sapi B'],
                    ['class' => 'form-control']
                ) ?>

                <?= $form->field($model, 'biaya_pakan')->textInput(['placeholder' => 'Rp. 0', 'class' => 'form-control biaya-input']) ?>
                <?= $form->field($model, 'biaya_suplemen')->textInput(['placeholder' => 'Rp. 0', 'class' => 'form-control biaya-input']) ?>
                <?= $form->field($model, 'biaya_obat')->textInput(['placeholder' => 'Rp. 0', 'class' => 'form-control biaya-input']) ?>
                <?= $form->field($model, 'biaya_peralatan')->textInput(['placeholder' => 'Rp. 0', 'class' => 'form-control biaya-input']) ?>
                <?= $form->field($model, 'upah_tenaga_kerja')->textInput(['placeholder' => 'Rp. 0', 'class' => 'form-control biaya-input']) ?>
                <?= $form->field($model, 'biaya_anak_sapi')->textInput(['placeholder' => 'Rp. 0', 'class' => 'form-control biaya-input']) ?>

                <div class="form-group mt-3">
                    <?= Html::submitButton('Hitung', ['class' => 'btn btn-success']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h6><strong>Total Harga Jual</strong></h6>
                <input type="text" id="total-harga" class="form-control" value="Rp. 0" readonly>
            </div>
        </div>
    </div>
</div>

