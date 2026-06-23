<?php

namespace common\models;

use common\helpers\UserHelper;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "notification".
 *
 * @property int $id
 * @property int $recipient_id
 * @property int|null $sender_id
 * @property string $title
 * @property string $body
 * @property string|null $web_link
 * @property string|null $api_link
 * @property int|null $read_at
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $recipient
 * @property User|null $sender
 */
class Notification extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notification';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['recipient_id', 'title', 'body'], 'required'],
            [['recipient_id', 'sender_id', 'read_at', 'created_at', 'updated_at'], 'integer'],
            [['body'], 'string'],
            [['title', 'web_link', 'api_link'], 'string', 'max' => 255],
            [['recipient_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['recipient_id' => 'id']],
            [['sender_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['sender_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'recipient_id' => 'Recipient ID',
            'sender_id' => 'Sender ID',
            'title' => 'Title',
            'body' => 'Body',
            'web_link' => 'Web Link',
            'api_link' => 'API Link',
            'read_at' => 'Read At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function fields()
    {
        $fields = parent::fields();

        $fields['recipient'] = 'recipient';
        $fields['sender'] = 'sender';

        return [...$fields];
    }

    /**
     * Gets query for [[Recipient]].
     *
     * @return ActiveQuery
     */
    public function getRecipient()
    {
        return $this->hasOne(User::class, ['id' => 'recipient_id'])->select(UserHelper::$basicSelect);
    }

    /**
     * Gets query for [[Sender]].
     *
     * @return ActiveQuery
     */
    public function getSender()
    {
        return $this->hasOne(User::class, ['id' => 'sender_id'])->select(UserHelper::$basicSelect);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead()
    {
        $this->read_at = time();
        return $this->save();
    }
}
