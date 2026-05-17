<?php

use common\enums\CertificateGrade;
use common\enums\CertificateLevel;
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
        } elseif (str_contains($label, 'SASPRI-P')) {
            $data = $tmp->regency->province->name;
        } else {
            $data = $tmp->name;
        }
        break;

    case str_contains($label, 'Level'):
        $data = CertificateLevel::list()[$data];
        break;

    case str_contains($label, 'Pred'):
        $data = CertificateGrade::list()[$data];
        break;

    case str_contains($label, 'Tanggal'):
        $data = $data
            ? (
                is_numeric($data)
                ? date('d-m-Y', $data)
                : date('d-m-Y', strtotime($data))
            )
            : '-';
        break;

    default:
        break;
}
?>

<div class="d-flex align-middle align-items-center">
  <p class="w-50 mb-1"><?php echo $label ?></p>
  <p class="mx-3 mb-1">:</p>
  <p class="w-50 mb-1"><?php echo (string)$data . " " . $shingles ?></p>
</div>