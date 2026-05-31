<?php

namespace common\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;

class AuditLogBehavior extends Behavior
{
    public $ignoredAttributes = [
        'created_at', 
        'updated_at',
        'auth_key', 
        'password_hash', 
        'password_reset_token',
        'verification_token',
        'access_token',
    ];

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * @param AfterSaveEvent $event
     */
    public function afterInsert($event): void
    {
        $this->log('INSERT', null, $this->owner->getAttributes());
    }

    /**
     * @param AfterSaveEvent $event
     */
    public function afterUpdate($event): void
    {
        $changedAttributes = $event->changedAttributes;
        $oldValues = [];
        $newValues = [];

        foreach ($changedAttributes as $name => $oldValue) {
            if (in_array($name, $this->ignoredAttributes)) {
                continue;
            }
            $newValue = $this->owner->$name;
            if ($oldValue != $newValue) {
                $oldValues[$name] = $oldValue;
                $newValues[$name] = $newValue;
            }
        }

        if (!empty($newValues)) {
            $this->log('UPDATE', $oldValues, $newValues);
        }
    }

    /**
     * @param Event $event
     */
    public function afterDelete($event): void
    {
        $this->log('DELETE', $this->owner->getAttributes(), null);
    }

    protected function log(string $action, array|null $oldValues, array|null $newValues)
    {
        // Pastikan aman jika dijalankan via Console (Cronjob)
        $userId = isset(Yii::$app->user) && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        $ip = isset(Yii::$app->request->userIP) ? Yii::$app->request->userIP : null;
        $userAgent = isset(Yii::$app->request->userAgent) ? Yii::$app->request->userAgent : null;

        Yii::$app->db->createCommand()->insert('audit_log', [
            'table_name' => $this->owner->tableName(),
            'model_id' => $this->owner->getPrimaryKey(),
            'action' => $action,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'user_id' => $userId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'created_at' => time(),
        ])->execute();
    }
}