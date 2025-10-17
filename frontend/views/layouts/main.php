<?php
/** @var \yii\web\View $this */
/** @var string $content */

use frontend\assets\AppAsset;
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
    <div style="float:left">
        <img src="/images/logo.png" width="70%"/>
    </div>
    <div style="float:right">
        Login
    </div>
</header>
<div id="notification-banner" class="notification"></div>
<div class="app">

    <aside class="sidebar">
        <h4>
            Folders
            <!-- Minimal change: add id to the button image -->
            <img src="/icons/square-plus.svg" id="btn-new-folder" style="float:right;height:20px;"/>
        </h4>
        <div style="padding-left:10px">
            <div id="folderTree"></div>
        </div>
    </aside>

    <main class="main">
        <?= $content ?>
    </main>
</div>

<!-- Minimal add: jQuery UI dialog markup (hidden by default) -->
<div id="new-folder-dialog" title="Create Folder" style="display:none;">
    <p style="margin-bottom:8px;">Enter folder name:</p>
    <input type="text" id="folder-name" style="width:100%; padding:6px;">
    <div id="folder-error" style="color:red; margin-top:6px; display:none;"></div>
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
    // Your existing jsTree init (unchanged)
    var \$treeEl = \$('#folderTree');
    \$treeEl.jstree({
        'core' : {
            'data' : {
                'url' : '/json/folders',
                'dataType' : 'json'
            },
            check_callback: true,
            'themes': { 'variant': 'large' }
        },
        types: {
            default: { icon: 'fa fa-folder' },
            opened: { icon: 'fa fa-folder-open' }
        },
        'plugins' : ['types', 'wholerow','dnd']
    }).on('open_node.jstree', function (e, data) {
        if (Number.isInteger(data.node.id)) {
            data.instance.set_icon(data.node, 'fa fa-folder-open');
        }
    }).on('close_node.jstree', function (e, data) {
        if (Number.isInteger(data.node.id)) {
            data.instance.set_icon(data.node, 'fa fa-folder');
        }
    }).on('move_node.jstree', function(e, data) {
        // data.node.id — the node being moved
        // data.parent — the new parent ID
        // data.position — position among siblings
        // data.old_parent — the old parent

        // Optionally convert parent to null if 'root'
        var newParent = data.parent;
        if (!/^\d+$/.test(newParent)) {
            newParent = null;
        }

        // Send to backend to update
        $.ajax({
            url: '/folder/move',  // you need an action for this
            type: 'POST',
            dataType: 'json',
            data: {
                id: data.node.id,
                parent_id: newParent,
                position: data.position,
                _csrf: yii.getCsrfToken()
            },
            success: function(res) {
                if (!res.ok) {
                    alert('Failed to move folder: ' + (res.error || 'unknown'));
                    // Optionally, rollback move in jsTree
                    data.instance.refresh();  // or restore old state
                }
                data.instance.refresh();
            },
            error: function() {
                alert('Server error while moving folder');
                data.instance.refresh();
            }
        });
    });

    // Keep your existing click handler (unchanged)
    \$treeEl.on('select_node.jstree', function(e, data) {
        // selected node logic (if any)
    });

    // Minimal add: init jQuery UI dialog
    \$('#new-folder-dialog').dialog({
        autoOpen: false,
        modal: true,
        width: 380,
        buttons: {
            "Create": function() {
                var name = \$('#folder-name').val().trim();
                if (!name) {
                    \$('#folder-error').text('Please enter a folder name.').show();
                    return;
                }

                var tree = $('#folderTree').jstree(true);
                var selectedNode = tree.get_selected(true)[0]; // returns the full node object
                var parentId = selectedNode ? selectedNode.id : null;
                var csrf = (typeof yii !== 'undefined' && yii.getCsrfToken) ? yii.getCsrfToken() : \$('meta[name="csrf-token"]').attr('content');

                \$.ajax({
                    url: '/folder/add',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        name: name,
                        parent_id: (!parentId || parentId === '#' || parentId.startsWith('j1_')) ? null : parentId,
                        _csrf: csrf
                    },
                    success: function(res) {
                        if (res && res.ok) {
                            // Insert node client-side under selected parent
                            var jsParent = parentId && parentId !== '' ? parentId : '#';
                            jsParent = jsParent ? String(jsParent) : '#';
                            var folderId = res.node.id;
                            
                            if (!Number.isInteger(parseInt(jsParent))) {
                                jsParent = '#';
                            }
                            
                            var tree = $('#folderTree').jstree(true);
                            tree.refresh();
                            
                            $('#folderTree').on('refresh.jstree', function() {
                                var t = $(this).jstree(true);
                                // Wait a short moment for async loading to complete
                                setTimeout(function() {
                                    if (jsParent === '#') {
                                        var roots = t.get_node('#').children;
                                        if (roots.length) {
                                            t.open_node(roots[0]);
                                            tree.deselect_all();
                                            t.select_node(folderId);
                                        }
                                    }else{
                                        t.open_node(jsParent);
                                        tree.deselect_all();
                                        t.select_node(folderId);
                                    }
                                }, 200); // small delay ensures data is ready
                            });
                            showBanner('Folder created successfully!', 'success');
                        } else {
                            alert('Error creating folder' + (res && res.errors ? ': ' + JSON.stringify(res.errors) : ''));
                        }
                    },
                    error: function() {
                        alert('Error creating folder');
                    }
                });

                \$(this).dialog('close');
            },
            "Cancel": function() { \$(this).dialog('close'); }
        },
        open: function() {
            \$('#folder-name').val('').focus();
            \$('#folder-error').hide();
            
            $('#folder-name').off('keypress').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    $(".ui-dialog-buttonpane button:contains('Create')").trigger('click');
                }
            });            
        }
    });

    // Minimal add: open dialog on plus icon click
    \$('#btn-new-folder').on('click', function() {
        \$('#new-folder-dialog').dialog('open');
    });
});

function showBanner(message, type = 'error') {
    var \$banner = $('#notification-banner');
    var bgColor = '#F4B6B6'; // default red

    if (type === 'success') bgColor = '#AEE8B2'; // green

    \$banner.stop(true, true)
        .css({
            'background-color': bgColor,
            'display': 'none'
        })
        .text(message)
        .slideDown(200)
        .delay(1500) // visible for 4 seconds
        .fadeOut(600);
}
JS;
$this->registerJs($js);
?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>
