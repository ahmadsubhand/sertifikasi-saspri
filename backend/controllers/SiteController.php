<?php

namespace backend\controllers;

use common\enums\CertificateLevel;
use common\enums\CertificationStatus;
use common\helpers\UserHelper;
use common\models\Certification;
use common\models\LoginForm;
use common\models\SaspriK;
use Exception;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
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
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
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
            $query->andWhere(['like', 'LOWER(user.username)', strtolower($wali)]);
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

    /**
     * Login action.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
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
