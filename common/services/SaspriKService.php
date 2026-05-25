<?php

namespace common\services;

use common\models\form\AddMembersForm;
use common\models\User;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class SaspriKService
{
    public static function findSaspriKAsCoordinatorOrFail()
    {
        $saspri_k = User::findOne(['id' => Yii::$app->user->id])
            ->saspriKAsCoordinator;
        if (!$saspri_k) {
            throw new ForbiddenHttpException('Hanya wali yang boleh mengakses halaman ini');
        }
        return $saspri_k;
    }

    public static function findMember(int $user_id, int $saspri_k_id): User
    {
        $user = User::findOne([
            'id' => $user_id,
            'saspri_k_id' => $saspri_k_id,
        ]);
        if (!$user) {
            throw new NotFoundHttpException('Anggota tidak ditemukan dalam SASPRI-K');
        }
        return $user;
    }

    public static function addMembers(AddMembersForm $data)
    {
        $saspri_k = SaspriKService::findSaspriKAsCoordinatorOrFail();

        $valid_users = User::find()->availableForSaspriK()
            ->andWhere(['id' => $data->user_ids])
            ->select('username')
            ->column();

        if (count($valid_users) !== count($data->user_ids)) {
            throw new BadRequestHttpException('Beberapa anggota tidak valid atau sudah terdaftar di SASPRI-K lain');
        }

        $saspri_k->addMembers($data->user_ids);

        return $valid_users;
    }

    public static function removeMember(int $user_id)
    {
        $saspri_k = SaspriKService::findSaspriKAsCoordinatorOrFail();
        $user = SaspriKService::findMember($user_id, $saspri_k->id);
        $user->removeUserFromSaspriK()->save();

        return $user;
    }
}