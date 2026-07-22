<?php

// Import semua yang dibutuhin buat halaman ini
use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\CowFamilyTree;
use common\models\Livestock;

/**
 * Halaman detail ternak - buat nampilin info lengkap sapi
 * @var yii\web\View $this
 * @var Livestock $model
 */

// Set judul halaman
$this->title = 'Detail Ternak: ' . ($model->name ?? 'N/A');

// ========================================
// BAGIAN 1: AMBIL DATA KELUARGA SAPI
// ========================================

// Cari data silsilah si sapi ini
$dataKeluarga = CowFamilyTree::find()->where(['main_cow_id' => $model->id])->one();
$bapak = $dataKeluarga->father ?? null;  // Ayah si sapi
$emak = $dataKeluarga->mother ?? null;   // Ibu si sapi

// ========================================
// BAGIAN 2: CEK STATUS KEMURNIAN RAS
// ========================================

// Default: ga tau statusnya
$statusKemurnian = 'Tidak dapat ditentukan';

// Kalo ada data bapak sama emak, baru bisa tau
if ($bapak && $emak) {
    // Kalo ras bapak sama emak sama = murni, kalo beda = persilangan
    $statusKemurnian = ($bapak->breed_of_livestock === $emak->breed_of_livestock) ? 'Murni' : 'Persilangan';
}

// ========================================
// BAGIAN 3: HITUNG UMUR SAPI
// ========================================

// Default kalo ga ada tanggal lahir
$umurString = 'Data tanggal lahir tidak valid';

// Kalo ada tanggal lahir, hitung umurnya
if ($model->birthdate) {
    try {
        // Hitung selisih dari tanggal lahir ke sekarang
        $umur = (new DateTime($model->birthdate))->diff(new DateTime());
        $umurString = "{$umur->y} tahun, {$umur->m} bulan, {$umur->d} hari";
    } catch (Exception $e) {
        // Kalo error ya udah, pake pesan default aja
    }
}

// ========================================
// BAGIAN 4: TENTUIN ATRIBUT APA AJA YANG MAU DITAMPILIN
// ========================================

// Cek apakah ini sapi yang perlu info detail (indukan, penggemukan, potongan)
$perluDetailLengkap = in_array($model->purpose, ['Indukan', 'Penggemukan', 'Potongan']);

// Atribut dasar yang pasti ditampilin
$atributYangDitampilin = [
    ['attribute' => 'name', 'label' => 'Nama'],
    ['attribute' => 'vid', 'label' => 'VID'],
    ['attribute' => 'purpose', 'label' => 'Peruntukan'],
];

// Kalo perlu detail lengkap, tambahin info lainnya
if ($perluDetailLengkap) {
    $atributYangDitampilin = array_merge($atributYangDitampilin, [
        ['attribute' => 'body_weight', 'label' => 'Berat (kg)'],
        ['attribute' => 'breed_of_livestock', 'label' => 'Rumpun/Galur (Ras)'],
        ['label' => 'Umur', 'value' => $umurString],
        'gender',
        ['label' => 'Lokasi Kandang', 'value' => $model->cage->location ?? 'Tidak ada data kandang'],
    ]);
} else {
    // Kalo ga perlu detail lengkap, cuma tambahin ras aja
    $atributYangDitampilin[] = ['attribute' => 'breed_of_livestock', 'label' => 'Ras'];
}

// Tambahin status kemurnian ras
$atributYangDitampilin[] = ['label' => 'Status Murni/Persilangan', 'value' => $statusKemurnian];

// Kalo ini sapi indukan, tambahin info silsilah
if ($model->purpose === 'Indukan') {
    $atributYangDitampilin[] = ['label' => 'Silsilah Ayah', 'value' => $bapak ? "{$bapak->name} ({$bapak->vid})" : 'Tidak Diketahui'];
    $atributYangDitampilin[] = ['label' => 'Silsilah Ibu', 'value' => $emak ? "{$emak->name} ({$emak->vid})" : 'Tidak Diketahui'];
}

?>

<!-- ========================================
     BAGIAN 5: TAMPILIN DETAIL SAPI DALAM BENTUK CARD
     ======================================== -->

<div class="card shadow-sm" data-livestock-title="<?= Html::encode('Detail Ternak: ' . $model->name . ' (' . ($model->vid ?: 'Tanpa VID') . ')') ?>">
    <div class="card-body p-3">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => $atributYangDitampilin,  // Pake atribut yang udah kita tentuin tadi
            'options' => ['class' => 'table table-hover table-bordered mb-0'],
            'template' => '<tr><th style="width: 30%;">{label}</th><td>{value}</td></tr>',
        ]) ?>
    </div>
    
    <?php if (!($isAjax ?? false)): ?>
    <!-- Tombol kembali (cuma muncul kalo bukan ajax request) -->
    <div class="card-footer d-flex justify-content-end">
        <?= Html::a('Kembali', Yii::$app->request->referrer ?: ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>
    <?php endif; ?>
</div>
