<?php

namespace common\models\form;

use yii\base\Model;

class HargaJual extends Model //dummy
{
    public ?string $nama_sapi = null;
    public ?float $biaya_pakan = null;
    public ?float $biaya_suplemen = null;
    public ?float $biaya_obat = null;
    public ?float $biaya_peralatan = null;
    public ?float $upah_tenaga_kerja = null;
    public ?float $biaya_anak_sapi = null;

    public function rules()
    {
        return [
            [['nama_sapi'], 'string'],
            [['biaya_pakan', 'biaya_suplemen', 'biaya_obat', 'biaya_peralatan', 'upah_tenaga_kerja', 'biaya_anak_sapi'], 'number'],
        ];
    }
}
