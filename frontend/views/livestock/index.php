<?php

use common\models\Cage;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;


/**
 * @var \yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var common\models\Livestock $model
 * @var common\models\LivestockImage $image
 * @var yii\data\Pagination $pagination
 * @var array $cageOptions
 */

$this->title = 'Tambah Sapi';
$this->registerJs("
    // Add symbol 'kg' after the input in the feed_weight field
    $('#livestock-form #body_weight').on('input', function() {
        var value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(value + ' kg');
    });
    // Add symbol 'cm' after the input in the feed_weight field
    $('#livestock-form #chest_size').on('input', function() {
        var value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(value + ' cm');
    });
    // Add symbol 'cm' after the input in the feed_weight field
    $('#livestock-form #hips').on('input', function() {
        var value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(value + ' cm');
    });

    // Before the form is submitted, strip the symbols
    $('#livestock-form').on('submit', function() {
        var chestSizeValue = $('#livestock-form #chest_size').val().replace(' cm', '');
        $('#livestock-form #chest_size').val(chestSizeValue);
        var bodyWeightValue = $('#livestock-form #body_weight').val().replace(' kg', '');
        $('#livestock-form #body_weight').val(bodyWeightValue);
        var hipsValue = $('#livestock-form #hips').val().replace(' cm', '');
        $('#livestock-form #hips').val(hipsValue);
    });
");
?>
<?php
// Check if there is a flash message to show
if (\Yii::$app->session->hasFlash('error')): ?>
    <div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="warningModalLabel">Peringatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= \Yii::$app->session->getFlash('error') ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (\Yii::$app->session->hasFlash('success')): ?>
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">Sukses</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= \Yii::$app->session->getFlash('success') ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<div class="page-content"> 
    <section class="row">
    <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Sapi Terdaftar</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example" class="table table-borderless mb-0">
                            <?php if (!empty($livestock)): ?> 
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>VID</th>
                                    <th>Nama</th>
                                    <th>Kandang</th>
                                    <th>Umur</th>
                                    <th>Kesehatan</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $counter = 1; ?>
                            <?php foreach ($livestock as $livestock): ?>
                                <tr>
                                    <td class="text-bold-500"><?= $counter++ ?></td> <!-- Menambahkan kolom nomor -->
                                    <td class="text-bold-500"><?= $livestock->vid?></td>
                                    <td class="text-bold-500"><?= $livestock->name ?></td>
                                    <td class="text-bold-500"><?= $livestock->cage->name ?></td>
                                    <td class="text-bold-500"><?= $livestock->getAgeLabel() ?></td>
                                    <td class="text-bold-500"><?= $livestock->health ?></td>
                                    <td><div class="comment-actions">
                                                    <button class="btn icon icon-left btn-primary me-2 text-nowrap" data-bs-toggle="modal" data-bs-target="#modalView<?= $livestock->id ?>">
                                                        <i class="bi bi-eye-fill"></i> Show
                                                    </button>
                                                    <?= Html::a(
                                                        '<i class="bi bi-pencil-square"></i> Edit',
                                                        ['livestock/update', 'id' => $livestock->id],
                                                        [
                                                            'class' => 'btn icon icon-left btn-warning me-2 text-nowrap',
                                                            'data' => [
                                                                'method' => 'put', // Use GET method to access the update page
                                                            ],
                                                        ]
                                                    ) ?>                                                    
                                                    <?= Html::a(
                                                        '<i class="bi bi-x-circle"></i> Remove',
                                                        ['livestock/delete', 'id' => $livestock->id],
                                                        [
                                                            'class' => 'btn icon icon-left btn-danger me-2 text-nowrap',
                                                            'data' => [
                                                                'confirm' => 'Apakah Anda yakin ingin menghapus kandang ini?',
                                                                'method' => 'delete',
                                                            ],
                                                        ]
                                                    ) ?>
                                                     <?= Html::a('Lihat Data BCS', ['livestock/bcs-data', 'id' => $livestock->id], [
                                                        'class' => 'btn btn-info',
                                                    ]) ?>
                                                    <?= Html::a(
                                                        '<i class="bi bi-journal-text"></i> Lihat Catatan',
                                                        ['note/livestock-notes', 'id' => $livestock->id],
                                                        [
                                                            'class' => 'btn text-dark',
                                                            'style' => 'background-color: #4df0ddff; border-color: #4df0ddff; margin-left: 5px;',
                                                        ]
                                                    ) ?>


                                                </div></td>
                                </tr>
                                <div class="modal fade" id="modalView<?= $livestock->id ?>" tabindex="-1" aria-labelledby="modalViewLabel<?= $livestock->id ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalViewLabel<?= $livestock->id ?>">Detail Sapi</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                        
                                                        <?php foreach ($livestock->attributes as $attributeLabels => $value): ?>
                                                            <?php foreach ($livestock->attributeLabels() as $keyattr => $valueattr):?>
                                                                <?php if ($keyattr === $attributeLabels ):?>
                                                                    <?php
                                                                        // Custom labels and formatting
                                                                        if ($keyattr === 'cage_id') {
                                                                            $value = $livestock->cage->name;
                                                                        }elseif ($keyattr === 'chest_size') {
                                                                            $label = ucfirst(str_replace('_', ' ', $valueattr));
                                                                            $value = $value . ' cm';
                                                                        } elseif ($keyattr === 'age') {
                                                                            $label = ucfirst(str_replace('_', ' ', $valueattr));
                                                                            $value = $livestock->getAgeLabel();
                                                                        } elseif ($keyattr === 'body_weight') {
                                                                            $label = ucfirst(str_replace('_', ' ', $valueattr));
                                                                            $value = $value . ' kg';
                                                                        } elseif ($keyattr === 'hips') {
                                                                            $label = ucfirst(str_replace('_', ' ', $valueattr));
                                                                            $value = $value . ' cm';
                                                                        } else {
                                                                            $label = ucfirst(str_replace('_', ' ', $valueattr));
                                                                        }
                                                                        ?>
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
                            <?= LinkPager::widget([
                                'pagination' => $pagination,
                                'options' => ['class' => 'pagination pagination-primary mt-3'], // Set the pagination class
                                'linkOptions' => ['class' => 'page-link'], // Set the link class
                                'pageCssClass' => 'page-item', // Set the page item class
                                'prevPageCssClass' => 'page-item', // Set the previous button class
                                'nextPageCssClass' => 'page-item', // Set the next button class
                                'activePageCssClass' => 'active', // Set the active page class
                                'prevPageLabel' => '<i class="bi bi-chevron-left"></i>', // Previous page label
                                'nextPageLabel' => '<i class="bi bi-chevron-right"></i>', // Next page label
                                'disabledPageCssClass' => 'disabled', // Disabled page class
                            ]); ?>
                            <?php else: ?>
                               <p>Anda belum memiliki Sapi.</p>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>    
    <div class="col-12">
            <section class="section">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Buat Sapi Baru</h4>
                                <?php $form = ActiveForm::begin([
                                    'id'=> 'livestock-form',
                                    'action'=> ['create'],
                                    'method'=> 'post',
                                    'options' => ['enctype' => 'multipart/form-data'],
                                ]); ?>
                                    <div class="form-body">
                                        <div class="form-body row">
                                            <div class = "col">
                                            <?= $form->field($model, 'eid')->textInput([
                                                'maxlength'   => 32,
                                                'placeholder' => 'Masukkan kode EID (32 digit angka)',
                                                'inputmode'   => 'numeric', // tampilkan keypad angka di mobile
                                                'oninput'     => 'this.value = this.value.replace(/[^0-9]/g, "").slice(0,32);',
                                            ]) ?>
                                            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Masukkan nama hewan ternak']) ?>
                                            <?= $form->field($model, 'birthdate')->input('date', ['placeholder' => 'Masukkan tanggal lahir']) ?>
                                            <?= $form->field($model, 'cage_id')->dropDownList(
                                                \yii\helpers\ArrayHelper::map(Cage::find()->where(['user_id' => \Yii::$app->user->id])->all(), 'id', 'name'),
                                                ['prompt' => 'Pilih Kandang']
                                            ) ?>

                                            <?= $form->field($model, 'type_of_livestock')->dropDownList([
                                                'Kambing' => 'Kambing',
                                                'Sapi' => 'Sapi',
                                            ], ['prompt' => 'Pilih jenis hewan ternak']) ?>

                                            <?= $form->field($model, 'breed_of_livestock')->dropDownList([
                                                'Madura' => 'Madura',
                                                'Bali' => 'Bali',
                                                'Limousin' => 'Limousin',
                                                'Brahman' => 'Brahman',
                                            ], ['prompt' => 'Pilih ras hewan ternak']) ?>

                                            <?= $form->field($model, 'purpose')->dropDownList([
                                                'Indukan' => 'Indukan',
                                                'Penggemukan' => 'Penggemukan',
                                                'Tabungan' => 'Tabungan',
                                                'Belum Tahu' => 'Belum Tahu',
                                            ], ['prompt' => 'Pilih tujuan pemeliharaan']) ?>

                                            <?= $form->field($model, 'maintenance')->dropDownList([
                                                'Kandang' => 'Kandang',
                                                'Gembala' => 'Gembala',
                                                'Campuran' => 'Campuran',
                                            ], ['prompt' => 'Pilih jenis pemeliharaan']) ?>

                                            <?= $form->field($model, 'source')->dropDownList([
                                                'Sejak Lahir' => 'Sejak Lahir',
                                                'Bantuan Pemerintah' => 'Bantuan Pemerintah',
                                                'Beli' => 'Beli',
                                                'Beli dari Luar Kelompok' => 'Beli dari Luar Kelompok',
                                                'Beli dari Dalam Kelompok' => 'Beli dari Dalam Kelompok',
                                                'Inseminasi Buatan' => 'Inseminasi Buatan',
                                                'Kawin Alam' => 'Kawin Alam',
                                                'Tidak Tahu' => 'Tidak Tahu',
                                            ], ['prompt' => 'Pilih sumber hewan ternak']) ?>
                                            <!-- <?= $form->field($model, 'livestock_image[]')->fileInput(['multiple' => true, 'class' => 'form-control']) ?> -->
                                            </div>
                                            <div class = "col">
                                                <?= $form->field($model, 'vid')->textInput(['maxlength' => true, 'placeholder' => 'Masukkan kode VID']) ?>
                                                <?= $form->field($model, 'ownership_status')->dropDownList([
                                                    'Sendiri' => 'Sendiri',
                                                    'Kelompok' => 'Kelompok',
                                                    'Titipan' => 'Titipan',
                                                ], ['prompt' => 'Pilih status kepemilikan']) ?>

                                            <?= $form->field($model, 'reproduction')->dropDownList([
                                                'Tidak Bunting' => 'Tidak Bunting',
                                                'Bunting < 1 bulan' => 'Bunting < 1 bulan',
                                                'Bunting 1 bulan' => 'Bunting 1 bulan',
                                                'Bunting 2 bulan' => 'Bunting 2 bulan',
                                                'Bunting 3 bulan' => 'Bunting 3 bulan',
                                                'Bunting 4 bulan' => 'Bunting 4 bulan',
                                                'Bunting 5 bulan' => 'Bunting 5 bulan',
                                                'Bunting 6 bulan' => 'Bunting  bulan',
                                                'Bunting 7 bulan' => 'Bunting 7 bulan',
                                                'Bunting 8 bulan' => 'Bunting 8 bulan',
                                                'Bunting 9 bulan' => 'Bunting 9 bulan',
                                                'Bunting 10 bulan' => 'Bunting 10 bulan',
                                                'Bunting 11 bulan' => 'Bunting 11 bulan',
                                                'Bunting > 11 bulan' => 'Bunting > 11 bulan',
                                            ], ['prompt' => 'Pilih status reproduksi']) ?>

                                            <?= $form->field($model, 'gender')->dropDownList([
                                                'Jantan' => 'Jantan',
                                                'Betina' => 'Betina',
                                            ], ['prompt' => 'Pilih jenis kelamin']) ?>

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

                                            <div class="business-field business-field-penggemukan" style="display:none;">
                                                <?= $form->field($model, 'first_price')->input('number', [
                                                    'placeholder' => 'Masukkan Harga Pedet (Rp)',
                                                    'id'=> 'first_price',
                                                    'min' => 0,
                                                    'class' => 'form-control no-spinner'

                                                ]) ?>
                                            </div>

                                            <div class="business-field business-field-breeding" style="display:none;">
                                                <?= $form->field($model, 'breeding_investment')->input('number', [
                                                    'placeholder' => 'Masukkan Harga Investasi Indukan (Rp)',
                                                    'id'=> 'breeding_investment',
                                                    'min' => 0,
                                                    'class' => 'form-control no-spinner'

                                                ]) ?>
                                            </div>

                                            <?= $form->field($model, 'health')->dropDownList([
                                                'Sehat' => 'Sehat',
                                                'Sakit' => 'Sakit',
                                            ], ['prompt' => 'Pilih status kesehatan']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-actions d-flex justify-content-end mt-3">
                                        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                                        <!-- <button type="submit" class="btn btn-primary me-1">Submit</button> -->
                                    </div>
                                <?php ActiveForm::end(); ?>
                                <?php
                                    $this->registerJs(<<<JS
                                    (function() {
                                        function toggleBusinessPriceFields() {
                                            var purpose = $('#livestock-purpose').val();
                                            if (purpose === 'Penggemukan') {
                                                $('.business-field-penggemukan').show();
                                                $('.business-field-breeding').hide();
                                            } else if (purpose === 'Indukan') {
                                                $('.business-field-penggemukan').hide();
                                                $('.business-field-breeding').show();
                                            } else {
                                                $('.business-field-penggemukan, .business-field-breeding').hide();
                                            }
                                        }

                                        toggleBusinessPriceFields();
                                        $(document).on('change', '#livestock-purpose', toggleBusinessPriceFields);
                                    })();
                                    JS);
                                ?>
                                <script>
                                // Show the modal if there's a flash message
                                <?php if (\Yii::$app->session->hasFlash('error')): ?>
                                    $(document).ready(function() {
                                        $('#warningModal').modal('show');
                                    });
                                <?php endif; ?>
                                <?php if (\Yii::$app->session->hasFlash('success')): ?>
                                    $(document).ready(function() {
                                        $('#successModal').modal('show');
                                    });
                                <?php endif; ?>
                            </script>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        
    </section>
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
