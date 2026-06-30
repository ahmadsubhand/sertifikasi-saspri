<?php

namespace backend\controllers;

use common\enums\CertificateLevel;
use common\enums\CertificationStatus;
use common\enums\UserRole;
use common\helpers\ModelHelper;
use common\helpers\UserHelper;
use common\models\Certification;
use common\models\form\LoginForm;
use common\models\SaspriK;
use common\models\User;
use common\services\UserService;
use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'saspri-k', 'detail'],
                        'allow' => true,
                        'roles' => [UserRole::ADMIN],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(
        ?string $wilayah = null,
        ?string $wali = null,
        ?string $level = null,
        ?int $limit = 20,
        ?int $offset = 0
    ) {
        $query = SaspriK::find()->andWhere(['request_status' => 'approved'])->joinWith(['coordinator', 'validCertificate']);
        $saspri_counter = (clone $query);
        $certs = Certification::find()->distinct()->andWhere([
            'not in',
            'status',
            [
                CertificationStatus::PENDING_SELF_TEAM_FORMATION, // mau mulai dari sini atau sebelumnya lagi?
                CertificationStatus::COMPLETED,
            ]
        ])->count();

        if ($wilayah) {
            $query->andWhere(['like', 'LOWER(region_name)', strtolower($wilayah)]);
        }
        if ($wali) {
            $query->andWhere([
                'or',
                ['like', 'LOWER(user.username)', strtolower($wali)],
                ['like', 'LOWER(user.full_name)', strtolower($wali)]
                ]);
        }
        if (in_array($level, CertificateLevel::values())) {
            $query->andWhere(['certification.level' => $level]);
        }

        $saspri = (clone $query)
            ->orderBy([
                'updated_at' => SORT_DESC
            ])
            ->limit($limit + 1)
            ->offset($offset)
            ->all();

        $has_next = count($saspri) > $limit;
        if ($has_next) array_pop($saspri);

        return $this->render('index', [
            'saspris' => $saspri,
            'active_saspri' => $saspri_counter->count(),
            'weania_plus' => $saspri_counter->andWhere(['not in', 'certification.level', [CertificateLevel::WEANIA, CertificateLevel::NATALIA]])->count(),
            'active_certifications_count' => $certs,
            'prev_link' => $offset > 0 ? Url::current(['offset' => max(0, $offset - $limit)]) : null,
            'next_link' => $has_next ? Url::current(['offset' => $offset + $limit]) : null,
            'offset' => $offset,
        ]);
    }

    public function actionSaspriK(
        int $saspri_id,
        ?int $user_limit = 10,
        ?int $user_offset = 0,
        ?int $certification_limit = 10,
        ?int $certification_offset = 0
    ) {
        try {
            $saspri_k = SaspriK::find()->andWhere(["id" => $saspri_id])->one();

            $certs = $saspri_k->getCertifications()
                ->where(['status' => CertificationStatus::COMPLETED])
                ->orderBy(['updated_at' => SORT_DESC])
                ->limit($certification_limit + 1)
                ->offset($certification_offset)
                ->all();
            $cert_has_next = count($certs) > $certification_limit;
            if ($cert_has_next) array_pop($certs);

            $users = $saspri_k->getUsers()
                ->where(['!=', 'id', Yii::$app->user->id])
                ->orderBy(['updated_at' => SORT_DESC])
                ->select(UserHelper::$basicSelect)
                ->limit($user_limit + 1)
                ->offset($user_offset)
                ->all();
            $user_has_next = count($users) > $user_limit;
            if ($user_has_next) array_pop($users);

            return $this->render('saspri-k', [
                'saspri_k' => $saspri_k,
                'valid_certificate' => $saspri_k->validCertificate,
                'completed_certifications' => $certs,
                'cert_prev_link' => $certification_offset > 0 ? Url::current(['certification_offset' => max(0, $certification_offset - $certification_limit)]) : null,
                'cert_next_link' => $cert_has_next ? Url::current(['certification_offset' => $certification_offset + $certification_limit]) : null,
                'certification_offset' => $certification_offset,
                'saspri_k_members' => $users,
                'user_prev_link' => $user_offset > 0 ? Url::current(['user_offset' => max(0, $user_offset - $user_limit)]) : null,
                'user_next_link' => $user_has_next ? Url::current(['user_offset' => $user_offset + $user_limit]) : null,
                'user_offset' => $user_offset,
            ]);
        } catch (Exception $error) {
            if ($error instanceof ForbiddenHttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                return $this->goHome();
            }
            throw $error;
        }
    }

    public function actionDetail(int $case_id)
    {
        try {
            $cert = Certification::findOne(['id' => $case_id]);
            if (!$cert) {
                throw new NotFoundHttpException('Sertifikasi tidak ditemukan');
            }
            $saspri_k = $cert->saspriK;
            $selfTeam = $cert->getSelfTeamMembers()
                ->with([
                    'user' => function (ActiveQuery $query) {
                        $query->select(UserHelper::$basicSelect);
                    },
                ])
                ->all();
            $peerTeam = $cert->getPeerTeamMembers()
                ->with([
                    'user' => function (ActiveQuery $query) {
                        $query->select(UserHelper::$basicSelect);
                    },
                ])
                ->all();
            return $this->render('detail', [
                'id' => $case_id,
                'saspri' => $saspri_k,
                'cert' => $cert,
                'selfTeam' => $selfTeam,
                'peerTeam' => $peerTeam,
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof ForbiddenHttpException) {
                    return $this->goHome();
                } elseif ($error instanceof NotFoundHttpException) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    /**
     * Login action.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        $this->layout = 'blank';
        $data = new LoginForm();
        try {
            if (!Yii::$app->user->isGuest) {
                return $this->goHome();
            }

            ModelHelper::loadAndValidateOrFail($data, Yii::$app->request->post(), 'LoginForm');

            $user = User::findByUsername($data->username);
            if ($user === null) {
                throw new NotFoundHttpException('Username atau password salah');
            } 
            if ($user->role->item_name !== UserRole::ADMIN) {
                throw new ForbiddenHttpException('Hanya boleh diakses oleh Admin SASPRI-N');
            } 

            UserService::login($data);

            return $this->goBack();
        } catch (Exception $error) {
            if (
                $error instanceof NotFoundHttpException || 
                $error instanceof ForbiddenHttpException
            ) {
                $data->addErrors([
                    'username' => $error->getMessage(),
                    'password' => $error->getMessage(),
                ]);
            }
            $data->password = '';
            return $this->render('login', [
                'model' => $data,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
