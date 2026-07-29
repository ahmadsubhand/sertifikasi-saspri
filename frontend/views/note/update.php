<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var \yii\web\View $this
 * @var \yii\widgets\ActiveForm $form
 * @var common\models\Note $model
 */

$this->title = 'Update Catatan: ' . $model->livestock->name;
?>

<div class="cage-update">
        <?php $form = ActiveForm::begin([
            'id' => 'update-form',
            'method' => 'put',
        ]); ?>
<div class="form-body">
    <div class="form-body row">
        <div class = "col">
            <br>
            <h4 class="card-title mb-4">Catatan Ternak</h4>
            <?= $form->field($model, 'id')->hiddenInput(['value' => $model->id])->label(false) ?>

            <?= $form->field($model, 'details')->textarea(['rows' => 6]) ?>
            <br>
            <br>
            <h4 class="card-title mb-4">Data Pakan Hari Ini</h4>
            <?= $form->field($model, 'livestock_feed')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'forage_costs')->input('number', [
                'id' => 'forage_costs',
                'class' => 'form-control no-spinner',
                'placeholder' => 'Rp. 0'
            ]) ?>

            <?= $form->field($model, 'forage_weight')->input('number', [
                'id' => 'forage_weight',
                'placeholder' => '0 kg'
            ]) ?>

            <?= $form->field($model, 'consentrate_costs')->input('number', [
                'id' => 'consentrate_costs',
                'class' => 'form-control no-spinner',
                'placeholder' => 'Rp. 0'
            ]) ?>

            <?= $form->field($model, 'consentrate_weight')->input('number', [
                'id' => 'consentrate_weight',
                'placeholder' => '0 kg'
            ]) ?>

            <?= $form->field($model, 'additive_costs')->input('number', [
                'id' => 'additive_costs',
                'class' => 'form-control no-spinner',
                'placeholder' => 'Rp. 0'
            ]) ?>

            <?= $form->field($model, 'additive_weight')->input('number', [
                'id' => 'additive_weight',
                'placeholder' => '0 kg'
            ]) ?>

            <br>
            <br>
            <h4 class="card-title mb-4">Kesehatan (isi harga apabila dilakukan pada hari ini)</h4>
            <?= $form->field($model, 'vaccine')->input('number', [
                'class' => 'form-control no-spinner',
                'placeholder' => 'Rp. 0'
            ]) ?>

            <?= $form->field($model, 'insemination')->input('number', [
                'class' => 'form-control no-spinner',
                'placeholder' => 'Rp. 0'
            ]) ?>

            <?= $form->field($model, 'pregnancy_check')->input('number', [
                'class' => 'form-control no-spinner',
                'placeholder' => 'Rp. 0'
            ]) ?>

            <?= $form->field($model, 'antibiotics')->input('number', [
                'class' => 'form-control no-spinner',
                'placeholder' => 'Rp. 0'
            ]) ?>

            <?= $form->field($model, 'anthelmintic')->input('number', [
                'class' => 'form-control no-spinner',
                'placeholder' => 'Rp. 0'
            ]) ?>

            <?= $form->field($model, 'vitamin')->input('number', [
                'class' => 'form-control no-spinner',
                'placeholder' => 'Rp. 0'
            ]) ?>

        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            <a href="<?= \yii\helpers\Url::to(['/note/index']) ?>" class="btn btn-primary me-1">
                Cancel
            </a>
        </div>
    <?php ActiveForm::end(); ?>
</div>
