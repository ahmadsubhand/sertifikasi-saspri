<?php

namespace common\models\query;

use common\enums\UserRole;
use common\models\Certification;
use common\models\SaspriK;

/**
 * This is the ActiveQuery class for [[\common\models\User]].
 *
 * @see \common\models\User
 */
class UserQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \common\models\User[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\User|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function availableForSaspriK()
    {
        return $this->joinWith('role r')
            ->andWhere(['!=', 'r.item_name', UserRole::ADMIN])
            ->andWhere(['saspri_k_id' => null]);
    }

    public function availableForSelfTeam(SaspriK $saspri_k, ?Certification $certification): self
    {
        $existing_member_ids = $certification
            ? $certification->getSelfTeamMembers()
            ->select('user_id')
            ->column()
            : [];

        return $saspri_k->getUsers()
            ->where(['!=', 'id', $saspri_k->coordinator_id])
            ->andWhere(['not in', 'id', $existing_member_ids]);
    }

    public function availableForPeerTeam(Certification $certification): self
    {
        $existing_member_ids = $certification
            ->getPeerTeamMembers()
            ->select('user_id')
            ->column();

        return $this->joinWith('role r')
            ->andWhere(['not in', 'id', $existing_member_ids])
            ->andWhere([
                'or',
                ['r.item_name' => UserRole::ADMIN],
                [
                    'and',
                    ['not', ['saspri_k_id' => null]],
                    ['!=', 'saspri_k_id', $certification->saspri_k_id]
                ]
            ]);
    }
}
