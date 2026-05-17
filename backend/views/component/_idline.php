<?php

use common\models\District;
use common\models\User;


/** @var string $label */
/** @var string|null $shingles */
/** @var string|int $data */

switch (true) {
  case str_contains(strtolower($label), 'wali'):
    $data = User::findOne(['id' => $data])->username;
    break;

  case str_contains($label, 'SASPRI-'):
    $tmp = District::find()->andWhere(['id' => $data])->one();
    if (str_contains($label, 'SASPRI-KK')) {
      $data = $tmp->regency->name;
    } else if (str_contains($label, 'SASPRI-P')) {
      $data = $tmp->regency->province->name;
    } else {
      $data = $tmp->name;
    }
    break;

  case str_contains($label, 'Level'):
    $data = ucwords($data);
    break;

  case str_contains($label, 'Pred'):
    $data = strtoupper($data);
    break;

  case str_contains($label, 'Tanggal'):
    $data = date('d-m-Y', strtotime($data)) ?? '-';
    break;

  default:
    break;
}
?>

<div class="d-flex align-middle align-items-center">
  <p class="w-50 mb-1"><?php echo $label ?></p>
  <p class="mx-3 mb-1">:</p>
  <p class="w-50 mb-1 text-break"><?php echo (string)$data . " " . $shingles ?></p>
</div>