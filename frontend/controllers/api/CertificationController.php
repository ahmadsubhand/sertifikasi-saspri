<?php

namespace frontend\controllers\api;

use common\helpers\UserHelper;
use common\models\Certification;
use yii\db\ActiveQuery;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;

class CertificationController extends ActiveController
{
    public $modelClass = Certification::class;

    public function actions()
    {
        return [];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'detail' => ['GET'],
                'saspri-k' => ['GET'],
                'self-team' => ['GET'],
                'peer-team' => ['GET'],
            ]
        ];

        return $behaviors;
    }

    public function actionDetail(int $certification_id)
    {
        $certification = $this->findCertificationOrFail($certification_id);
        if (!$certification) {
            throw new NotFoundHttpException('Certification not found');
        }
        return $certification;
    }

    public function actionSaspriK(int $certification_id)
    {
        $certification = $this->findCertificationOrFail($certification_id);
        if (!$certification) {
            throw new NotFoundHttpException('Certification not found');
        }
        return $certification->saspriK;
    }

    public function actionSelfTeam(int $certification_id, ?int $limit = 5, ?int $offset = 0)
    {
        $certification = $this->findCertificationOrFail($certification_id);
        if (!$certification) {
            throw new NotFoundHttpException('Certification not found');
        }
        $members = $certification->getSelfTeamMembers()
            ->with(['user' => function (ActiveQuery $query) {
                $query->select(UserHelper::$basicSelect);
            }])
            ->orderBy(['role' => SORT_ASC])
            ->limit($limit)
            ->offset($offset)
            ->asArray()
            ->all();
        return $members;
    }

    public function actionPeerTeam(int $certification_id, ?int $limit = 5, ?int $offset = 0)
    {
        $certification = $this->findCertificationOrFail($certification_id);
        $members = $certification->getPeerTeamMembers()
            ->with(['user' => function (ActiveQuery $query) {
                $query->select(UserHelper::$basicSelect);
            }])
            ->orderBy(['role' => SORT_ASC])
            ->limit($limit)
            ->offset($offset)
            ->asArray()
            ->all();
        return $members;
    }

    protected function findCertificationOrFail(int $id)
    {
        $certification = Certification::findOne($id);
        if (!$certification) {
            throw new NotFoundHttpException('Certification not found');
        }
        return $certification;
    }
}
