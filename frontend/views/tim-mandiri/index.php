<?php

use common\enums\ApprovalStatus;
use common\enums\CertificateGrade;
use common\enums\CertificateLevel;
use common\enums\CertificationStatus;
use common\enums\TeamRole;
use yii\helpers\Html;

/** @var \common\models\SelfTeamMember[] $self_team_member_request */
/** @var \common\models\SelfTeamMember[] $self_team_member_uncompleted */
/** @var \common\models\SelfTeamMember[] $self_team_member_completed */
?>

<div class="d-flex flex-column align-items-start gap-3">
    <h1>Tim Mandiri</h1>

    <div class="card p-3 d-flex flex-column gap-2">
        <h2>Permintaan Partisipasi Tim Mandiri</h2>
        <table class="table align-middle text-center">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Wilayah</th>
                    <th scope="col">Alamat Sekretaris</th>
                    <th scope="col">Tingkatan</th>
                    <th scope="col">Peran</th>
                    <th scope="col">Status</th>
                    <th scope="col">Tenggat Waktu Konfirmasi</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($self_team_member_request as $index => $member): ?>
                <tr>
                    <th scope="row"><?= $index + 1 ?></th>
                    <td><?= ucfirst($member->certification->saspriK->region_name) ?>
                    </td>
                    <td><?= $member->certification->saspriK->address ?>
                    </td>
                    <td><?= CertificateLevel::list()[$member->certification->level] ?></td>
                    <td><?= TeamRole::list()[$member->role] ?></td>
                    <td><?= ApprovalStatus::list()[$member->status] ?></td>
                    <td><?=
                            $member->certification->self_review_due_date
                            ? date('Y-m-d', strtotime($member->certification->self_review_due_date))
                            : '-'
                    ?></td>
                    <td>
                        <?= Html::a('Setuju', ['setuju', 'self_team_member_id' => $member->id], [
                            'class' => 'btn btn-success',
                            'data-method' => 'post',
                        ]) ?>
                        <?= Html::a('Tolak', ['tolak', 'self_team_member_id' => $member->id], [
                            'class' => 'btn btn-danger',
                            'data-method' => 'post',
                            'data-confirm' => 'Apakah Anda yakin ingin menolak permintaan bergabung Tim Mandiri ini?',
                        ]) ?>
                        <button class="btn btn-primary">Lihat</button>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <div class="card p-3 d-flex flex-column gap-2">
        <h2>Sertifikasi Berjalan</h2>
        <table class="table align-middle text-center">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Wilayah</th>
                    <th scope="col">Tingkatan</th>
                    <th scope="col">Tahapan</th>
                    <th scope="col">Tenggat Waktu Penilaian</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($self_team_member_uncompleted as $index => $member): ?>
                <tr>
                    <th scope="row"><?= $index + 1 ?></th>
                    <td><?= ucfirst($member->certification->saspriK->region_name) ?></td>
                    <td><?= CertificateLevel::list()[$member->certification->level] ?></td>
                    <td><?= CertificationStatus::list()[$member->certification->status] ?></td>
                    <td><?=
                        $member->certification->self_review_due_date
                        ? date('Y-m-d', strtotime($member->certification->self_review_due_date))
                        : '-'
                    ?></td>
                    <td>
                        <a class="btn btn-primary"
                            href="<?= \yii\helpers\Url::to(['tim-mandiri/self-review', 'certification_id' => $member->certification->id]) ?>">
                            Nilai
                        </a>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <div class="card p-3 d-flex flex-column gap-2">
        <h2>Riwayat Sertifikasi</h2>
        <table class="table align-middle text-center">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Wilayah</th>
                    <th scope="col">Tingkatan</th>
                    <th scope="col">Tanggal Pengajuan</th>
                    <th scope="col">Tanggal Penerbitan</th>
                    <th scope="col">Predikat</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($self_team_member_completed as $index => $member): ?>
                <tr>
                    <th scope="row"><?= $index + 1 ?></th>
                    <td><?= ucfirst($member->certification->saspriK->region_name) ?></td>
                    <td><?= CertificateLevel::list()[$member->certification->level] ?></td>
                    <td><?=
                        $member->certification->submitted_at
                        ? date('Y-m-d', strtotime($member->certification->submitted_at))
                        : '-'
                    ?></td>
                    <td><?=
                        $member->certification->issued_at
                        ? date('Y-m-d', strtotime($member->certification->issued_at))
                        : '-'
                    ?></td>
                    <td><?= CertificateGrade::list()[$member->certification->grade] ?: '-' ?></td>
                    <td>
                        <button class="btn btn-primary">Lihat</button>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
