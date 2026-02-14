<?php
/** @var yii\web\View $this */
$this->title = 'Fotuka';
\yii\web\JqueryAsset::register($this);
if (!$id){
    $id = 'null';
}
?>

<input type="file" id="folderInput" webkitdirectory directory multiple style="display:none;">
<!-- Temporarily commenting as it needs to be determined where this will go. 
<button id="pickFolderBtn" type="button">Upload Folder</button>
-->

<div class="right-panel" id="rightPanel">
    <div class="folder-header">
        <div class="folder-title">
            <i class="fa fa-folder-open" style="color: #E2CB91;"></i>
            <span id="currentFolderName"></span>
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
        </div>    </div>

    <!-- Drop zone -->
    <div id="dropZone" class="drop-zone">
        <div class="section-header">
            <span class="section-title">Sub-folders</span>
            <span class="section-count" id="subfolderCount"></span>
        </div>
        <div class="folder-section" id="subfolders"></div>
        <div id="folderControls" class="folder-controls"></div>

        <? if ($id && $id != 'null'){ ?>
            <div class="section-header">
                <span class="section-title">Assets</span>
                <span class="section-count" id="assetCount"></span>
            </div>
            <div id="assetControls" class="asset-controls"></div>
            <div class="asset-grid" id="assetGrid"></div>
        <? } ?>
    </div>
</div>
<?php
$js = <<<JS
\$(function () {
    // Toggle folder menu
    \$('#folderMenuBtn').on('click', function (e) {
        e.stopPropagation();
        \$('#folderMenu').toggle();
    });

    \$(document).on('click', function () {
        \$('#folderMenu').hide();
    });

    let folderPagination = {
        folderId: null,
        offset: 0,
        limit: 14,
        allLoaded: false
    };            
    
    function loadFolder(folderId) {
        // reset state
        folderPagination = { folderId, offset: 0, limit: 14, allLoaded: false };
        \$('#subfolders').empty();
        \$('#subfolderCount').text('');

        // also reset asset pagination
        assetPagination.folderId = folderId;
        assetPagination.offset = 0;
        assetPagination.allLoaded = false;
        
        // fetch first page (and folder name)
        fetchFolders(folderId, /*append*/ false, /*loadAll*/ false);
    }

    function fetchFolders(folderId, append = false, loadAll = false) {
        if (folderId == null){
            folderId = 0;
        }  
    
        const params = {
            offset: append ? folderPagination.offset : 0,
            limit: loadAll ? 0 : folderPagination.limit
        };
    
        \$.ajax({
            url: '/json/folder/' + folderId,
            type: 'GET',
            data: params,
            dataType: 'json',
            success: function(res) {
                if (!res || res.ok === false) {
                    showBanner('Error loading subfolders', 'error');
                    return;
                }
            
                // Update folder name if provided
                if (res.folder_name) {
                    \$('#currentFolderName').text(res.folder_name);
                }
            
                // Render
                const items = res.subfolders || [];
                renderSubfolders(items, append);
            
                // Update counts / state
                if (typeof res.total === 'number') {
                    \$('#subfolderCount').text(res.total);
                } else {
                    \$('#subfolderCount').text(\$('#subfolders .folder-card').length);
                }
        
                if (append) {
                    folderPagination.offset += items.length;
                } else {
                    folderPagination.offset = items.length;
                }
        
                // Prefer server flag; otherwise derive it
                folderPagination.allLoaded = (res.allLoaded === true) ||
                    (!loadAll && typeof res.total === 'number' && folderPagination.offset >= res.total) ||
                    (loadAll && true);
        
                updateFolderButtons();
        
                // Smooth scroll for appended / show-all loads (peek assets start)
                if (append || loadAll) {
                    scrollToAssetsPeek(computeScrollOffset());
                }
            },
            error: function() {
            showBanner('Server error while loading subfolders', 'error');
            }
        });
    }
    
    function computeScrollOffset() {
        const \$folders = \$('#folderGrid');
        if (\$folders.length && !isNaN(\$folders.outerHeight())) {
            // subtract 100 to peek into assets
            return Math.max(0, \$folders.outerHeight() - 100);
        }
        return 850; // fallback
    }            

    function scrollToAssetsPeek(peekOffset) {
        const \$panel       = \$('#rightPanel');
        const \$assetsStart = \$('#assetGrid');
        const \$foldersEnd  = \$('#folderGrid'); // if you use a different id, update this
    
        // Optional: measure any sticky header inside the panel so we don't hide content under it
        const \$stickyHeader = \$('#rightPanel .sticky-header');
        const headerOffset = \$stickyHeader.length ? \$stickyHeader.outerHeight() : 0;
    
        // Default peek if none provided
        const peek = (typeof peekOffset === 'number' ? peekOffset : 100);
    
        // Use rAF twice to let the layout settle (images, fonts) before measuring
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                const hasPanel = \$panel.length && \$panel[0];
                const panelEl = hasPanel ? \$panel[0] : null;
                const panelCanScroll = !!(panelEl && (panelEl.scrollHeight - panelEl.clientHeight > 1));
    
                // Decide which element we measure from:
                // 1) bottom of folders block if it exists
                // 2) otherwise top of assets block
                const useFoldersBottom = \$foldersEnd && \$foldersEnd.length > 0;
                const \$measureEl = useFoldersBottom ? \$foldersEnd : \$assetsStart;
    
                if (!\$measureEl || \$measureEl.length === 0) {
                    // Nothing to scroll to — bail safely
                    return;
                }
    
                const panelTopInDoc = \$panel.offset() ? \$panel.offset().top : 0;
                const currentScroll = hasPanel ? \$panel.scrollTop() : \$(window).scrollTop();
    
                let targetInDoc;
                if (useFoldersBottom) {
                    // Bottom edge of the folders section in document space
                    targetInDoc = \$measureEl.offset().top + \$measureEl.outerHeight();
                } else {
                    // Top of the assets section in document space
                    targetInDoc = \$measureEl.offset().top;
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
                    \$panel.stop(true).animate({ scrollTop: target }, 700, 'swing');
                } else {
                    // Fallback: scroll the page (if rightPanel isn't actually scrollable)
                    // For page scroll, we already have document coords in targetInDoc
                    const pageTarget = Math.max(0, targetInDoc - desiredOffset);
                    \$('html, body').stop(true).animate({ scrollTop: pageTarget }, 700, 'swing');
                }
            });
        });
    }

    function updateFolderButtons() {
        const \$controls = \$('#folderControls');
        \$controls.empty();
    
        if (!folderPagination.allLoaded && folderPagination.folderId) {
            \$controls.append('<button id="loadMoreBtn">Load More</button>');
            \$controls.append('<button id="showAllBtn">Show All</button>');
    
            // bind fresh (avoid duplicates)
            \$('#loadMoreBtn').off('click').on('click', function() {
                fetchFolders(folderPagination.folderId, /*append*/ true, /*loadAll*/ false);
            });
    
            \$('#showAllBtn').off('click').on('click', function() {
                fetchFolders(folderPagination.folderId, /*append*/ false, /*loadAll*/ true);
            });
        }
    }

    function renderSubfolders(folders, append = false) {
        const container = \$('#subfolders');
        if (!append) container.empty(); // only clear if full reload
    
        folders.forEach(f => {
            const safeName = f.name || 'Untitled';
            const shortName = safeName.length > 18 ? safeName.slice(0, 18) + '…' : safeName;
    
            const thumbHtml = f.thumbnail
                ? `<div class="thumb thumb--large"><img src="\${f.thumbnail}" alt="\${safeName}" onerror="this.onerror=null;this.src='/icons/folder-placeholder.svg';"></div>`
                : `<div class='demoji' title="\${safeName}"><a href="/folder/\${f.id}"> <span class="emoji" style="font-size: 70px;">📁</span></a></div>`;            
        
            const card = $(`<div class="folder-card" title="\${safeName}">\${thumbHtml}<span>\${safeName}</span></div>`);
            container.append(card);
        });
        updateFolderButtons();
    }
    
    // --- Drag & Drop Upload (with progress) ---
    const dropZone = \$('#dropZone');
    const progressBar = \$('<div id="uploadProgress"><div></div></div>').appendTo(dropZone);
    
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
            return handleUpload(files, {$id});
        }).catch(function (err) {
            console.error('Drop read failed:', err);
            showBanner('Could not read dropped folder(s).', 'error');
        });
    });
    
    /** Traverse folders using HTML5 FileSystem API (Chrome/Edge/Safari desktop) */
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
            a.replace(/\\\\/g, '/').replace(/^\\/+/g, '').replace(/\\/+$/g, '') +
            '/' +
            b.replace(/\\\\/g, '/').replace(/^\\/+/g, '').replace(/\\/+$/g, '')
        );
    }            

    loadFolder($id);
    loadAssets($id);
    initInfiniteAssetScroll();
});

// Open folder picker
\$('#pickFolderBtn').off('click').on('click', function() {
    \$('#folderInput').click();
});
        
// Immediately upload on selection (folders/files)
\$('#folderInput').off('change').on('change', function(e) {
    const files = e.target.files;
    if (!files || !files.length) return;
    (async () => {
        await handleUpload(files, {$id});
    })();
    // reset so selecting the same folder again retriggers change
    \$(this).val('');
});

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

        
let assetPagination = {
    folderId: null,
    offset: 0,
    limit: 25,
    allLoaded: false
};
                
function renderAssets(assets, append = false) {
    const \$grid = \$('#assetGrid');
        
    if (!append) {
        \$grid.empty();
    }
        
    assets.forEach(asset => {
        /*
        const card = `
            <div class="asset-card" data-title="\${asset.title || ''}">
                <img src="\${asset.thumbnail_url || '/images/placeholder.png'}" alt="">
                <span class="asset-title">\${asset.title || 'Untitled'}</span>
            </div>
        `;
        */
        var card = '';
        if (asset.thumbnail_state == 'pending'){
            card = `<div class="asset-card" id='asset_\${asset.id}'>
                    <div style="width:250px;height:220px;display:flex;flex-direction:column;align-items:center;justify-content:center;font-family:sans-serif;">
                      <div style="font-size:25px;margin-bottom:12px;">Processing</div>
                      <div class="spinner" style="width:40px;height:40px;border:4px solid #ccc;border-top:4px solid #3498db;border-radius:50%;animation:spin 1s linear infinite;"></div>
                    </div>
                    <span class="asset-title">\${asset.title}</span>
                </div>`;
            card = `<div class="asset-card" id='asset_\${asset.id}'>
                    <div style="width:250px;height:220px;display:flex;flex-direction:column;align-items:center;justify-content:center;font-family:sans-serif;">
                    <img class='asset' src='/images/sample1.jpg' width='250' height='220'>
                    </div>
                    <span class="asset-title">\${asset.title}</span>
                </div>`;
            
        }else{
            card = `<div class="asset-card" id='asset_\${asset.id}'>
                    <div style="width:250px;height:220px;display:flex;flex-direction:column;align-items:center;justify-content:center;font-family:sans-serif;">
                    <img class='asset' src='` + asset.thumbnail_url + `' width='250' height='220'>
                    </div>
                    <span class="asset-title">\${asset.title}</span>
                </div>`;
        }
        \$grid.append(card);
    });
        
    \$('#assetCount').text(\$('.asset-card').length);
        
    // Smooth scroll only if appending
    if (append) {
        const \$panel = \$('#rightPanel');
        const target = \$grid[0].scrollHeight - \$panel.height();
        \$panel.stop(true).animate({ scrollTop: target }, 600, 'swing');
    }
}

function initInfiniteAssetScroll() {
    const \$panel = \$('#rightPanel');
    let scrollLock = false; // prevent spamming

    \$panel.on('scroll', function() {
        if (assetPagination.allLoaded || scrollLock) return;

        const scrollTop = \$panel.scrollTop();
        const scrollHeight = \$panel.prop('scrollHeight');
        const panelHeight = \$panel.height();

        // Trigger when near bottom (within 200px)
        if (scrollTop + panelHeight >= scrollHeight - 200) {
            scrollLock = true;

            // ✅ Don't increment here; let loadAssets handle it
            loadAssets(assetPagination.folderId, false, assetPagination.offset);
            setTimeout(() => { scrollLock = false; }, 1000);
        }
    });
}

\$(document).ready(function() {
    // Toggle folder action menu
    \$('.folder-menu-btn').on('click', function(e) {
        e.stopPropagation(); // prevent bubbling to document
        const \$parent = \$(this).closest('.folder-actions');
        // Close any other open menus
        \$('.folder-actions').not(\$parent).removeClass('active');
        // Toggle this one
        \$parent.toggleClass('active');
    });

    // Close when clicking outside
    \$(document).on('click', function() {
        \$('.folder-actions').removeClass('active');
    });
  
    // Handle "Rename" menu click
    \$('.folder-rename').on('click', function() {
        const \$nameSpan = \$('#currentFolderName');
        const \$input = \$('#renameInput');
        const currentName = \$nameSpan.text().trim();

        // show text field for editing
        \$nameSpan.hide();
        \$input.val(currentName).show().focus();

        // close folder menu if open
        \$('.folder-actions').removeClass('active');
    });

    // Handle Enter press inside rename field
    \$('#renameInput').on('keypress', function(e) {
        if (e.which !== 13) return; // only Enter key
        e.preventDefault();

        const \$input = \$(this);
        const newName = \$input.val().trim();
        const folderId = selectedFolderId; // already defined elsewhere
        const oldName = \$('#currentFolderName').text().trim();

        if (!newName || newName === oldName) {
            \$input.hide();
            \$('#currentFolderName').show();
            return;
        }

        // Disable field while saving
        \$input.prop('disabled', true);

        // --- unified AJAX pattern (same as jsTree rename) ---
        \$.ajax({
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
                    \$input.val(oldName);
                    \$('#currentFolderName').text(oldName).show();
                } else {
                    // success: update displayed name
                    showBanner('Folder renamed successfully', 'success');
                    \$('#currentFolderName').text(newName).show();
                    const tree = $('#folderTree').jstree(true);
                    tree.set_text(folderId, newName);
                }
                \$input.hide().prop('disabled', false);
            },
            error: function() {
                showBanner('Error communicating with server', 'error');
                \$input.hide().prop('disabled', false);
                \$('#currentFolderName').text(oldName).show();
            }
        });
    });

    // Handle blur (cancel rename)
    \$('#renameInput').on('blur', function() {
        const \$input = \$(this);
        const \$span = \$('#currentFolderName');
        \$input.hide();
        \$span.show();
    });
  
    \$('.folder-delete').on('click', function() {
        const folderId = selectedFolderId; // already defined globally
        const tree = \$('#folderTree').jstree(true);
        const node = tree ? tree.get_node(folderId) : null;
    
        if (!folderId || !node) {
            showBanner('No folder selected or node not found.', 'error');
            return;
        }
    
        // Confirm delete
        if (!confirm('Are you sure you want to delete this folder?')) {
            return;
        }
    
        // AJAX request to delete folder
        \$.ajax({
            url: '/folder/delete',
            type: 'POST',
            dataType: 'json',
            data: {
                id: folderId,
                _csrf: yii.getCsrfToken()
            },
            success: function(res) {
                if (res && res.ok) {
                    const parentId = node.parent;
    
                    // Remove node from tree
                    tree.delete_node(node);
    
                    // Select parent or fallback to first root
                    if (parentId && parentId !== '#') {
                        tree.deselect_all();
                        tree.select_node(parentId);
                    } else {
                        const roots = tree.get_node('#').children;
                        if (roots.length) {
                            tree.deselect_all();
                            tree.select_node(roots[0]);
                            tree.open_node(roots[0]);
                        }
                    }
    
                    // Update UI
                    showBanner('Folder deleted successfully', 'success');
    
                    // Optionally redirect to parent folder
                    if (parentId && parentId !== '#') {
                        window.location.href = '/folder/' + parentId;
                    } else {
                        window.location.href = '/folder';
                    }
                } else {
                    showBanner(res.message || 'Failed to delete folder', 'error');
                }
            },
            error: function() {
                showBanner('Error deleting folder', 'error');
            }
        });
    
        // Close dropdown
        \$('.folder-actions').removeClass('active');
    });  
    
    let currentUploadXhr = null;

    $(document).on('drop', function (e) {
        if (!$(e.target).closest('#dropZone').length) {
            e.preventDefault();
            e.stopPropagation();
            return false; // ignore drops outside
        }
    });
});

/**
 * Unified handler for uploading files/folders
 * @param {FileList|File[]} files
 * @param {number} folderId
 */
async function handleUpload(files, folderId) {
    if (!files || !files.length) return;

    // Build (file, relativePath) pairs
    const collected = [];
    for (const file of files) {
        const path = file.webkitRelativePath || file.relativePath || file.name;
        collected.push({ file, path });
    }

    const formData = new FormData();
    collected.forEach(({ file, path }) => {
        formData.append('files[]', file);
        formData.append('paths[]', path);
    });

    formData.append('id', folderId);
    formData.append('_csrf', yii.getCsrfToken());

    // progress bar (create once if missing)
    if (!\$('#uploadProgress').length) {
        const \$progress = \$('<div id="uploadProgress"><div></div></div>');
        \$('#dropZone').append(\$progress);
    }
    \$('#uploadProgress div').css('width', '0%').show();

    currentUploadXhr = \$.ajax({
        url: `/asset/upload/\${folderId}`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function () {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function (evt) {
                if (evt.lengthComputable) {
                    const percent = Math.round((evt.loaded / evt.total) * 100);
                    \$('#uploadProgress div').css('width', percent + '%');
                }
            });
            return xhr;
        },
        complete: function () {
            currentUploadXhr = null; // clear reference when done
        },    
        success: function (res) {
            if (res && res.ok) {
                showBanner(`Uploaded \${res.uploaded} file(s) successfully`, 'success');
                \$('#uploadProgress div').css('width', '100%');
                setTimeout(() => \$('#uploadProgress div').fadeOut(), 1000);
                // Refresh assets + tree
                assetPagination.allLoaded = false;
                loadAssets(folderId, true);
                const tree = \$('#folderTree').jstree(true);
                tree.refresh();
            } else {
                showBanner((res && res.error) || 'Upload failed', 'error');
            }
        },
        error: function (xhr, status) {
            if (status === 'abort') {
                showBanner('Upload canceled by user', 'info');
            } else {
                showBanner('Error uploading files', 'error');
            }
        }
    });
}        
        
JS;
$this->registerJs($js);
?>