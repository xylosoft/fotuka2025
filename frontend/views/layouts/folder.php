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

                    <?php if (Yii::$app->user->isGuest || !Yii::$app->user->identity->profile_picture){?>
                        <svg width="51" height="51" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="12" fill="#E5E7EB"/>
                            <circle cx="12" cy="9" r="4" fill="#9CA3AF"/>
                            <path d="M4 20c1.5-3.5 4.5-5 8-5s6.5 1.5 8 5" fill="#9CA3AF"/>
                        </svg>
                    <?php }else{?>
                        <img src="<?=Yii::$app->user->identity->profile_picture?>?v=<?=Yii::$app->user->identity->profile_update_date?>" alt="User profile" class="profile-pic">
                    <?php } ?>
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


<?php
$controllerId = Yii::$app->controller->id;
$actionId = Yii::$app->controller->action->id;

/*
 * Wide workspace pages:
 * keep full width for folder / asset style screens.
 * Everything else gets a centered content container.
 */
$isWideWorkspacePage = in_array($controllerId, ['folder', 'asset', 'site']);
?>

<div class="layout-page <?= $isWideWorkspacePage ? 'layout-page--wide' : 'layout-page--centered' ?>">
    <?= $content ?>
</div>

<div id="new-folder-dialog" title="Create Folder" style="display:none;">
    <div class="modal-body">
        <label class="modal-label" for="folder-name">Folder name</label>
        <input
                id="folder-name"
                type="text"
                class="modal-input"
                placeholder="Please enter your folder's name"
                autocomplete="off"
                maxlength="50"
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

document.addEventListener('DOMContentLoaded', function () {
    const $menu = $('.user-dropdown-menu');

    // Hide dropdown when clicking anywhere else
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.user-menu-container').length) {
            $menu.hide();
        }
    });

    $('#menu-profile').on('click', function() {
        window.location.href = '/profile';
    });

    $('#menu-settings').on('click', function() {
        alert('Open Settings');
    });

    $('#menu-logout').on('click', function() {
        window.location.href = '/logout';
    });

    // Toggle dropdown when clicking profile image
    $('.user-profile').on('click', function(e) {
        e.stopPropagation();
        $menu.toggle();
    });

});
</script>
<style>
    :root {
        --app-header-height: 70px;
        --page-side-padding: 24px;
        --page-top-gap: 18px;
        --page-max-width: 1380px;
    }

    /* Base wrapper */
    .layout-page {
        box-sizing: border-box;
        padding-left: var(--page-side-padding);
        padding-right: var(--page-side-padding);
    }

    .layout-page--centered {
        max-width: var(--page-max-width);
        margin: 0 auto;
        padding-top: calc(var(--app-header-height) + var(--page-top-gap));
        padding-bottom: 24px;
        min-height: 100vh;
    }

    .layout-page--wide {
        width: 100%;
        max-width: none;
        margin: 0;
        padding-top: 0;
        padding-bottom: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* First child inside the wide wrapper must fill all available height */
    .layout-page--wide > * {
        flex: 1 1 auto;
        min-height: 0;
        height: 100%;
    }

    /* If your folder/assets page root uses .app, make it fill the wrapper */
    .layout-page--wide .app {
        height: 100% !important;
        min-height: 0 !important;
    }

    /* Let inner columns shrink correctly inside flex/grid layouts */
    .layout-page--wide .main,
    .layout-page--wide .sidebar {
        min-height: 0;
    }

    @media (max-width: 900px) {
        :root {
            --page-side-padding: 14px;
            --page-top-gap: 12px;
        }

        .layout-page--centered,
        .layout-page--wide {
            max-width: none;
        }

        .layout-page--wide {
            height: calc(100vh - var(--app-header-height) - var(--page-top-gap));
        }
    }
</style>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>
