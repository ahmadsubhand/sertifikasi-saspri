<?php

namespace frontend\controllers;

use common\enums\CertificateLevel;
use common\enums\CertificationStatus;
use common\enums\UserRole;
use common\helpers\UserHelper;
use common\models\Certification;
use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use common\models\SaspriK;
use Exception;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\db\ActiveQuery;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

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
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
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
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
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
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {

            if(yii::$app->user->can(UserRole::COORDINATOR)){
                return $this->redirect('/saspri-k');
            } else if(yii::$app->user->can(UserRole::USER)){
                return $this->redirect('/tim-mandiri');
            } else{
                return $this->goBack();
            }
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            }

            Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($model->verifyEmail()) {
            Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }
}
