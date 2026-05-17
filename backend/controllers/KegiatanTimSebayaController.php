<?php

namespace backend\controllers;

use common\enums\UserRole;
use frontend\controllers\TimSebayaController;

class KegiatanTimSebayaController extends TimSebayaController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'roles' => [UserRole::ADMIN],
            ],
        ];
        return $behaviors;
    }
}
