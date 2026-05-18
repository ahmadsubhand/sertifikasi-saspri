<?php

use common\enums\TeamRole;
use common\models\PeerTeamMember;
use common\models\SelfTeamMember;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var SelfTeamMember[]|PeerTeamMember[]|null $model 
 * @var bool $is_self
*/
?>

<div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
  <div class="collapse show px-4" id="collapse-running">
    <?php if ($is_self): ?>
      <p class=" fw-bold h5">Anggota Tim Mandiri</p>
    <?php else : ?>
      <p class=" fw-bold h5">Anggota Tim Sebaya</p>
    <?php endif ?>
    <table class="table self-request text-center mt-3">
      <thead>
        <tr>
          <th scope="col">No</th>
          <th scope="col">Nama</th>
          <th scope="col">Peran</th>
          <th scope="col">Nomor Telepon</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($model) : ?>
          <?php foreach ($model as $key => $value) : ?>
            <tr>
              <td scope="row"><?php echo (int)$key + 1 ?></th>
              <td><?= Html::encode($value->user->username) ?></td>
              <td><?= Html::encode(TeamRole::list()[$value->role]) ?></td>
              <td><?= Html::encode($value->user->phone_number) ?></td>
            </tr>
          <?php endforeach ?>
        <?php endif ?>
      </tbody>
    </table>
  </div>
</div>