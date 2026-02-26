<?php
/** @var \yii\web\View $this */
/** @var string $content */
use frontend\assets\FolderAsset;
use yii\bootstrap5\Html;

FolderAsset::register($this);
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

    <?php
    // Minimal: keep your existing assets
    $this->registerCssFile('@web/css/awesome.min.css');
    $this->registerCssFile('@web/jstree/themes/default/style.min.css');
    $this->registerJsFile('@web/jstree/jstree.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

    // Minimal add: jQuery UI for the dialog
    $this->registerCssFile('https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
    $this->registerJsFile('https://code.jquery.com/ui/1.13.2/jquery-ui.min.js', [
        'depends' => [\yii\web\JqueryAsset::class],
    ]);
    ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header>
    <div>
        <img src="/images/logo.png" width="70%"/>
    </div>
    <div>
        <div style="float:left">
            <?php
            if (Yii::$app->user->isGuest) {
                ?>
                <a href="/login">Login</a>
                <?php
            } else {
                ?>
                <a href="/logout">Logout (<?=Yii::$app->user->identity->username?>)</a>
                <?php
            }
            ?>
        </div>
        <div style="float:right;padding-right: 10px">
            <a href="/settings"><i class="fa fa-cog"></i></a>
        </div>
        <div class="user-profile" style="float:right;">
            <div class="user-menu-container">
                <div class="user-profile">
                    <img src="/images/profile_icon.jpg" alt="User profile" class="profile-pic">
                </div>
                <div class="user-dropdown-menu">
                    <div class="menu-item" id="menu-profile">
                        <span class="menu-icon">👤</span> Profile
                    </div>
                    <div class="menu-item" id="menu-settings">
                        <span class="menu-icon">⚙️</span> Settings
                    </div>
                    <div class="menu-separator"></div>
                    <div class="menu-item" id="menu-logout">
                        <span class="menu-icon">🚪</span> Logout
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>


<?= $content ?>

<div id="new-folder-dialog" title="Create Folder" style="display:none;">
    <div class="modal-body">
        <label class="modal-label" for="folder-name">Folder name</label>
        <input
                id="folder-name"
                type="text"
                class="modal-input"
                placeholder="Please enter your folder's name"
                autocomplete="off"
        />
        <div id="folder-error" class="modal-error" style="display:none;"></div>
    </div>
</div>
<script>

function showBanner(message, type = 'error') {
    var $banner = $('#notification-banner');
    var bgColor = '#F4B6B6'; // default red

    if (type === 'success') bgColor = '#AEE8B2'; // green

    $banner.stop(true, true)
        .css({
            'background-color': bgColor,
            'display': 'none'
        })
        .text(message)
        .slideDown(200)
        .delay(3000) // visible for 4 seconds
        .fadeOut(600);
}

</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>
