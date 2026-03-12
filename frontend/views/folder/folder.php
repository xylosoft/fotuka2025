<?php
use common\models\Folder;

/** @var yii\web\View $this */
$this->title = 'Fotuka';
\yii\web\JqueryAsset::register($this);
$id = $this->params['id'];
$selectedId = $id ?? '#';

$id = $_COOKIE[Yii::$app->params['FOLDER_COOKIE']]??null;

if ($id == "null"){
    $id = null;
}else if ($id != null){
    $exists = Folder::find()
        ->where(['id' => $id])
        ->andWhere(['=', 'status', Folder::STATUS_ACTIVE])
        ->exists();
    if (!$exists){
        $id = null;
    }
}

$user = Yii::$app->user->identity;
$gdImport = (int)Yii::$app->request->get('gd_import', 0);
$googleConnected = $user && $user->hasGoogleDriveConnected();
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
                    <button type="button" id="btnEnterSelection" class="action-btn" style="display:none">
                        Select Assets
                    </button>

                    <div id="bulkActionBar" class="bulk-toolbar" style="display:none;">
                        <button type="button" id="btnToggleSelectAll" class="action-btn">
                            Select All
                        </button>
                        <button type="button" id="btnBulkDelete" class="action-btn" disabled>
                            Delete
                        </button>
                        <button type="button" id="btnBulkShare" class="action-btn" disabled>
                            Share
                        </button>
                        <button type="button" id="btnBulkDownload" class="action-btn" disabled>
                            Download
                        </button>
                        <button type="button" id="btnCancelSelection" class="action-btn">
                            Cancel
                        </button>
                    </div>

                    <!--
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
                    -->

                </div>

            </div>

            <div id="folderview">
                <div class="folder-section" id="subfolders"></div>
            </div>

            <div id="dropZone" class="drop-zone">
                <div id="assetControls" class="asset-controls"></div>
                <div class="asset-grid" id="assetGrid"></div>


                <div id="empty-assets" class="empty-folders" style="display:none;margin-top:-65px;">
                    <div class="empty-card">
                        <div><img src='/images/filetypes.png' width="300" style="padding-bottom:30px"></div>
                        <h2 class="empty-title">You don't have any assets.</h2>
                        <p class="empty-subtitle">
                            You can drag & drop folders or files into this area to upload your files.
                        </p>

                        <button type="button" id="btn-upload-file" class="btn-primary">
                            Upload Files
                        </button>
                    </div>
                </div>
            </div>

            <div id="empty-folders" class="empty-folders" style="display:none;">
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
<!-- Asset Preview Dialog -->
<div id="asset-preview-dialog" title="Preview" style="display:none;">
    <div class="asset-preview-topbar">
        <div class="asset-preview-topbar-left">
            <div class="asset-preview-topbar-title">Asset Details</div>
        </div>
        <div class="asset-preview-topbar-actions">
            <button type="button" class="asset-nav-btn" id="adPrev">← Prev</button>
            <button type="button" class="asset-nav-btn" id="adNext">Next →</button>
            <button type="button" class="asset-nav-btn" id="adClose">✕ Close</button>
        </div>
    </div>

    <div class="asset-preview-layout">
        <div class="asset-preview-media">

            <img id="assetPreviewImg" src="" alt="Preview" style="display:none;">
            <div id="assetPreviewPlaceholder" class="asset-preview-placeholder">
                <div class="placeholder-icon">🖼️</div>
                <div class="placeholder-title">Preview not available</div>
                <div class="placeholder-subtitle">This asset is still processing or no preview exists.</div>
            </div>
        </div>
        <div class="asset-preview-details">
            <div class="asset-details-title">Asset Details</div>

            <div class="asset-details-row">
                <div class="k">Filename</div>
                <div class="v" id="ad_filename">—</div>
            </div>
            <div class="asset-details-row">
                <div class="k">File Type</div>
                <div class="v" id="ad_filetype">—</div>
            </div>
            <div class="asset-details-row">
                <div class="k">File Size</div>
                <div class="v" id="ad_filesize">—</div>
            </div>
            <div class="asset-details-row">
                <div class="k">Orientation</div>
                <div class="v" id="ad_orientation">—</div>
            </div>
            <div class="asset-details-row">
                <div class="k">Image Type</div>
                <div class="v" id="ad_imagetype">—</div>
            </div>
            <div class="asset-details-row">
                <div class="k">Width</div>
                <div class="v" id="ad_width">—</div>
            </div>
            <div class="asset-details-row">
                <div class="k">Height</div>
                <div class="v" id="ad_height">—</div>
            </div>
            <div class="asset-details-row tags-row">
                <div class="k">
                    <strong>Tags</strong>
                    <button type="button" class="asset-nav-btn" id="btnShowAddTag">+ Add</button>

                    <div class="tag-add-buttons">
                        <button type="button" class="asset-nav-btn tag-cancel-btn" id="btnCancelTag">Cancel</button>
                        <button type="button" class="tag-save-btn" id="btnSaveTag">Save</button>
                    </div>
                </div>

                <div class="v">
                    <div id="ad_tags" class="tag-list"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Asset Right-Click Context Menu -->
<div id="assetContextMenu" class="asset-context-menu" style="display:none;">
    <div class="menu-item" data-action="download"><span class="menu-icon">⬇️</span> Download</div>
    <div class="menu-item" data-action="convert"><span class="menu-icon">🔁</span> Convert</div>
    <div class="menu-item" data-action="regen"><span class="menu-icon">🧩</span> Regenerate Thumbnail</div>
    <div class="menu-separator"></div>
    <div class="menu-item" data-action="share"><span class="menu-icon">🔗</span> Share</div>
</div>

<div id="gd-import-modal" style="display:none;">
    <div class="gd-modal-body">
        <div class="gd-row">
            <div class="gd-title">Import from Google Drive</div>
            <div class="gd-subtitle">Pick files or folders to import into Fotuka.</div>
        </div>

        <div class="gd-row gd-actions">
            <button type="button" class="btn btn-primary" id="gd-btn-connect">Connect Google Drive</button>
            <button type="button" class="btn btn-success" id="gd-btn-pick" disabled>Pick items</button>
            <button type="button" class="btn btn-default" id="gd-btn-cancel">Cancel</button>
        </div>

        <div class="gd-row">
            <div id="gd-status" class="gd-status"></div>
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
    const assetCache = {};       // id -> json asset
    let assetMenuForId = null;
    let selectionMode = false;
    const autoImport = <?= $gdImport ?>;
    let currentPreviewAssetId = null;

    function getAssetOrder() {
        return $('#assetGrid .asset-card[data-asset-id]')
            .map(function () { return parseInt($(this).attr('data-asset-id'), 10); })
            .get()
            .filter(id => !isNaN(id));
    }

    function getCardPreviewUrl(assetId) {
        const $card = $('#assetGrid .asset-card[data-asset-id="' + assetId + '"]');
        if (!$card.length) return '';
        return (($card.attr('data-preview-url') || '') + '').trim();
    }

    function updateDialogNavButtons() {
        const order = getAssetOrder();
        const idx = order.indexOf(currentPreviewAssetId);

        const enabled = (idx !== -1 && order.length > 1);
        $('#adPrev').prop('disabled', !enabled);
        $('#adNext').prop('disabled', !enabled);
    }

    function goDialogPrev() {
        const order = getAssetOrder();
        const idx = order.indexOf(currentPreviewAssetId);
        if (idx === -1 || order.length < 2) return;

        const prevId = order[(idx - 1 + order.length) % order.length];
        openAssetPreview(prevId, getCardPreviewUrl(prevId));
    }

    function goDialogNext() {
        const order = getAssetOrder();
        const idx = order.indexOf(currentPreviewAssetId);
        if (idx === -1 || order.length < 2) return;

        const nextId = order[(idx + 1) % order.length];
        openAssetPreview(nextId, getCardPreviewUrl(nextId));
    }

    function resetTagAddUI(){
        $('#ad_tag_add').removeClass('is-open');
        $('#ad_tag_input').val('');
    }

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

                const thumbUrl   = (a.thumbnail_url || '').toString().trim();
                const previewUrl = (a.preview_url  || '').toString().trim();
                const title = $card.find('.asset-title').text() || '';

                $card
                    .attr('data-thumb-state', 'ready')
                    .attr('data-thumb-url', thumbUrl)
                    .attr('data-preview-url', previewUrl)
                    .html(
                        '<label class="asset-select" title="Select">' +
                        '<input type="checkbox" class="asset-select-box" value="' + a.id + '">' +
                        '</label>' +
                        '<div class="asset-thumb-wrap" style="width:250px;height:220px;display:flex;align-items:center;justify-content:center;">' +
                        '<img class="asset asset-clickable" src="' + thumbUrl + '" width="250" height="220">' +
                        '</div>' +
                        '<span class="asset-title">' + title + '</span>'
                    );
            });

        });
    }

    // CHECKED
    function loadFolder(folderId) {
        console.log("Loading folders for "  + folderId);
        if (!folderId || isNaN(folderId)){
            console.log("Setting FolderID to null");
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
            $('#currentFolderName').text("Home");
            $('#subfolders').empty();
            $('#btnEnterSelection').hide();
            selectHome();
            $('#folderview').show();
        }else{
            $('#folderview').hide();
            $('#btnEnterSelection').show();
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
        console.log("fetchFolders");
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
                    setEmptyStateVisible('folders', true);
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

    // CHECKED
    function loadAssets(folderId, showAll = false, offset = 0) {
        if (!folderId){
            return;
        }

        if (assetPagination.allLoaded) return;

        const limit = showAll ? 0 : assetPagination.limit;

        jQuery.getJSON('/json/assets/' + folderId, { limit, offset }, function(response) {
            if (response && response.assets) {
                renderAssets(response.assets, offset > 0);

                if (offset === 0) {
                    const $scroller =
                        $('#assetScroll:visible').length ? $('#assetScroll') :
                            $('#rightPanel:visible').length ? $('#rightPanel') :
                                $('#assetGrid').closest('.scroll-container').length ? $('#assetGrid').closest('.scroll-container') :
                                    $(window);

                    if ($scroller[0] === window) {
                        window.scrollTo(0, 0);
                    } else {
                        $scroller.scrollTop(0);
                    }
                }

                startPendingThumbnailPolling();

                // Update state
                assetPagination.offset += response.assets.length;

                const hasAnyAssets = $('#assetGrid .asset-card').length > 0 || offset > 0;
                setEmptyStateVisible('assets', !hasAnyAssets && response.assets.length === 0);

                // Detect end of list
                if (response.assets.length < assetPagination.limit || response.assets.length === 0 || showAll) {
                    assetPagination.allLoaded = true;
                }
            }else{
                const hasAnyAssets = $('#assetGrid .asset-card').length > 0 || offset > 0;
                setEmptyStateVisible('assets', !hasAnyAssets);
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
            card = '<div class="asset-card" id="asset_' +asset.id +'" data-thumb-state="pending" data-thumb-url="" data-asset-id="' + asset.id + '">' +
                '<div style="width:250px;height:220px;display:flex;flex-direction:column;align-items:center;justify-content:center;font-family:sans-serif;">' +
                '<div style="font-size:25px;margin-bottom:12px;">Processing</div>' +
                '<div class="spinner" style="width:40px;height:40px;border:4px solid #ccc;border-top:4px solid #3498db;border-radius:50%;animation:spin 1s linear infinite;"></div>' +
                '</div>' +
                '<span class="asset-title">' + asset.title + '</span>' +
                '</div>';
            $('#assetGrid').append(card);
        }else {
            const thumbUrl   = (asset.thumbnail_url || '').toString().trim();
            const previewUrl = (asset.preview_url  || '').toString().trim();

            card =
                '<div class="asset-card" ' +
                'data-asset-id="' + asset.id + '" ' +
                'data-thumb-state="ready" ' +
                'data-thumb-url="' + thumbUrl + '" ' +
                'data-preview-url="' + previewUrl + '">' +

                // checkbox overlay (hidden unless selection mode is on)
                '<label class="asset-select" title="Select">' +
                '<input type="checkbox" class="asset-select-box" value="' + asset.id + '">' +
                '</label>' +

                '<div class="asset-thumb-wrap" style="width:250px;height:220px;display:flex;align-items:center;justify-content:center;">' +
                '<img class="asset asset-clickable" src="' + thumbUrl + '" width="250" height="220">' +
                '</div>' +
                '<span class="asset-title">' + (asset.title || '') + '</span>' +
                '</div>';

            $grid.append(card);

        }
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

    function setEmptyStateVisible(type, isEmpty) {
        if (isEmpty) {
            $('#empty-' + type).css('display', 'flex');
        } else {
            $('#empty-' + type).hide();
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

        const uploadHadFolders = collected.some(function(x) {
            const p = normalizeRelPath(x.path);
            return p.includes('/');   // folder upload produces relative paths with slashes
        });

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
                            setEmptyStateVisible('assets', false);
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
        });
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

            if (uploadHadFolders) {
                const tree = $('#folderTree').jstree(true);
                const selectedId = (tree.get_selected() || [])[0];

                $('#folderTree').one('refresh.jstree', function() {
                    const t = $('#folderTree').jstree(true);
                    if (selectedId) {
                        t.deselect_all();
                        t.select_node(selectedId);
                        jstreeOpenAncestors(t, selectedId, function() {
                            t.open_node(selectedId);
                        });
                    }
                });

                tree.refresh();
            }
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


    // CHECKED
    function selectHome(){
        console.log("Selecting Home...");
        var tree = $('#folderTree').jstree(true);
        var roots = tree.get_node('#').children;
        var allNodes = tree.get_json('#', { flat: true });

        if (roots.length) {
            tree.open_node(roots[0]);
            tree.deselect_all();
            tree.select_node(roots[0]);
        }

        setEmptyStateVisible('assets', false);

        if (allNodes.length === 1) {
            setEmptyStateVisible('folders', true);
        }else{
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

    function showUploadOverlay(title) {
        const t = title || "Upload Process...";
        $('#uploadOverlay .upload-overlay-title').text(t);
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

    function setUploadOverlayIndeterminate(on) {
        $('#uploadOverlay').toggleClass('indeterminate', !!on);
    }

    function formatBytes(bytes) {
        const n = Number(bytes || 0);
        if (!n) return '0 B';
        const units = ['B','KB','MB','GB','TB'];
        const i = Math.floor(Math.log(n) / Math.log(1024));
        return (n / Math.pow(1024, i)).toFixed(i === 0 ? 0 : 1) + ' ' + units[i];
    }

    function renderTags(tags) {
        const $wrap = $('#ad_tags');
        $wrap.empty();

        if (!tags || !tags.length) {
            $wrap.append('<div style="color:#6B7280;font-size:12px;">No tags</div>');
            return;
        }

        tags.forEach(t => {
            const id = t.id;              // asset_labels.id
        const name = (t.name || '').toString();

        const chip =
            '<span class="tag-chip" data-asset-label-id="' + id + '">' +
            '<span class="tag-name">' + escapeHtml(name) + '</span>' +
            '<div class="tag-trash" title="Remove tag">&#x2612;</div>' +
            '</span>';

        $wrap.append(chip);
    });
    }

    function escapeHtml(s) {
        return (s || '').toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function openAssetPreview(assetId, initialPreviewUrl) {
        if (!assetId) return;

        resetTagAddUI();
        currentPreviewAssetId = parseInt(assetId, 10);
        updateDialogNavButtons();

        const $dlg = $('#asset-preview-dialog');
        const $img = $('#assetPreviewImg');
        const $ph  = $('#assetPreviewPlaceholder');

        // Make sure dialog markup exists
        if ($dlg.length === 0 || $img.length === 0 || $ph.length === 0) {
            return;
        }

        // Normalize initial url (from card)
        initialPreviewUrl = ((initialPreviewUrl || '') + '').trim();

        // Reset preview area without flashing the placeholder
        $img.off('load.preview error.preview');
        $img.attr('src', '').hide();
        $ph.hide();

        // If we have an initial preview url, only show image after it loads
        if (initialPreviewUrl && initialPreviewUrl !== 'undefined' && initialPreviewUrl !== 'null') {
            $img
                .one('load.preview', function() {
                    $ph.hide();
                    $img.show();
                })
                .one('error.preview', function() {
                    $img.hide();
                    $ph.show();
                })
                .attr('src', initialPreviewUrl);
        } else {
            $ph.show();
        }

        // Open dialog immediately (fast UX)
        $dlg.dialog('open');

        // Fill placeholders in details panel while loading
        setAssetDetailsLoading();

        // Use cache if available
        if (assetCache[assetId]) {
            fillAssetDetails(assetCache[assetId]);
            return;
        }

        $.getJSON('/json/asset/' + assetId, function(res) {
            if (!res || !res.ok || !res.asset) {
                setAssetDetailsError('Unable to load asset details');
                return;
            }

            assetCache[assetId] = res.asset;
            fillAssetDetails(res.asset);

        }).fail(function() {
            setAssetDetailsError('Unable to load asset details');
        });
    }

    function setAssetDetailsLoading() {
        $('#ad_filename').text('Loading...');
        $('#ad_filetype').text('Loading...');
        $('#ad_filesize').text('Loading...');
        $('#ad_orientation').text('Loading...');
        $('#ad_imagetype').text('Loading...');
        $('#ad_width').text('Loading...');
        $('#ad_height').text('Loading...');
        $('#ad_tags').empty().append('<div style="color:#6B7280;font-size:12px;">Loading...</div>');
    }

    function setAssetDetailsError(msg) {
        $('#ad_filename').text('Error');
        $('#ad_filetype').text('—');
        $('#ad_filesize').text('—');
        $('#ad_orientation').text('—');
        $('#ad_imagetype').text('—');
        $('#ad_width').text('—');
        $('#ad_height').text('—');
        $('#ad_tags').empty().append('<div style="color:#6B7280;font-size:12px;">—</div>');
    }


    function fillAssetDetails(a) {
        // Update details panel (safe fallbacks)
        $('#ad_filename').text(a.filename || a.title || '—');
        $('#ad_filetype').text(a.file_type || a.mime_type || '—');
        $('#ad_filesize').text(a.file_size ? formatBytes(a.file_size) : '—');
        $('#ad_orientation').text(a.orientation || '—');
        $('#ad_imagetype').text(a.image_type || '—');
        $('#ad_width').text(a.width || '—');
        $('#ad_height').text(a.height || '—');

        // Update preview image from latest API data
        const $img = $('#assetPreviewImg');
        const $ph  = $('#assetPreviewPlaceholder');

        const previewUrl = ((a.preview_url || a.previewUrl || '') + '').trim();

        $img.off('load.preview error.preview');

        if (previewUrl && previewUrl !== 'undefined' && previewUrl !== 'null') {
            if ($img.attr('src') !== previewUrl) {
                $ph.hide();
                $img.hide()
                    .one('load.preview', function() {
                        $ph.hide();
                        $img.show();
                    })
                    .one('error.preview', function() {
                        $img.attr('src', '').hide();
                        $ph.show();
                    })
                    .attr('src', previewUrl);
            } else {
                $ph.hide();
                $img.show();
            }
        } else {
            $img.attr('src', '').hide();
            $ph.show();
        }

        renderTags(a.tags || []);
        updateDialogNavButtons();
    }

    function getSelectableCheckboxes() {
        // Only ready cards have checkboxes; this returns those present
        return $('#assetGrid .asset-select-box');
    }

    function getSelectedAssetIds() {
        return $('#assetGrid .asset-select-box:checked')
            .map(function () { return parseInt($(this).val(), 10); })
            .get()
            .filter(id => !isNaN(id));
    }

    function updateBulkUI() {
        const $boxes = getSelectableCheckboxes();
        const total = $boxes.length;
        const selected = getSelectedAssetIds().length;

        $('#bulkSelectedCount').text(selected + ' selected');

        const enabled = selected > 0;
        $('#btnBulkDelete').prop('disabled', !enabled);
        $('#btnBulkShare').prop('disabled', !enabled);
        $('#btnBulkDownload').prop('disabled', !enabled);

        // Toggle label
        if (total > 0 && selected === total) {
            $('#btnToggleSelectAll').text('Unselect All');
        } else {
            $('#btnToggleSelectAll').text('Select All');
        }
    }

    function enterSelectionMode() {
        selectionMode = true;
        $('#rightPanel').addClass('selection-mode');
        $('#btnEnterSelection').hide();
        $('#bulkActionBar').show();
        updateBulkUI();
    }

    function exitSelectionMode() {
        selectionMode = false;
        $('#rightPanel').removeClass('selection-mode');
        $('#bulkActionBar').hide();
        $('#btnEnterSelection').show();

        // Clear selection visuals + checkboxes
        $('#assetGrid .asset-select-box').prop('checked', false);
        $('#assetGrid .asset-card').removeClass('is-selected');
        updateBulkUI();
    }

    function closeAllContextMenus(options) {
        options = options || {};

        if (typeof window.closeAllFotukaMenus === 'function') {
            window.closeAllFotukaMenus(options);
            return;
        }

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
    }

    function hideAddTagRow() {
        $('#ad_tag_add').removeClass('is-open');
        $('#ad_tag_input').val('');
    }

    function createTagForCurrentAsset() {
        const name = ($('#ad_tag_input').val() || '').trim();
        if (!name) return;

        const payload = {
            asset_id: currentPreviewAssetId,
            name: name
        };

        $.ajax({
            url: '/asset/createtag',
            type: 'POST',
            dataType: 'json',
            data: payload,
            success: function (res) {
                if (!res || !res.ok || !res.tag) {
                    showBanner((res && res.message) ? res.message : 'Unable to create tag', 'error');
                    return;
                }

                hideAddTagRow();

                // Update cache + UI
                if (!assetCache[currentPreviewAssetId]) assetCache[currentPreviewAssetId] = {};
                if (!assetCache[currentPreviewAssetId].tags) assetCache[currentPreviewAssetId].tags = [];

                // Avoid duplicates in cache
                const exists = assetCache[currentPreviewAssetId].tags.some(t => parseInt(t.id, 10) === parseInt(res.tag.id, 10));
                if (!exists) assetCache[currentPreviewAssetId].tags.push(res.tag);

                renderTags(assetCache[currentPreviewAssetId].tags);
            },
            error: function () {
                showBanner('Server error creating tag', 'error');
            }
        });
    }

    window.FotukaGoogleDrive = (function() {
        let pickerApiLoaded = false;
        let apiKey = "<?= Yii::$app->params['googleDrive']['apiKey'] ?>";
        let clientId = "<?= Yii::$app->params['googleDrive']['clientId'] ?>";
        let targetFolderId = null;
        const IMPORT_BATCH_SIZE = 1;      // 1 = per-file UI updates (what you want)
        const IMPORT_CONCURRENCY = 2;     // adjust as desired

        function openImportModal(opts) {
            targetFolderId = opts.targetFolderId;

            // Use jQuery UI dialog if you're already using it
            $("#gd-import-modal").dialog({
                modal: true,
                width: 520,
                title: "Import from Google Drive",
                close: function() {
                    $("#gd-status").text("");
                    $("#gd-btn-pick").prop("disabled", true);
                }
            });

            $("#gd-status").text("Not connected.");
            bindButtons();
            checkSession();
            loadPickerApi();
        }

        function bindButtons() {
            $("#gd-btn-cancel").off("click").on("click", function() {
                $("#gd-import-modal").dialog("close");
            });

            $("#gd-btn-connect").off("click").on("click", function() {
                $("#gd-status").text("Redirecting to Google to connect…");
                window.location.href = "/google-drive/start";
            });

            $("#gd-btn-pick").off("click").on("click", function() {

                // Close the "Import from Google" dialog
                $("#gd-import-modal").dialog("close");

                // Open the Google Picker
                createPicker();
            });
        }

        function checkSession() {
            $.getJSON("/google-drive/status", function(resp) {
                if (resp.connected) {
                    // Hide connect button if connected
                    $("#gd-btn-connect").hide();

                    // Enable picker
                    $("#gd-btn-pick").prop("disabled", false).show();

                    let msg = resp.email
                        ? "Connected as " + resp.email + ". Pick items to import."
                        : "Connected. Pick items to import.";

                    $("#gd-status").text(msg);
                } else {
                    // Show connect button if not connected
                    $("#gd-btn-connect").show();

                    $("#gd-btn-pick").prop("disabled", true).show();

                    $("#gd-status").text("Not connected. Click “Connect Google Drive”.");
                }
            });
        }

        function loadPickerApi() {
            if (pickerApiLoaded) return;

            // Load the Google APIs JS (Picker uses this)
            if (!document.getElementById("google-api-js")) {
                let s = document.createElement("script");
                s.id = "google-api-js";
                s.src = "https://apis.google.com/js/api.js";
                s.onload = function() {
                    gapi.load("picker", { callback: function() { pickerApiLoaded = true; }});
                };
                document.head.appendChild(s);
            } else {
                gapi.load("picker", { callback: function() { pickerApiLoaded = true; }});
            }
        }

        function createPicker() {
            if (!pickerApiLoaded) {
                $("#gd-status").text("Picker is still loading… try again in a second.");
                return;
            }

            $("#gd-status").text("Requesting access token…");

            // IMPORTANT: token must come from your backend (OAuth flow).
            // We'll fetch a short-lived access token from your server.
            $.getJSON("/google-drive/token", function(resp) {
                if (!resp.ok) {
                    $("#gd-status").text("Not connected. Click “Connect Google Drive”.");
                    $("#gd-btn-pick").prop("disabled", true);
                    return;
                }

                let accessToken = resp.accessToken;

                let view = new google.picker.DocsView(google.picker.ViewId.DOCS)
                    .setIncludeFolders(true)
                    .setSelectFolderEnabled(true);

                let picker = new google.picker.PickerBuilder()
                    .setAppId(resp.projectNumber || "") // optional
                    .setOAuthToken(accessToken)
                    .setDeveloperKey(apiKey)
                    .addView(view)
                    .enableFeature(google.picker.Feature.MULTISELECT_ENABLED)
                    .setCallback(pickerCallback)
                    .build();

                picker.setVisible(true);
                $("#gd-status").text("Picker opened.");
            }).fail(function() {
                $("#gd-status").text("Failed to fetch token from server.");
            });
        }

        function pickerCallback(data) {
            if (data.action === google.picker.Action.CANCEL) {
                showBanner("Your Google Drive import has been canceled", "error");
                return;
            }

            if (data.action !== google.picker.Action.PICKED) return;

            let items = (data.docs || []).map(function(d) {
                return {
                    id: d.id,
                    name: d.name || "",
                    mimeType: d.mimeType || ""
                };
            });

            if (!items.length) {
                showBanner("Your Google Drive import has been canceled", "error");
                return;
            }

            // ✅ incremental importing with overlay + per-file UI updates
            importItems(items).catch(function(err) {
                showBanner(err && err.message ? err.message : "Google Drive import failed.", "error");
            });
        }

        function autoPickIfConnected() {
            $.getJSON("/google-drive/status", function(resp) {
                if (!resp.connected) {
                    // Not connected - show connect button and stop
                    $("#gd-btn-connect").show();
                    $("#gd-btn-pick").prop("disabled", true);
                    $("#gd-status").text("Not connected. Click “Connect Google Drive”.");
                    return;
                }

                // Connected: hide connect, enable pick
                $("#gd-btn-connect").hide();
                $("#gd-btn-pick").prop("disabled", false);
                $("#gd-status").text("Connected. Opening picker…");

                // Ensure picker API loaded, then open picker
                // If your module uses loadPickerApi(), call it here too.
                loadPickerApi();

                // Poll until picker is loaded
                let tries = 0;
                let t = setInterval(function() {
                    tries++;
                    if (pickerApiLoaded) {
                        clearInterval(t);
                        createPicker();
                    } else if (tries > 20) {
                        clearInterval(t);
                        $("#gd-status").text("Picker failed to load. Please click “Pick items”.");
                    }
                }, 200);
            });
        }

        function openPicker(opts) {
            targetFolderId = opts.targetFolderId;

            loadPickerApi();

            let tries = 0;
            let t = setInterval(function() {
                tries++;
                if (pickerApiLoaded) {
                    clearInterval(t);
                    createPicker();
                } else if (tries > 20) {
                    clearInterval(t);
                    showBanner("Google Picker failed to load. Please try again.", "error");
                }
            }, 200);
        }

        async function importItems(items) {
            if (!targetFolderId) {
                showBanner("Import failed: target folder is missing.", "error");
                return;
            }

            showUploadOverlay("Importing from Google Drive...");

            const googleImportHadFolders = items.some(i => i.mimeType === 'application/vnd.google-apps.folder');
            setUploadOverlayIndeterminate(googleImportHadFolders);

            let completed = 0;
            const total = items.length;

            // Make batches (size 1 => per-file)
            const batches = [];
            for (let i = 0; i < items.length; i += IMPORT_BATCH_SIZE) {
                batches.push(items.slice(i, i + IMPORT_BATCH_SIZE));
            }

            const importBatch = (batch) => {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: "/asset/import-google-drive",
                        method: "POST",
                        dataType: "json",
                        data: {
                            targetFolderId: targetFolderId,
                            items: batch,
                            _csrf: yii.getCsrfToken()
                        },
                        success: function(resp) {
                            // Treat server-side failure as a rejection so the caller can handle it uniformly.
                            if (!resp || resp.ok !== true) {
                                console.log("GD importBatch server error:", resp);
                                reject(new Error((resp && resp.error) ? resp.error : "Google import failed"));
                                return;
                            }

                            // Optional: log for debugging
                            // console.log("GD importBatch success:", resp);

                            resolve(resp);
                        },
                        error: function(xhr) {
                            reject(new Error("Google import request failed: HTTP " + xhr.status));
                        }
                    });
            });
            };

            const worker = async () => {
                while (batches.length) {
                    const batch = batches.shift();
                    if (!batch) return;

                    const resp = await importBatch(batch);

                    // Update counters + overlay progress
                    completed += batch.length;
                    const pct = Math.round((completed / total) * 100);
                    updateUploadOverlay(pct);

                    // Render processing cards as soon as each batch is done
                    if (resp.assets && resp.assets.length) {
                        let renderable = resp.assets;

                        // Only render files that belong to the imported folder root (not subfolders)
                        if (resp.rootImportFolderId) {
                            renderable = renderable.filter(a => String(a.folder_id) === String(resp.rootImportFolderId));
                        }

                        // Also: only render into the currently visible folder
                        renderable = renderable.filter(a => String(a.folder_id) === String(window.selectedFolderId));

                        if (renderable.length) {
                            renderAssets(renderable, true);
                            startPendingThumbnailPolling();
                            setEmptyStateVisible('assets', false);
                        }
                    }
                }
            };

            try {
                const n = Math.max(1, IMPORT_CONCURRENCY);
                const workers = [];
                for (let i = 0; i < n; i++) workers.push(worker());
                await Promise.all(workers);

                updateUploadOverlay(100);
                hideUploadOverlay();

                showBanner("Google Drive import completed. " + total + " item(s) processed.", "success");

                if (googleImportHadFolders) {
                    const tree = $('#folderTree').jstree(true);
                    const selectedId = (tree.get_selected() || [])[0];

                    $('#folderTree').one('refresh.jstree', function() {
                        const t = $('#folderTree').jstree(true);
                        if (selectedId) {
                            t.deselect_all();
                            t.select_node(selectedId);
                            jstreeOpenAncestors(t, selectedId, function() {
                                t.open_node(selectedId);
                            });
                        }
                    });

                    tree.refresh();
                }
            } finally {
                setUploadOverlayIndeterminate(false);
                hideUploadOverlay();
            }
        }

        return { openImportModal, autoPickIfConnected, openPicker };
    })();

    document.addEventListener('DOMContentLoaded', function () {
        var $treeEl = $('#folderTree');
        const $container = $('.user-menu-container');
        const dropZone = $('#dropZone');
        const progressBar = $('<div id="uploadProgress"><div></div></div>').appendTo(dropZone);

        $('#folderSearch').on('click', function() {
            $(this).val('');
            folderSearchState.lastQuery = '';
            folderSearchState.matches = [];
            folderSearchState.index = -1;
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
                    };
                    if (isNumericId) {
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
                        };
                        menu.renameItem = {
                            label: '<span style="font-size:16px;padding-right:10px;">✏️</span> Rename',
                            action: function() { tree.edit(node); } // opens inline rename input
                        };
                        <?php /*
                    menu.moveItem = {
                        label: '<span style="font-size:16px;padding-right:10px;">↷️</span> Move',
                        action: function() { javascript:void(0); }
                    };*/
                        ?>
                        menu.deleteItem = {
                            label: '<span style="font-size:16px;padding-right:10px;">🗑️</span> Delete',
                            action: function() {
                                deleteFolder(node.id);
                            }
                        };
                        menu.googleImport = {
                            label: '<span style="font-size:16px;padding-right:10px;"><img src="/images/gdrive.png" width="20"></span> Import from Google Drive',
                            action: function() {

                                <?php if ($googleConnected): ?>

                                // User already authorized → open picker directly
                                FotukaGoogleDrive.openPicker({
                                    targetFolderId: window.selectedFolderId
                                });

                                <?php else: ?>

                                // Not authorized → show connect dialog
                                FotukaGoogleDrive.openImportModal({
                                    targetFolderId: window.selectedFolderId
                                });

                                <?php endif; ?>

                            }
                        };
                        /*
                        menu.dropboxImport = {
                            label: '<span style="font-size:16px;padding-right:10px;"></span> Import from Dropbox',
                            action: function() {
                                // To be implemented
                            }
                        };
                        */
                        menu.publish = {
                            label: '<span style="font-size:16px;padding-right:10px;">🗑️</span> Publish Folder',
                            separator_before: true,
                            action: function() {
                                const tree = $('#folderTree').jstree(true);
                                const selectedId = (tree.get_selected() || [])[0];
                                window.location.href = '/publish/' + selectedId;
                            }
                        };

                    };
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
                        if (name.length > 50) {
                            $('#folder-error').text('Folder names can\'t contain more than 50 characters').show();
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
                                setEmptyStateVisible('folders', false);
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
            openNewFolderDialog();
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
                        tree.set_text(data.node, oldName);
                    }else{
                        $('#currentFolderName').text(data.text);
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


        $('#assetContextMenu, .folder-dropdown-menu').on('click', function(e) {
            e.stopPropagation();
        });

        $('.folder-menu-btn').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $parent = $(this).closest('.folder-actions');
            const shouldOpen = !$parent.hasClass('active');

            closeAllContextMenus({ keepFolder: true });
            $('.folder-actions').removeClass('active');

            if (shouldOpen) {
                $parent.addClass('active');
            }
        });

        $('.folder-dropdown-menu').off('click').on('click', function(e) {
            e.stopPropagation();
        });

        $('#folderTree').off('contextmenu.menuSync', '.jstree-anchor, .jstree-wholerow')
            .on('contextmenu.menuSync', '.jstree-anchor, .jstree-wholerow', function() {
                closeAllContextMenus({ keepTree: true });
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
            openNewFolderDialog();
        });

        $('#asset-preview-dialog').dialog({
            autoOpen: false,
            modal: true,
            width: 1090,
            height: 720,
            resizable: false,
            draggable: true,
        });

        $('#assetGrid').on('click', '.asset-clickable', function(e) {
            e.stopPropagation();

            const $card = $(this).closest('.asset-card');

            // If in selection mode, toggle checkbox instead of opening preview
            if (selectionMode) {
                const $cb = $card.find('.asset-select-box');
                if ($cb.length) {
                    $cb.prop('checked', !$cb.prop('checked')).trigger('change');
                }
                return;
            }

            // FIX: this was broken in your current code (quote mismatch)
            const id = $card.data('asset-id');

            // Read preview url from DOM safely
            let previewUrl = (($card.data('preview-url') || '') + '').trim();
            if (!previewUrl) {
                previewUrl = (($card.attr('data-preview-url') || '') + '').trim();
            }

            openAssetPreview(id, previewUrl);
        });

        $('#assetGrid').on('contextmenu', '.asset-card', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const id = $(this).data('asset-id');
            if (!id) return;

            assetMenuForId = id;

            closeAllContextMenus();

            $('#assetContextMenu').css({
                display: 'block',
                position: 'fixed',
                top: e.clientY + 'px',
                left: e.clientX + 'px',
                zIndex: 99999
            });
        });

        $(window).on('scroll resize', function() {
            closeAllContextMenus();
        });

        // handle menu actions
        $('#assetContextMenu').on('click', '.menu-item', function(e) {
            const action = $(this).data('action');
            const id = assetMenuForId;
            $('#assetContextMenu').hide();

            if (!id) return;

            // grab cached details if available
            const a = assetCache[id] || {};

            if (action === 'download') {
                // Option A: if your JSON returns a download_url
                if (a.download_url) {
                    window.location.href = a.download_url;
                    return;
                }

                // Option B: implement this route in your app
                window.location.href = '/asset/download/' + id;
                return;
            }

            if (action === 'convert') {
                alert('Convert (TODO) for asset ' + id);
                return;
            }

            if (action === 'regen') {
                alert('Regenerate Thumbnail (TODO) for asset ' + id);
                return;
            }

            if (action === 'share') {
                alert('Share (TODO) for asset ' + id);
                return;
            }
        });

        // Enter selection mode
        $('#btnEnterSelection').on('click', function () {
            enterSelectionMode();
        });

        // Cancel selection mode
        $('#btnCancelSelection').on('click', function () {
            exitSelectionMode();
        });

        // Select All / Unselect All toggle
        $('#btnToggleSelectAll').on('click', function () {
            const $boxes = getSelectableCheckboxes();
            const total = $boxes.length;
            const selected = $boxes.filter(':checked').length;

            const shouldSelectAll = !(total > 0 && selected === total);
            $boxes.prop('checked', shouldSelectAll).trigger('change');
        });

        // Keep card highlight in sync + bulk buttons enabled state
        $('#assetGrid').on('change', '.asset-select-box', function () {
            const $card = $(this).closest('.asset-card');
            $card.toggleClass('is-selected', $(this).is(':checked'));
            updateBulkUI();
        });

        // Prevent checkbox click from triggering preview click
        $('#assetGrid').on('click', '.asset-select-box', function (e) {
            e.stopPropagation();
        });

        // Optional: in selection mode, clicking the card toggles its checkbox
        $('#assetGrid').on('click', '.asset-card', function (e) {
            if (!selectionMode) return;
            if ($(e.target).is('.asset-select-box')) return;

            const $cb = $(this).find('.asset-select-box');
            if ($cb.length) {
                $cb.prop('checked', !$cb.prop('checked')).trigger('change');
            }
        });

        // If you want ESC to cancel selection mode
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && selectionMode) {
                exitSelectionMode();
            }
        });

        $('#btnBulkDelete').on('click', function() {
            const ids = getSelectedAssetIds();
            if (!ids.length) return;

            if (!confirm('Delete ' + ids.length + ' selected asset(s)?')) return;

            $.ajax({
                url: '/asset/delete',
                type: 'POST',
                dataType: 'json',
                data: {
                    ids: ids,
                    _csrf: yii.getCsrfToken()
                },
                success: function(res) {
                    if (res && res.ok) {
                        ids.forEach(function(id) {
                            $('.asset-card[data-asset-id="' + id + '"]').remove();
                        });

                        $('#assetCount').text($('.asset-card').length);

                        // After deleting, keep selection mode but update state
                        updateBulkUI();

                        // If nothing left selected, your action buttons auto-disable
                        // Optionally exit selection mode if grid is empty
                        if ($('.asset-card').length === 0) {
                            exitSelectionMode();
                            setEmptyStateVisible('assets', true);
                        }
                    } else {
                        showBanner((res && res.message) ? res.message : 'Bulk delete failed.', 'error');
                    }
                },
                error: function() {
                    showBanner('Server error while deleting assets.', 'error');
                }
            });
        });

        if (autoImport === 1) {
            // Remove the query param so refresh/back doesn't re-trigger the auto popup
            history.replaceState({}, document.title, window.location.pathname);

            // Open the import modal
            FotukaGoogleDrive.openImportModal({ targetFolderId: window.selectedFolderId });

            // Auto-open picker once status says connected
            setTimeout(function() {
                FotukaGoogleDrive.autoPickIfConnected();
            }, 500);
        }

        $('#adClose').on('click', function () {
            $('#asset-preview-dialog').dialog('close');
        });

        $('#adPrev').on('click', function () { goDialogPrev(); });
        $('#adNext').on('click', function () { goDialogNext(); });

        // keyboard nav when dialog is open
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape'){
                $('#asset-preview-dialog').dialog('close');
                closeAllContextMenus();
            }
            if (e.key === 'ArrowLeft') goDialogPrev();
            if (e.key === 'ArrowRight') goDialogNext();
        });

        $('#asset-preview-dialog').on('click', '.tag-trash', function () {
            const $chip = $(this).closest('.tag-chip');
            const assetLabelId = parseInt($chip.attr('data-asset-label-id'), 10);
            if (!assetLabelId) return;

            $.ajax({
                url: '/asset/deletetag/' + assetLabelId,
                type: 'POST',
                dataType: 'json',
                success: function (res) {
                    if (!res || !res.ok) {
                        showBanner((res && res.message) ? res.message : 'Unable to delete tag', 'error');
                        return;
                    }

                    // Remove from UI
                    $chip.remove();

                    // Keep cache in sync if present
                    if (assetCache[currentPreviewAssetId] && assetCache[currentPreviewAssetId].tags) {
                        assetCache[currentPreviewAssetId].tags =
                            assetCache[currentPreviewAssetId].tags.filter(t => parseInt(t.id, 10) !== assetLabelId);
                        renderTags(assetCache[currentPreviewAssetId].tags);
                    }
                },
                error: function () {
                    showBanner('Server error deleting tag', 'error');
                }
            });
        });


        jQuery(document).on('click', '#btnShowAddTag', function (e) {
            e.preventDefault();
            console.log('Add clicked');
            const row = jQuery('#ad_tag_add');
            console.log(row);
            console.log(jQuery('#ad_tag_input'));

            if (row.length) {
                row.addClass('is-open');
                jQuery('#ad_tag_input').focus();
            }
        });

        $('#btnPrevAsset').on('click', function(){
            resetTagAddUI();
        });

        $(document).on('click', '#btnCancelTag', function (e) {
            e.preventDefault();
            $('#ad_tag_add').removeClass('is-open');
            $('#ad_tag_input').val('');
        });

        $('#btnSaveTag').on('click', function () {
            createTagForCurrentAsset();
        });

        $('#ad_tag_input').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                createTagForCurrentAsset();
            }
        });


        initInfiniteAssetScroll();
    });

    function openNewFolderDialog() {
        var tree = $('#folderTree').jstree(true);
        var selectedNode = tree.get_selected(true)[0];
        var currentFolder = selectedNode.text;
        $('#new-folder-dialog').dialog('option', 'title', 'Creating folder under "' + currentFolder + '"')
            .dialog('open');

        /*
        const folderName = ($('#currentFolderName').text() || 'Home').trim();

        $('#new-folder-dialog')
            .dialog('option', 'title', 'Creating folder under "' + folderName + '"')
            .dialog('open');
            */
    }

</script>