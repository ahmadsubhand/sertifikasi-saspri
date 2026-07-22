<?php
use yii\grid\GridView;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var common\models\Livestock $model
 */

$this->title = 'Data BCS untuk ' . Html::encode($model->name);
?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => [
                    'class' => 'table table-borderless mb-0',
                    'id' => 'example',
                ],
                'emptyText' => 'Tidak ada data BCS pada ' . Html::encode($model->name),
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'created_at',
                        'label' => 'Dibuat pada',
                        'format' => ['date', 'php:Y-m-d'],
                    ],
                    [
                        'attribute' => 'chest_size',
                        'label' => 'Lingkar Dada',
                        'value' => function ($model) {
                            return $model->chest_size . ' cm';
                        },
                    ],
                    [
                        'attribute' => 'body_weight',
                        'label' => 'Berat Sapi',
                        'value' => function ($model) {
                            return $model->body_weight . ' kg';
                        },
                    ],
                    [
                        'attribute' => 'hips',
                        'label' => 'Ukuran Pinggul',
                        'value' => function ($model) {
                            return $model->hips . ' cm';
                        },
                    ],
                ],
                'summary' => '', // Hilangkan summary text
            ]); ?>
        </div>
    </div>
</div>
