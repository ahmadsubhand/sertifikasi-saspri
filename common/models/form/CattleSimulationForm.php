<?php

namespace common\models\form;

use yii\base\Model;

class CattleSimulationForm extends Model
{
    public ?string $namaSimulasi = null;

    public string $businessType = 'penggemukan';

    public float $hargaPedet = 0.0;

    public float $pakanHijauan = 0.0;
    public float $pakanKonsentrat = 0.0;
    public float $feedAdditive = 0.0;

    public float $insemination = 0.0;
    public float $vaksin = 0.0;
    public float $vitamin = 0.0;
    public float $pemeriksaanKebuntingan = 0.0;
    public float $antibiotik = 0.0;
    public float $anthelmintic = 0.0;

    public float $investasiKandang = 0.0;
    public float $tenagaKerja = 0.0;
    public float $listrik = 0.0;
    public float $lahan = 0.0;

    public float $biayaTambahan = 0.0;
    public float $marginKeuntungan = 0.0;
    public float $inflasi = 0.0;

    public float $maintenanceDays = 0.0;
    public float $maintenanceMonths = 0.0;
    public float $maintenanceYears = 0.0;

    public function rules(): array
    {
        return [
            [['businessType', 'hargaPedet', 'pakanHijauan', 'pakanKonsentrat', 'feedAdditive', 'investasiKandang', 'tenagaKerja', 'listrik', 'lahan', 'marginKeuntungan', 'inflasi'], 'required'],
            [['hargaPedet', 'pakanHijauan', 'pakanKonsentrat', 'feedAdditive', 'insemination', 'vaksin', 'vitamin', 'pemeriksaanKebuntingan', 'antibiotik', 'anthelmintic', 'investasiKandang', 'tenagaKerja', 'listrik', 'lahan', 'biayaTambahan', 'marginKeuntungan', 'inflasi', 'maintenanceDays', 'maintenanceMonths', 'maintenanceYears'], 'number', 'min' => 0],
            ['businessType', 'in', 'range' => ['penggemukan', 'breeding']],
            [['namaSimulasi'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'namaSimulasi'            => 'Nama Simulasi',
            'businessType'           => 'Jenis Usaha',
            'hargaPedet'             => 'Harga Pedet / Investasi Indukan',
            'pakanHijauan'           => 'Biaya Pakan Hijauan',
            'pakanKonsentrat'        => 'Biaya Pakan Konsentrat',
            'feedAdditive'           => 'Biaya Feed Additive',
            'insemination'           => 'Biaya Inseminasi',
            'vaksin'                 => 'Biaya Vaksin',
            'vitamin'                => 'Biaya Vitamin',
            'pemeriksaanKebuntingan' => 'Biaya Pemeriksaan Kebuntingan',
            'antibiotik'             => 'Biaya Antibiotik',
            'anthelmintic'           => 'Biaya Obat Cacing',
            'investasiKandang'       => 'Investasi Kandang & Peralatan',
            'tenagaKerja'            => 'Gaji Tenaga Kerja',
            'listrik'                => 'Biaya Listrik & Air',
            'lahan'                  => 'Biaya Lahan',
            'biayaTambahan'          => 'Biaya Tambahan',
            'marginKeuntungan'       => 'Margin Keuntungan (%)',
            'inflasi'                => 'Inflasi & Risiko (%)',
            'maintenanceDays'        => 'Lama Pemeliharaan (hari)',
            'maintenanceMonths'      => 'Lama Pemeliharaan (bulan)',
            'maintenanceYears'       => 'Lama Pemeliharaan (tahun)',
        ];
    }

    public function calculate(): array
    {
        if (!$this->validate()) {
            return [];
        }

        $feedBreakdown = [
            'forage'      => (float) $this->pakanHijauan,
            'concentrate' => (float) $this->pakanKonsentrat,
            'additive'    => (float) $this->feedAdditive,
        ];

        $healthBreakdown = [
            'insemination'    => (float) $this->insemination,
            'vaccine'         => (float) $this->vaksin,
            'vitamin'         => (float) $this->vitamin,
            'pregnancy_check' => (float) $this->pemeriksaanKebuntingan,
            'antibiotics'     => (float) $this->antibiotik,
            'anthelmintic'    => (float) $this->anthelmintic,
        ];

        $components = [
            'hargaPedet'       => (float) $this->hargaPedet,
            'hargaPakan'       => array_sum($feedBreakdown),
            'hargaKandang'     => (float) $this->investasiKandang,
            'hargaKesehatan'   => array_sum($healthBreakdown),
            'hargaTenagaKerja' => (float) $this->tenagaKerja,
            'hargaListrik'     => (float) $this->listrik,
            'hargaLahan'       => (float) $this->lahan,
            'biayaTambahan'    => (float) $this->biayaTambahan,
        ];

        $hpp = array_sum($components);
        $marginRate = ((float) $this->marginKeuntungan + (float) $this->inflasi) / 100;
        $hargaMarginInflasi = $hpp * $marginRate;
        $hargaJual = $hpp + $hargaMarginInflasi;

        return [
            'businessType'       => $this->businessType,
            'namaSimulasi'       => $this->namaSimulasi,
            'components'         => $components,
            'feedBreakdown'      => $feedBreakdown,
            'healthBreakdown'    => $healthBreakdown,
            'totalHPP'           => $hpp,
            'hargaMarginInflasi' => $hargaMarginInflasi,
            'hargaJual'          => $hargaJual,
            'margin'             => (float) $this->marginKeuntungan,
            'inflasi'            => (float) $this->inflasi,
            'maintenance'        => [
                'months' => (float) $this->maintenanceMonths,
            ],
        ];
    }
}
