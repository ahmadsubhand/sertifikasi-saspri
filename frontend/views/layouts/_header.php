<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Html;

$items = [
   
    [
        'label' => 'Signup',
        'url' => ['/site/signup'],
        'visible' => Yii::$app->user->isGuest,
    ],
    [
        'label' => 'Login',
        'url' => ['/site/login'],
        'visible' => Yii::$app->user->isGuest,
    ],
    [
        'label' => 'Logout (' . Html::encode(Yii::$app->user->identity?->username) . ')',
        'url' => ['/site/logout'],
        'linkOptions' => [
            'data-method' => 'post',
            'class' => 'logout',
        ],
        'visible' => !Yii::$app->user->isGuest,
    ],
];

?>
<header id="">
    <?php NavBar::begin(
        [
            'brandUrl' => Yii::$app->homeUrl,
            'options' => ['class' => 'navbar-expand-md navbar border-bottom border-2']
        ],
    ) ?>
    <div class="d-flex w-100 justify-content-end">
        <?= Nav::widget(
            [
                'options' => ['class' => 'navbar-nav ms-auto'],
                'encodeLabels' => false,
                'items' => $items,
            ],
        ) ?>
    </div>
    <?php NavBar::end() ?>
</header>