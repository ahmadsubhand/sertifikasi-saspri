<?php
// Import semua library yang dibutuhin
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

// Set judul halaman
$this->title = 'Daftar Hewan Ternak';
$this->params['breadcrumbs'][] = $this->title;

/**
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array<int, string> $livestockOptions
 * @var array<int, array{
 *     gender: string,
 *     name: string,
 *     father_id: int|null,
 *     mother_id: int|null
 * }> $livestockDataForJs
 */
?>

<p class="text-muted mb-3">Kelola silsilah dan detail ternak kamu</p>

<!-- ========================================
     BAGIAN 2: FORM BUAT TAMBAH RELASI KELUARGA
     ======================================== -->
<?php if (!\Yii::$app->user->isGuest): ?>
<section class="card shadow-sm mb-4">
  <div class="card-header py-2"><strong><i class="bi bi-diagram-3"></i> Tambah Relasi</strong></div>
  <div class="card-body p-3">
    <?php 
    // Form buat nambah relasi keluarga sapi
    $formRelasi = ActiveForm::begin([
        'id'=>'family-tree-form',
        'action'=>['silsilah/add-relation'],
        'options'=>['novalidate'=>true]
    ]); ?>
      <div class="row g-2 align-items-end">
        <!-- Dropdown pilih sapi utama -->
        <div class="col-12 col-md">
          <?= Html::dropDownList('main_cow_id','',$livestockOptions,['class'=>'form-select','prompt'=>'Hewan Utama','required'=>true,'id'=>'main-cow']) ?>
        </div>
        
        <!-- Dropdown pilih jenis relasi -->
        <div class="col-12 col-md">
          <?= Html::dropDownList('relation_type',null,['parent'=>'Orang Tua','partner'=>'Pasangan'],['class'=>'form-select','prompt'=>'Jenis Relasi','required'=>true,'id'=>'relation-type']) ?>
        </div>
        
        <!-- Dropdown pilih sapi yang mau direlasikan -->
        <div class="col-12 col-md">
          <?= Html::dropDownList('related_cow_id','',[],['class'=>'form-select','prompt'=>'Hewan Relasi','required'=>true,'id'=>'related-cow']) ?>
          <div class="invalid-feedback small" id="validation-msg"></div>
        </div>
        
        <!-- Dropdown tipe orang tua (ayah/ibu) - muncul kalo pilih parent -->
        <div class="col-12 col-md-auto d-none" id="parent-type-wrap">
          <?= Html::dropDownList('parent_type',null,['father'=>'Ayah','mother'=>'Ibu'],['class'=>'form-select','prompt'=>'Tipe Orang Tua','id'=>'parent-type']) ?>
        </div>
        
        <!-- Tombol submit -->
        <div class="col-12 col-md-auto">
          <?= Html::submitButton('<i class="bi bi-plus-circle"></i> Tambah',['class'=>'btn btn-primary w-100','id'=>'submit-btn']) ?>
        </div>
      </div>
    <?php ActiveForm::end(); ?>
  </div>
</section>
<?php endif; ?>

<!-- ========================================
     BAGIAN 3: TABEL DATA TERNAK + FILTER
     ======================================== -->
<section class="card shadow-sm">
  <div class="card-header py-2"><strong>Data Ternak</strong></div>
  <div class="card-body p-3">

    <!-- Form filter buat nyari data ternak -->
    <?php $formFilter = ActiveForm::begin(['method'=>'get','options'=>['data-pjax'=>true,'class'=>'mb-3']]); ?>
      <div class="row g-3">

        <!-- Input pencarian berdasarkan VID atau nama -->
        <div class="col-12 col-lg-6">
          <label class="form-label small fw-bold mb-1">Cari VID / Nama</label>
          <?= Html::textInput('search_query',\Yii::$app->request->get('search_query'),[
                'class'=>'form-control',
                'placeholder'=>'ketik VID atau nama…'
          ]) ?>
        </div>

        <!-- Filter berdasarkan jenis kelamin -->
        <div class="col-12 col-md-6 col-lg-2">
          <label class="form-label small fw-bold mb-1">Jenis&nbsp;Kelamin</label>
          <?= Html::dropDownList('filter_gender',\Yii::$app->request->get('filter_gender'),[
                'Jantan'=>'Jantan','Betina'=>'Betina'
          ],['class'=>'form-select','prompt'=>'Semua']) ?>
        </div>

        <!-- Filter berdasarkan ras sapi -->
        <div class="col-12 col-md-6 col-lg-2">
          <label class="form-label small fw-bold mb-1">Ras</label>
          <?= Html::dropDownList('filter_breed',\Yii::$app->request->get('filter_breed'),[
                'Madura'=>'Madura','Bali'=>'Bali','Limousin'=>'Limousin','Brahman'=>'Brahman'
          ],['class'=>'form-select','prompt'=>'Semua']) ?>
        </div>

        <!-- Filter berdasarkan rentang usia -->
        <div class="col-12 col-md-6 col-lg-2">
          <label class="form-label small fw-bold mb-1">Rentang&nbsp;Usia</label>
          <?= Html::dropDownList('filter_age',\Yii::$app->request->get('filter_age'),[
                '0-6'=>'0-6 Bulan','7-12'=>'7-12 Bulan','13-24'=>'1-2 Tahun','25-36'=>'2-3 Tahun','37+'=>'>3 Tahun'
          ],['class'=>'form-select','prompt'=>'Semua']) ?>
        </div>

        <!-- Tombol filter dan reset -->
        <div class="col-12 col-md-6 col-lg-6 d-flex gap-2 align-items-end">
          <?= Html::submitButton('<i class="bi bi-funnel-fill"></i> Filter',['class'=>'btn btn-primary w-100']) ?>
          <?= Html::a('<i class="bi bi-x-lg"></i> Reset',['silsilah/index'],['class'=>'btn btn-outline-secondary w-100']) ?>
        </div>
      </div>
    <?php ActiveForm::end(); ?>

    <!-- Tabel data ternak -->
    <?php if ($dataProvider->totalCount): ?>
      <?php Pjax::begin(); ?>
        <div class="table-responsive">
          <?= GridView::widget([
            'dataProvider'=>$dataProvider,
            'layout'=>'{items}{pager}',
            'tableOptions'=>['class'=>'table table-sm table-hover align-middle'],
            'columns'=>[
              // Kolom nomor urut
              ['class'=>'yii\grid\SerialColumn','header'=>'#'],
              
              // Kolom VID dan nama sapi
              ['attribute'=>'vid',  'label'=>'VID',  'enableSorting'=>false],
              ['attribute'=>'name', 'label'=>'Nama', 'enableSorting'=>false],
              
              // Kolom jenis kelamin
              [
                'attribute'=>'gender','label'=>'JK','enableSorting'=>false,
                'contentOptions'=>['class'=>'text-center']
              ],
              
              // Kolom jenis ternak
              [
                'attribute'=>'type_of_livestock','label'=>'Jenis','enableSorting'=>false,
                'contentOptions'=>['class'=>'text-center']
              ],
              
              // Kolom ras/breed
              [
                'attribute'=>'breed_of_livestock','label'=>'Ras','enableSorting'=>false,
                'contentOptions'=>['class'=>'text-center']
              ],
              // Kolom usia (dihitung otomatis)
              [
                'attribute'=>'age','label'=>'Usia','format'=>'raw','enableSorting'=>false,
                'contentOptions'=>['class'=>'text-center'],
                'value'=>static fn($modelSapi)=>($dataUmur=$modelSapi->getAgeData())
                    ? (($dataUmur['years']?$dataUmur['years'].' th<br>':'').($dataUmur['months']?$dataUmur['months'].' bl':'')?:'0 bl')
                    : '-'
              ],
              
              // Kolom status kesehatan dengan badge warna
              [
                'attribute'=>'health','label'=>'Kesehatan','format'=>'raw','enableSorting'=>false,
                'value'=>fn($modelSapi)=>Html::tag('span',$modelSapi->health,[
                    'class'=>'badge bg-'.($modelSapi->health==='Sehat'?'success':'danger')]),
                'contentOptions'=>['class'=>'text-center']
              ],
              // Kolom tombol aksi (silsilah, edit, detail)
              [
                'class'=>'yii\grid\ActionColumn','template'=>'{tree} {edit} {det}',
                'contentOptions'=>['class'=>'text-center'],
                'buttons'=>[
                  // Tombol lihat silsilah
                  'tree'=>fn($urlAksi,$modelSapi)=>Html::a('<i class="bi bi-diagram-3"></i>',
                            ['silsilah/view','id'=>$modelSapi->id],
                            ['class'=>'btn btn-sm btn-primary','title'=>'Silsilah']),
                  
                  // Tombol edit (cuma muncul kalo user login)
                  'edit' => function ($urlAksi, $modelSapi, $kunciData) {
                      if (!\Yii::$app->user->isGuest) {
                          return Html::a('<i class="bi bi-pencil-square"></i>', 
                                         ['silsilah/update', 'id' => $modelSapi->id], 
                                         ['class' => 'btn btn-sm btn-warning', 'title' => 'Edit Silsilah']);
                      }
                      return '';
                  },
                  
                  // Tombol detail (buka modal)
                  'det'=>fn($urlAksi,$modelSapi)=>Html::button('<i class="bi bi-info-circle"></i>', [
                        'class' => 'btn btn-sm btn-info',
                        'title' => 'Lihat Detail',
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#detailModal',
                        'data-url' => \Yii::$app->urlManager->createUrl(['silsilah/detail-ajax', 'id' => $modelSapi->id])
                  ]),
                ],
              ],
            ],
            'pager'=>['class'=>'yii\bootstrap5\LinkPager','options'=>['class'=>'pagination justify-content-center']]
          ]); ?>
        </div>
      <?php Pjax::end(); ?>
    <?php else: ?>
      <!-- Pesan kalo ga ada data -->
      <div class="text-center text-muted py-5"><i class="bi bi-inbox fs-1"></i><br>Tidak ada data</div>
    <?php endif; ?>
  </div>
</section>

<!-- ========================================
     BAGIAN 4: MODAL BUAT NAMPILIN DETAIL TERNAK
     ======================================== -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel">Detail Ternak</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Konten detail bakal dimuat pake AJAX di sini -->
      </div>
    </div>
  </div>
</div>

<?php
// Data sapi dalam format JSON buat JavaScript
$dataSapiJS = json_encode($livestockDataForJs);
$this->registerJs(<<<JS
// ========================================
// BAGIAN 5: JAVASCRIPT BUAT FORM RELASI
// ========================================

// Ambil semua element yang dibutuhin
const dataSapi = $dataSapiJS;
const dropdownSapiUtama = $('#main-cow');
const dropdownJenisRelasi = $('#relation-type');
const dropdownSapiRelasi = $('#related-cow');
const wrapperTipeParent = $('#parent-type-wrap');
const dropdownTipeParent = $('#parent-type');
const pesanValidasi = $('#validation-msg');
const tombolSubmit = $('#submit-btn');

// Fungsi buat ngisi ulang opsi sapi relasi
function isiUlangOpsiRelasi() {
  // Reset dropdown sapi relasi
  dropdownSapiRelasi.html('<option value="">Hewan Relasi</option>');
  
  // Ambil nilai yang dipilih user
  const idSapiUtama = dropdownSapiUtama.val();
  const jenisRelasi = dropdownJenisRelasi.val();
  const tipeParent = dropdownTipeParent.val();
  
  // Kalo belum pilih sapi utama, ga usah lanjut
  if (!idSapiUtama) return;
  
  // Data sapi utama yang dipilih
  const sapiUtama = dataSapi[idSapiUtama];
  
  // Loop semua sapi buat ngisi opsi
  $.each(dataSapi, (idSapi, dataSapiIni) => {
    // Skip kalo sama dengan sapi utama
    if (idSapi === idSapiUtama) return;
    
    // Cek apakah sapi ini bisa jadi relasi
    let bisaJadiRelasi = true;
    
    if (jenisRelasi === 'partner') {
      // Kalo pasangan, jenis kelamin harus beda
      bisaJadiRelasi = sapiUtama.gender !== dataSapiIni.gender;
    } else if (jenisRelasi === 'parent') {
      // Kalo parent, cek tipe parent
      if (tipeParent) {
        if (tipeParent === 'father') {
          bisaJadiRelasi = dataSapiIni.gender === 'Jantan';
        } else if (tipeParent === 'mother') {
          bisaJadiRelasi = dataSapiIni.gender === 'Betina';
        }
      }
    }
    
    // Kalo bisa jadi relasi, tambahin ke dropdown
    if (bisaJadiRelasi) {
      dropdownSapiRelasi.append(`<option value="\${idSapi}">\${dataSapiIni.name}</option>`);
    }
  });
}

// Event listener buat dropdown jenis relasi
dropdownJenisRelasi.on('change', () => {
  // Tampilkan/sembunyikan dropdown tipe parent
  wrapperTipeParent.toggleClass('d-none', dropdownJenisRelasi.val() !== 'parent');
  
  // Set required kalo pilih parent
  dropdownTipeParent.prop('required', dropdownJenisRelasi.val() === 'parent');
  
  // Isi ulang opsi relasi
  isiUlangOpsiRelasi();
});

// Event listener buat dropdown sapi utama dan tipe parent
dropdownSapiUtama.add(dropdownTipeParent).on('change', isiUlangOpsiRelasi);

// Event listener buat validasi dropdown sapi relasi
dropdownSapiRelasi.on('change', function() {
  // Reset pesan error
  pesanValidasi.hide().removeClass('d-block');
  $(this).removeClass('is-invalid');
  tombolSubmit.prop('disabled', false);
  
  // Ambil data sapi yang dipilih
  const sapiUtama = dataSapi[dropdownSapiUtama.val()];
  const sapiRelasi = dataSapi[$(this).val()];
  
  // Kalo belum pilih sapi relasi, skip validasi
  if (!sapiRelasi) return;
  
  // Validasi 1: Ga boleh pilih sapi yang sama
  if (dropdownSapiUtama.val() === $(this).val()) {
    pesanValidasi.text('Tidak boleh sama').addClass('d-block').show();
    $(this).addClass('is-invalid');
    tombolSubmit.prop('disabled', true);
    return;
  }
  
  // Validasi 2: Kalo pasangan, jenis kelamin harus beda
  if (dropdownJenisRelasi.val() === 'partner') {
    if (sapiUtama.gender === sapiRelasi.gender) {
      pesanValidasi.text('Jenis kelamin sama').addClass('d-block').show();
      $(this).addClass('is-invalid');
      tombolSubmit.prop('disabled', true);
      return;
    }
  }
});

// Panggil fungsi pertama kali buat ngisi opsi
isiUlangOpsiRelasi();

// ========================================
// BAGIAN 6: JAVASCRIPT BUAT MODAL DETAIL
// ========================================

// Ambil element modal
const modalDetail = document.getElementById('detailModal');
const judulModal = modalDetail.querySelector('.modal-title');
const isiModal = modalDetail.querySelector('.modal-body');

// Event listener ketika modal mau dibuka
modalDetail.addEventListener('show.bs.modal', function (event) {
    // Ambil tombol yang diklik
    const tombolYangDiklik = event.relatedTarget;
    const urlDetail = tombolYangDiklik.getAttribute('data-url');

    // Tampilkan loading dulu
    judulModal.textContent = 'Memuat Detail Ternak...';
    isiModal.innerHTML = '<div class="text-center p-5"><span class="spinner-border" role="status"></span></div>';

    // Ambil data detail pake AJAX
    fetch(urlDetail)
        .then(response => {
            // Cek apakah response berhasil
            if (!response.ok) throw new Error('Gagal mengambil data dari server');
            return response.text();
        })
        .then(htmlResponse => {
            // Buat element sementara buat ekstrak judul
            const divSementara = document.createElement('div');
            divSementara.innerHTML = htmlResponse;
            
            // Ambil judul dari atribut data-livestock-title
            const judulDariData = divSementara.querySelector('.card')?.getAttribute('data-livestock-title') || 'Detail Ternak';
            
            // Update modal dengan data yang didapat
            judulModal.textContent = judulDariData;
            isiModal.innerHTML = htmlResponse;
        })
        .catch(error => {
            // Kalo error, tampilin pesan error
            console.error('Error saat mengambil detail:', error);
            judulModal.textContent = 'Error';
            isiModal.innerHTML = '<div class="alert alert-danger">Gagal memuat detail. Silakan coba lagi.</div>';
        });
});
JS);

$this->registerCss(<<<CSS
.card{border-radius:.8rem}
.table-hover tbody tr:hover{background-color:rgba(255,255,255,.02)}
@media(max-width:768px){
  .table-responsive{font-size:.85rem}
  .btn-sm{padding:.25rem .5rem;font-size:.75rem}
}
CSS);
?>
