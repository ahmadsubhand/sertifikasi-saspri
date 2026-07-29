<?php

/** 
 * @var yii\web\View $this 
 * @var array $calculationHistory
 * */

$this->title = 'History Perhitungan Harga Jual Sapi';
$this->registerJsVar('calculationHistory', $calculationHistory);
$this->registerJsVar('isGuest', \Yii::$app->user->isGuest);
$this->registerJsVar('csrfToken', \Yii::$app->request->getCsrfToken());

?>


<div class="container-fluid px-4 pt-4">
    <div class="content">
        <h2 class="section-title">Pilih Sapi untuk Melihat Perkembangan Harga Jual</h2>

        <div class="sapi-grid" id="sapiGrid"></div>

        <div id="trackingDetail" style="display: none;">
            <div class="chart-container">
                <h3 id="hargaJualChartTitle">Perkembangan Harga Jual - </h3>
                <canvas id="hargaJualChart" width="400" height="200"></canvas>
            </div>

            <div class="chart-container">
                <h3 id="totalHPPChartTitle">Perkembangan Total HPP - </h3>
                <canvas id="totalHPPChart" width="400" height="200"></canvas>
            </div>


            <div class="history-container">
                <h3>Tabel History Perhitungan</h3>
                <div class="table-responsive">
                    <table class="history-table" id="historyTable">
                        <thead>
                        <tr>
                            <th>Tanggal Perhitungan</th>
                            <th>Harga Jual</th>
                            <th>Perkembangan</th>
                            <th>HPP</th>
                            <th>Profit</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody id="historyTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Perhitungan Harga Jual</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detailModalBody">
        <!-- Detail diisi oleh JS -->
      </div>
    </div>
  </div>
</div>

<script>
    let selectedSapi = null;
    let hargaJualChart = null;
    let totalHPPChart = null;


    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR', minimumFractionDigits: 0
        }).format(number);
    }

    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'short', day: 'numeric', timeZone: 'Asia/Jakarta' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    }

    function sortRecords(records) {
        return [...records].sort((a, b) => {
            const da = new Date(a.tanggalPerhitungan);
            const db = new Date(b.tanggalPerhitungan);
            if (da.getTime() !== db.getTime()) return da - db;
            return (a.id || 0) - (b.id || 0);
        });
    }

    function displaySapiCards() {
        const sapiGrid = document.getElementById('sapiGrid');
        const sapiGroups = {};

        calculationHistory.forEach(item => {
            if (!sapiGroups[item.nama_sapi]) sapiGroups[item.nama_sapi] = [];
            sapiGroups[item.nama_sapi].push(item);
        });

        Object.keys(sapiGroups).forEach(name => {
            sapiGroups[name] = sortRecords(sapiGroups[name]);
        });

        sapiGrid.innerHTML = Object.keys(sapiGroups).map(name => {
            const records = sapiGroups[name];
            const latest = records[records.length - 1];
            return `
                <div class="sapi-card ${selectedSapi === name ? 'selected' : ''}" onclick="selectSapi('${name}')">
                    <div class="sapi-name">${name}</div>
                    <div class="sapi-info">Jenis: ${latest.businessType === 'penggemukan' ? 'Penggemukan' : 'Breeding'}</div>
                    <div class="latest-price">Harga Terakhir: ${formatRupiah(latest.hargaJual)}</div>
                    <div class="record-count">${records.length} perhitungan</div>
                </div>`;
        }).join('');
    }

    function selectSapi(name) {
        selectedSapi = name;
        displaySapiCards();
        showTrackingDetail(name);
    }

    function showTrackingDetail(name) {
        const records = sortRecords(calculationHistory.filter(item => item.nama_sapi === name));
        const latestPerDate = pickLatestPerDate(records);
        document.getElementById('trackingDetail').style.display = 'block';
        document.getElementById('hargaJualChartTitle').textContent = `Perkembangan Harga Jual - ${name}`;
        document.getElementById('totalHPPChartTitle').textContent = `Perkembangan Total HPP - ${name}`;
        updateProgressCharts(latestPerDate);

        if (!isGuest) {
            updateHistoryTable(records);
            document.querySelector('.history-container').style.display = 'block';
            document.querySelector('.chart-container:nth-of-type(2)').style.display = 'block'; // chart Total HPP
        } else {
            document.querySelector('.history-container').style.display = 'none';
            document.querySelector('.chart-container:nth-of-type(2)').style.display = 'none'; // sembunyikan chart Total HPP
        }
    }

    function dateKey(dateString) {
        return new Date(dateString).toISOString().slice(0, 10);
    }

    function pickLatestPerDate(records) {
        const map = {};
        records.forEach(r => {
            const key = dateKey(r.tanggalPerhitungan);
            const current = map[key];
            const currentTime = current ? new Date(current.tanggalPerhitungan) : null;
            const candidateTime = new Date(r.tanggalPerhitungan);
            if (!current || candidateTime > currentTime || (candidateTime.getTime() === currentTime.getTime() && (r.id || 0) > (current.id || 0))) {
                map[key] = r;
            }
        });
        return sortRecords(Object.values(map));
    }

    function buildTimeSeries(records, field) {
        return records.map(r => ({
            // gunakan timestamp agar adapter waktu dapat memplot dengan pasti
            x: new Date(r.tanggalPerhitungan).getTime(),
            y: r[field],
            isEdited: r.isEdited || r.status === 'edited',
            status: r.status
        }));
    }

    function updateProgressCharts(records) {
        const hargaJualSeries = buildTimeSeries(records, 'hargaJual');
        const hppSeries = buildTimeSeries(records, 'totalHPP');

        // Harga Jual Chart
        const ctxHargaJual = document.getElementById('hargaJualChart').getContext('2d');
        if (hargaJualChart) hargaJualChart.destroy();
        hargaJualChart = new Chart(ctxHargaJual, {
            type: 'line',
            data: {
                datasets: [
                    {
                        label: 'Harga Jual',
                        data: hargaJualSeries,
                        parsing: false,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#28a745',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const harga = context.parsed.y;
                                let label = `Harga Jual: Rp. ${(harga / 1e6).toFixed(0)}jt`;
                                
                                // Tambahkan "edited" jika point punya flag edited
                                const point = hargaJualSeries[context.dataIndex];
                                if (point?.isEdited || point?.status === 'edited') {
                                    label += ' (edited)';
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'dd MMM yyyy'
                        },
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 10
                        }
                    },
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: value => 'Rp. ' + (value / 1e6).toFixed(0) + 'jt'
                        }
                    }
                }
            }
        });


        // Total HPP Chart
        const ctxTotalHPP = document.getElementById('totalHPPChart').getContext('2d');
        if (totalHPPChart) totalHPPChart.destroy();
        totalHPPChart = new Chart(ctxTotalHPP, {
            type: 'line',
            data: {
                datasets: [
                    {
                        label: 'Total HPP',
                        data: hppSeries,
                        parsing: false,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#dc3545',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const hpp = context.parsed.y;
                                let label = `Total HPP: Rp. ${(hpp / 1e6).toFixed(0)}jt`;
                                
                                const point = hppSeries[context.dataIndex];
                                if (point?.isEdited || point?.status === 'edited') {
                                    label += ' (edited)';
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'dd MMM yyyy'
                        },
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 10
                        }
                    },
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: value => 'Rp. ' + (value / 1e6).toFixed(0) + 'jt'
                        }
                    }
                }
            }
        });
    }

    function updateHistoryTable(records) {
        const sorted = sortRecords(records);
        const tbody = document.getElementById('historyTableBody');
        tbody.innerHTML = sorted.map((item, i) => {
            let change = 'Perhitungan pertama';
            let changeClass = 'price-same';
            if (i > 0) {
                const prev = sorted[i - 1].hargaJual;
                const diff = item.hargaJual - prev;
                const percent = ((diff / prev) * 100).toFixed(1);
                if (diff > 0) {
                    change = `+${formatRupiah(diff)} (+${percent}%)`;
                    changeClass = 'price-up';
                } else if (diff < 0) {
                    change = `${formatRupiah(diff)} (${percent}%)`;
                    changeClass = 'price-down';
                } else {
                    change = 'Tidak berubah';
                }
            }
            const profit = item.hargaJual - item.totalHPP;
            return `
                <tr>
                    <td>${formatDate(item.tanggalPerhitungan)}</td>
                    <td>${formatRupiah(item.hargaJual)}</td>
                    <td class="${changeClass}">${change}</td>
                    <td>${formatRupiah(item.totalHPP)}</td>
                    <td class="${profit > 0 ? 'price-up' : 'price-down'}">${formatRupiah(profit)}</td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            <button type="button" class="btn btn-sm btn-info" title="Lihat Detail" onclick="showDetail(${item.id})">
                                <i class="bi bi-info-circle"></i> Detail
                            </button>
                            <?php if (!\Yii::$app->user->isGuest): ?>
                            <button type="button" class="btn btn-sm btn-secondary" title="Log" onclick="openHistoryLog(${item.id})">
                                <i class="bi bi-journal-text"></i> Log
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" title="Hapus" onclick="deleteRecord(${item.id})">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>`;
        }).join('');

    }

    function openHistoryLog(id) {
        const baseUrl = "<?= \yii\helpers\Url::to(['harga-jual/history-log']) ?>";
        window.location.href = `${baseUrl}?historyId=${id}`;
    }


    function deleteRecord(id) {
        if (!confirm('Yakin ingin menghapus perhitungan ini?')) return;
        const csrf = (typeof csrfToken !== 'undefined' && csrfToken) || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch(`<?= \yii\helpers\Url::to(['harga-jual/delete']) ?>?id=${id}`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-Token': csrf,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({_csrf: csrf})
        })
        .then(async res => {
            if (!res.ok) {
                throw new Error('Request gagal');
            }
            return res.json();
        })
        .then(res => {
            if (!res.success) {
                alert(res.message || 'Gagal menghapus history.');
                return;
            }
            const index = calculationHistory.findIndex(item => item.id === id);
            if (index !== -1) {
                calculationHistory.splice(index, 1);
            }
            if (selectedSapi) {
                showTrackingDetail(selectedSapi);
            } else {
                displaySapiCards();
            }
        })
        .catch(() => alert('Terjadi kesalahan saat menghapus history.'));
    }

    function showDetail(id) {
        const data = calculationHistory.find(item => item.id === id);
        if (!data) return;

        let detailHtml = `
            <table class="table table-bordered">
            <tr><th>Nama Sapi</th><td>${data.nama_sapi}</td></tr>
            <tr><th>Ras Sapi</th><td>${data.rasSapi}</td></tr>
            <tr><th>Visual ID</th><td>${data.visualId}</td></tr>
            <tr><th>Tanggal Perhitungan</th><td>${formatDate(data.tanggalPerhitungan)}</td></tr>
            <tr><th>Jenis Usaha</th><td>${data.businessType === 'breeding' ? 'Breeding' : 'Penggemukan'}</td></tr>
            <tr><th colspan="2" class="text-center hpp-title">HPP (Harga Pokok Produksi)</th></tr>
            <tr><th>${data.businessType === 'breeding' ? 'Nilai Investasi Indukan' : 'Harga Pedet'}</th><td>${formatRupiah(data.hargaPedet)}</td></tr>

            <tr class="table-secondary text-center"><th colspan="2">Biaya Pakan</th></tr>
            <tr><th>Pakan Hijauan</th><td>${formatRupiah(data.pakanHijauan)}</td></tr>
            <tr><th>Konsentrat</th><td>${formatRupiah(data.konsentrat)}</td></tr>
            <tr><th>Feed Additive</th><td>${formatRupiah(data.feedAdditive)}</td></tr>

            <tr class="table-secondary text-center"><th colspan="2">Biaya Kesehatan</th></tr>
            <tr><th>Inseminasi</th><td>${formatRupiah(data.insemination)}</td></tr>
            <tr><th>Vaksin</th><td>${formatRupiah(data.vaccine)}</td></tr>
            <tr><th>Vitamin</th><td>${formatRupiah(data.vitamin)}</td></tr>
            <tr><th>Pemeriksaan Kebuntingan</th><td>${formatRupiah(data.pemeriksaanKebuntingan)}</td></tr>
            <tr><th>Antibiotik</th><td>${formatRupiah(data.antibiotics)}</td></tr>
            <tr><th>Obat Cacing / Anthelmintic</th><td>${formatRupiah(data.anthelmintic)}</td></tr>

            <tr class="table-secondary text-center"><th colspan="2">Penyusutan & Tenaga Kerja</th></tr>
            <tr><th>Investasi Kandang & Peralatan</th><td>${formatRupiah(data.investasiKandang)}</td></tr>
            <tr><th>Umur Ekonomis (tahun)</th><td>${data.umurEkonomis}</td></tr>
            <tr><th>Gaji Tenaga Kerja</th><td>${formatRupiah(data.gajiPekerja)}</td></tr>
            <tr><th>Jumlah Sapi per Pekerja</th><td>${data.jumlahSapi}</td></tr>

            <tr class="table-secondary text-center"><th colspan="2">Biaya Lainnya</th></tr>
            <tr><th>Biaya Tambahan</th><td>${formatRupiah(data.biayaTambahan ?? 0)}</td></tr>

            <tr class="table-secondary text-center"><th colspan="2">Keuntungan dan Risiko</th></tr>
            <tr><th>Margin Keuntungan</th><td>${data.marginKeuntungan}%</td></tr>
            <tr><th>Inflasi & Risiko</th><td>${data.inflasi}%</td></tr>
            </table>
        `;

        document.getElementById('detailModalBody').innerHTML = detailHtml;
        new bootstrap.Modal(document.getElementById('detailModal')).show();
    }



    document.addEventListener('DOMContentLoaded', displaySapiCards);
</script>


<?php
$this->registerCss(<<<CSS
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
    font-weight: 500 !important;
    letter-spacing: 0 !important;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    background-color: var(--bs-body-bg);
    color: var(--bs-body-color);
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.content {
    padding: clamp(15px, 4vw, 40px); /* responsif padding */
}

.section-title {
    font-size: clamp(18px, 5vw, 32px); /* responsif judul */
    color: var(--bs-body-color);
    margin-bottom: 30px !important;
    text-align: center;
}

.sapi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); /* responsif grid */
    gap: 20px;
    margin-bottom: 30px;
}

.sapi-card {
    background-color: rgba(255, 255, 255, 0.03);
    border-left: 5px solid var(--bs-primary);
    color: var(--bs-body-color);
    border-radius: 15px;
    padding: clamp(15px, 4vw, 20px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    cursor: pointer;
    transition: all 0.3s ease;
}

.sapi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
}

.sapi-card.selected {
    border-left-color: var(--bs-danger);
    background-color: rgba(255, 0, 0, 0.05);
}

.sapi-name {
    font-size: clamp(14px, 4vw, 22px);
    font-weight: bold;
    color: var(--bs-body-color);
    margin-bottom: 10px;
}

.sapi-info {
    font-size: clamp(12px, 3.5vw, 16px);
    color: var(--bs-secondary-color);
    margin-bottom: 5px;
}

.latest-price {
    font-size: clamp(13px, 3.8vw, 18px);
    font-weight: bold;
    color: var(--bs-success);
}

.record-count {
    background-color: rgba(0, 123, 255, 0.15);
    color: var(--bs-info);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: clamp(11px, 3vw, 14px);
    display: inline-block;
    margin-top: 10px;
}

.chart-container, .history-container {
    background-color: rgba(255, 255, 255, 0.04);
    border-radius: 15px;
    padding: clamp(15px, 4vw, 25px);
    color: var(--bs-body-color);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    margin-top: 30px;
}

.history-container h3, .chart-container h3 {
    color: var(--bs-body-color);
    margin-bottom: 20px;
    text-align: center;
    font-size: clamp(16px, 4.5vw, 24px);
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    color: var(--bs-body-color);
}

.history-table th, .history-table td {
    padding: clamp(8px, 2.5vw, 12px);
    text-align: left;
    border-bottom: 1px solid var(--bs-border-color);
    font-size: clamp(11px, 3vw, 14px);
}

.history-table th {
    background-color: rgba(255, 255, 255, 0.05);
    font-weight: 600;
}

.hpp-title {
    color: var(--bs-primary) !important;
    font-weight: 600 !important;
    background-color: transparent !important;
}

.price-up {
    color: var(--bs-success);
}

.price-down {
    color: var(--bs-danger);
}

.price-same {
    color: var(--bs-secondary-color);
}

@media (max-width: 768px) {
    .history-table th, .history-table td {
        white-space: nowrap;
    }
}
CSS);
?>
