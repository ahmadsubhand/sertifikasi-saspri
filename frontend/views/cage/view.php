<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var common\models\Cage $model
 */

$this->title = 'Detail Kandang: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Daftar Kandang', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<p class="text-subtitle text-muted">Informasi lengkap dan pengelolaan kandang <?= Html::encode($model->name) ?></p>

<section class="section">
    <div class="row">
        <!-- Informasi Dasar Kandang -->
        <div class="col-lg-8 col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Informasi Kandang</h4>
                    <div>
                        <?= Html::a(
                            '<i class="bi bi-pencil"></i> Edit Kandang',
                            ['update', 'id' => $model->id],
                            ['class' => 'btn btn-warning btn-sm me-2']
                        ) ?>
                        <?= Html::a(
                            '<i class="bi bi-arrow-left"></i> Kembali',
                            ['index'],
                            ['class' => 'btn btn-secondary btn-sm']
                        ) ?>
                    </div>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped table-bordered detail-view'],
                        'attributes' => [
                            'name:text:Nama Kandang',
                            'location:text:Lokasi',
                            [
                                'attribute' => 'capacity',
                                'label' => 'Kapasitas',
                                'value' => $model->capacity . ' ekor',
                            ],
                            [
                                'attribute' => 'description',
                                'label' => 'Deskripsi',
                                'value' => $model->description ?: '-',
                            ],
                            [
                                'attribute' => 'livestock_count',
                                'label' => 'Jumlah Ternak Saat Ini',
                                'value' => function ($model) {
                                    $count = $model->getLivestockCount();
                                    $capacity = $model->capacity;
                                    $percentage = $capacity > 0 ? ($count / $capacity) * 100 : 0;
                                    $class = $percentage > 80 ? 'danger' : ($percentage > 60 ? 'warning' : 'success');
                                    return Html::tag('span', $count . ' / ' . $capacity . ' ekor', [
                                        'class' => "badge bg-{$class}"
                                    ]);
                                },
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'created_at',
                                'label' => 'Tanggal Dibuat',
                                'value' => $model->created_at ? date('d F Y H:i', strtotime($model->created_at)) : '-',
                            ],
                            [
                                'attribute' => 'updated_at',
                                'label' => 'Terakhir Diperbarui',
                                'value' => $model->updated_at ? date('d F Y H:i', strtotime($model->updated_at)) : '-',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Statistik dan Actions -->
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Statistik Kandang</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between">
                                <span><strong>Total Ternak:</strong></span>
                                <span class="badge bg-primary"><?= $model->getLivestockCount() ?> ekor</span>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between">
                                <span><strong>Kapasitas:</strong></span>
                                <span class="badge bg-info"><?= $model->capacity ?> ekor</span>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between">
                                <span><strong>Sisa Kapasitas:</strong></span>
                                <span class="badge bg-success"><?= $model->capacity - $model->getLivestockCount() ?> ekor</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">Aksi Cepat</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?= Html::a(
                            '<i class="bi bi-plus-circle"></i> Tambah Ternak',
                            ['livestock/index'],
                            ['class' => 'btn btn-success btn-sm']
                        ) ?>
                        <?= Html::a(
                            '<i class="bi bi-list"></i> Lihat Semua Kandang',
                            ['index'],
                            ['class' => 'btn btn-primary btn-sm']
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Hewan Ternak di Kandang -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Daftar Hewan Ternak di Kandang</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($model->livestocks)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>VID</th>
                                        <th>Nama</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Ras</th>
                                        <th>Usia</th>
                                        <th>Kesehatan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $counter = 1; ?>
                                    <?php foreach ($model->livestocks as $livestock): ?>
                                        <tr>
                                            <td><?= $counter++ ?></td>
                                            <td><?= Html::encode($livestock->vid ?: '-') ?></td>
                                            <td><?= Html::encode($livestock->name) ?></td>
                                            <td>
                                                <?php
                                                $class = $livestock->gender === 'Jantan' ? 'primary' : 'danger';
                                                echo Html::tag('span', $livestock->gender, [
                                                    'class' => "badge bg-{$class}"
                                                ]);
                                                ?>
                                            </td>
                                            <td><?= Html::encode($livestock->breed_of_livestock ?: '-') ?></td>
                                            <td><?= Html::encode($livestock->getAgeLabel()) ?></td>
                                            <td>
                                                <?php
                                                $class = $livestock->health === 'Sehat' ? 'success' : 'danger';
                                                echo Html::tag('span', $livestock->health, [
                                                    'class' => "badge bg-{$class}"
                                                ]);
                                                ?>
                                            </td>
                                            <td>
                                                <?= Html::a(
                                                    '<i class="bi bi-eye"></i>',
                                                    ['silsilah/detail', 'id' => $livestock->id],
                                                    [
                                                        'class' => 'btn btn-sm btn-outline-primary',
                                                        'title' => 'Lihat Detail'
                                                    ]
                                                ) ?>
                                                <?= Html::a(
                                                    '<i class="bi bi-pencil"></i>',
                                                    ['livestock/update', 'id' => $livestock->id],
                                                    [
                                                        'class' => 'btn btn-sm btn-outline-warning',
                                                        'title' => 'Edit Ternak'
                                                    ]
                                                ) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-house text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">Belum ada hewan ternak di kandang ini</p>
                            <?= Html::a(
                                '<i class="bi bi-plus-circle"></i> Tambah Ternak Sekarang',
                                ['livestock/index'],
                                ['class' => 'btn btn-success']
                            ) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section> 
