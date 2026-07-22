<?php

use common\models\Livestock;
use common\models\BodyCountScore;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this 
 * @var yii\widgets\ActiveForm $form 
 * @var common\models\BodyCountScore $model 
 */

 $this->title = 'Tambah BCS';
 $this->registerJs("
    // Add symbol 'kg' after the input in the feed_weight field
    $('#bcs-form #body_weight').on('input', function() {
        var value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(value + ' kg');
    });
    // Add symbol 'cm' after the input in the feed_weight field
    $('#bcs-form #chest_size').on('input', function() {
        var value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(value + ' cm');
    });
    // Add symbol 'cm' after the input in the feed_weight field
    $('#bcs-form #hips').on('input', function() {
        var value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(value + ' cm');
    });

    // Before the form is submitted, strip the symbols
    $('#bcs-form').on('submit', function() {
        var chestSizeValue = $('#bcs-form #chest_size').val().replace(' cm', '');
        $('#bcs-form #chest_size').val(chestSizeValue);
        var bodyWeightValue = $('#bcs-form #body_weight').val().replace(' kg', '');
        $('#bcs-form #body_weight').val(bodyWeightValue);
        var hipsValue = $('#bcs-form #hips').val().replace(' cm', '');
        $('#bcs-form #hips').val(hipsValue);
    });
");
 ?>

<div class="page-content"> 

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

    <section class="row">
        <div class="col-12 col-lg-5">
            <section class="section">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body bcs-form">
                            <h4 class="card-title mb-4">Buat BCS Baru</h4>
                                <?php $form = ActiveForm::begin([
                                    'id' => 'bcs-form',
                                    'action' => ['create'],
                                    'method' => 'POST',
                                ]); ?>
                                <div class="form-body">
                                    <?= $form->field($model, 'livestock_id')->dropDownList(
                                        \yii\helpers\ArrayHelper::map(Livestock::find()->where(['user_id' => \Yii::$app->user->id])->all(), 'id', 'name'),
                                        ['prompt' => 'Pilih Sapi']
                                    )->label('Nama Ternak') ?>
                                    <?= $form->field($model, 'chest_size')->textInput([
                                                'id' => 'chest_size',
                                                'type' => 'text',
                                                'maxlength'=> true,
                                                'placeholder' => 'Masukkan ukuran dada (cm)'
                                                ]) ?>

                                            <?= $form->field($model, 'body_weight')->textInput([
                                                'placeholder' => 'Masukkan berat badan (kg)',
                                                'id'=> 'body_weight',
                                                'type'=> 'text',
                                                'maxlength'=> true,
                                                ]) ?>

                                            <?= $form->field($model, 'hips')->textInput([
                                                'placeholder' => 'Masukkan ukuran pinggul (cm)',
                                                'id'=> 'hips',
                                                'type'=> 'text',
                                                'maxlength'=> true,
                                                ]) ?>
                                </div>
                                <div class="form-actions d-flex justify-content-end mt-3">
                                            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>

                                    <!-- <button type="submit" class="btn btn-primary me-1">Submit</button> -->
                                </div>
                                <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h4>Log BCS</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                        <?php if (!empty($bcs)): ?>
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Nama Sapi</th>
                                                <th>Berat Sapi</th>
                                                <th>Lingkar Dada</th>
                                                <th>Ukuran Pinggul</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $counter=1; ?>
                                            <?php foreach ($bcs as $bcs): ?>
                                                <tr>
                                                    <td class="text-bold-500"><?= $counter++ ?></td> <!-- Menambahkan kolom nomor -->
                                                    <td class="text-bold-500 post"><?= $bcs->livestock->name ?></td>
                                                    <td><?= $bcs->body_weight ?> kg</td>
                                                    <td><?= $bcs->chest_size ?> cm</td>
                                                    <td><?= $bcs->hips ?> cm</td>  
                                                    <td><div class="comment-actions">
                                                        <button class="btn icon icon-left btn-primary me-2 text-nowrap" data-bs-toggle="modal" data-bs-target="#modalView<?= $bcs->livestock_id ?>">
                                                            <i class="bi bi-eye-fill"></i> Show
                                                        </button>
                                                        <?= Html::a(
                                                            '<i class="bi bi-pencil-square"></i> Edit',
                                                            ['bcs/update', 'id' => $bcs->id],
                                                            [
                                                                'class' => 'btn icon icon-left btn-warning me-2 text-nowrap',
                                                                'data' => [
                                                                    'method' => 'put',
                                                                    ]
                                                            ]
                                                        ) ?>
                                                        <?= Html::a(
                                                            '<i class="bi bi-x-circle"></i> Remove',
                                                            ['bcs/delete', 'id' => $bcs->id],
                                                            [
                                                                'class' => 'btn icon icon-left btn-danger me-2 text-nowrap',
                                                                'data' => [
                                                                    'confirm' => 'Apakah Anda yakin ingin menghapus kandang ini?',
                                                                    'method' => 'delete',
                                                                ],
                                                            ]
                                                        ) ?>
                                                    </div></td>
                                                </tr>

                                                <div class="modal fade" id="modalView<?= $bcs->livestock_id ?>" tabindex="-1" aria-labelledby="modalViewLabel<?= $bcs->livestock_id ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="modalViewLabel<?= $bcs->livestock_id ?>">Riwayat Pencatatan BCS - <?= $bcs->livestock->name ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <?php                                                                 $history = BodyCountScore::find()->where(['livestock_id' => $bcs->livestock_id])->orderBy(['created_at' => SORT_DESC])->all();
                                                                foreach ($history as $record): ?>
                                                                    <strong><?= $record->created_at ?>:</strong><br>
                                                                    Berat: <?= $record->body_weight ?> kg<br>
                                                                    Lingkar Dada: <?= $record->chest_size ?> cm<br>
                                                                    Ukuran Pinggul: <?= $record->hips ?> cm<br><br>
                                                                <?php endforeach; ?>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    <?php else: ?>
                                        <p>Anda belum memiliki log BCS.</p>
                                    <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
