<?php

namespace backend\controllers;

use common\enums\CertificateLevel;
use common\enums\UserRole;
use common\models\Assessment;
use common\models\Indicator;
use common\models\IndicatorGroup;
use common\models\IndicatorOption;
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
            $assessment = new Assessment();
            if ($assessment_id) {
                $cloned_assessment = $this->findAssessmentOrFail($assessment_id);
                $assessment = Assessment::clone($cloned_assessment);
            } else {
                $request = Yii::$app->request->post();
                $assessment->title = $request['title'];
                $assessment->level = $request['level'];
                $assessment->save(false);
            }

            Yii::$app->session->setFlash('success', "\"$assessment->title\" berhasil dibuat");
            return $this->redirect(['index']);
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

    public function actionKelola(int $assessment_id)
    {
        try {
            $assessment = $this->findAssessmentOrFail($assessment_id);
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
            $assessment = $this->findAssessmentOrFail($assessment_id);
            $assessment->title = Yii::$app->request->post('title');
            $assessment->save(false);

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
            $assessment = $this->findAssessmentOrFail($assessment_id);
            $assessment->activate()->save(false);

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
            $assessment = $this->findAssessmentOrFail($assessment_id);

            if ($assessment->getCertifications()->exists()) {
                throw new UnprocessableEntityHttpException(
                    'Tingkat asesmen tidak dapat diubah karena asesmen sudah digunakan dalam proses sertifikasi'
                );
            }
            if ($assessment->active_at_level) {
                throw new UnprocessableEntityHttpException(
                    'Tingkat asesmen tidak dapat diubah karena asesmen sedang aktif. ' .
                    'Silakan aktifkan asesmen lain pada tingkat ' . CertificateLevel::list()[$assessment->level].
                    ' untuk menggantikan asesmen ini sehingga proses sertifikasi tetap memiliki asesmen aktif'
                );
            }

            $assessment->level = Yii::$app->request->post('level');
            $assessment->deactivate()->save(false);

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
            $IndicatorGroup = Yii::$app->request->post('IndicatorGroup');
            $model = new IndicatorGroup();
            if ($indicator_group_id) {
                $model = $this->findGroupOrFail($indicator_group_id);

                // Mengecek parent atau child langsung dari model db,
                // bukan dari model yang sudah disisipkan input user ($model->load)
                $parent_group_id = $IndicatorGroup['parent_group_id'];
                if ($model->parent_group_id) {
                    if (!$parent_group_id) {
                        throw new BadRequestHttpException(
                            ($IndicatorGroup['code'] ?? $model->code) . ' adalah subgrup sehinggga wajib memiliki grup utama'
                        );
                    } else {
                        $indicator_group = Assessment::findOne($assessment_id)
                            ->getRootGroups()
                            ->where(['id' => $parent_group_id])
                            ->exists();
                        if (!$indicator_group) {
                            throw new NotFoundHttpException(
                                'Grup utama yang dipilih tidak ditemukan atau bukan grup utama yang valid'
                            );
                        }
                    }
                } else if (!$model->parent_group_id && $parent_group_id) {
                    throw new BadRequestHttpException(
                        ($IndicatorGroup['code'] ?? $model->code) . ' adalah grup utama sehingga tidak dapat dipindahkan ke dalam grup lain'
                    );
                }
            }
    
            $model->assessment_id = $assessment_id;
            $model->load(Yii::$app->request->post());
            $this->validateWeight($IndicatorGroup['weight']);
            $this->checkGroupWeight($model);
            $model->save();
            
            Yii::$app->session->setFlash('success', 'Grup ' . $model->code . ' berhasil disimpan');
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
            $model = $indicator_id ? $this->findIndicatorOrFail($indicator_id) : new Indicator();

            $indicator_group_id = Yii::$app->request->post('Indicator')['indicator_group_id'];
            if (!$indicator_group_id) {
                throw new BadRequestHttpException('Indikator wajib memiliki subgrup');
            }
            $indicator_group = Assessment::findOne($assessment_id)
                ->getChildGroups()
                ->andWhere(['child.id' => $indicator_group_id])
                ->exists();
            if (!$indicator_group) {
                throw new NotFoundHttpException(
                    'Subgrup yang dipilih tidak ditemukan atau bukan subgrup yang valid'
                );
            }

            $model->load(Yii::$app->request->post());
            $model->save();
            
            Yii::$app->session->setFlash(
                'success', 
                'Indikator ' . $model->code . ' dari grup ' . $model->indicatorGroup->code . ' berhasil disimpan'
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
            $Indicator = Yii::$app->request->post('IndicatorOption');

            if (!$Indicator['indicator_id']) {
                throw new BadRequestHttpException('Opsi wajib memiliki subgrup');
            }
            $this->findIndicatorOrFail($Indicator['indicator_id']);
            $this->validateWeight($Indicator['weight']);
    
            $model = $indicator_option_id ? $this->findOptionOrFail($indicator_option_id) : new IndicatorOption();
            $model->load(Yii::$app->request->post());
            $model->save();
    
            Yii::$app->session->setFlash(
                'success', 
                'Opsi ' . $model->code .
                ' dari indikator ' . $model->indicator->code . 
                ' dalam grup ' . $model->indicator->indicatorGroup->code . ' berhasil disimpan'
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
            $assessment = $this->findAssessmentOrFail($assessment_id);

            if ($assessment->getCertifications()->exists()) {
                throw new UnprocessableEntityHttpException(
                    'Asesmen tidak dapat dihapus karena sudah digunakan dalam proses sertifikasi'
                );
            }

            if ($assessment->active_at_level) {
                throw new UnprocessableEntityHttpException(
                    'Asesmen tidak dapat dihapus karena sedang aktif. ' .
                    'Silakan aktifkan asesmen lain pada tingkat ' . CertificateLevel::list()[$assessment->level] .
                    ' untuk menggantikan asesmen ini sebelum menghapusnya'
                );
            }

            $title = $assessment->title;
            $assessment->delete();

            Yii::$app->session->setFlash('success', "Asesmen \"$title\" berhasil dihapus");
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
            $model = $this->findGroupOrFail($indicator_group_id);
        
            $assessment_id = $model->assessment_id;
            $model->delete();

            Yii::$app->session->setFlash('success', 'Grup ' . $model->code . ' beserta isinya berhasil dihapus');
            return $this->redirect(['kelola', 'assessment_id' => $assessment_id]);
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
            $model = $this->findIndicatorOrFail($indicator_id);
    
            $assessment_id = $model->indicatorGroup->assessment_id;
            $model->delete();
            
            Yii::$app->session->setFlash(
                'success', 
                'Indikator ' . $model->code . ' dari grup ' . $model->indicatorGroup->code . ' berhasil dihapus'
            );
            return $this->redirect(['kelola', 'assessment_id' => $assessment_id]);
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
            $model = $this->findOptionOrFail($indicator_option_id);
    
            $assessment_id = $model->indicator->indicatorGroup->assessment_id;
            $model->delete();
    
            Yii::$app->session->setFlash(
                'success', 
                'Opsi ' . $model->code .
                ' dari indikator ' . $model->indicator->code . 
                ' dalam grup ' . $model->indicator->indicatorGroup->code . ' berhasil dihapus'
            );
            return $this->redirect(['kelola', 'assessment_id' => $assessment_id]);
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

    private function checkGroupWeight(IndicatorGroup $new_indicator_group)
    {
        $remaining_weight = $new_indicator_group->countRemainingWeight();
        if ($remaining_weight < $new_indicator_group->weight) {
            $parent_group = $new_indicator_group->parentGroup;
            if ($parent_group) {
                throw new UnprocessableEntityHttpException(
                    'Total bobot dalam grup ' .
                    $parent_group->code .
                    ' tidak boleh melebihi 100. Saat ini sisa bobot yang tersedia hanya ' .
                    $remaining_weight
                );
            } else {
                throw new UnprocessableEntityHttpException(
                    'Total bobot grup utama dalam asesmen ini ' .
                    ' tidak boleh melebihi 100. Saat ini sisa bobot yang tersedia hanya ' .
                    $remaining_weight
                );
            }
        }
    }

    private function validateWeight(int|string $weight)
    {   
        if (
            filter_var(
                $weight,
                FILTER_VALIDATE_INT
            ) === false ||
            $weight <= 0 ||
            $weight >= 100
        ) {
            throw new BadRequestHttpException('Bobot harus bilangan bulat positif kurang dari 100');
        }
    }
}