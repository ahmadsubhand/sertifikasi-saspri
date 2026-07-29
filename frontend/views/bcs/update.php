<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Cage $model */
/** @var yii\widgets\ActiveForm $form */

$this->title = 'Update BCS: ' . $model->livestock_id;
?>

<div class="bcs-update">
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
