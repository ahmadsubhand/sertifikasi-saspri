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

<div class="vh-100 d-flex overflow-hidden">
    <!-- header -->
    <?php if(!Yii::$app->user->isGuest) :?>
    <?php echo $this->render("_sidebar")?>
    <?php endif ?>
    <!-- <br><br> -->
    <!-- center -->
    <main role="main" class="flex-grow-1 d-flex flex-column d-grid gap-3 overflow-auto position-relative">
        <?php echo $this->render("_header")?>
        <div class="content-wrap">
            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
            <div class="p-3 z-1050 position-absolute w-100">
                <?= Alert::widget() ?>
            </div>
            <?= $content ?>
        </div>
    </main>
</div>
<?php $this->endContent() ?>