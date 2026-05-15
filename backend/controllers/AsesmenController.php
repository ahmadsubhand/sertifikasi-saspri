<?php

namespace backend\controllers;

use common\enums\UserRole;
use common\models\Assessment;
use common\models\Indicator;
use common\models\IndicatorGroup;
use common\models\IndicatorOption;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class AsesmenController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [UserRole::ADMIN],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'simpan-group' => ['post'],
                    'simpan-indikator' => ['post'],
                    'simpan-opsi' => ['post'],
                    'hapus-group' => ['post'],
                    'hapus-indikator' => ['post'],
                    'hapus-opsi' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $assessments = Assessment::find()->all();
        return $this->render('index', [
            'assessments' => $assessments,
        ]);
    }

    public function actionKelola(int $assessment_id)
    {
        try {
            $assessment = $this->findAssessmentOrFail($assessment_id);
            $root_groups = $assessment->getRootGroups()
                ->with('childGroups')
                ->all();
            return $this->render('kelola', [
                'assessment' => $assessment,
                'root_groups' => $root_groups
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof NotFoundHttpException) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionSimpanGroup(int $assessment_id, ?int $indicator_group_id = null)
    {
        $model = $indicator_group_id ? $this->findGroupOrFail($indicator_group_id) : new IndicatorGroup();

        $model->assessment_id = $assessment_id;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Group berhasil disimpan');
        } else {
            Yii::$app->session->setFlash('error', 'Gagal menyimpan group: ' . implode(', ', $model->getFirstErrors()));
        }

        return $this->redirect(['kelola', 'assessment_id' => $assessment_id]);
    }

    public function actionSimpanIndikator(int $assessment_id, ?int $indicator_id = null)
    {
        $model = $indicator_id ? $this->findIndicatorOrFail($indicator_id) : new Indicator();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Indikator berhasil disimpan');
        } else {
            Yii::$app->session->setFlash('error', 'Gagal menyimpan indikator: ' . implode(', ', $model->getFirstErrors()));
        }

        return $this->redirect(['kelola', 'assessment_id' => $assessment_id]);
    }

    public function actionSimpanOpsi(int $assessment_id, ?int $indicator_option_id = null)
    {
        $model = $indicator_option_id ? $this->findOptionOrFail($indicator_option_id) : new IndicatorOption();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Opsi berhasil disimpan');
        } else {
            Yii::$app->session->setFlash('error', 'Gagal menyimpan opsi: ' . implode(', ', $model->getFirstErrors()));
        }

        return $this->redirect(['kelola', 'assessment_id' => $assessment_id]);
    }

    public function actionHapusGroup(int $indicator_group_id)
    {
        $model = $this->findGroupOrFail($indicator_group_id);
        
        $assessment_id = $model->assessment_id;
        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Group berhasil dihapus');
        }

        return $this->redirect(['kelola', 'assessment_id' => $assessment_id]);
    }

    public function actionHapusIndikator(int $indicator_id)
    {
        $model = $this->findIndicatorOrFail($indicator_id);

        $assessment_id = $model->indicatorGroup->assessment_id;
        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Indikator berhasil dihapus');
        }

        return $this->redirect(['kelola', 'assessment_id' => $assessment_id]);
    }

    public function actionHapusOpsi(int $indicator_option_id)
    {
        $model = $this->findOptionOrFail($indicator_option_id);

        $assessment_id = $model->indicator->indicatorGroup->assessment_id;
        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Opsi berhasil dihapus');
        }

        return $this->redirect(['kelola', 'assessment_id' => $assessment_id]);
    }

    private function findAssessmentOrFail(int $assessment_id)
    {
        $assessment = Assessment::findOne($assessment_id);
        if (!$assessment) {
            throw new NotFoundHttpException('Asesmen tidak ditemukan');
        }
        return $assessment;
    }

    private function findGroupOrFail(int $indicator_group_id)
    {
        $group = IndicatorGroup::findOne($indicator_group_id);
        if (!$group) {
            throw new NotFoundHttpException('Grup tidak ditemukan');
        }
        return $group;
    }

    private function findIndicatorOrFail(int $indicator_id)
    {
        $indicator = Indicator::findOne($indicator_id);
        if (!$indicator) {
            throw new NotFoundHttpException('Indikator tidak ditemukan');
        }
        return $indicator;
    }

    private function findOptionOrFail(int $indicator_option_id)
    {
        $option = IndicatorOption::findOne($indicator_option_id);
        if (!$option) {
            throw new NotFoundHttpException('Opsi tidak ditemukan');
        }
        return $option;
    }
}