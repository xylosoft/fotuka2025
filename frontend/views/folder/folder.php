<?php
/** @var yii\web\View $this */
$this->title = 'Fotuka';
\yii\web\JqueryAsset::register($this);
$id = $this->params['id'];
$selectedId = $id ?? '#';

$id = $_COOKIE[Yii::$app->params['FOLDER_COOKIE']]??null;
if ($id == "null"){
    $id = null;
}
$user = Yii::$app->user->identity;
?>

<div class="app split-layout" id="splitLayout">
    <aside class="sidebar" id="leftpanel">
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
    <div id="panelResizer"></div>
    <main class="main">
        <input type="file" id="folderInput" webkitdirectory directory multiple style="display:none;">

        <div class="right-panel" id="rightPanel">
            <div id="notification-banner" class="notification"></div>
            <div class="folder-header">
                <div class="folder-title">
                    <span id="currentFolderName"><?=$folder?$folder->name:"Home"?></span>
                    <input type="text" id="renameInput" class="rename-input" style="display:none;">
                </div>

                <div class="folder-actions">
                    <i class="fa fa-ellipsis-v folder-menu-btn"></i>
                    <div class="folder-dropdown-menu">
                        <div class="menu-item folder-rename">
                            <span class="menu-icon">✏️</span> Rename
                        </div>
                        <div class="menu-item folder-upload">
                            <span class="menu-icon">📤</span> Upload
                        </div>
                        <div class="menu-separator"></div>
                        <div class="menu-item folder-delete">
                            <span class="menu-icon">🗑️</span> Delete
                        </div>
                    </div>
                </div>
            </div>

            <div id="folderview">
                <div class="folder-section" id="subfolders"></div>
            </div>

            <div id="empty-state" class="empty-state" style="display:none;">
                <div class="empty-card">
                    <div class="empty-icon" aria-hidden="true">📁</div>
                    <h2 class="empty-title">You don't have any folders.</h2>
                    <p class="empty-subtitle">
                        Create your first folder to start uploading and organizing your assets.<br/>
                        After creating a folder, you can right click it for additional options.
                    </p>

                    <button type="button" id="btn-create-folder" class="btn-primary">
                        + Create folder
                    </button>
                </div>
            </div>

            <div id="dropZone" class="drop-zone">
                <div id="assetControls" class="asset-controls"></div>
                <div class="asset-grid" id="assetGrid"></div>
            </div>
        </div>
    </main>
</div>
<div id="uploadOverlay" class="upload-overlay" style="display:none;">
    <div class="upload-overlay-card">
        <div class="upload-overlay-title">Upload Process...</div>
        <div class="upload-overlay-bar">
            <div class="upload-overlay-bar-fill" style="width:0%;"></div>
        </div>
    </div>
</div>


<script>
const UPLOAD_BATCH_SIZE = 1;        // 1 = one file per request (recommended)
const UPLOAD_CONCURRENCY = 2;       // how many requests at once (2 is a good start)
let currentUploadXhr = null;
let assetPagination = {
    folderId: null,
    offset: 0,
    limit: 25,
    allLoaded: false
};
const selectedFolderId = '<?=$selectedId?>';
const folderSearchState = {
    lastQuery: '',
    matches: [],
    index: -1
};
let pendingThumbPoller = null;
let overallPct = 0;


function getPendingAssetIds() {
    return $('.asset-card[data-thumb-state="pending"]')
        .map(function () { return $(this).data('asset-id'); })
        .get()
        .filter(Boolean);
}

function startPendingThumbnailPolling() {
    if (pendingThumbPoller) return;
    pendingThumbPoller = setInterval(function () {
        pollPendingThumbnails();
    }, 1000);

    // also run immediately once
    pollPendingThumbnails();
}

function stopPendingThumbnailPolling() {
    if (pendingThumbPoller) {
        clearInterval(pendingThumbPoller);
        pendingThumbPoller = null;
    }
}

function pollPendingThumbnails() {
    const ids = getPendingAssetIds();

    if (!ids.length) return stopPendingThumbnailPolling();

    const cleanIds = ids.map(id => String(id).trim()).filter(id => id.length > 0);
    const url = '/json/pending/' + window.selectedFolderId + '/' + cleanIds.join(',');

    $.getJSON(url, function (response) {
        if (!response || !response.ok || !response.assets) return;

        response.assets.forEach(function (a) {
            if (!a.thumbnail_url) return;

            const $card = $('#asset_' + a.id);
            if (!$card.length) return;

            const $content = $card.children('div').first();
            $content.html(
                '<img class="asset" src="' + a.thumbnail_url + '" width="250" height="220">'
            );

            $card.attr('data-thumb-state', 'ready');
        });
    });
}

// CHECKED
function loadFolder(folderId) {
    if (!folderId || isNaN(folderId)){
        folderId = null;
    }

    // also reset asset pagination
    assetPagination.folderId = folderId;
    assetPagination.offset = 0;
    assetPagination.allLoaded = false;

    // fetch first page (and folder name)

    var tree = window.jQuery('#folderTree').jstree(true);
    tree.deselect_all();
    tree.open_node(folderId?folderId:0);
    tree.select_node(folderId?folderId:0);
    window.selectedFolderId = folderId?folderId:null;

    if (folderId == null){
        $('#dropZone').hide();
        $('#folderview').hide();
        $('#currentFolderName').text("Home");
        $('#subfolders').empty();
        selectHome();
        $('#folderview').show();
    }else{
        $('#folderview').hide();
        var selected = tree.get_selected(true);
        $('#currentFolderName').text(selected[0].text);
        loadAssets(assetPagination.folderId, false, 0);
        $('#dropZone').show();
    }
    document.cookie = "<?=Yii::$app->params['FOLDER_COOKIE']?>=" + folderId + "; path=/; max-age=3600; SameSite=Lax";
    const cookie = getCookie("<?=Yii::$app->params['FOLDER_COOKIE']?>");
}

// CHECKED
function fetchFolders(folderId, append = false, loadAll = false) {
    if (folderId == null || isNaN(folderId)){
        folderId = 0;
    }

    const params = {
        offset: 0,
        limit: 1000
    };

    $.ajax({
        url: '/json/folder/' + folderId,
        type: 'GET',
        data: params,
        dataType: 'json',
        success: function(res) {
            if (!res || res.ok === false) {
                showBanner('Error loading subfolders', 'error');
                return;
            }

            // Render
            const items = res.subfolders || [];
            if (folderId == 0 && items.length > 0) {
                renderSubfolders(items, append);
            }else if (folderId == 0){
                setEmptyStateVisible(true);
            }
        },
        error: function() {
            showBanner('Server error while loading subfolders', 'error');
        }
    });
}

function renderSubfolders(folders, append = false) {
    const container = $('#subfolders');
    container.hide();
    if (!append){
        container.empty(); // only clear if full reload
    }

    folders.forEach(f => {
        const safeName = f.name || 'Untitled';
        const shortName = safeName.length > 18 ? safeName.slice(0, 18) + '…' : safeName;

        const thumbHtml = f.thumbnail
            ? '<div class="thumb thumb--large"><img src="' + f.thumbnail + '" alt="' + safeName + '" onerror="this.onerror=null;this.src=\'/icons/folder-placeholder.svg\';"></div>'
            : '<div class="demoji" title="' + safeName + '"><a href="javascript:loadFolder('+  f.id + ');"> <img src="/images/folder100.png"></a></div>';

        const card = $('<div class="folder-card" title="' + safeName + '">' + thumbHtml + '<span>' + safeName + '</span></div>');
        container.append(card);
    });
    container.show();
}

function readDroppedItems(items) {
    const readers = [];
    for (let i = 0; i < items.length; i++) {
        const entry = items[i].webkitGetAsEntry ? items[i].webkitGetAsEntry() : null;
        if (!entry) continue;
        readers.push(readEntryRecursive(entry, ''));
    }
    return Promise.all(readers).then(nested => nested.flat());
}

function readEntryRecursive(entry, pathPrefix) {
    return new Promise((resolve, reject) => {
        if (entry.isFile) {
            entry.file(file => {
                resolve([{ file, path: joinPath(pathPrefix, file.name) }]);
            }, reject);
        } else if (entry.isDirectory) {
            const dirReader = entry.createReader();
            dirReader.readEntries(async entries => {
                try {
                    const results = [];
                    for (const ent of entries) {
                        const child = await readEntryRecursive(ent, joinPath(pathPrefix, entry.name));
                        results.push(...child);
                    }
                    resolve(results);
                } catch (e) {
                    reject(e);
                }
            }, reject);
        } else {
            resolve([]); // unknown entry
        }
    });
}

function joinPath(a, b) {
    if (!a) return b;
    if (!b) return a;

    return (
        a.replace(/\\/g, '/')
            .replace(/^\/+/g, '')
            .replace(/\/+$/g, '') +
        '/' +
        b.replace(/\\/g, '/')
            .replace(/^\/+/g, '')
            .replace(/\/+$/g, '')
    );
}

function loadAssets(folderId, showAll = false, offset = 0) {
    if (!folderId){
        return;
    }

    if (assetPagination.allLoaded) return;

    const limit = showAll ? 0 : assetPagination.limit;

    jQuery.getJSON('/json/assets/' + folderId, { limit, offset }, function(response) {
        if (response && response.assets) {
            renderAssets(response.assets, offset > 0);

            // Update state
            assetPagination.offset += response.assets.length;

            // Detect end of list
            if (response.assets.length < assetPagination.limit || response.assets.length === 0 || showAll) {
                assetPagination.allLoaded = true;
            }
        } else {
            assetPagination.allLoaded = true;
            showBanner('No assets found in this folder.', 'info');
        }
    });
}

function renderAssets(assets, append = false) {
    const $grid = $('#assetGrid');

    if (!append) {
        $grid.empty();
    }

    assets.forEach(asset => {
        var card = '';
        if (asset.thumbnail_state == 'pending'){
            card = '<div class="asset-card" id="asset_' +asset.id +'" data-thumb-state="pending" data-asset-id="' + asset.id + '">' +
                   '<div style="width:250px;height:220px;display:flex;flex-direction:column;align-items:center;justify-content:center;font-family:sans-serif;">' +
                   '<div style="font-size:25px;margin-bottom:12px;">Processing</div>' +
                   '<div class="spinner" style="width:40px;height:40px;border:4px solid #ccc;border-top:4px solid #3498db;border-radius:50%;animation:spin 1s linear infinite;"></div>' +
                   '</div>' +
                   '<span class="asset-title">' + asset.title + '</span>' +
                '</div>';

        }else{
            card = '<div class="asset-card" id="asset_' + asset.id + '">' +
                   '<div style="width:250px;height:220px;display:flex;flex-direction:column;align-items:center;justify-content:center;font-family:sans-serif;">' +
                   '<img class="asset" src="' + asset.thumbnail_url + '" width="250" height="220">' +
                   '</div>' +
                   '<span class="asset-title">' + asset.title + '</span>' +
                '</div>';
        }
        $grid.append(card);
    });

    $('#assetCount').text($('.asset-card').length);

    // Smooth scroll only if appending
    if (append) {
        const $panel = $('#rightPanel');
        const target = $grid[0].scrollHeight - $panel.height();
        $panel.stop(true).animate({ scrollTop: target }, 600, 'swing');
    }
}

function scrollToAssetsPeek(peekOffset) {
    const $panel       = $('#rightPanel');
    const $assetsStart = $('#assetGrid');
    const $foldersEnd  = $('#folderGrid'); // if you use a different id, update this

    // Optional: measure any sticky header inside the panel so we don't hide content under it
    const $stickyHeader = $('#rightPanel .sticky-header');
    const headerOffset = $stickyHeader.length ? $stickyHeader.outerHeight() : 0;

    // Default peek if none provided
    const peek = (typeof peekOffset === 'number' ? peekOffset : 100);

    // Use rAF twice to let the layout settle (images, fonts) before measuring
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
        const hasPanel = $panel.length && $panel[0];
        const panelEl = hasPanel ? $panel[0] : null;
        const panelCanScroll = !!(panelEl && (panelEl.scrollHeight - panelEl.clientHeight > 1));

        // Decide which element we measure from:
        // 1) bottom of folders block if it exists
        // 2) otherwise top of assets block
        const useFoldersBottom = $foldersEnd && $foldersEnd.length > 0;
        const $measureEl = useFoldersBottom ? $foldersEnd : $assetsStart;

        if (!$measureEl || $measureEl.length === 0) {
            // Nothing to scroll to — bail safely
            return;
        }

        const panelTopInDoc = $panel.offset() ? $panel.offset().top : 0;
        const currentScroll = hasPanel ? $panel.scrollTop() : $(window).scrollTop();

        let targetInDoc;
        if (useFoldersBottom) {
            // Bottom edge of the folders section in document space
            targetInDoc = $measureEl.offset().top + $measureEl.outerHeight();
        } else {
            // Top of the assets section in document space
            targetInDoc = $measureEl.offset().top;
        }

        // We want target inside the panel's scroll space:
        // currentScroll + (targetInDoc - panelTopInDoc) - (peek + headerOffset)
        const desiredOffset = peek + headerOffset;
        let target = currentScroll + (targetInDoc - panelTopInDoc) - desiredOffset;

        if (panelCanScroll) {
            // Clamp to valid panel range
            const maxScroll = panelEl.scrollHeight - panelEl.clientHeight;
            if (target < 0) target = 0;
            if (target > maxScroll) target = maxScroll;
                    $panel.stop(true).animate({ scrollTop: target }, 700, 'swing');
        } else {
            // Fallback: scroll the page (if rightPanel isn't actually scrollable)
            // For page scroll, we already have document coords in targetInDoc
            const pageTarget = Math.max(0, targetInDoc - desiredOffset);
                    $('html, body').stop(true).animate({ scrollTop: pageTarget }, 700, 'swing');
        }
        });
    });
}

function initInfiniteAssetScroll() {
    const $panel = $('#rightPanel');
    let scrollLock = false; // prevent spamming

    $panel.on('scroll', function() {
        if (assetPagination.allLoaded || scrollLock) return;

        const scrollTop = $panel.scrollTop();
        const scrollHeight = $panel.prop('scrollHeight');
        const panelHeight = $panel.height();

        // Trigger when near bottom (within 200px)
        if (scrollTop + panelHeight >= scrollHeight - 200) {
            scrollLock = true;

            // ✅ Don't increment here; let loadAssets handle it
            loadAssets(assetPagination.folderId, false, assetPagination.offset);
            setTimeout(() => { scrollLock = false; }, 1000);
        }
    });
}

function setEmptyStateVisible(isEmpty) {
    if (isEmpty) {
        $('#empty-state').css('display', 'flex');
    } else {
        $('#empty-state').hide();
    }
}

async function handleUpload(files, folderId) {
    if (!files || !files.length) return;

    // Normalize folderId
    if (!folderId || isNaN(folderId)) folderId = 0;

    // Build (file, relativePath) pairs
    const collected = [];
    for (const file of files) {
        const path = file.webkitRelativePath || file.relativePath || file.name;
        collected.push({ file, path });
    }

    showUploadOverlay();

    let completed = 0;
    const total = collected.length;

    // Helper: upload one batch (1..N files)
    const uploadBatch = (batch) => {
        const formData = new FormData();
        batch.forEach(({ file, path }) => {
            formData.append('files[]', file);
            formData.append('paths[]', path);
        });

        formData.append('id', folderId);
        formData.append('_csrf', yii.getCsrfToken());

        return new Promise((resolve, reject) => {
            currentUploadXhr = $.ajax({
                url: '/asset/upload/' + folderId,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function () {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function (evt) {
                        // batch progress -> overall progress (approx based on file count)
                        if (evt.lengthComputable) {
                            const batchPct = evt.total ? (evt.loaded / evt.total) : 0;
                            overallPct = Math.round(((completed + (batchPct * batch.length)) / total) * 100);
                            updateUploadOverlay(overallPct);
                        }
                    });
                    return xhr;
                },
                complete: function () {
                    currentUploadXhr = null;
                },
                success: function (res) {
                    if (res && res.ok) {
                        resolve(res.assets || []); // return uploaded assets for this batch
                    } else {
                        reject(new Error((res && res.error) || 'Upload failed'));
                    }
                },
                error: function (xhr, status) {
                    if (status === 'abort') {
                        reject(new Error('abort'));
                    } else {
                        reject(new Error('Error uploading files'));
                    }
                }
            });
        UPLOAD_BATCH_SIZE});
    };

    // Create batches
    const batches = [];
    for (let i = 0; i < collected.length; i += UPLOAD_BATCH_SIZE) {
        batches.push(collected.slice(i, i + UPLOAD_BATCH_SIZE));
    }

    // Run batches with limited concurrency
    const worker = async () => {
        while (batches.length) {
            const batch = batches.shift();
            if (!batch) return;

            try {
                const uploadedAssets = await uploadBatch(batch);

                // ✅ IMPORTANT: increment completed (you currently never do)
                completed += batch.length;

                // ✅ Decide whether this batch belongs to the current folder UI
                // Only render if at least one file in the batch is root-of-drop
                const batchHasRootFiles = batch.some(function (item) {
                    return isRootOfDroppedFolder(item.path);
                });

                // ✅ Remove folders from render list
                const renderable = (uploadedAssets || []).filter(function (a) {
                    return !isProbablyFolderAsset(a);
                });

                // ✅ Render only if these uploads belong in the current folder view
                if (batchHasRootFiles && renderable.length && typeof renderAssets === 'function') {
                    renderAssets(renderable, true);
                    startPendingThumbnailPolling();
                }

                updateUploadOverlay(overallPct);

            } catch (err) {
                if (err && err.message === 'abort') {
                    showBanner('Upload canceled by user', 'info');
                } else {
                    showBanner(err && err.message ? err.message : 'Upload failed', 'error');
                }
                // Stop further uploads on first failure (change if you want "continue on error")
                throw err;
            }
        }
    };

    try {
        const workers = [];
        const n = Math.max(1, UPLOAD_CONCURRENCY);
        for (let i = 0; i < n; i++) workers.push(worker());
        await Promise.all(workers);

        // Done
        updateUploadOverlay(100);
        hideUploadOverlay(); // remove immediately

        showBanner(total +' file' + (total>1?'s were':' was')+ ' uploaded successfully.', 'success');

        assetPagination.allLoaded = false;
        const tree = $('#folderTree').jstree(true);
        tree.refresh();
    } catch (e) {
        // already bannered in worker; keep progress bar state
    } finally {
        hideUploadOverlay();
    }
}

function normalizeRelPath(p) {
    if (!p) return '';
    return String(p).replace(/\\/g, '/').replace(/^\/+/, '').replace(/\/+$/, '');
}

// Root-of-drop means:
// - file.jpg                            => root (1 segment)
// - DroppedFolder/file.jpg              => root (2 segments)
// - DroppedFolder/sub/file.jpg          => NOT root (3+ segments)
function isRootOfDroppedFolder(path) {
    const p = normalizeRelPath(path);
    if (!p) return true;
    const parts = p.split('/').filter(Boolean);
    return parts.length <= 2;
}

function isProbablyFolderAsset(a) {
    return (
        a && (
            a.is_folder === true ||
            a.isFolder === true ||
            a.type === 'folder' ||
            a.kind === 'folder'
        )
    );
}
function jstreeCollectMatches(tree, query) {
    const q = String(query).trim().toLowerCase();
    if (!q) return [];
    const nodes = tree.get_json('#', { flat: true });
    return nodes.filter(n => (n.text || '').toLowerCase().includes(q)).map(n => n.id);
}

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
    var allNodes = tree.get_json('#', { flat: true });

    if (roots.length) {
        tree.open_node(roots[0]);
        tree.deselect_all();
        tree.select_node(roots[0]);
    }

    if (allNodes.length === 1) {
        console.log("Displaying empty");
        setEmptyStateVisible(true);
    }else{
        console.log("Fetching Folders");
        fetchFolders();
    }

}

// CHECKED
function deleteFolder(nodeId){
    var tree = $('#folderTree').jstree(true);
    var node = tree.get_node(nodeId);
    var parentId = node.parent;

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
                    tree.delete_node(node);
                    loadFolder(parentId);
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

function getCookie(name) {
    const cookies = document.cookie.split('; ');
    for (let c of cookies) {
        const [key, value] = c.split('=');
        if (key === name) return decodeURIComponent(value);
    }
    return null;
}

function showUploadOverlay() {
    $('#uploadOverlay').show();
    updateUploadOverlay(0);
}

function updateUploadOverlay(pct) {
    const p = Math.max(0, Math.min(100, Number(pct) || 0));
    $('#uploadOverlay .upload-overlay-bar-fill').css('width', p + '%');
}

function hideUploadOverlay() {
    $('#uploadOverlay').hide();
}

document.addEventListener('DOMContentLoaded', function () {
    var $treeEl = $('#folderTree');
    const $menu = $('.user-dropdown-menu');
    const $container = $('.user-menu-container');
    const dropZone = $('#dropZone');
    const progressBar = $('<div id="uploadProgress"><div></div></div>').appendTo(dropZone);

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
                            deleteFolder(node.id);
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
    }).on("changed.jstree", function (e, data) { // CHECKED
        if (data.action === "select_node" || data.action === "deselect_node") {
            var node = data.node;
            var folderId = node.id;
            if (!folderId || isNaN(folderId)){
                folderId = null;
            }

            if (folderId != window.selectedFolderId){
                window.selectedFolderId = folderId;
                loadFolder(folderId)
            }
        }
    });

    $('#new-folder-dialog').dialog({
        autoOpen: false,
        modal: true,
        width: 520,
        position: {
            my: "center+27% top",
            at: "center top+30%",
            of: window
        },
        buttons: [
            {
                text: 'Cancel',
                click: function () {
                    $(this).dialog('close');
                }
            },
            {
                text: 'Create',
                class: 'btn-primary', // <- used by the CSS override
                click: function () {
                    var name = $('#folder-name').val().trim();
                    if (!name) {
                        $('#folder-error').text('Please enter a folder name.').show();
                        return;
                    }

                    var tree = $('#folderTree').jstree(true);
                    var selectedNode = tree.get_selected(true)[0]; // returns the full node object
                    var parentId = selectedNode ? selectedNode.id : null;
                    var csrf = (typeof yii !== 'undefined' && yii.getCsrfToken)
                        ? yii.getCsrfToken()
                        : $('meta[name="csrf-token"]').attr('content');

                    $.ajax({
                        url: '/folder/add',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            name: name,
                            parent_id: (!parentId || parentId === '#' || parentId.startsWith('j1_')) ? null : parentId,
                            _csrf: csrf
                        },
                        success: function (res) {
                            if (res && res.ok) {
                                var jsParent = parentId && parentId !== '' ? parentId : '#';
                                jsParent = jsParent ? String(jsParent) : '#';

                                var tree = $('#folderTree').jstree(true);
                                tree.refresh();

                                if (!Number.isInteger(parseInt(jsParent))) {
                                    jsParent = '#';
                                    selectHome();
                                }


                                showBanner('Folder created successfully!', 'success');
                            }
                            setEmptyStateVisible(false);
                        },
                        error: function (xhr, status, errorThrown) {
                            const firstField = Object.keys(xhr.responseJSON.errors)[0];
                            let message = xhr.responseJSON.errors[firstField][0];
                            showBanner(message, 'error');
                        }
                    });

                    $(this).dialog('close');
                }
            }
        ],
        open: function () {
            $('#folder-name').val('').focus();
            $('#folder-error').hide().text('');

            const $pane = $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane');
            $pane.find('button:contains("Create")').addClass('btn-primary');

            $('#folder-name').off('keypress').on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $pane.find('button.btn-primary').trigger('click');
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
            showBanner('No folders match "${query}".', 'error');
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

    // Toggle folder menu
    $('#folderMenuBtn').on('click', function (e) {
        e.stopPropagation();
        $('#folderMenu').toggle();
    });

    $(document).on('click', function () {
        $('#folderMenu').hide();
    });

    dropZone.off('dragover').on('dragover', function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropZone.addClass('dragover');
    });

    dropZone.off('dragleave drop').on('dragleave drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropZone.removeClass('dragover');
    });

    dropZone.off('drop').on('drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropZone.removeClass('dragover');

        const dt = e.originalEvent && e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer : null;
        const items = dt && dt.items ? dt.items : [];

        if (!items.length) {
            const files = dt && dt.files ? dt.files : [];
            if (files.length) {
                handleUpload(files, {$id});
            }
            return;
        }

        // Classic promise syntax instead of await (no syntax errors)
        readDroppedItems(items).then(function (collected) {
            if (!collected.length) return;
            const files = collected.map(function (c) {
                c.file.relativePath = c.path;
                return c.file;
            });
            return handleUpload(files, window.selectedFolderId);
        }).catch(function (err) {
            showBanner('Could not read dropped folder(s).', 'error');
        });
    });

    $('#pickFolderBtn').off('click').on('click', function() {
        $('#folderInput').click();
    });

    $('#folderInput').off('change').on('change', function(e) {
        const files = e.target.files;
        if (!files || !files.length) return;
        (async () => {
            await handleUpload(files, {$id});
    })();
        // reset so selecting the same folder again retriggers change
    $(this).val('');
    });

    // Toggle folder action menu
    $('.folder-menu-btn').on('click', function(e) {
        e.stopPropagation(); // prevent bubbling to document
        const $parent = $(this).closest('.folder-actions');
        // Close any other open menus
        $('.folder-actions').not($parent).removeClass('active');
        // Toggle this one
        $parent.toggleClass('active');
    });

    // Close when clicking outside
    $(document).on('click', function() {
        $('.folder-actions').removeClass('active');
    });

    // Handle "Rename" menu click
    $('.folder-rename').on('click', function() {
        const $nameSpan = $('#currentFolderName');
        const $input = $('#renameInput');
        const currentName = $nameSpan.text().trim();

        // show text field for editing
        $nameSpan.hide();
        $input.val(currentName).show().focus();

        // close folder menu if open
        $('.folder-actions').removeClass('active');
    });

    // Handle Enter press inside rename field
    $('#renameInput').on('keypress', function(e) {
        if (e.which !== 13) return; // only Enter key
        e.preventDefault();

        const $input = $(this);
        const newName = $input.val().trim();
        const folderId = window.selectedFolderId; // already defined elsewhere
        const oldName = $('#currentFolderName').text().trim();

        if (!newName || newName === oldName) {
        $input.hide();
            $('#currentFolderName').show();
            return;
        }

        // Disable field while saving
        $input.prop('disabled', true);

        // --- unified AJAX pattern (same as jsTree rename) ---
        $.ajax({
            url: '/folder/rename',
            type: 'POST',
            dataType: 'json',
            data: {
                id: folderId,
                name: newName,
                _csrf: yii.getCsrfToken()
            },
            success: function(res) {
                if (!res.ok) {
                    showBanner(res.message || 'Rename failed', 'error');
                    // revert UI to old name
                $input.val(oldName);
                    $('#currentFolderName').text(oldName).show();
                } else {
                    // success: update displayed name
                    showBanner('Folder renamed successfully', 'success');
                    $('#currentFolderName').text(newName).show();
                    const tree = $('#folderTree').jstree(true);
                    tree.set_text(folderId, newName);
                }
                $input.hide().prop('disabled', false);
            },
            error: function() {
                showBanner('Error communicating with server', 'error');
                $input.hide().prop('disabled', false);
                $('#currentFolderName').text(oldName).show();
            }
        });
    });

    // Handle blur (cancel rename)
    $('#renameInput').on('blur', function() {
        const $input = $(this);
        const $span = $('#currentFolderName');
        $input.hide();
        $span.show();
    });

    // CHECKED
    $('.folder-delete').on('click', function() {
        const folderId = window.selectedFolderId;
        const tree = $('#folderTree').jstree(true);
        const node = tree ? tree.get_node(folderId) : null;

        if (!folderId || !node) {
            showBanner('No folder selected or node not found.', 'error');
            return;
        }

        if (!confirm('Are you sure you want to delete this folder?')) {
            return;
        }

        $.ajax({
            url: '/folder/delete',
            type: 'POST',
            dataType: 'json',
            data: {
                id: folderId,
                _csrf: yii.getCsrfToken()
            },
            success: function(res) {
                if (res && res.ok) {
                    showBanner('Folder deleted successfully', 'success');
                } else {
                    showBanner(res.message || 'Failed to delete folder.', 'error');
                }
            },
            error: function() {
                showBanner('Error deleting folder.', 'error');
            }
        });

        // Close dropdown
        $('.folder-actions').removeClass('active');
    });

    $(document).on('drop', function (e) {
        if (!$(e.target).closest('#dropZone').length) {
            e.preventDefault();
            e.stopPropagation();
            return false; // ignore drops outside
        }
    });

    $('#folderTree').on('ready.jstree', function () {
        loadFolder(<?=$id?>);
    })

    const layout = document.getElementById('splitLayout');
    const left = document.getElementById('leftpanel');
    const resizer = document.getElementById('panelResizer');

    if (!layout || !left || !resizer) return;

    const MIN = 220;
    const MAX = () => Math.min(window.innerWidth * 0.55, 700);

    let dragging = false;

    function setLeftWidth(px) {
        const clamped = Math.max(MIN, Math.min(MAX(), px));
        left.style.width = clamped + 'px';
        localStorage.setItem('fotuka.leftpanelWidth', String(clamped));
    }

    function onMove(clientX) {
        const rect = layout.getBoundingClientRect();
        const newWidth = clientX - rect.left;
        setLeftWidth(newWidth);
    }

    function stopDrag() {
        if (!dragging) return;
        dragging = false;
        layout.classList.remove('is-resizing');
    }

    resizer.addEventListener('pointerdown', function (e) {
        e.preventDefault();
        dragging = true;
        layout.classList.add('is-resizing');
        if (resizer.setPointerCapture) {
            resizer.setPointerCapture(e.pointerId);
        }
    });

    window.addEventListener('pointermove', function (e) {
        if (!dragging) return;
        onMove(e.clientX);
    });

    window.addEventListener('pointerup', stopDrag);
    window.addEventListener('pointercancel', stopDrag);

    /* Restore saved width */
    const saved = Number(localStorage.getItem('fotuka.leftpanelWidth'));
    if (saved) {
        setLeftWidth(saved);
    }

    $(document).on('click', '#btn-create-folder', function () {
        $('#new-folder-dialog').dialog('open');
    });

    initInfiniteAssetScroll();
});
</script>



