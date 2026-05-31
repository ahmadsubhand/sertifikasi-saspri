<?php

namespace backend\controllers;

use yii\web\Controller;
use common\enums\UserRole;
use common\models\AuditLog;
use yii\filters\AccessControl;

class AuditLogController extends Controller
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
        ];
    }

    public function actionIndex(
        ?string $table_name = null,
        ?string $action = null,
        ?string $user_id = null,
        ?string $sort = 'desc',
        ?int $limit = 20,
        ?int $offset = 0,
    ) {
        $query = AuditLog::find()
            ->andFilterWhere(['table_name' => $table_name])
            ->andFilterWhere(['action' => $action])
            ->andFilterWhere(['user_id' => $user_id]);

        $sortDirection = $sort === 'asc' ? SORT_ASC : SORT_DESC;
        $query->orderBy(['created_at' => $sortDirection]);

        $query->limit($limit + 1)->offset($offset);
        $logs = $query->all();

        $hasNextPage = false;
        if (count($logs) > $limit) {
            $hasNextPage = true;
            array_pop($logs);
        }

        return $this->render('index', [
            'logs' => $logs,
            'filters' => [
                'table_name' => $table_name,
                'action' => $action,
                'user_id' => $user_id,
                'sort' => $sort,
            ],
            'pagination' => [
                'hasPrev' => $offset > 0,
                'hasNext' => $hasNextPage,
                'prevOffset' => max(0, $offset - $limit),
                'nextOffset' => $offset + $limit,
                'currentOffset' => $offset,
            ]
        ]);
    }
}
