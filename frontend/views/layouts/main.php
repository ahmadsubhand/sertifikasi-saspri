<?php

/** @var \yii\web\View $this */
/** @var string $content */

use frontend\assets\AppAsset;
use common\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;

AppAsset::register($this);
$this->beginContent('@frontend/views/layouts/base.php');
?>

<div class="vh-100 d-flex flex-column overflow-hidden">

    <div class="flex-shrink-0">
        <?= $this->render("_header") ?>
    </div>

    <div class="d-flex flex-grow-1 h-100 position-relative overflow-hidden">

        <?php if (!Yii::$app->user->isGuest) : ?>
            <div class="offcanvas-lg offcanvas-start flex-shrink-0 h-100 w-fit s-sidebar-wrapper"
                tabindex="-1"
                id="sidebarOffcanvas"
                aria-labelledby="sidebarOffcanvasLabel">

                <div class="offcanvas-body p-0 h-100 s-bg-main">

                    <?= $this->render("_sidebar") ?>
                </div>
            </div>
        <?php endif ?>

        <main role="main" class="w-100 h-100 overflow-auto p-md-4 p-2 bg-light">

            <?php if (isset($this->params['breadcrumbs'])): ?>
                <div class="mb-3">
                    <?= Breadcrumbs::widget([
                        'links' => $this->params['breadcrumbs'],
                    ]) ?>
                </div>
            <?php endif ?>

            <div class="mb-3">
                <?= Alert::widget() ?>
            </div>

            <div class="pb-5"> <?= $content ?>
            </div>

        </main>
    </div>
</div>
<?php $this->endContent() ?>