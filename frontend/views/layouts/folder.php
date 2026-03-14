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
    <style>
        :root {
            --app-header-height: 70px;
            --page-side-padding: 24px;
            --page-top-gap: 0px;
            --page-max-width: 1380px;
        }

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

        .layout-page--wide > * {
            flex: 1 1 auto;
            min-height: 0;
            height: auto;
        }

        .layout-page--wide .app {
            height: 100% !important;
            min-height: 0 !important;
        }

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
    <?php $this->head() ?>

    <?php
    $this->registerCssFile('@web/css/awesome.min.css');
    $this->registerCssFile('@web/jstree/themes/default/style.min.css');
    $this->registerJsFile('@web/jstree/jstree.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
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
            <?php if (Yii::$app->user->isGuest): ?>
                <a href="/login">Login</a>
            <?php else: ?>
                <a href="/logout">Logout (<?= Yii::$app->user->identity->username ?>)</a>
            <?php endif; ?>
        </div>
        <div style="float:right;padding-right:10px">
            <a href="/settings"><i class="fa fa-cog"></i></a>
        </div>
        <div style="float:right;">
            <div class="user-menu-container">
                <div class="user-profile" role="button" tabindex="0" aria-label="Open profile menu">
                    <?php if (Yii::$app->user->isGuest || !Yii::$app->user->identity->profile_picture): ?>
                        <svg width="51" height="51" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="12" fill="#E5E7EB"/>
                            <circle cx="12" cy="9" r="4" fill="#9CA3AF"/>
                            <path d="M4 20c1.5-3.5 4.5-5 8-5s6.5 1.5 8 5" fill="#9CA3AF"/>
                        </svg>
                    <?php else: ?>
                        <img src="<?= Yii::$app->user->identity->profile_picture ?>?v=<?= Yii::$app->user->identity->profile_update_date ?>" alt="User profile" class="profile-pic">
                    <?php endif; ?>
                </div>

                <div class="user-dropdown-menu" style="display:none;">
                    <div class="menu-item" id="menu-profile">
                        <span class="menu-icon">👤</span> Profile
                    </div>
                    <div class="menu-item" id="menu-settings">
                        <span class="menu-icon">⚙️</span> Settings
                    </div>
                    <div class="menu-separator"></div>
                    <div class="menu-item" id="menu-templates">
                        <span class="menu-icon">&#128196;</span> Website Templates
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
<div id="publishToastStack" class="tpl-toast-stack" aria-live="polite" aria-atomic="true"></div>
<?php $pageFlashes = Yii::$app->session->getAllFlashes(); ?>
    <script>
        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (m) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[m];
            });
        }

        let toastHideTimer = null;
        const publishToastStack = document.getElementById('publishToastStack');


        function showPageToast(message, type = 'success', duration = 4000) {
            if (!publishToastStack) return;

            clearTimeout(toastHideTimer);

            publishToastStack.innerHTML = `
                <div class="tpl-toast tpl-toast--${escapeHtml(type)}" role="status">
                    ${escapeHtml(message)}
                </div>`;

            const toast = publishToastStack.querySelector('.tpl-toast');
            if (!toast) return;

            requestAnimationFrame(function () {
                toast.classList.add('is-visible');
            });

            toastHideTimer = setTimeout(function () {
                toast.classList.remove('is-visible');

                setTimeout(function () {
                    if (publishToastStack.contains(toast)) {
                        publishToastStack.innerHTML = '';
                    }
                }, 180);
            }, duration);
        }

        const flashes = <?= \yii\helpers\Json::htmlEncode($pageFlashes) ?>;
        let delay = 0;

        Object.entries(flashes).forEach(function ([type, value]) {
            const messages = Array.isArray(value) ? value : [value];

            messages.forEach(function (message) {
                setTimeout(function () {
                    showPageToast(message, type, 4000);
                }, delay);
                delay += 250;
            });
        });
    </script>

<?php
$controllerId = Yii::$app->controller->id;
$isWideWorkspacePage = in_array($controllerId, ['folder', 'asset', 'site']);
?>

<div class="layout-page <?= $isWideWorkspacePage ? 'layout-page--wide' : 'layout-page--centered' ?>">
    <div id="notification-banner" class="notification"></div>
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
    window.closeAllFotukaMenus = function(options) {
        options = options || {};

        if (!options.keepAsset) {
            $('#assetContextMenu').hide();
        }
        if (!options.keepFolder) {
            $('.folder-actions').removeClass('active');
        }
        if (!options.keepProfile) {
            $('.user-dropdown-menu').hide();
        }
        if (!options.keepTree) {
            $('.vakata-context').hide();
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        $('.user-profile').off('click.fotukaProfile keydown.fotukaProfile');
        $('.user-profile').on('click.fotukaProfile', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $menu = $(this).closest('.user-menu-container').find('.user-dropdown-menu');
            const shouldOpen = !$menu.is(':visible');

            window.closeAllFotukaMenus({ keepProfile: true });
            $('.user-dropdown-menu').hide();

            if (shouldOpen) {
                $menu.show();
            }
        });

        $('.user-profile').on('keydown.fotukaProfile', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
        });

        $('.user-dropdown-menu').on('click', function(e) {
            e.stopPropagation();
        });

        $('#menu-profile').on('click', function() {
            window.location.href = '/profile';
        });

        $('#menu-settings').on('click', function() {
            window.location.href = '/settings';
        });

        $('#menu-templates').on('click', function() {
            window.location.href = '/templates';
        });

        $('#menu-logout').on('click', function() {
            window.location.href = '/logout';
        });

        $(document).off('click.fotukaMenus contextmenu.fotukaMenus')
            .on('click.fotukaMenus contextmenu.fotukaMenus', function(e) {
                const $target = $(e.target);
                if ($target.closest('.user-menu-container, .folder-actions, #assetContextMenu, .vakata-context, .asset-card, .jstree-anchor, .jstree-wholerow').length) {
                    return;
                }

                window.closeAllFotukaMenus();
            });

        $(window).off('scroll.fotukaMenus resize.fotukaMenus').on('scroll.fotukaMenus resize.fotukaMenus', function() {
            window.closeAllFotukaMenus();
        });
    });
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>
