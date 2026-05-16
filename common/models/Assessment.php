<?php

namespace common\models;

use ErrorException;
use Exception;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "assessments".
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
        return 'assessments';
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
            ->innerJoin(
                ['parent' => IndicatorGroup::tableName()],
                'parent.id = child.parent_group_id'
            )
            ->where(['not', ['child.parent_group_id' => null]])
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
}
