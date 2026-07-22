<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Cage;

/** @var yii\web\View $this */
/** @var common\models\Cage $model */
/** @var yii\widgets\ActiveForm $form */

$this->title = 'Update Ternak: ' . $model->name;
?>

<div class="cage-update">

        <?php $form = ActiveForm::begin([
            'id' => 'update-form',
            'options' => [
                'method'  => 'post',      // HTML attribute method
                'enctype' => 'multipart/form-data',
            ],
        ]); ?>
<div class="form-body">
                                        <div class="form-body row">
                                        <div class = "col">
                                            <?= $form->field($model, 'eid')->textInput([
                                                'maxlength'   => 32,
                                                'placeholder' => 'Masukkan kode EID (32 digit angka)',
                                                'inputmode'   => 'numeric',
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
                                            <!-- <?= $form->field($model, 'livestock_image[]')->fileInput(['class' => 'form-control']) ?> -->
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
                                                'placeholder' => 'Masukkan berat badan (kg)',
                                                'id'=> 'body_weight',
                                                'type'=> 'text',
                                                'maxlength'=> true,
                                                ]) ?>

                                            <?= $form->field($model, 'health')->dropDownList([
                                                'Sehat' => 'Sehat',
                                                'Sakit' => 'Sakit',
                                            ], ['prompt' => 'Pilih status kesehatan']) ?>
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
                                            </div>
                                        </div>
                                    </div>  

        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            <?= Html::a('Kelola Silsilah', ['silsilah/update', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
            <a href='<?= yii\helpers\Url::toRoute(['/livestock/index']) ?>' class="btn btn-primary me-1">Cancel</a>
        </div>

        <?php ActiveForm::end(); ?>
</div>
