<?php

namespace common\services;

use common\models\Assessment;
use common\models\IndicatorGroup;
use common\models\form\IndicatorGroupForm;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\UnprocessableEntityHttpException;

class IndicatorGroupService
{
    public static function findOrFail(int $id): IndicatorGroup
    {
        $group = IndicatorGroup::findOne($id);
        if (!$group) { 
            throw new NotFoundHttpException('Grup tidak ditemukan'); 
        }
        return $group;
    }

    public static function save(?int $group_id, IndicatorGroupForm $data): IndicatorGroup
    {
        $indicator_group = $group_id ? self::findOrFail($group_id) : new IndicatorGroup();

        if ($group_id) {
            // Mengecek parent atau child langsung dari model db,
            // bukan dari model yang sudah disisipkan input user ($indicator_group->load)
            if ($indicator_group->parent_group_id) {
                if (!$data->parent_group_id) {
                    throw new BadRequestHttpException(
                        ($data->code ?? $indicator_group->code) . ' adalah subgrup sehinggga wajib memiliki grup utama'
                    );
                }
            } else if (!$indicator_group->parent_group_id && $data->parent_group_id) {
                throw new BadRequestHttpException(
                    ($data->code ?? $indicator_group->code) . ' adalah grup utama sehingga tidak dapat dipindahkan ke dalam grup lain'
                );
            }
        }

        if ($data->parent_group_id) {
            $isValidParent = Assessment::findOne($data->assessment_id)
                ->getRootGroups()
                ->where(['id' => $data->parent_group_id])
                ->exists();
            if (!$isValidParent) {
                throw new NotFoundHttpException('Grup utama yang dipilih tidak ditemukan atau bukan grup utama yang valid');
            }
        }

        $indicator_group->assessment_id = $data->assessment_id;
        $indicator_group->setAttributes($data->attributes);

        if ($indicator_group->assessment->getCertifications()->exists()) {
            throw new UnprocessableEntityHttpException(
                'Grup tidak dapat ditambah/diubah karena asesmen sudah digunakan dalam proses sertifikasi'
            );
        }

        $remaining_weight = $indicator_group->countRemainingWeight();
        if ($remaining_weight < $indicator_group->weight) {
            $parent_group = $indicator_group->parentGroup;
            if ($parent_group) {
                throw new UnprocessableEntityHttpException(
                    'Total bobot dalam grup ' .
                    $parent_group->code .
                    ' tidak boleh melebihi 100. Saat ini sisa bobot yang tersedia hanya ' .
                    $remaining_weight
                );
            } else {
                throw new UnprocessableEntityHttpException(
                    'Total bobot grup utama dalam asesmen ini ' .
                    'tidak boleh melebihi 100. Saat ini sisa bobot yang tersedia hanya ' .
                    $remaining_weight
                );
            }
        }
        
        $indicator_group->save();
        return $indicator_group;
    }

    public static function delete(int $id): IndicatorGroup
    {
        $model = self::findOrFail($id);
        
        if ($model->assessment->getCertifications()->exists()) {
            throw new UnprocessableEntityHttpException(
                'Grup tidak dapat dihapus karena asesmen sudah digunakan dalam proses sertifikasi'
            );
        }

        $model->delete();
        return $model;
    }
}