<?php
/** @var \yii\web\View $this */
/** @var string $content */

use common\widgets\Alert;
use frontend\assets\AppAsset;
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
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

    <?
    //$this->registerCssFile('@web/css/lucide.css');
    $this->registerCssFile('@web/css/awesome.min.css');
    $this->registerCssFile('@web/jstree/themes/default/style.min.css');
    $this->registerJsFile('@web/jstree/jstree.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
    ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header>
    <div style="float:left">
        <img src="/images/logo.png" width="70%"/>
    </div>
    <div style="float:right">
        Login
    </div>
</header>
<div class="app">

    <aside class="sidebar">
        <h4>
            Folders
            <img src="/icons/square-plus.svg" style="float:right;height:24px;"/>
        </h4>
        <div style="padding-left:10px">
            <div id="folderTree"></div>
        </div>
    </aside>

    <main class="main">
        <?= $content ?>
    </main>
</div>




<footer class="footer mt-auto py-3 text-muted">
    <div class="container">
        <p class="float-start">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>
        <p class="float-end"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php
$js = <<<JS
$(function() {
    $('#folderTree').jstree({
        'core' : {
            'data' : {
                'url' : '/json/folders',   // <-- your controller action path
                'dataType' : 'json'
            },
            'themes': {
                'variant': 'large'
            }
        },
        types: {
            default: { icon: 'fa fa-folder' },
            opened: { icon: 'fa fa-folder-open' }
        },        
        'plugins' : ['types', 'wholerow']
    }).on('open_node.jstree', function (e, data) {
        data.instance.set_icon(data.node, 'fa fa-folder-open');
    }).on('close_node.jstree', function (e, data) {
        data.instance.set_icon(data.node, 'fa fa-folder');
    });
    ;
    
    // Click Action
    $('#folderTree').on('select_node.jstree', function(e, data) {
        //alert('Selected: ' + data.node.text);
        //alert('Selected: ' + data.node.id);
    });
});
JS;
$this->registerJs($js);
?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage();
