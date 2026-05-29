<?php

namespace backend\controllers;

use common\enums\CertificateLevel;
use common\enums\UserRole;
use common\helpers\ModelHelper;
use common\models\Assessment;
use common\models\form\ChangeLevelForm;
use common\models\form\CreateAssessmentForm;
use common\models\form\IndicatorForm;
use common\models\form\IndicatorGroupForm;
use common\models\form\IndicatorOptionForm;
use common\models\form\UpdateAssessmentTitleForm;
use common\services\AssessmentService;
use common\services\IndicatorGroupService;
use common\services\IndicatorOptionService;
use common\services\IndicatorService;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

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
                    'buat' => ['post'],
                    'ubah-judul' => ['patch'],
                    'aktifkan' => ['post'],
                    'ganti-tingkat' => ['post'],
                    'simpan-group' => ['post'],
                    'simpan-indikator' => ['post'],
                    'simpan-opsi' => ['post'],
                    'hapus-group' => ['delete'],
                    'hapus-indikator' => ['delete'],
                    'hapus-opsi' => ['delete'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $assessments = Assessment::find()->orderBy(['updated_at' => SORT_DESC])->all();
        return $this->render('index', [
            'assessments' => $assessments,
        ]);
    }

    public function actionBuat(int $assessment_id)
    {
        try {
            $data = null;
            if (!$assessment_id) {
                $data = new CreateAssessmentForm();
                ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->post());
            }
            $assessment = AssessmentService::create($assessment_id, $data);

            Yii::$app->session->setFlash('success', "\"$assessment->title\" berhasil dibuat");
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof BadRequestHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionKelola(int $assessment_id)
    {
        try {
            $assessment = AssessmentService::findOrFail($assessment_id);
            $root_groups = $assessment->getRootGroups()
                ->with('childGroups')
                ->all();
            $root_groups_only = $assessment->rootGroups;
            $child_groups_only = $assessment->childGroups;

            return $this->render('kelola', [
                'assessment' => $assessment,
                'root_groups' => $root_groups,
                'root_groups_only' => $root_groups_only,
                'child_groups_only' => $child_groups_only,
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

    public function actionUbahJudul(int $assessment_id)
    {
        try {
            $data = new UpdateAssessmentTitleForm();
            ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->post());
            $assessment = AssessmentService::updateTitle($assessment_id, $data);

            Yii::$app->session->setFlash('success', 'Berhasil mengubah judul asesmen');
            return $this->redirect(['kelola', 'assessment_id' => $assessment->id]);
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

    public function actionAktifkan(int $assessment_id)
    {
        try {
            $assessment = AssessmentService::activate($assessment_id);

            Yii::$app->session->setFlash(
                'success',
                'Asesmen "' . $assessment->title . 
                '" berhasil diaktifkan untuk tingkat ' . CertificateLevel::list()[$assessment->level]
            );
            return $this->redirect(['kelola', 'assessment_id' => $assessment->id]);
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

    public function actionGantiTingkat(int $assessment_id)
    {
        try {
            $data = new ChangeLevelForm();
            ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->post());
            $assessment = AssessmentService::changeLevel($assessment_id, $data);

            Yii::$app->session->setFlash(
                'success',
                'Level asesmen "' . $assessment->title .
                '" berhasil diubah menjadi tingkat ' .
                CertificateLevel::list()[$assessment->level] .
                ' dan asesmen telah dinonaktifkan'
            );
            return $this->redirect(['kelola', 'assessment_id' => $assessment->id]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof NotFoundHttpException) {
                    return $this->redirect(['index']);
                } elseif ($error instanceof UnprocessableEntityHttpException) {
                    return $this->redirect(['kelola', 'assessment_id' => $assessment_id]);
                }
            }
            throw $error;
        }
    }

    public function actionSimpanGrup(int $assessment_id, ?int $indicator_group_id = null)
    {
        try {
            $data = new IndicatorGroupForm();
            ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->post('IndicatorGroup'));
            $indicator_group = IndicatorGroupService::save($assessment_id, $indicator_group_id, $data);

            Yii::$app->session->setFlash('success', 'Grup ' . $indicator_group->code . ' berhasil disimpan');
            return $this->redirect(['kelola', 'assessment_id' => $assessment_id]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof BadRequestHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['kelola', 'assessment_id' => $assessment_id]);
                }
            }
            throw $error;
        }

    }

    public function actionSimpanIndikator(int $assessment_id, ?int $indicator_id = null)
    {
        try {
            $data = new IndicatorForm();
            ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->post('Indicator'));
            $indicator = IndicatorService::save($assessment_id, $indicator_id, $data);
            
            Yii::$app->session->setFlash(
                'success', 
                'Indikator ' . $indicator->code . ' dari grup ' . $indicator->indicatorGroup->code . ' berhasil disimpan'
            );
            return $this->redirect(['kelola', 'assessment_id' => $assessment_id]);

        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof BadRequestHttpException ||
                    $error instanceof NotFoundHttpException
                ) {
                    return $this->redirect(['kelola', 'assessment_id' => $assessment_id]);
                }
            }
            throw $error;
        }
    }

    public function actionSimpanOpsi(int $assessment_id, ?int $indicator_option_id = null)
    {
        try {            
            $data = new IndicatorOptionForm();
            ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->post('IndicatorOption'));
            $indicator_option = IndicatorOptionService::save($indicator_option_id, $data);
    
            Yii::$app->session->setFlash(
                'success', 
                'Opsi ' . $indicator_option->code .
                ' dari indikator ' . $indicator_option->indicator->code . 
                ' dalam grup ' . $indicator_option->indicator->indicatorGroup->code . ' berhasil disimpan'
            );
            return $this->redirect(['kelola', 'assessment_id' => $assessment_id]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof BadRequestHttpException ||
                    $error instanceof NotFoundHttpException
                ) {
                    return $this->redirect(['kelola', 'assessment_id' => $assessment_id]);
                }
            }
            throw $error;
        }
    }

    public function actionHapus(int $assessment_id)
    {
        try {
            $assessment = AssessmentService::delete($assessment_id);

            Yii::$app->session->setFlash('success', "Asesmen \"$assessment->title\" berhasil dihapus");
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof NotFoundHttpException || $error instanceof UnprocessableEntityHttpException) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionHapusGrup(int $indicator_group_id)
    {
        try {
            $indicator_group = IndicatorGroupService::delete($indicator_group_id);

            Yii::$app->session->setFlash('success', 'Grup ' . $indicator_group->code . ' beserta isinya berhasil dihapus');
            return $this->redirect(['kelola', 'assessment_id' => $indicator_group->assessment_id]);
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

    public function actionHapusIndikator(int $indicator_id)
    {
        try {
            $indicator = IndicatorService::delete($indicator_id);
            
            Yii::$app->session->setFlash(
                'success', 
                'Indikator ' . $indicator->code . ' dari grup ' . $indicator->indicatorGroup->code . ' berhasil dihapus'
            );
            return $this->redirect(['kelola', 'assessment_id' => $indicator->indicatorGroup->assessment_id]);
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

    public function actionHapusOpsi(int $indicator_option_id)
    {
        try {
            $indicator_option = IndicatorOptionService::delete($indicator_option_id);
    
            Yii::$app->session->setFlash(
                'success', 
                'Opsi ' . $indicator_option->code .
                ' dari indikator ' . $indicator_option->indicator->code . 
                ' dalam grup ' . $indicator_option->indicator->indicatorGroup->code . ' berhasil dihapus'
            );
            return $this->redirect([
                'kelola', 
                'assessment_id' => $indicator_option->indicator->indicatorGroup->assessment_id
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
}