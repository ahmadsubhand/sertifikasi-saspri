<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "audit_log".
 *
 * @property int $id
 * @property string $table_name
 * @property int $model_id
 * @property string $action
 * @property string|null $old_values
 * @property string|null $new_values
 * @property int|null $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int $created_at
 *
 * @property User $user
 */
class AuditLog extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'audit_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['old_values', 'new_values', 'user_id', 'ip_address', 'user_agent'], 'default', 'value' => null],
            [['table_name', 'model_id', 'action', 'created_at'], 'required'],
            [['model_id', 'user_id', 'created_at'], 'integer'],
            [['old_values', 'new_values'], 'safe'],
            [['table_name', 'user_agent'], 'string', 'max' => 255],
            [['action'], 'string', 'max' => 20],
            [['ip_address'], 'string', 'max' => 45],
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
            'table_name' => 'Table Name',
            'model_id' => 'Model ID',
            'action' => 'Action',
            'old_values' => 'Old Values',
            'new_values' => 'New Values',
            'user_id' => 'User ID',
            'ip_address' => 'Ip Address',
            'user_agent' => 'User Agent',
            'created_at' => 'Created At',
        ];
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
