<?php

/** @var \yii\web\View $this */
/** @var string $content */

use frontend\assets\AppAsset;
use common\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;

AppAsset::register($this);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet">
    <?php $this->registerCsrfMetaTags() ?>
    <?php 
        $this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css');
        $this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');
        $this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js', [
            'depends' => [\yii\web\JqueryAsset::className()]
        ]);
        $this->registerJsFile('https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js', [
            'depends' => [\yii\web\JqueryAsset::className()]
        ]);
    ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<div class="h-100 d-flex flex-column">
    <?php echo $content ?>
</div>

<script>
    window.isUserLoggedIn = <?= Yii::$app->user->isGuest ? 'false' : 'true' ?>;
</script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage();
