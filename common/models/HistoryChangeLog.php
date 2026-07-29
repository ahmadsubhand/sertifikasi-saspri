<?php

namespace common\models;

use yii\db\ActiveRecord;

class HistoryChangeLog extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%history_change_log}}';
    }

    public function rules()
    {
        return [
            [['history_id', 'changed_at'], 'required'],
            [['history_id'], 'integer'],
            [['changed_at'], 'safe'],
            [['previous_data', 'new_data'], 'string'],
            [['history_id'], 'exist', 'skipOnError' => true, 'targetClass' => History::class, 'targetAttribute' => ['history_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'history_id'   => 'History ID',
            'previous_data'=> 'Data Sebelumnya',
            'new_data'     => 'Data Terbaru',
            'changed_at'   => 'Diubah Pada',
        ];
    }

    public function getHistory()
    {
        return $this->hasOne(History::class, ['id' => 'history_id']);
    }

    public static function logChange(History $history, array $previous, array $new)
    {
        $log = new self();
        $log->history_id = $history->id;
        $log->previous_data = json_encode($previous, JSON_UNESCAPED_UNICODE);
        $log->new_data = json_encode($new, JSON_UNESCAPED_UNICODE);
        $log->changed_at = date('Y-m-d H:i:s');
        $log->save(false);
    }

    public static function logDeletion(History $history): void
    {
        $previous = $history->buildSnapshot();
        $new = [
            'deleted' => true,
            'history_id' => $history->id,
            'business_type' => $history->business_type,
            'livestock_id' => $history->livestock_id,
            'livestock_name' => $history->livestock->name ?? null,
            'visual_id' => $history->livestock->vid ?? null,
        ];

        $log = new self();
        $log->history_id = $history->id;
        $log->previous_data = json_encode($previous, JSON_UNESCAPED_UNICODE);
        $log->new_data = json_encode($new, JSON_UNESCAPED_UNICODE);
        $log->changed_at = date('Y-m-d H:i:s');
        $log->save(false);
    }
}
