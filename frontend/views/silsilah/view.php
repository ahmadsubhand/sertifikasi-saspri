<?php
// Import library yang dibutuhin
use yii\helpers\Html;
use yii\helpers\Json;
use common\models\CowFamilyTree;

/** @var yii\web\View   $this */
/** @var common\models\Livestock $model */

// Sembunyiin judul halaman default
$this->params['hidePageTitle'] = true;

// ========================================
// BAGIAN 1: AMBIL DATA SILSILAH
// ========================================

// Ambil data keluarga sapi dari database
$dataKeluargaSapi = CowFamilyTree::getFamilyData($model->id);

// Convert data ke format yang bisa dipake D3.js
/** @var common\models\Livestock $model */
$dataD3 = CowFamilyTree::convertToD3Data($model, $dataKeluargaSapi);

// ========================================
// BAGIAN 2: LOAD LIBRARY D3.JS
// ========================================

// Load D3.js dari CDN buat bikin diagram silsilah
$this->registerJsFile(
    'https://d3js.org/d3.v7.min.js',
    ['position' => \yii\web\View::POS_HEAD]
);

// ========================================
// BAGIAN 3: CSS BUAT STYLING DIAGRAM
// ========================================

$this->registerCss(<<<CSS
/* Container utama diagram silsilah */
.family-tree-container{width:100%;min-height:500px}
#familyTreeDiagram{
    width:100%;height:70vh;
    border:2px solid #e2e8f0;border-radius:12px;
    background:#fff;overflow:hidden;cursor:grab}
#familyTreeDiagram:active,
#familyTreeDiagram svg:active{cursor:grabbing}

/* Card buat setiap sapi dalam diagram */
.node-card{
    background:#fff;border:3px solid #e2e8f0;border-radius:16px;
    width:240px;height:140px;padding:12px;
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    box-shadow:0 4px 12px rgba(0,0,0,.12);transition:.25s;
    text-align:center;
}
/* Efek hover buat card sapi */
.node-card:hover{box-shadow:0 8px 20px rgba(0,0,0,.22);transform:translateY(-4px) scale(1.03)}
/* Warna beda buat jantan dan betina */
.node-card.male{background:#f0f9ff;border-color:#3b82f6}
.node-card.female{background:#fdf2f8;border-color:#ec4899}

/* Styling teks dalam card sapi */
.node-name{font-weight:700;font-size:1rem;margin-bottom:4px;color:#111827}
.node-role{
    font-size:.75rem;font-weight:700;color:#4f46e5;
    background:rgba(79,70,229,.1);
    border-radius:999px;padding:3px 10px;margin-bottom:8px;
    display:inline-block;
}
.node-details{font-size:.8rem;color:#6b7280;line-height:1.5}

/* Ikon gender (udah ga dipake lagi, tapi tetep disimpen buat jaga-jaga) */
/*
.gender-icon{
    position:absolute;top:-10px;right:-10px;
    width:34px;height:34px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:20px;font-weight:900;border:3px solid #fff;
    box-shadow:0 4px 10px rgba(0,0,0,.2)}
.gender-icon.male{color:#3b82f6}
.gender-icon.female{color:#ec4899}
*/

/* Garis penghubung antar sapi */
.link-line{stroke:#6b7280;stroke-width:2;fill:none}
.link-marriage{stroke:#ec4899;stroke-width:3;stroke-dasharray:6 3;fill:none;opacity:.7}

/* Tampilan kalo ga ada data silsilah */
.no-family{
    padding:60px 20px;text-align:center;
    border:2px dashed #d1d5db;border-radius:12px;
    background:#f9fafb;color:#6b7280}

/* Tombol kontrol zoom buat mobile (melayang di pojok kanan bawah) */
.tree-controls-mobile{
    position:fixed;bottom:80px;right:16px;
    background:#fff;border-radius:50px;
    box-shadow:0 4px 12px rgba(0,0,0,.2);
    padding:6px;z-index:1050}
.tree-controls-mobile .btn{
    border-radius:50%;width:38px;height:38px;padding:0}

/* Styling buat dark mode (kalo user pake tema gelap) */
html[data-bs-theme="dark"] #familyTreeDiagram,
html[data-bs-theme="dark"] .no-family{
    background:var(--bs-dark);
    border-color:var(--bs-border-color);
    color:var(--bs-secondary-color)}
html[data-bs-theme="dark"] .tree-controls-mobile{background:var(--bs-dark)}
html[data-bs-theme="dark"] .node-card{background:#212529;border-color:#495057}
html[data-bs-theme="dark"] .node-card.male{background:#0c2238;border-color:#3b82f6}
html[data-bs-theme="dark"] .node-card.female{background:#2c111e;border-color:#ec4899}
html[data-bs-theme="dark"] .node-name{color:#f8f9fa}
html[data-bs-theme="dark"] .node-role{color:#a5b4fc;background:rgba(165,180,252,.15)}
html[data-bs-theme="dark"] .node-details{color:#adb5bd}
CSS);

// ========================================
// BAGIAN 4: JAVASCRIPT BUAT BIKIN DIAGRAM
// ========================================

// Masukin data ke JavaScript
$jsKode  = 'const dataSilsilah = ' . Json::encode($dataD3) . "\n";
$jsKode .= <<<'JS'
// Fungsi utama buat inisialisasi diagram
function initDiagram(){
    // ========================================
    // Setup canvas SVG dan zoom functionality
    // ========================================
    
    const containerDiagram = d3.select('#familyTreeDiagram');
    const {width: lebarCanvas, height: tinggiCanvas} = containerDiagram.node().getBoundingClientRect();
    
    // Bikin element SVG
    const svgElement = containerDiagram.append('svg')
                   .attr('viewBox', `0 0 ${lebarCanvas} ${tinggiCanvas}`)
                   .attr('width', '100%').attr('height', '100%');
    
    // Group buat semua element diagram
    const groupUtama = svgElement.append('g');
    
    // Setup zoom dan pan
    const zoomHandler = d3.zoom()
                 .scaleExtent([.1, 3])  // Batas zoom dari 10% sampai 300%
                 .filter(event => !event.target.closest('.node-card'))  // Ga zoom kalo klik card
                 .on('zoom', event => groupUtama.attr('transform', event.transform));
    
    svgElement.call(zoomHandler);

    // ========================================
    // Hitung posisi setiap sapi dalam diagram
    // ========================================
    
    const posisiSemua = [];
    const pusatX = lebarCanvas / 2;
    const pusatY = tinggiCanvas / 2;
    const jarakHorizontal = 300;
    const jarakVertikal = 240;
    const jarakCucu = 210;
    
    const dataSapi = dataSilsilah.nodeDataArray;
    const anakAnak = dataSapi.filter(sapi => sapi.role === 'Anak');
    const jumlahAnak = anakAnak.length;
    
    // Counter buat posisi
    let hitungPasangan = 0;
    let hitungSaudara = 0;
    let hitungAnak = 0;

    // Atur posisi utama, ortu, pasangan, saudara, dan anak
    dataSapi.forEach(sapi => {
        // Skip cucu dulu, nanti diatur terpisah
        if(sapi.role === 'Cucu') return;
        
        let posisiX = pusatX;
        let posisiY = pusatY;
        const jarakKakekNenek = jarakVertikal * 2;
        
        // Tentuin posisi berdasarkan role
        switch(sapi.role){
            case 'Pasangan':
                posisiX = pusatX + jarakHorizontal + (hitungPasangan++ * 260);
                break;
            case 'Saudara':
                posisiX = pusatX - jarakHorizontal - (hitungSaudara++ * 260);
                break;
            case 'Ayah':
                posisiX = pusatX - 200;
                posisiY = pusatY - jarakVertikal;
                break;
            case 'Ibu':
                posisiX = pusatX + 200;
                posisiY = pusatY - jarakVertikal;
                break;
            case 'Kakek':
                posisiY = pusatY - jarakKakekNenek;
                posisiX = (sapi.lineage === 'paternal' ? pusatX - 200 - 140 : pusatX + 200 - 140);
                break;
            case 'Nenek':
                posisiY = pusatY - jarakKakekNenek;
                posisiX = (sapi.lineage === 'paternal' ? pusatX - 200 + 140 : pusatX + 200 + 140);
                break;
            case 'Anak':
                const mulaiX = pusatX - ((jumlahAnak - 1) * 260) / 2;
                posisiX = mulaiX + (hitungAnak++ * 260);
                posisiY = pusatY + jarakVertikal;
                break;
        }
        
        posisiSemua.push({k: sapi.key, x: posisiX, y: posisiY});
    });

    // Atur posisi cucu di bawah orang tua mereka
    const cucuPerOrangTua = {};
    dataSapi.forEach(sapi => {
        if(sapi.role !== 'Cucu') return;
        
        // Cari posisi orang tua
        const posisiOrtu = posisiSemua.find(pos => pos.k === sapi.parentId);
        if(!posisiOrtu) return;
        
        // Hitung berapa cucu yang udah ada buat ortu ini
        if(!cucuPerOrangTua[sapi.parentId]){
            cucuPerOrangTua[sapi.parentId] = 0;
        }
        
        const indexCucu = cucuPerOrangTua[sapi.parentId];
        
        // Tambahin posisi cucu
        posisiSemua.push({
            k: sapi.key,
            x: posisiOrtu.x,
            y: posisiOrtu.y + jarakCucu + (indexCucu * 210)
        });
        
        cucuPerOrangTua[sapi.parentId]++;
    });

    // ========================================
    // Gambar garis penghubung antar sapi
    // ========================================
    
    const semuaGaris = groupUtama.selectAll('.link')
                 .data(dataSilsilah.linkDataArray).enter().append('g');

    // Garis penghubung orang tua ke anak
    semuaGaris.filter(garisData => garisData.relationship === 'parent-child')
         .append('path')
         .attr('class', 'link-line')
         .attr('d', garisData => {
            // Cari posisi awal dan tujuan
            const posisiAwal = posisiSemua.find(pos => pos.k === garisData.from);
            const posisiTujuan = posisiSemua.find(pos => pos.k === garisData.to);
            
            if(!posisiAwal || !posisiTujuan) return '';

            const sapiTujuan = dataSapi.find(sapi => sapi.key === garisData.to);
            
            // Garis lurus buat anak ke cucu
            if(sapiTujuan && (sapiTujuan.role === 'Cucu')){
                return `M${posisiAwal.x},${posisiAwal.y+70}L${posisiTujuan.x},${posisiTujuan.y-70}`;
            }
            
            // Garis siku buat yang lain
            const titikTengah = posisiAwal.y + (posisiTujuan.y - posisiAwal.y) / 2;
            return `M${posisiAwal.x},${posisiAwal.y+70}L${posisiAwal.x},${titikTengah}L${posisiTujuan.x},${titikTengah}L${posisiTujuan.x},${posisiTujuan.y-70}`;
         });

    // Garis penghubung pernikahan
    semuaGaris.filter(garisData => garisData.relationship === 'marriage')
         .append('line')
         .attr('class', 'link-marriage')
         .attr('x1', garisData => {
            const posisiAwal = posisiSemua.find(pos => pos.k === garisData.from);
            const posisiTujuan = posisiSemua.find(pos => pos.k === garisData.to);
            const sapiAwal = dataSapi.find(sapi => sapi.key === garisData.from);
            const sapiTujuan = dataSapi.find(sapi => sapi.key === garisData.to);
            
            if(!posisiAwal || !posisiTujuan || !sapiAwal || !sapiTujuan) return 0;
            
            // Sesuaikan buat garis pernikahan orang tua/kakek nenek
            if((sapiAwal.role === 'Ayah' && sapiTujuan.role === 'Ibu') ||
               (sapiAwal.role === 'Kakek' && sapiTujuan.role === 'Nenek')){
                return posisiAwal.x + 120;
            }
            return posisiAwal.x + 120;
         })
         .attr('y1', garisData => posisiSemua.find(pos => pos.k === garisData.from)?.y || 0)
         .attr('x2', garisData => {
            const posisiAwal = posisiSemua.find(pos => pos.k === garisData.from);
            const posisiTujuan = posisiSemua.find(pos => pos.k === garisData.to);
            const sapiAwal = dataSapi.find(sapi => sapi.key === garisData.from);
            const sapiTujuan = dataSapi.find(sapi => sapi.key === garisData.to);
            
            if(!posisiAwal || !posisiTujuan || !sapiAwal || !sapiTujuan) return 0;
            
            if((sapiAwal.role === 'Ayah' && sapiTujuan.role === 'Ibu') ||
               (sapiAwal.role === 'Kakek' && sapiTujuan.role === 'Nenek')){
                return posisiTujuan.x - 120;
            }
            return posisiTujuan.x - 120;
         })
         .attr('y2', garisData => posisiSemua.find(pos => pos.k === garisData.to)?.y || 0);

    // ========================================
    // Gambar card sapi dalam diagram
    // ========================================
    
    const elementNode = groupUtama.selectAll('.node')
                 .data(dataSilsilah.nodeDataArray).enter().append('g')
                 .attr('transform', sapiData => {
                    const posisiSapi = posisiSemua.find(pos => pos.k === sapiData.key);
                    return posisiSapi ? `translate(${posisiSapi.x-120},${posisiSapi.y-70})` : '';
                 });

    // Bikin card HTML buat setiap sapi
    elementNode.append('foreignObject')
         .attr('width', 240).attr('height', 140)
         .append('xhtml:div')
         .attr('class', sapiData => {
            let kelasCard = 'node-card' + (sapiData.gender === 'Jantan' ? ' male' : ' female');
            if(sapiData.isMain) kelasCard += ' main';
            return kelasCard;
         })
         .html(sapiData => `
            <!-- 
            ==================================================
            KODE LAMA (DIKOMENTARI BUAT JAGA-JAGA)
            ==================================================
            <div style="position:relative;text-align:center">
                <span class="gender-icon ${sapiData.gender === 'Jantan' ? 'male' : 'female'}">
                    ${sapiData.gender === 'Jantan' ? '♂' : '♀'}
                </span>
                <div class="node-name">${sapiData.name}</div>
                <div class="node-vid">ID: ${sapiData.vid}</div>
                ${sapiData.role ? `<div class="node-role">${sapiData.role}${sapiData.role === 'Utama' ? ' ★' : ''}"</div>` : ''}
                <div style="font-size:.75rem;color:#6b7280">
                    <div>Ras: ${sapiData.breed}</div>
                    <div>Lahir: ${sapiData.birthYear}</div>
                    <div>Umur: ${sapiData.age}</div>
                </div>
            </div>
            -->
            
            <!-- 
            ==================================================
            KODE BARU - STYLE MINIMALIS YANG LEBIH KECE
            ==================================================
            -->
            <div>
                <div class="node-role">${sapiData.role}${sapiData.isMain ? ' ★' : ''}</div>
                <div class="node-name">${sapiData.name}</div>
                <div class="node-details">
                    ID: ${sapiData.vid}<br/>
                    Lahir: ${sapiData.birthdate}<br/>
                    Kelamin: ${sapiData.gender}
                </div>
            </div>
         `)
         .on('click', (event, sapiData) => {
            event.stopPropagation();
            
            // Kalo ada URL, tampilin detail di modal
            if (sapiData.url) {
                // Ganti URL buat ambil data via AJAX
                const urlDetail = sapiData.url.replace('view', 'detail-ajax');
                const modalDetail = $('#detailModal');

                // Tampilin loading dulu
                modalDetail.find('.modal-title').text('Memuat Detail Ternak...');
                modalDetail.find('.modal-body').html('<div class="text-center p-5"><span class="spinner-border" role="status"></span></div>');
                modalDetail.modal('show');

                // Ambil data detail pake fetch
                fetch(urlDetail)
                    .then(response => response.text())
                    .then(htmlContent => {
                        modalDetail.find('.modal-body').html(htmlContent);
                        
                        // Ambil judul dari data attribute
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = htmlContent;
                        const judulModal = $(tempDiv).find('.card').data('livestock-title') || 'Detail Ternak';
                        modalDetail.find('.modal-title').text(judulModal);
                    })
                    .catch(error => {
                        console.error("Gagal ambil detail sapi:", error);
                        modalDetail.find('.modal-body').html('<div class="alert alert-danger">Gagal memuat detail. Silakan coba lagi ya!</div>');
                    });
            }
         });

    // ========================================
    // Atur zoom awal diagram
    // ========================================
    
    // Fungsi buat fit semua diagram ke layar
    const fitKeLayar = () => {
        const batasArea = groupUtama.node().getBBox();
        const skalaFit = Math.min(lebarCanvas / batasArea.width, tinggiCanvas / batasArea.height) * 0.8;
        
        svgElement.call(zoomHandler.transform,
            d3.zoomIdentity
              .translate((lebarCanvas - batasArea.width * skalaFit) / 2 - batasArea.x * skalaFit,
                        (tinggiCanvas - batasArea.height * skalaFit) / 2 - batasArea.y * skalaFit)
              .scale(skalaFit));
    };
    
    // Fungsi buat fokus ke sapi utama
    const fokusKeSapiUtama = () => {
        const sapiUtama = dataSilsilah.nodeDataArray.find(sapi => sapi.isMain);
        const posisiUtama = posisiSemua.find(pos => pos.k === sapiUtama.key);
        const skalaFokus = Math.min(lebarCanvas / 260, 3);
        
        svgElement.call(zoomHandler.transform,
            d3.zoomIdentity.translate(lebarCanvas / 2 - posisiUtama.x * skalaFokus,
                                   tinggiCanvas / 2 - posisiUtama.y * skalaFokus)
                          .scale(skalaFokus));
    };
    
    // Pilih mode zoom berdasarkan ukuran layar
    (lebarCanvas >= 992 ? fitKeLayar : fokusKeSapiUtama)();

    // Simpen referensi buat kontrol zoom
    window.diagramSilsilah = {svg: svgElement, zoom: zoomHandler};
}

// ========================================
// FUNGSI KONTROL ZOOM (DIPANGGIL DARI TOMBOL)
// ========================================

// Zoom masuk (perbesar)
window.zoomIn = function () {
    if (!window.diagramSilsilah) return;
    window.diagramSilsilah.svg.transition().duration(250)
               .call(window.diagramSilsilah.zoom.scaleBy, 1.3);
};

// Zoom keluar (perkecil)
window.zoomOut = function () {
    if (!window.diagramSilsilah) return;
    window.diagramSilsilah.svg.transition().duration(250)
               .call(window.diagramSilsilah.zoom.scaleBy, 0.7);
};

// Reset zoom ke posisi awal
window.resetZoom = function () {
    if (!window.diagramSilsilah) return;
    
    // Hapus diagram lama dan gambar ulang
    d3.select('#familyTreeDiagram').selectAll('*').remove();
    initDiagram();  // Gambar ulang diagram dari awal
};

// ========================================
// JALANIN DIAGRAM PAS HALAMAN UDAH SIAP
// ========================================
$(initDiagram);
JS;

// Daftarin JavaScript ke halaman
$this->registerJs($jsKode, \yii\web\View::POS_READY);
?>

<!-- ========================================
     BAGIAN 5: HTML LAYOUT HALAMAN
     ======================================== -->
     
<!-- Tombol navigasi atas -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div class="btn-group">
        <?= Html::a('<i class="bi bi-arrow-left"></i> Kembali',
            ['index'],
            ['class'=>'btn btn-outline-secondary btn-sm']) ?>
    </div>
</div>

<!-- Card utama buat nampilin diagram silsilah -->
<div class="card">
    <!-- Header card dengan info sapi dan tombol kontrol -->
    <div class="card-header d-flex flex-wrap justify-content-between">
        <!-- Info detail sapi -->
        <div>
            <h5 class="mb-1">
                <i class="bi bi-diagram-3"></i>
                Nama: <?= Html::encode($model->name) ?>
            </h5>
            <small class="text-muted d-block">
                <i class="bi bi-person-fill"></i>
                Pemilik: <?= Html::encode($model->user->username ?? '—') ?>
            </small>
            <small class="text-muted d-block">
                <i class="bi bi-house-door-fill"></i>
                Kandang: <?= Html::encode($model->cage->name ?? '—') ?>
                (<?= Html::encode($model->cage->location ?? '—') ?>)
            </small>
        </div>

        <?php if ($dataKeluargaSapi): ?>
            <!-- Tombol kontrol zoom buat desktop -->
            <div class="btn-group d-none d-lg-inline-flex align-self-start mt-2 mt-lg-0">
                <button class="btn btn-outline-primary btn-sm" onclick="zoomIn()"><i class="bi bi-zoom-in"></i></button>
                <button class="btn btn-outline-secondary btn-sm" onclick="zoomOut()"><i class="bi bi-zoom-out"></i></button>
                <button class="btn btn-outline-info btn-sm" onclick="resetZoom()"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Body card tempat diagram silsilah ditampilin -->
    <div class="card-body">
        <div class="family-tree-container">
            <?php if ($dataKeluargaSapi): ?>
                <!-- Container buat diagram D3.js -->
                <div id="familyTreeDiagram"></div>
            <?php else: ?>
                <!-- Pesan kalo ga ada data silsilah -->
                <div class="no-family">Tidak ada data silsilah tersedia.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($dataKeluargaSapi): ?>
<!-- Tombol kontrol zoom yang melayang buat mobile & tablet -->
<div class="tree-controls-mobile d-lg-none">
    <button class="btn btn-primary me-1"  onclick="zoomIn()"><i class="bi bi-zoom-in"></i></button>
    <button class="btn btn-secondary me-1" onclick="zoomOut()"><i class="bi bi-zoom-out"></i></button>
    <button class="btn btn-info"         onclick="resetZoom()"><i class="bi bi-arrow-clockwise"></i></button>
</div>
<?php endif; ?>

<!-- Modal buat nampilin detail sapi pas diklik -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel">Detail Ternak</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Konten detail sapi bakal dimuat di sini via AJAX -->
      </div>
    </div>
  </div>
</div>
