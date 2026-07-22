<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "note_images".
 *
 * @property int $id
 * @property int $note_id
 * @property string $image_path
 *
 * @property Note $note
 */
class NoteImage extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%note_images}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['note_id', 'image_path'], 'required'],
            [['note_id'], 'integer'],
            [['image_path'], 'string', 'max' => 255],
            [['note_id'], 'exist', 'skipOnError' => true, 'targetClass' => Note::class, 'targetAttribute' => ['note_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'note_id' => 'Note ID',
            'image_path' => 'Image Path',
        ];
    }

    /**
     * Gets query for [[Note]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNote()
    {
        return $this->hasOne(Note::class, ['id' => 'note_id']);
    }
}
