<?php 
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\StringHelper;


/**
 * @var yii\web\View $this 
 * @var yii\widgets\ActiveForm $form 
 * @var common\models\Cage $model 
 */

 $this->title = 'Tambah Kandang';
 ?>

<div class="page-content"> 
    <section class="row">
        <div class="col-12 col-lg-5">
            <section class="section">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body cage-form">
                            <h4 class="card-title mb-4">Buat Kandang Baru</h4>
                                <?php $form = ActiveForm::begin([
                                    'id' => 'cage-form',
                                    'action' => ['create'],
                                    'method' => 'POST',
                                ]); ?>
                                <div class="form-body">
                                    <?= $form->field($model, 'name')->textInput(['placeholder' => 'Ketik nama kandang', 'maxlength' => true]) ?>
                                    <?= $form->field($model, 'location')->textInput(['placeholder' => 'Ketik lokasi kandang', 'maxlength' => true]) ?>
                                    <?= $form->field($model, 'capacity')->input('number', ['min' => 1, 'placeholder' => 'Masukkan kapasitas kandang']) ?>
                                    <?= $form->field($model, 'investasi_kandang')->input('number', [
                                        'min' => 0,
                                        'placeholder' => 'Total investasi kandang & peralatan (Rp)',
                                        'class' => 'form-control no-spinner'
                                    ]) ?>

                                    <?= $form->field($model, 'umur_ekonomis')->input('number', ['min' => 0, 'step' => '0.1', 'placeholder' => 'Umur ekonomis (tahun)']) ?>
                                    <?= $form->field($model, 'description')->textarea(['rows' => 6, 'placeholder' => 'Masukkan deskripsi kandang']) ?>
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
                    <h4>Kandang Terdaftar</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                        <?php if (!empty($cage)): ?>
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Nama Kandang</th>
                                                <th>Lokasi</th>
                                                <th>Kapasitas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $counter = 1;?>
                                            <?php foreach ($cage as $cage): ?>
                                            <tr>
                                                <td class="text-bold-500"><?= $counter++ ?></td> <!-- Menambahkan kolom nomor -->
                                                <td class="text-bold-500 post"><?= StringHelper::truncate($cage->name, 12, '...') ?></td>
                                                <td><?= StringHelper::truncate($cage->location, 12, '...') ?></td>
                                                <td><?= $cage->getLivestockCount() . '/' . $cage->capacity ?></td>
                                                <td><div class="comment-actions">
                                                    <button class="btn icon icon-left btn-primary me-2 text-nowrap" data-bs-toggle="modal" data-bs-target="#modalView<?= $cage->id ?>">
                                                        <i class="bi bi-eye-fill"></i> Show
                                                    </button>
                                                    <?= Html::a(
                                                        '<i class="bi bi-pencil-square"></i> Edit',
                                                        ['cage/update', 'id' => $cage->id],
                                                        [
                                                            'class' => 'btn icon icon-left btn-warning me-2 text-nowrap',
                                                            'data' => [
                                                                'method' => 'put', // Use GET method to access the update page
                                                            ],
                                                        ]
                                                    ) ?>                                                    
                                                    <?= Html::a(
                                                        '<i class="bi bi-x-circle"></i> Remove',
                                                        ['cage/delete', 'id' => $cage->id],
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
                                            <div class="modal fade" id="modalView<?= $cage->id ?>" tabindex="-1" aria-labelledby="modalViewLabel<?= $cage->id ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalViewLabel<?= $cage->id ?>">Detail Kandang</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                        <?php foreach ($cage->attributes as $attributeLabels => $value): ?>
                                                            <?php foreach ($cage->attributeLabels() as $keyattr => $valueattr):?>
                                                                <?php if ($keyattr === $attributeLabels ):?>
                                                                    <strong><?= ucfirst(str_replace('_', ' ', $valueattr)) ?> : </strong> <?= $value ?><br>
                                                                <?php endif; ?>
                                                               
                                                                <?php endforeach; ?>
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
                                        <p>Anda belum memiliki kandang.</p>
                                    <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
