<?php

namespace common\models;

use yii\base\Model;

class HargaJual extends Model //dummy
{
    public $nama_sapi;
    public $biaya_pakan;
    public $biaya_suplemen;
    public $biaya_obat;
    public $biaya_peralatan;
    public $upah_tenaga_kerja;
    public $biaya_anak_sapi;

    public function rules()
    {
        return [
            [['nama_sapi'], 'string'],
            [['biaya_pakan', 'biaya_suplemen', 'biaya_obat', 'biaya_peralatan', 'upah_tenaga_kerja', 'biaya_anak_sapi'], 'number'],
        ];
    }
}
