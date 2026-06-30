<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var common\models\form\RegisterForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Signup';

?>
<div class="site-signup d-flex justify-content-center align-items-center mx-auto">
    <div class="border shadow-sm rounded-1 bg-white p-5 width-50-100">
        <h1 class="fw-bold text-start text-dark h2 mb-2 font-monospace"><?= Html::encode($this->title) ?></h1>
    
        <p class="text-muted text-start">Please fill out the following fields to signup:</p>
    
        <div class="row">
            <div >
                <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
    
                <?= $form->field($model, 'username')->textInput(['autofocus' => true])->label('Username') ?>
    
                <?= $form->field($model, 'email') ?>
    
                <?= $form->field($model, 'password')->passwordInput()->label('Password') ?>
    
                <?= $form->field($model, 'full_name')->textInput(['autofocus' => true]) ?>
    
                <?= $form->field($model, 'phone_number')->textInput() ?>
    
                <div class="form-group w-100">
                    <?= Html::submitButton('Signup', ['class' => 'btn s-btn-main w-100', 'name' => 'signup-button']) ?>
                </div>
    
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>