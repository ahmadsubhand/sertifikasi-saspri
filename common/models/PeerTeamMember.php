<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "peer_team_members".
 *
 * @property int $id
 * @property int $certification_id
 * @property int $user_id
 * @property string $status
 * @property string $role
 *
 * @property Certification $certification
 * @property User $user
 */
class PeerTeamMember extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'peer_team_members';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['certification_id', 'user_id', 'status', 'role'], 'required'],
            [['certification_id', 'user_id'], 'integer'],
            [['status', 'role'], 'string', 'max' => 255],
            [['certification_id', 'user_id'], 'unique', 'targetAttribute' => ['certification_id', 'user_id']],
            [['certification_id'], 'exist', 'skipOnError' => true, 'targetClass' => Certification::class, 'targetAttribute' => ['certification_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'certification_id' => 'Certification ID',
            'user_id' => 'User ID',
            'status' => 'Status',
            'role' => 'Role',
        ];
    }

    /**
     * Gets query for [[Certification]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCertification()
    {
        return $this->hasOne(Certification::class, ['id' => 'certification_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

}
