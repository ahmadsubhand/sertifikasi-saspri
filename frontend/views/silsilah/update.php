<?php
// Import library yang dibutuhin
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var array<int, string> $fatherOptions
 * @var array<int, string> $motherOptions
 * @var array<int, string> $partnerOptions
 * @var int $currentFather 
 * @var int $currentMother 
 * @var int $currentPartners 
 * @var \common\models\Livestock $model
 */

// Set judul halaman
$this->title = 'Perbarui Silsilah: ' . $model->name;

?>

<!-- ========================================
     HALAMAN UPDATE SILSILAH SAPI
     ======================================== -->
<div class="silsilah-update">

    <?php 
    // Form buat update silsilah
    $formUpdate = ActiveForm::begin([
        'id' => 'silsilah-update-form',
        'action' => ['silsilah/update', 'id' => $model->id],
        'method' => 'post',
    ]); ?>

    <!-- Form input buat update silsilah -->
    <div class="d-flex flex-wrap align-items-start gap-3 mt-4">
        
        <!-- Dropdown pilih ayah -->
        <div style="flex: 1; min-width: 200px;">
            <?= Html::label('Ayah', 'father-id', ['class' => 'form-label']) ?>
            <?= Html::dropDownList('father_id', $currentFather, $fatherOptions, [
                'class' => 'form-select',
                'prompt' => 'Pilih Ayah',
                'id' => 'father-id'
            ]) ?>
        </div>
        
        <!-- Dropdown pilih ibu -->
        <div style="flex: 1; min-width: 200px;">
            <?= Html::label('Ibu', 'mother-id', ['class' => 'form-label']) ?>
            <?= Html::dropDownList('mother_id', $currentMother, $motherOptions, [
                'class' => 'form-select',
                'prompt' => 'Pilih Ibu',
                'id' => 'mother-id'
            ]) ?>
        </div>
        
        <!-- Container buat dropdown pasangan (bisa lebih dari 1) -->
        <div style="flex: 2; min-width: 250px;">
            <?= Html::label('Pasangan', null, ['class' => 'form-label']) ?>
            <div id="partner-container" 
                 data-partners='<?= json_encode($currentPartners ?: []) ?>'
                 data-options='<?= json_encode($partnerOptions) ?>'>
                <!-- Dropdown pasangan bakal di-generate pake JavaScript -->
            </div>
        </div>
    </div>
    
    <!-- Tombol simpan dan batal -->
    <div class="form-group mt-4">
        <?= Html::submitButton('Simpan Silsilah', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Batal', ['silsilah/index', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
// ========================================
// JAVASCRIPT BUAT HANDLE DROPDOWN PASANGAN
// ========================================
$this->registerJs(<<<JS
(function() {
    // Ambil element container dan data yang dibutuhin
    const containerPasangan = $('#partner-container');
    const opsiPasangan = containerPasangan.data('options');
    const pasanganSekarang = containerPasangan.data('partners');

    // Fungsi buat bikin dropdown pasangan baru
    const buatInputPasangan = (idYangDipilih = '') => {
        // Bikin element select
        const selectPasangan = $('<select>', { 
            name: 'partner_ids[]', 
            class: 'form-select partner-select' 
        });
        
        // Tambahin opsi kosong dulu
        selectPasangan.append($('<option>', { value: '', text: 'Pilih Pasangan' }));
        
        // Loop semua opsi pasangan yang tersedia
        $.each(opsiPasangan, (nilaiOpsi, teksOpsi) => {
            selectPasangan.append($('<option>', { value: nilaiOpsi, text: teksOpsi }));
        });
        
        // Set nilai yang dipilih
        selectPasangan.val(idYangDipilih);
        
        // Bikin tombol hapus
        const tombolHapus = $('<button>', { 
            type: 'button', 
            class: 'btn btn-outline-danger remove-partner-btn', 
            title: 'Hapus Pasangan' 
        }).html('<i class="bi bi-trash"></i>');
        
        // Gabungin jadi satu input group
        return $('<div>', { class: 'input-group mb-2' }).append(selectPasangan, tombolHapus);
    };

    // Fungsi buat refresh tampilan dropdown
    const refreshTampilan = () => {
        // Ambil semua dropdown pasangan
        const semuaDropdown = containerPasangan.find('.partner-select');
        
        // Ambil semua nilai yang udah dipilih
        const yangUdahDipilih = semuaDropdown.map((index, element) => $(element).val())
                                             .get()
                                             .filter(nilai => nilai);

        // Loop setiap dropdown buat disable opsi yang udah dipilih
        semuaDropdown.each(function() {
            const nilaiSekarang = $(this).val();
            
            // Loop setiap opsi dalam dropdown ini
            $(this).find('option').each(function() {
                const nilaiOpsi = $(this).attr('value');
                
                // Skip kalo opsi kosong
                if (!nilaiOpsi) return;
                
                // Disable kalo udah dipilih di dropdown lain
                const harusDisable = yangUdahDipilih.includes(nilaiOpsi) && nilaiOpsi !== nilaiSekarang;
                $(this).prop('disabled', harusDisable).toggle(!harusDisable);
            });
        });

        // Kalo semua dropdown udah diisi, tambahin dropdown baru
        const dropdownKosong = semuaDropdown.filter((index, element) => !$(element).val());
        if (dropdownKosong.length === 0) {
            containerPasangan.append(buatInputPasangan());
        }
    };

    // Event listener ketika dropdown pasangan berubah
    containerPasangan.on('change', '.partner-select', refreshTampilan);
    
    // Event listener ketika tombol hapus diklik
    containerPasangan.on('click', '.remove-partner-btn', function() {
        // Kalo masih ada lebih dari 1 dropdown, hapus yang ini
        if (containerPasangan.find('.input-group').length > 1) {
            $(this).closest('.input-group').remove();
        } else {
            // Kalo cuma tinggal 1, reset aja nilainya
            $(this).closest('.input-group').find('.partner-select').val('');
        }
        
        // Refresh tampilan setelah hapus
        refreshTampilan();
    });

    // Setup awal: tambahin dropdown buat pasangan yang udah ada
    (pasanganSekarang || []).forEach(idPasangan => {
        containerPasangan.append(buatInputPasangan(idPasangan));
    });
    
    // Tambahin 1 dropdown kosong buat nambah pasangan baru
    containerPasangan.append(buatInputPasangan());
    
    // Refresh tampilan pertama kali
    refreshTampilan();
})();
JS
);
?>