<?php

namespace common\services;

use common\models\IndicatorOption;
use common\models\form\IndicatorOptionForm;
use yii\web\ConflictHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class IndicatorOptionService
{
    public static function findOrFail(int $id): IndicatorOption
    {
        $option = IndicatorOption::findOne($id);
        if (!$option) throw new NotFoundHttpException('Opsi tidak ditemukan');
        return $option;
    }

    public static function save(?int $option_id, IndicatorOptionForm $data)
    {
        $indicator_option = $option_id ? self::findOrFail($option_id) : new IndicatorOption();

        if ($option_id) {
            if ($indicator_option->indicator->indicatorGroup->assessment->getCertifications()->exists()) {
                throw new UnprocessableEntityHttpException(
                    'Opsi tidak dapat diubah karena asesmen sudah digunakan dalam proses sertifikasi'
                );
            }
        }

        $already_exists = IndicatorOption::find()
            ->where(['indicator_id' => $data->indicator_id])
            ->andWhere(['code' => $data->code])
            ->andFilterWhere(['!=', 'id', $indicator_option->id ?? null])
            ->exists();
        if ($already_exists) {
            throw new ConflictHttpException('Opsi dengan kode yang sama sudah ada dalam indikator ini');
        }

        $indicator_option->setAttributes($data->attributes);

        if ($indicator_option->indicator->indicatorGroup->assessment->getCertifications()->exists()) {
            throw new UnprocessableEntityHttpException(
                'Opsi tidak dapat ditambah/diubah karena asesmen sudah digunakan dalam proses sertifikasi'
            );
        }

        $indicator_option->save();
        return $indicator_option;
    }

    public static function delete(int $id): IndicatorOption
    {
        $indicator_option = self::findOrFail($id);

        if ($indicator_option->indicator->indicatorGroup->assessment->getCertifications()->exists()) {
            throw new UnprocessableEntityHttpException(
                'Opsi tidak dapat dihapus karena asesmen sudah digunakan dalam proses sertifikasi'
            );
        }

        $indicator_option->delete();
        return $indicator_option;
    }
}