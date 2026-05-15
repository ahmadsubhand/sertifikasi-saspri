<?php

namespace common\models;

use ErrorException;
use Exception;
use yii\db\ActiveQuery;
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
            ->where(['assessment_id' => $this->id])
            ->orderBy(['order' => SORT_ASC]);
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

    /**
     * @return IndicatorGroup[]
     */
    public function getCurrentChildGroups(IndicatorGroup $root_indicator_group, int $certification_id)
    {
        $indicator_groups = $root_indicator_group->getIndicatorGroups()
            ->orderBy(['order' => SORT_ASC])
            ->with([
                'indicators' => function (ActiveQuery $query) use ($certification_id) {
                    $query->orderBy(['order' => SORT_ASC])
                        ->with([
                            'indicatorOptions',
                            'indicatorScores' => function (ActiveQuery $query) use ($certification_id) {
                                $query->where(['certification_id' => $certification_id]);
                            },
                        ]);
                },
            ])
            ->all();

        // Kalau pakai ini malah menyusahkan type di frontend, jadi lepas saja tidak snake case
        // foreach ($indicator_groups as &$group) {
        //     foreach ($group['indicators'] as &$indicator) {
        //         $indicator['indicator_options'] = $indicator['indicatorOptions'] ?? [];
        //         unset($indicator['indicatorOptions']);

        //         $indicator['indicator_scores'] = $indicator['indicatorScores'][0] ?? [];
        //         unset($indicator['indicatorScores']);
        //     }
        // }

        return $indicator_groups;
    }
}
