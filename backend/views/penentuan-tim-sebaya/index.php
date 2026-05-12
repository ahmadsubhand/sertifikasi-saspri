<?php

use yii\helpers\Html;

/** @var \common\models\Certification[] $certifications */

$this->title = 'Permintaan Pembentukan Tim Sebaya';
?>

<div class="d-flex flex-column align-items-start gap-3">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card p-3 d-flex flex-column gap-2 w-100">
        <table class="table align-middle text-center">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Wilayah</th>
                    <th scope="col">Alamat Sekretaris</th>
                    <th scope="col">Tingkatan</th>
                    <th scope="col">Tenggat Waktu Pembentukan</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($certifications as $index => $certification): ?>
                <tr>
                    <th scope="row"><?= $index + 1 ?></th>
                    <td><?= Html::encode(ucfirst($certification->saspriK->region_name)) ?></td>
                    <td><?= Html::encode($certification->saspriK->address) ?></td>
                    <td><?= Html::encode(ucfirst($certification->level)) ?></td>
                    <td><?= 
                        $certification->peer_team_due_date 
                        ? date('Y-m-d', strtotime($certification->peer_team_due_date)) 
                        : '-' 
                    ?></td>
                    <td>
                        <a href="<?= \yii\helpers\Url::to(['pembentukan-tim-sebaya', 'certification_id' => $certification->id]) ?>" class="btn btn-primary">
                            Lihat / Bentuk Tim
                        </a>
                    </td>
                </tr>
                <?php endforeach ?>
                <?php if (empty($certifications)): ?>
                <tr>
                    <td colspan="6" class="text-center">Tidak ada sertifikasi yang menunggu pembentukan tim sebaya.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
