<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\bootstrap5\Nav;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<header class="s-bg-main border-bottom shadow-sm">
    <nav class="container-fluid d-flex align-items-center justify-content-between py-2 px-3">

        <div class="d-flex align-items-center gap-2">
            <?php if (!Yii::$app->user->isGuest): ?>
            <a class="d-lg-none text-white text-decoration-none fs-3"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#sidebarOffcanvas"
                aria-controls="sidebarOffcanvas">
                <i class="fa-solid fa-bars"></i>
            </a>
            <?php endif ?>

            <a href="<?= Url::to(Yii::$app->homeUrl) ?>" class="d-flex gap-2 align-items-center text-decoration-none text-white">
                <?= Html::img('@web/images/matasapi.svg', [
                    'alt' => 'Matasapi Digdaya Logo',
                    'class' => 'bg-white rounded-3 p-1',
                    'style' => 'width: 45px; height: 45px; object-fit: contain;'
                ]) ?>
                <div class="d-flex flex-column lh-sm">
                    <h2 class="mb-0 fs-5 fw-bold text-uppercase tracking-wide">Sertifikasi</h2>
                    <small class="text-white-50 font-monospace" style="font-size: 10px; letter-spacing: 0.5px;">SASPRI-K</small>
                </div>
            </a>
        </div>

        <div class="d-flex align-items-center text-decoration-none">
            <?php if (Yii::$app->user->isGuest): ?>
                <div class="d-flex gap-4">
                    <a href="<?= Url::to('/site/login') ?>" class="text-white text-decoration-none">Login</a>
                    <a href="<?= Url::to('/site/signup') ?>" class="text-white text-decoration-none">Signup</a>
                </div>
            <?php else: ?>
                <div class="d-md-flex d-none gap-2 text-white align-middle h-100">
                    <p class="mb-0"><?= Html::encode(Yii::$app->user->identity?->username) ?></p>
                    <i class="fa-solid fa-circle-user fs-4 mb-0"></i>
                    <a href="<?= Url::to('/site/logout') ?>" data-method="post" class="text-white text-decoration-none logout mb-0"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Logout"><i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>
                </div>
                <div class="dropdown d-flex d-md-none gap-2 text-white align-middle h-100">
                    <a class="btn dropdown-toggle acc-btn" type="button" id="dropdownAccount" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-circle-user fs-4 mb-0 text-white"></i>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="dropdownAccount">
                        <li>
                            <p class="dropdown-item mb-0"><?= Html::encode(Yii::$app->user->identity?->username) ?></p>
                        </li>
                        <li>
                            <a href="<?= Url::to('/site/logout') ?>" data-method="post" class="dropdown-item text-decoration-none logout mb-0"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" title="Logout">Logout <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            <?php endif ?>
        </div>

    </nav>
</header>