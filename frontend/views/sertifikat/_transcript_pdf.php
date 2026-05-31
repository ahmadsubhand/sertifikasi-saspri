<?php

use common\models\Certification;
use common\models\SaspriK;
use yii\helpers\Html;

/**
 * @var Certification $certification
 * @var SaspriK $saspri_k
 * @var array $transcripts
 */
?>

<?php
// Helper untuk format angka Indonesia (koma untuk desimal)
function formatAngka(float $angka, int $desimal = 2) {
    return number_format($angka, $desimal, ',', '.');
}
?>

<div class="transcript-container">
    <h3 class="text-center font-weight-bold">
        TRANSKRIP PERINGKAT <?= strtoupper(Html::encode($certification->level)) ?>
    </h3>
    <br><br>

    <h4 class="font-weight-bold mb-3">IDENTITAS :</h4>
    <table class="table" style="margin-bottom: 0;">
        <tbody>
            <tr>
                <td width="5%" class="text-center">1</td>
                <td width="35%">SASPRI Kawasan</td>
                <td width="60%" class="font-weight-bold">
                    <?= Html::encode($saspri_k->district->name) ?>
                </td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Alamat Sekretariat</td>
                <td class="font-weight-bold">
                    <?= Html::encode($saspri_k->address) ?>
                </td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Nama unit usaha Koperasi</td>
                <td class="font-weight-bold">
                    <?= Html::encode($saspri_k->cooperative_name) ?>
                </td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Jumlah kelompok yang dibina</td>
                <td class="font-weight-bold">
                    <?= Html::encode($saspri_k->number_of_groups) ?> kelompok
                </td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td>Jumlah anggota aktif dalam kelompok yang dibina</td>
                <td class="font-weight-bold">
                    <?= Html::encode($saspri_k->number_of_active_members) ?> peternak
                </td>
            </tr>
            <tr>
                <td class="text-center">6</td>
                <td>Ternak yang diusahakan</td>
                <td class="font-weight-bold">
                    <?= Html::encode($saspri_k->livestock_type) ?>
                </td>
            </tr>
            <tr>
                <td class="text-center">7</td>
                <td>Jumlah total ternak milik anggota aktif</td>
                <td class="font-weight-bold">
                    <?= Html::encode($saspri_k->total_livestock_count) ?> ekor
                </td>
            </tr>
            <tr>
                <td class="text-center">8</td>
                <td>Jumlah ternak indukan (pernah beranak)</td>
                <td class="font-weight-bold">
                    <?= Html::encode($saspri_k->breeding_livestock_count) ?> ekor
                </td>
            </tr>
            <tr>
                <td class="text-center">9</td>
                <td>Jumlah ternak dara produktif</td>
                <td class="font-weight-bold">
                    <?= Html::encode($saspri_k->productive_heifer_count) ?> ekor
                </td>
            </tr>
        </tbody>
    </table>

    <pagebreak />

    <h3 class="text-center font-weight-bold">TRANSKRIP NILAI SERTIFIKASI</h3>
    <br>

    <table class="table">
        <thead>
            <tr>
                <th width="55%">INDIKATOR UTAMA DAN ASPEK YANG DINILAI</th>
                <th width="15%">BOBOT</th>
                <th width="15%">NILAI<br>TERBOBOT</th>
                <th width="15%">NILAI<br>AKHIR</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transcripts as $root): ?>
                <tr style="background-color: #e9ecef;">
                    <td colspan="4" class="font-weight-bold">
                        <?= $root['code'] ?> <?= strtoupper($root['label']) ?> [<?= $root['weight'] * 100 ?>%]
                    </td>
                </tr>

                <?php foreach ($root['indicator_group'] as $child): ?>
                    <tr>
                        <td><?= $child['code'] ?> <?= $child['label'] ?></td>
                        <td class="text-center"><?= formatAngka($child['weight'], 3) ?></td>
                        <td class="text-center"><?= formatAngka($child['weighted_score'], 2) ?></td>
                        <td class="text-center"><?= formatAngka($child['score'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <td colspan="3" class="font-weight-bold">TOTAL NILAI <?= $root['code'] ?></td>
                    <td class="text-center font-weight-bold"><?= formatAngka($root['score'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>