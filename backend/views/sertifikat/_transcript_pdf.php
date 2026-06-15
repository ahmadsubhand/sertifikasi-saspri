<?php

use common\models\Certification;
use common\models\SaspriK;
use yii\helpers\Html;

/**
 * @var Certification $certification
 * @var SaspriK $saspri_k
 * @var array $transcripts
 */

$label = [
    'Alamat Sekretariat',
    'Nama unit usaha (koperasi)',
    'Nama wali SASPRI',
    'Jumlah kelompok yang dibina',
    'Jumlah anggota aktif dalam kelompok yang dibina',
    'Ternak yang diusahakan',
    'Jumlah total ternak milik anggota aktif',
    'Jumlah ternak indukan (pernah beranak)',
    'Jumlah ternak dara produktif (siap dikawinkan)',
];
$index = [
    'address',
    'cooperative_name',
    'coordinator_id',
    'number_of_groups',
    'number_of_active_members',
    'livestock_type',
    'total_livestock_count',
    'breeding_livestock_count',
    'productive_heifer_count',

];
$shingles = [
    'number_of_active_members' => 'Orang',
    'total_livestock_count' => 'Ekor',
    'breeding_livestock_count' => 'Ekor',
    'productive_heifer_count' => 'Ekor',
];
$score_range = [
    'a' => '90 - 100',
    'ab' => '75 - 90',
    'b' => '60 - 75',
    'bc' => '50 - 60',
    'c' => '< 50',
];
$predicate = [
    'a' => 'Tersertifikasi dengan pujian tertinggi commendable',
    'ab' => 'Tersertifikasi di atas standard',
    'b' => 'Tersertifikasi sesuai standard',
    'bc' => 'Proses sertifikasi diulang dalam satu tahun setelah dilakukan usaha perbaikan',
    'c' => 'Proses sertifikasi diulang kembali dalam 2 tahun',
];

$saspri_idt = (string)$saspri_k->region_name. ", ". $saspri_k->district->regency->name. ", Provinsi ".$saspri_k->district->regency->province->name
?>

<?php
// Helper untuk format angka Indonesia (koma untuk desimal)
function formatAngka(float $angka, int $desimal = 2)
{
    return number_format($angka, $desimal, ',', '.');
}
?>
<div style="width: 100%; height: 100%; display:flex;">
    <!-- DONT CHANGE THE STYLES NO BORDER NO WORK!!!! (idk why) -->
    <div style=" text-align: center; margin-left: 46mm; border:.1px solid rgba(255,255,255,0.001);">
        <h2 style="margin-top: 112mm; color:#f8c263 ;">SASPRI-K <?= Html::encode($saspri_k->region_name) ?></h2>
        <h4 style="margin-top: 6mm;"><?= Html::encode($certification->code?? 'code') ?></h4>
        <h4 style="margin-top: 94mm;"><?= Html::encode(Yii::$app->formatter->asDate($certification->issued_at, 'php:d F Y')) ?></h4>
    </div>
</div>
<pagebreak/>

<div class="transcript-container px-4">
    <h3 class="text-center font-weight-bold">
        TRANSKRIP PERINGKAT <?= strtoupper(Html::encode($certification->level)) ?>
    </h3>
    <br><br>

    <h5 class="mb-3">IDENTITAS :</h5>
    <table class="table-none " style="margin-bottom: 0;">
        <tbody>
            <tr class="">
                <td style="padding: 2px 6px;" class="h5 text-center">1</td>
                <td class="h5 ">SASPRI Kawasan</td>
                <td class="h5 font-weight-bold"><strong><?= Html::encode($saspri_idt) ?></strong></td>
            </tr>
            <?php foreach ($index as $key => $dat) : ?>
                <tr class="">
                    <td style="padding: 2px 6px;" class="h5 text-center"><?= Html::encode($key + 2) ?></td>
                    <td class="h5 "><?= Html::encode($label[$key]) ?></td>
                    <td class="h5 "><?= Html::encode($saspri_k[$dat] . " " . ($shingles[$dat] ?? '')) ?></td>
                </tr>
            <?php endforeach ?>

        </tbody>
    </table>
    <br>
    <br>

    <table class="table">
        <tbody>
            <tr aria-colcount="" class="border-1 border border-black">
                <th width="10%">KODE</th>
                <th width="50%">INDIKATOR UTAMA DAN ASPEK YANG DINILAI</th>
                <th width="15%">BOBOT</th>
                <th width="15%">NILAI<br>TERBOBOT</th>
                <th width="15%">NILAI<br>AKHIR</th>
            </tr>
            <?php foreach ($transcripts as $root): ?>
                <tr style="background-color: #ffffff;">
                    <td class=" font-weight-bold"><?= $root['code'] ?></td>
                    <td colspan="4" class="font-weight-bold" style="background-color: #ffffff;">
                        <?= strtoupper($root['label']) ?> [<?= $root['weight'] * 100 ?>%]
                    </td>
                </tr>

                <?php foreach ($root['indicator_group'] as $child): ?>
                    <tr>
                        <td><?= $child['code'] ?></td>
                        <td><?= $child['label'] ?></td>
                        <td class="text-center"><?= formatAngka($child['weight'], 3) ?></td>
                        <td class="text-center"><?= formatAngka($child['weighted_score'], 2) ?></td>
                        <td class="text-center"><?= formatAngka($child['score'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <td colspan="4" class="font-weight-bold text-right">TOTAL NILAI <?= $root['code'] ?></td>
                    <td class="text-center font-weight-bold"><?= formatAngka($root['score'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <br>
    <div style="text-align: right; width: 100%; display: flex; flex-direction: column; justify-content: end; justify-items: end;">   
        <table align="right" class="font-weight-bold align-self-end" style="margin-left: auto; display: inline-table;">
            <tbody>
                <tr>
                    <td style="padding: 0px 4px; text-align: right; border: none; white-space: nowrap;">NILAI TOTAL</td>
                    <td style="padding: 0px 2px; text-align: center; border: none;width: 3%;">:</td>
                    <td style="padding: 0px 4px; text-align: left; border: none; white-space: nowrap; width: 40%;">
                        <?= Html::encode($certification->total_score) ?>
                    </td>
                </tr>
    
                <tr>
                    <td style="padding: 0px 4px; text-align: right; border: none; white-space: nowrap;">HURUF MUTU</td>
                    <td style="padding: 0px 2px; text-align: center; border: none;width: 3%;">:</td>
                    <td style="padding: 0px 4px; text-align: left; border: none;width: 40%;" class="text-uppercase">
                        <?= Html::encode($certification->grade) ?>
                    </td>
                </tr>
    
                <tr>
                    <td style="padding: 0px 4px; text-align: right; border: none; white-space: nowrap;">PREDIKAT</td>
                    <td style="padding: 0px 2px; text-align: center; border: none;width: 3%;">:</td>
                    <td style="padding: 0px 4px; text-align: left; border: none;width: 40%;">
                        <?= Html::encode($predicate[$certification->grade] ?? '') ?>
                    </td>
                </tr>
    
                <tr>
                    <td style="padding: 0px 4px; text-align: right; border: none; white-space: nowrap;">SERTIFIKASI BERIKUTNYA</td>
                    <td style="padding: 0px 2px; text-align: center; border: none;width: 3%;">:</td>
                    <td style="padding: 0px 4px; text-align: left; border: none; white-space: nowrap;width: 40%;">
                        Paling Cepat <?= Html::encode(Yii::$app->formatter->asDate($certification->next_certification_due_date, 'php:d F Y')) ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <br>
    <table class="table-none">
        <tbody class=" font-weight-normal" style="font-weight: normal;">
            <tr>
                <td style="padding: 2px 6px;">Huruf Mutu</td>
                <td style="padding: 2px 6px;">Nilai</td>
                <td style="padding: 2px 6px;">Predikat/Keterangan</td>
            </tr>
            <?php foreach ($predicate as $score => $items) : ?>
                <tr>
                    <td style="padding: 2px 6px;" class=" text-uppercase"><?= Html::encode($score) ?></th>
                    <td style="padding: 2px 6px;"><?= Html::encode($score_range[$score]) ?></th>
                    <td style="padding: 2px 6px;"><?= Html::encode($predicate[$score]) ?></th>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>