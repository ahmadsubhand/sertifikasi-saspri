<?php

use common\enums\RequestResponse;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var string|array $yes*/
/** @var string|array $no*/
/** @var string|array $look*/
?>

<div>
  <?= Html::a('<i class="fa-solid fa-check"></i>', $yes, [
    'class' => 's-btn-green btn btn-sm',
    'data-method' => 'post',
    'data-params' => [
        'action' => RequestResponse::APPROVE,
    ],
  ]) ?>

  <a href="<?php echo Url::to($look) ?>" class="s-btn-main btn btn-sm"><i class="fa-solid fa-magnifying-glass"></i></a>
  <?= Html::a('<i class="fa-solid fa-x"></i>', $no, [
    'class' => 's-btn-red btn btn-sm',
    'data-method' => 'post',
    'data-params' => [
        'action' => RequestResponse::REJECT,
    ],
    'data-confirm' => 'Apakah Anda yakin ingin menolak permintaan bergabung Tim Mandiri ini?',
  ]) ?>
</div>