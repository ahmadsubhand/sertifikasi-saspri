<?php

namespace common\models\form;

use yii\base\Model;

class CattleCalculatorForm extends Model
{
    public ?string $businessType = null;
    public ?string $nama_sapi = null;
    public float $marginKeuntungan = 0.0;
    public float $inflasi = 0.0;
    public float $biayaTambahan = 0.0;

    public function rules()
    {
        return [
            [['nama_sapi'], 'required'],
            ['businessType', 'in', 'range' => ['penggemukan', 'breeding'], 'skipOnEmpty' => true],
            [['marginKeuntungan', 'inflasi', 'biayaTambahan'], 'default', 'value' => 0],
            [['marginKeuntungan', 'inflasi', 'biayaTambahan'], 'number', 'min' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'businessType'      => 'Pilih Jenis Usaha',
            'nama_sapi'         => 'Nama Sapi',
            'marginKeuntungan'  => 'Margin Keuntungan (%)',
            'inflasi'           => 'Inflasi & Risiko (%)',
            'biayaTambahan'     => 'Biaya Tambahan per Sapi (Rp)',
        ];
    }

    public function calculate(array $stats = []): array
    {
        if (empty($stats)) {
            return [];
        }

        if (!empty($stats['businessType'])) {
            $this->businessType = $stats['businessType'];
        }

        $businessType = $this->businessType === 'breeding' ? 'breeding' : 'penggemukan';
        $base    = $stats['base'][$businessType] ?? [];
        $shared  = $stats['base']['shared'] ?? [];
        $labour  = $stats['labour'] ?? [];

        $hargaPedet = (float) ($base['harga_pedet'] ?? 0);
        $tambahan   = (float) $this->biayaTambahan;

        $components = [
            'hargaPedet'       => $hargaPedet,
            'hargaPakan'       => (float) ($base['harga_pakan'] ?? 0),
            'hargaKandang'     => (float) ($base['harga_kandang'] ?? 0),
            'hargaKesehatan'   => (float) ($base['harga_kesehatan'] ?? 0),
            'hargaTenagaKerja' => (float) ($base['harga_tenaga_kerja'] ?? 0),
            'hargaListrik'     => (float) ($base['harga_listrik'] ?? 0),
            'hargaLahan'       => (float) ($base['harga_lahan'] ?? 0),
            'biayaTambahan'    => $tambahan,
        ];

        $hpp = array_sum($components);

        $marginRate         = ((float) $this->marginKeuntungan + (float) $this->inflasi) / 100;
        $hargaMarginInflasi = $hpp * $marginRate;
        $hargaJual          = $hpp + $hargaMarginInflasi;

        return [
            'businessType'        => $businessType,
            'components'          => $components,
            'totalHPP'            => $hpp,
            'hargaMarginInflasi'  => $hargaMarginInflasi,
            'hargaJual'           => $hargaJual,
            'margin'              => (float) $this->marginKeuntungan,
            'inflasi'             => (float) $this->inflasi,
            'feedBreakdown'       => $base['feed_breakdown'] ?? [],
            'healthBreakdown'     => $base['health_breakdown'] ?? [],
            'maintenance'         => [
                'days'   => (float) ($shared['maintenance_days'] ?? 0),
                'months' => (float) ($shared['maintenance_months'] ?? 0),
                'years'  => (float) ($shared['maintenance_years'] ?? 0),
            ],
            'labour'              => $labour,
            'shared'              => $shared,
        ];
    }
}
