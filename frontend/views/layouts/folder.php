<?php
/** @var \yii\web\View $this */
/** @var string $content */
use frontend\assets\FolderAsset;
use yii\bootstrap5\Html;

FolderAsset::register($this);

$id = $this->params['id'];
$selectedId = $id ?? '#';
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
<div id="notification-banner" class="notification"></div>
<div class="app">
    <aside class="sidebar">
        <div class="folder-search">
            <i class="fa fa-search"></i>
            <input type="text" id="folderSearch" placeholder="Folder Search">
        </div>
        <h4>
            Folders
            <img src="/icons/square-plus.svg" id="btn-new-folder" style="float:right;height:20px;"/>
        </h4>
        <div class="folder-tree-container">
            <div id="folderTree"></div>
        </div>
    </aside>

    <main class="main">
        <?= $content ?>
    </main>
</div>

<div id="new-folder-dialog" title="Create Folder" style="display:none;">
    <p style="margin-bottom:8px;">Enter folder name:</p>
    <input type="text" id="folder-name" style="width:100%; padding:6px;">
    <div id="folder-error" style="color:red; margin-top:6px; display:none;"></div>
</div>
<script>
const selectedFolderId = '<?=$selectedId?>';
const folderSearchState = {
    lastQuery: '',
    matches: [],
    index: -1
};

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

function jstreeCollectMatches(tree, query) {
    const q = String(query).trim().toLowerCase();
    if (!q) return [];
    const nodes = tree.get_json('#', { flat: true });
    return nodes.filter(n => (n.text || '').toLowerCase().includes(q)).map(n => n.id);
}

// Helper: open all ancestors (handles lazy loads) then run callback
function jstreeOpenAncestors(tree, nodeId, done) {
    const parent = tree.get_parent(nodeId);
    if (!parent || parent === '#') return done && done();
    jstreeOpenAncestors(tree, parent, function () {
        tree.open_node(parent, function () {
            done && done();
        });
    });
}

function selectHome(){
    var tree = $('#folderTree').jstree(true);
    var roots = tree.get_node('#').children;
    if (roots.length) {
        tree.open_node(roots[0]);
        tree.deselect_all();
        tree.select_node(roots[0]);
    }
}


document.addEventListener('DOMContentLoaded', function () {
    var $treeEl = $('#folderTree');
    const $menu = $('.user-dropdown-menu');
    const $container = $('.user-menu-container');

    $('#folderSearch').on('click', function() {
        $(this).val('');
        folderSearchState.lastQuery = '';
        folderSearchState.matches = [];
        folderSearchState.index = -1;
    });

    // Toggle dropdown when clicking profile image
    $('.user-profile').on('click', function(e) {
        e.stopPropagation();
        $menu.toggle();
    });

    // Hide dropdown when clicking anywhere else
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.user-menu-container').length) {
        $menu.hide();
        }
    });

    // Hide dropdown when mouse leaves the menu area
    $container.on('mouseleave', function() {
        $menu.hide();
    });

    $('#menu-profile').on('click', function() {
        alert('Go to Profile');
    });

    $('#menu-settings').on('click', function() {
        alert('Open Settings');
    });

    $('#menu-logout').on('click', function() {
        alert('Log out');
    });
    
    $treeEl.jstree({
        'core' : {
            'multiple': false,
            'data' : {
                'url' : '/json/folders/<?=$id?>',
                'dataType' : 'json'
            },
            check_callback: true,
            'themes': { 'variant': 'large' }
        },
        types: {
            default: { icon: 'fa fa-folder' },
            opened: { icon: 'fa fa-folder-open' }
        },
        'plugins' : ['types', 'wholerow','dnd', 'contextmenu'],
        contextmenu: {
            items: function(node) {
                var tree = $('#folderTree').jstree(true);
                var isNumericId = !isNaN(parseInt(node.id)) && isFinite(node.id);
                var menu = {};

                if (isNumericId) {
                    menu.renameItem = {
                        label: '<span style="font-size:16px;padding-right:10px;">✏️</span> Rename',
                        action: function() { tree.edit(node); } // opens inline rename input
                    };
                    menu.moveItem = {
                        label: '<span style="font-size:16px;padding-right:10px;">↷️</span> Move',
                        action: function() { javascript:void(0); }
                    };
                    menu.deleteItem = {
                        label: '<span style="font-size:16px;padding-right:10px;">🗑️</span> Delete',
                        action: function() {
                            if (confirm('Are you sure you want to delete this folder?')) {
                                $.ajax({
                                    url: '/folder/delete',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        id: node.id,
                                        _csrf: yii.getCsrfToken()
                                    },
                                    success: function(res) {
                                        if (res && res.ok) {
                                            var parentId = node.parent;
                                            if (parentId && parentId !== '#') {
                                                tree.deselect_all();
                                                tree.select_node(parentId);
                                                loadFolder(parentId);
                                            } else {
                                                selectHome();
                                            }
                                            tree.delete_node(node);
                                            showBanner('Folder deleted successfully', 'success');
                                        } else {
                                            showBanner(res.message || 'Failed to delete folder', 'error');
                                        }
                                    },
                                    error: function() {
                                        showBanner('Error deleting folder', 'error');
                                    }
                                });
                            }
                        }
                    };
                };
                menu.collapseAll = {
                    label: '<span style="font-size:16px;padding-right:10px;">📂️</span> Collapse All',
                    separator_before: false,
                    action: function() {
                        var selectedNode = tree.get_selected(true)[0];
                        tree.close_all(selectedNode);
                        tree.open_node(selectedNode);
                        tree.deselect_all();
                        tree.select_node(selectedNode);
                    }
                }
                return menu;
            }
        },
    }).on('open_node.jstree', function (e, data) {
        if (Number.isInteger(data.node.id)) {
            data.instance.set_icon(data.node, 'fa fa-folder-open');
        }
    }).on('close_node.jstree', function (e, data) {
        if (Number.isInteger(data.node.id)) {
            data.instance.set_icon(data.node, 'fa fa-folder');
        }
    }).on('move_node.jstree', function(e, data) {
        var newParent = data.parent;
        if (!/^\d+$/.test(newParent)) {
            newParent = null;
        }

        $.ajax({
            url: '/folder/move',
            type: 'POST',
            dataType: 'json',
            data: {
                id: data.node.id,
                parent_id: newParent,
                position: data.position,
                _csrf: yii.getCsrfToken()
            },
            success: function(res) {
                const tree = data.instance;

                if (!res || !res.ok) {
                    const msg = res && res.message
                        ? res.message
                        : 'Failed to move folder due to an unknown error.';
                    showBanner(msg, 'error');
                    tree.refresh();
                    return;
                }
                showBanner('Folder moved successfully!', 'success');
                tree.refresh();
            },
            error: function(xhr, status, errorThrown) {
                const msg =
                    (xhr.responseJSON && xhr.responseJSON.message) ||
                    xhr.responseText ||
                    errorThrown ||
                    'Server error while moving folder.';
                showBanner(msg, 'error');
                data.instance.refresh();
            }
        });
    }).on('select_node.jstree', function (e, data) {
        // 0 = left click, 2 = right click
        if (data.event && data.event.button === 0) {
            var folderId = data.node.id;
            loadFolder(folderId);
        }
    }).one('ready.jstree', function (e, data) {
        const tree = data.instance;
        tree.deselect_all();
        const selected = selectedFolderId || 'home';

        // Try immediate selection; if not present yet, retry once after a short delay
        function trySelect(id) {
            const node = tree.get_node(id);
            if (node) {
                if (node.id === '#') {
                    selectHome();
                }else{
                    tree.open_node(node.parents);
                    tree.select_node(id);
                    tree.open_node(id);
                }
            } else {
                setTimeout(() => {
                    const retryNode = tree.get_node(id);
                if (retryNode) {
                    tree.open_node(retryNode.parents);
                    tree.select_node(id);
                }
            }, 400);
            }
        }
        trySelect(selected);
    });

    $('#new-folder-dialog').dialog({
        autoOpen: false,
        modal: true,
        width: 380,
        buttons: {
            "Create": function() {
                var name = $('#folder-name').val().trim();
                if (!name) {
                $('#folder-error').text('Please enter a folder name.').show();
                    return;
                }

                var tree = $('#folderTree').jstree(true);
                var selectedNode = tree.get_selected(true)[0]; // returns the full node object
                var parentId = selectedNode ? selectedNode.id : null;
                var csrf = (typeof yii !== 'undefined' && yii.getCsrfToken) ? yii.getCsrfToken() : $('meta[name="csrf-token"]').attr('content');

                $.ajax({
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

                            if (!Number.isInteger(parseInt(jsParent))) {
                                jsParent = '#';
                            }

                            var tree = $('#folderTree').jstree(true);
                            tree.refresh();

                            $('#folderTree').on('refresh.jstree', function() {
                                var tree = $('#folderTree').jstree(true);

                                if (jsParent === '#') {
                                    loadFolder(null);
                                }else{
                                    tree.open_node(jsParent);
                                    tree.deselect_all();
                                    tree.select_node(jsParent);
                                    window.fetchFolders(jsParent, false, true);
                                }
                            });
                            showBanner('Folder created successfully!', 'success');
                        }
                    },
                    error: function(xhr, status, errorThrown) {
                        const firstField = Object.keys(xhr.responseJSON.errors)[0];
                        let message = xhr.responseJSON.errors[firstField][0];
                        showBanner(message, 'error');
                    }
                });
                $(this).dialog('close');
            },
            "Cancel": function() { $(this).dialog('close'); }
        },
        open: function() {
        $('#folder-name').val('').focus();
            $('#folder-error').hide();

            $('#folder-name').off('keypress').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    $(".ui-dialog-buttonpane button:contains('Create')").trigger('click');
                }
            });
        }
    });

    // Minimal add: open dialog on plus icon click
    $('#btn-new-folder').on('click', function() {
        $('#new-folder-dialog').dialog('open');
    });

    $('#folderTree').on('rename_node.jstree', function(e, data) {
        const tree = $('#folderTree').jstree(true);
        const oldName = data.old;  // original folder name
        const newName = data.text; // new attempted name

        $.ajax({
            url: '/folder/rename',
            type: 'POST',
            dataType: 'json',
            data: {
                id: data.node.id,
                name: newName,
                _csrf: yii.getCsrfToken()
            },
            success: function(res) {
                if (!res.ok) {
                    showBanner(res.message, 'error');
                    // revert to old name
                    tree.set_text(data.node, oldName);
                }
            },
            error: function() {
                showBanner('Error communicating with server', 'error');
                // revert to old name
                tree.set_text(data.node, oldName);
            }
        });
    });

    $('#folderSearch').on('keydown', function (e) {
            if (e.key !== 'Enter') return;
    
            e.preventDefault();
            const query = $(this).val().trim();
            const tree = $('#folderTree').jstree(true);
    
            if (!query) {
                // Optional: clear selection / search highlight
                tree.clear_search && tree.clear_search();
                tree.deselect_all();
                folderSearchState.lastQuery = '';
                folderSearchState.matches = [];
                folderSearchState.index = -1;
                return;
            }
    
            // If query changed, rebuild matches and reset index
            if (folderSearchState.lastQuery.toLowerCase() !== query.toLowerCase()) {
                folderSearchState.lastQuery = query;
                folderSearchState.matches = jstreeCollectMatches(tree, query);
                folderSearchState.index = -1;
            }
    
            if (folderSearchState.matches.length === 0) {
                showBanner(`No folders match "${query}".`, 'error');
                return;
            }
    
            // Advance to next match (wrap around)
            folderSearchState.index =
                (folderSearchState.index + 1) % folderSearchState.matches.length;
    
            const targetId = folderSearchState.matches[folderSearchState.index];
    
            // (Optional) if you use jsTree's search plugin and want highlight:
            if (tree.search) {
                tree.search(query); // highlights all matches
            }
    
            // Open path, select node, and scroll into view
            jstreeOpenAncestors(tree, targetId, function () {
                tree.deselect_all();
                tree.select_node(targetId);
    
                // Ensure it's scrolled into view (centered if possible)
                const $el = tree.get_node(targetId, true);
                if ($el && $el.length) {
                    // anchor is usually the visible clickable element
                    const anchor = $el.children('.jstree-anchor').get(0) || $el.get(0);
                    if (anchor && anchor.scrollIntoView) {
                        anchor.scrollIntoView({ block: 'center', inline: 'nearest' });
                    }
                }
            });
        });    
});
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>
