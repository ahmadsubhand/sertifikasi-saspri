<?php

namespace common\models;

use ErrorException;
use Exception;
use yii\behaviors\TimestampBehavior;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "assessment".
 *
 * @property int $id
 * @property string $title
 * @property string|null $active_at_level
 * @property string $level
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $released_at
 *
 * @property Certification[] $certifications
 * @property IndicatorGroup[] $indicatorGroups
 * @property IndicatorGroup[] $rootGroups
 * @property IndicatorGroup[] $childGroups
 */
class Assessment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'assessment';
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
            [['active_at_level', 'released_at'], 'default', 'value' => null],
            [['title', 'level', 'created_at', 'updated_at'], 'required'],
            [['created_at', 'updated_at', 'released_at'], 'safe'],
            [['title', 'active_at_level', 'level'], 'string', 'max' => 255],
            [['active_at_level'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'active_at_level' => 'Active At Level',
            'level' => 'Level',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'released_at' => 'Released At',
        ];
    }

    /**
     * Gets query for [[Certifications]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCertifications()
    {
        return $this->hasMany(Certification::class, ['assessment_id' => 'id']);
    }

    /**
     * Gets query for [[IndicatorGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicatorGroups()
    {
        return $this->hasMany(IndicatorGroup::class, ['assessment_id' => 'id']);
    }

    /**
     * Gets query for [[IndicatorGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRootGroups()
    {
        return $this->hasMany(IndicatorGroup::class, ['assessment_id' => 'id'])
            ->where(['parent_group_id' => null])
            ->orderBy(['order' => SORT_ASC]);
    }

    /**
     * Gets query for [[IndicatorGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChildGroups()
    {
        return $this->hasMany(IndicatorGroup::class, ['assessment_id' => 'id'])
            ->alias('child')
            ->joinWith('parentGroup parent')
            ->andWhere(['not', ['child.parent_group_id' => null]])
            ->orderBy([
                'parent.order' => SORT_ASC,
                'child.order' => SORT_ASC,
            ]);
    }

    /**
     * @return IndicatorGroup
     */
    public function getCurrentRootGroupOrFail(int $page, array $root_indicator_groups)
    {
        try {
            return $root_indicator_groups[$page - 1];
        } catch (Exception $error) {
            if ($error instanceof ErrorException) {
                throw new BadRequestHttpException('Nomor halaman tidak valid');
            }
            throw $error;
        }
    }

    public function activate()
    {
        /** @var Assessment $active_assessment */
        $active_assessment = Assessment::find()
            ->where(['!=', 'id', $this->id])
            ->andWhere(['active_at_level' => $this->level])
            ->one();
        if ($active_assessment) {
            $active_assessment->active_at_level = null;
            $active_assessment->save(false);
        }

        if (!$this->released_at) { // kalau udah pernah rilis, tidak perlu diperbarui lagi
            $this->released_at = date('Y-m-d H:i:s');
        }

        $this->active_at_level = $this->level;
        return $this;
    }

    public function deactivate()
    {
        $this->active_at_level = null;
        return $this;
    }

    public static function clone(Assessment $cloned_assessment)
    {
        $new_assessment = new Assessment();
        $new_assessment->title = 'Salinan dari ' . $cloned_assessment->title;
        $new_assessment->level = $cloned_assessment->level;
        $new_assessment->save(false);

        foreach ($cloned_assessment->rootGroups as $old_root_group) {
            $old_root_group->clone($new_assessment->id);
        }

        return $new_assessment;
    }
}
