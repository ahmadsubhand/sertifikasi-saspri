<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Cage;
use common\models\CowFamilyTree;
use common\models\Livestock;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var common\models\Cage $model */
/** @var yii\widgets\ActiveForm $form */

$this->title = 'Update Ternak: ' . $model->name;

// Ambil semua ternak (selain ternak saat ini) milik user
$allLivestock = Livestock::find()
    ->where(['user_id' => \Yii::$app->user->id])
    ->andWhere(['<>', 'id', $model->id])
    ->all();

// Bagi berdasarkan jenis kelamin
$maleLivestock   = array_filter($allLivestock, fn($l) => $l->gender === 'Jantan');
$femaleLivestock = array_filter($allLivestock, fn($l) => $l->gender === 'Betina');

// Mapping ke opsi dropdown
$fatherOptions = ArrayHelper::map($maleLivestock, 'id', function ($item) {
    return $item->name . ' (' . ($item->vid ?: 'No VID') . ') - ' . $item->gender;
});

$motherOptions = ArrayHelper::map($femaleLivestock, 'id', function ($item) {
    return $item->name . ' (' . ($item->vid ?: 'No VID') . ') - ' . $item->gender;
});

// Opsi pasangan harus lawan jenis dari ternak yang sedang diedit
if ($model->gender === 'Jantan') {
    $partnerLivestock = $femaleLivestock;
} else {
    $partnerLivestock = $maleLivestock;
}

$partnerOptions = ArrayHelper::map($partnerLivestock, 'id', function ($item) {
    return $item->name . ' (' . ($item->vid ?: 'No VID') . ') - ' . $item->gender;
});

// Dapatkan data silsilah saat ini (jika ada)
$familyTree = CowFamilyTree::find()->where(['main_cow_id' => $model->id])->one();

// Pastikan nilai terpilih valid (bukan 0 / kosong)
$currentFather   = ($familyTree && !empty($familyTree->father_id)) ? (int)$familyTree->father_id : null;
$currentMother   = ($familyTree && !empty($familyTree->mother_id)) ? (int)$familyTree->mother_id : null;
$currentPartners = ($familyTree && !empty($familyTree->partners)) ? json_decode($familyTree->partners, true) : [];

// Ambil partner terpilih pertama (jika ada) untuk single-select
$selectedPartner = !empty($currentPartners) ? (int)$currentPartners[0] : null;

// Data livestock untuk validasi JavaScript
$livestockDataForJs = [];
foreach ($allLivestock as $livestock) {
    // Ambil data orang tua dari CowFamilyTree
    $familyTreeData = CowFamilyTree::find()->where(['main_cow_id' => $livestock->id])->one();
    
    $livestockDataForJs[$livestock->id] = [
        'gender' => $livestock->gender,
        'name' => $livestock->name . ' (' . ($livestock->vid ?: 'No VID') . ') - ' . $livestock->gender,
        'father_id' => $familyTreeData ? $familyTreeData->father_id : null,
        'mother_id' => $familyTreeData ? $familyTreeData->mother_id : null
    ];
}

// Tambahkan data ternak saat ini
$livestockDataForJs[$model->id] = [
    'gender' => $model->gender,
    'name' => $model->name . ' (' . ($model->vid ?: 'No VID') . ') - ' . $model->gender,
    'father_id' => $currentFather,
    'mother_id' => $currentMother
];

// Prefill ukuran tubuh dengan catatan BCS terbaru agar form menggunakan data terkini
$latestBcs = $model->latestBcs;
if ($latestBcs) {
    $model->chest_size = $latestBcs->chest_size;
    $model->body_weight = $latestBcs->body_weight;
    $model->hips = $latestBcs->hips;
}
?>

<div class="cage-update">

        <?php $form = ActiveForm::begin([
            'id' => 'update-form',
            'method' => 'put',
            'options' => ['enctype' => 'multipart/form-data']
        ]); ?>
        <?php
$this->registerJs(<<<JS
(function() {
    function toggleBusinessPriceFieldsUpdate() {
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

    toggleBusinessPriceFieldsUpdate();
    $(document).on('change', '#livestock-purpose', toggleBusinessPriceFieldsUpdate);
})();
JS);
        ?>
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

        <!--  Form relasi orang tua & pasangan -->
        <div class="row mt-4">
            <div class="col-md-4">
                <?= Html::label('Ayah', 'father-id', ['class' => 'form-label']) ?>
                <?= Html::dropDownList('father_id', $currentFather, $fatherOptions, [
                    'class' => 'form-select',
                    'prompt' => 'Pilih Ayah',
                    'id' => 'father-id'
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= Html::label('Ibu', 'mother-id', ['class' => 'form-label']) ?>
                <?= Html::dropDownList('mother_id', $currentMother, $motherOptions, [
                    'class' => 'form-select',
                    'prompt' => 'Pilih Ibu',
                    'id' => 'mother-id'
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= Html::label('Pasangan', null, ['class' => 'form-label']) ?>
                <div id="partner-container">
                    <?php
                    // Render dropdown untuk setiap partner yang sudah ada
                    foreach ($currentPartners as $pid) {
                        echo Html::dropDownList('partner_ids[]', $pid, $partnerOptions, [
                            'class' => 'form-select mb-2 partner-select',
                            'prompt' => 'Pilih Pasangan'
                        ]);
                    }
                    // Tambah satu dropdown kosong sebagai starter
                    echo Html::dropDownList('partner_ids[]', null, $partnerOptions, [
                        'class' => 'form-select mb-2 partner-select',
                        'prompt' => 'Pilih Pasangan'
                    ]);
                    ?>
                </div>
            </div>
        </div>
        <br>
        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            <button type="submit" href= '<?= \yii\helpers\Url::toRoute(['/livestock/index']) ?>' class="btn btn-primary me-1">Cancel</button>
        </div>

        <?php ActiveForm::end(); ?>
</div>

<!-- Modal Konfirmasi Saudara Kandung -->
<div class="modal fade" id="siblingConfirmModal" tabindex="-1" aria-labelledby="siblingConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="siblingConfirmModalLabel">
                    <i class="bi bi-exclamation-triangle text-warning"></i> Peringatan Saudara Kandung
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Peringatan:</strong> Kedua hewan yang akan dijadikan pasangan memiliki orang tua yang sama (saudara kandung).</p>
                <p>Perkawinan antara saudara kandung dapat menyebabkan:</p>
                <ul>
                    <li>Peningkatan risiko cacat genetik pada keturunan</li>
                    <li>Penurunan kualitas genetik populasi</li>
                    <li>Masalah kesehatan pada keturunan</li>
                </ul>
                <p><strong>Apakah Anda yakin ingin melanjutkan?</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Batal
                </button>
                <button type="button" class="btn btn-warning" id="confirmSiblingSubmit">
                    <i class="bi bi-check-circle"></i> Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Pass partner options and livestock data to JS
$partnerOptionsJson = json_encode($partnerOptions);
$livestockDataJson = json_encode($livestockDataForJs);
$this->registerJs("\n    (function(){\n        var partnerOptions = $partnerOptionsJson;\n        var livestockData = $livestockDataJson;\n        var pendingSiblingSubmit = false;\n        \n        function createPartnerSelect(){\n            var select = $('<select></select>')\n                .addClass('form-select mb-2 partner-select')\n                .attr('name','partner_ids[]')\n                .append($('<option>',{value:'',text:'Pilih Pasangan'}));\n            $.each(partnerOptions,function(val,label){\n                select.append($('<option>',{value:val,text:label}));\n            });\n            return select;\n        }\n\n        function refreshOptions(){\n            var selectedVals = $('#partner-container .partner-select').map(function(){return $(this).val();}).get();\n            $('#partner-container .partner-select').each(function(){\n                var current = $(this).val();\n                $(this).find('option').each(function(){\n                    var val = $(this).attr('value');\n                    if(val!=='' && val!==current && selectedVals.includes(val)){\n                        $(this).prop('disabled',true).hide();\n                    }else{\n                        $(this).prop('disabled',false).show();\n                    }\n                });\n            });\n        }\n        \n        // Fungsi untuk mengecek apakah dua hewan adalah saudara kandung\n        function areSiblings(mainCowId, partnerId) {\n            var mainCow = livestockData[mainCowId];\n            var partner = livestockData[partnerId];\n            \n            if (!mainCow || !partner) return false;\n            \n            // Cek apakah memiliki ayah yang sama (dan bukan null)\n            var sameFather = mainCow.father_id && partner.father_id && mainCow.father_id == partner.father_id;\n            \n            // Cek apakah memiliki ibu yang sama (dan bukan null)\n            var sameMother = mainCow.mother_id && partner.mother_id && mainCow.mother_id == partner.mother_id;\n            \n            return sameFather || sameMother;\n        }\n\n        // Ketika dropdown partner berubah\n        $('#partner-container').on('change','.partner-select',function(){\n            var partnerId = $(this).val();\n            var mainCowId = '$model->id';\n            \n            if (partnerId && areSiblings(mainCowId, partnerId)) {\n                // Tampilkan modal konfirmasi\n                $('#siblingConfirmModal').modal('show');\n                \n                // Simpan referensi dropdown yang sedang diubah\n                $('#siblingConfirmModal').data('currentSelect', $(this));\n                $('#siblingConfirmModal').data('selectedValue', partnerId);\n                \n                // Reset nilai dropdown sementara\n                $(this).val('');\n                return;\n            }\n            \n            var selects = $('#partner-container .partner-select');\n            var lastFilled = selects.last();\n            // Jika dropdown terakhir terisi, tambahkan dropdown baru\n            if(lastFilled.val() && selects.filter(function(){return $(this).val()==='' }).length===0){\n                $('#partner-container').append(createPartnerSelect());\n            }\n            refreshOptions();\n        });\n        \n        // Handle konfirmasi saudara kandung\n        $('#confirmSiblingSubmit').on('click', function() {\n            var modal = $('#siblingConfirmModal');\n            var currentSelect = modal.data('currentSelect');\n            var selectedValue = modal.data('selectedValue');\n            \n            if (currentSelect && selectedValue) {\n                // Set nilai yang dipilih\n                currentSelect.val(selectedValue);\n                \n                // Trigger logic untuk menambah dropdown baru jika perlu\n                var selects = $('#partner-container .partner-select');\n                var lastFilled = selects.last();\n                if(lastFilled.val() && selects.filter(function(){return $(this).val()==='' }).length===0){\n                    $('#partner-container').append(createPartnerSelect());\n                }\n                refreshOptions();\n            }\n            \n            modal.modal('hide');\n        });\n        \n        // Handle form submit dengan validasi saudara kandung\n        $('#update-form').on('submit', function(e) {\n            if (pendingSiblingSubmit) {\n                return true; // Izinkan submit jika sudah dikonfirmasi\n            }\n            \n            var mainCowId = '$model->id';\n            var hasSiblingPartner = false;\n            \n            $('#partner-container .partner-select').each(function() {\n                var partnerId = $(this).val();\n                if (partnerId && areSiblings(mainCowId, partnerId)) {\n                    hasSiblingPartner = true;\n                    return false; // break\n                }\n            });\n            \n            if (hasSiblingPartner) {\n                e.preventDefault();\n                $('#siblingConfirmModal').modal('show');\n                \n                // Set flag untuk submit setelah konfirmasi\n                $('#confirmSiblingSubmit').off('click.formSubmit').on('click.formSubmit', function() {\n                    pendingSiblingSubmit = true;\n                    $('#siblingConfirmModal').modal('hide');\n                    $('#update-form').submit();\n                });\n            }\n        });\n\n        // Refresh awal\n        refreshOptions();\n    })();\n");
?>

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
