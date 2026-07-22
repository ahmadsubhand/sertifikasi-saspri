<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Cage $model */
/** @var yii\widgets\ActiveForm $form */

$this->title = 'Update BCS: ' . $model->livestock_id;
?>

<div class="bcs-update">

    <!-- Alert Messages untuk BCS -->
    <?php if (\Yii::$app->session->hasFlash('bcs_success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> <?= \Yii::$app->session->getFlash('bcs_success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (\Yii::$app->session->hasFlash('bcs_error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle"></i> <?= \Yii::$app->session->getFlash('bcs_error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="bcs-form">
        <?php $form = ActiveForm::begin([
            'id' => 'update-form',
            'method' => 'put',
        ]); ?>
            <?= $form->field($model, 'body_weight')->input('number', ['placeholder' => 'Masukkan berat badan (kg)']) ?>
            <?= $form->field($model, 'chest_size')->input('number', ['placeholder' => 'Masukkan ukuran dada (cm)']) ?>
            <?= $form->field($model, 'hips')->input('number', ['placeholder' => 'Masukkan ukuran pinggul (cm)']) ?>
        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            <button type="submit" href= '<?= yii\helpers\Url::toRoute(['/bcs/index']) ?>' class="btn btn-primary me-1">Cancel</button>
        </div>


        <?php ActiveForm::end(); ?>
    </div>
</div>
