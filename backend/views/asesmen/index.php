<?php

/** @var \common\models\Assessment[] $assessments */

use common\enums\CertificateLevel;
use yii\helpers\Html;

$this->title = 'Asesmen Sertifikasi SASPRI-K'

?>

<div class="d-flex flex-column align-items-start gap-3">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card p-3 d-flex flex-column gap-2 w-100">
        <table class="table align-middle text-center">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Judul</th>
                    <th scope="col">Level</th>
                    <th scope="col">Status</th>
                    <th scope="col">Tanggal Rilis</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assessments as $index => $assessment): ?>
                <tr>
                    <th scope="row"><?= $index + 1 ?></th>
                    <td><?= Html::encode($assessment->title) ?></td>
                    <td><?= Html::encode(CertificateLevel::list()[$assessment->level]) ?></td>
                    <td><?= Html::encode($assessment->level === $assessment->active_at_level ? 'Aktif' : 'Nonaktif') ?></td>
                    <td><?= 
                        $assessment->released_at
                        ? date('Y-m-d', strtotime($assessment->released_at)) 
                        : '-' 
                    ?></td>
                    <td>
                        <a href="<?= \yii\helpers\Url::to(['kelola', 'assessment_id' => $assessment->id]) ?>" class="btn btn-primary">
                            Edit
                        </a>
                    </td>
                </tr>
                <?php endforeach ?>
                <?php if (empty($assessments)): ?>
                <tr>
                    <td colspan="6" class="text-center">Tidak ada asesmen.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>