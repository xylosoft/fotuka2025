<?php
/** @var yii\web\View $this */
$this->title = 'Fotuka';

\yii\web\JqueryAsset::register($this);
?>
<div class="right-panel" id="rightPanel">
    <div class="folder-header">
        <div class="folder-title">
            <i class="fa fa-folder-open" style="color: #E2CB91;"></i>
            <span id="currentFolderName"></span>
        </div>
        <div class="folder-actions">
            <i class="fa fa-ellipsis-v" id="folderMenuBtn"></i>
            <div id="folderMenu" class="folder-menu">
                <div class="menu-item">Rename</div>
                <div class="menu-item">Upload</div>
                <div class="menu-item">Delete</div>
            </div>
        </div>
    </div>

    <!-- Drop zone -->
    <div id="dropZone" class="drop-zone">
        <div class="section-header">
            <span class="section-title">Sub-folders</span>
            <span class="section-count" id="subfolderCount"></span>
        </div>
        <div class="folder-section" id="subfolders"></div>
        <div id="folderControls" class="folder-controls"></div>

        <div class="section-header">
            <span class="section-title">Assets</span>
            <span class="section-count" id="assetCount"></span>
        </div>
        <div id="assetControls" class="asset-controls"></div>
        <div class="asset-grid" id="assetGrid"></div>
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
              limit: 20,
              allLoaded: false
            };            
            
            let assetPagination = {
              folderId: null,
              offset: 0,
              limit: 25,
              allLoaded: false
            };
            
            function loadFolder(folderId) {
              // reset state
              folderPagination = { folderId, offset: 0, limit: 20, allLoaded: false };
              \$('#subfolders').empty();
              \$('#subfolderCount').text('');
            
              // fetch first page (and folder name)
              fetchFolders(folderId, /*append*/ false, /*loadAll*/ false);
            }

            function fetchFolders(folderId, append = false, loadAll = false) {
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
                    \$('#subfolderCount').text(res.total + ' total');
                  } else {
                    \$('#subfolderCount').text(\$('#subfolders .folder-card').length + ' total');
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
                  ? `<div class="thumb">
                       <img src="\${f.thumbnail}" alt="\${safeName}"
                            onerror="this.onerror=null;this.src='/icons/folder-placeholder.svg';">
                     </div>`
                  : `<div class="thumb thumb--placeholder" title="\${safeName}">
                       <span class="emoji">📁</span>
                     </div>`;
            
                const card = $(`
                  <div class="folder-card" title="\${safeName}">
                    \${thumbHtml}
                    <span>\${shortName}</span>
                  </div>
                `);
            
                container.append(card);
              });
              updateFolderButtons();
            }

            // Drag & Drop
            const dropZone = \$('#dropZone');
            dropZone.on('dragover', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropZone.addClass('dragover');
            });
            dropZone.on('dragleave drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropZone.removeClass('dragover');
            });
            dropZone.on('drop', function (e) {
                const files = e.originalEvent.dataTransfer.files;
                if (files.length) {
                    showBanner(`Uploading \${files.length} file(s)...`, 'info');
                    // TODO: upload logic
                }
            });

            // Example load
            loadFolder(1);
            loadAssets(1);
        });
         
        function loadAssets(folderId, showAll = false, offset = 0) {
            const limit = showAll ? 0 : 25;
        
            \$.getJSON('/json/assets/' + folderId, { limit: limit, offset: offset }, function(response) {
                if (response && response.assets) {
                    // Append or replace based on showAll
                    renderAssets(response.assets, offset > 0);
        
                    // Update counter
                    \$('#assetCount').text(\$('.asset-card').length);
        
                    // Disable Load More if no more assets
                    if (response.assets.length < 25 || showAll) {
                        allAssetsLoaded = true;
                        \$('#loadMoreAssets').prop('disabled', true);
                    }
                } else {
                    showBanner('No assets found in this folder.', 'info');
                }
            }).fail(function() {
                showBanner('Error loading assets.', 'error');
            });
        }

        
        function renderAssets(assets, append = false) {
            const \$grid = \$('#assetGrid');
        
            // 🧹 Clear previous items only if not appending
            if (!append) {
                \$grid.empty();
            }
        
            // 🟢 Render each asset card
            assets.forEach(asset => {
                const card = `
                    <div class="asset-card" data-title="\${asset.title || ''}">
                        <img src="\${asset.thumbnail_url || '/images/placeholder.png'}" alt="">
                        <span class="asset-title">\${asset.title || 'Untitled'}</span>
                    </div>
                `;
                \$grid.append(card);
            });
        
            // 🟢 Update asset count
            \$('#assetCount').text(\$('.asset-card').length);
        
            // 🟢 Add Load More / Show All buttons (only once)
            if (!\$('.asset-controls').length) {
                const controls = `
                    <div class="asset-controls">
                        <button id="loadMoreAssets">Load More</button>
                        <button id="showAllAssets">Show All</button>
                    </div>
                `;
                \$grid.after(controls);
            }
        }
        
        function updateAssetButtons() {
          const \$controls = \$('#assetControls');
          \$controls.empty();
        
          if (!assetPagination.allLoaded && assetPagination.folderId) {
            \$controls.append('<button id="loadMoreAssetsBtn">Load More</button>');
            \$controls.append('<button id="showAllAssetsBtn">Show All</button>');
          }
          
        // 🟢 Load More button
        \$(document).on('click', '#loadMoreAssets', function() {
            if (!allAssetsLoaded) {
                assetOffset += assetLimit;
                loadAssets(currentFolderId, false, assetOffset);
            }
        });
        
        // 🟢 Show All button
        \$(document).on('click', '#showAllAssets', function() {
            loadAssets(currentFolderId, true);
        });        }
        
        function scrollToAssetsEnd() {
          const \$panel = \$('#rightPanel');
          const \$assetGrid = \$('#assetGrid');
        
          setTimeout(() => {
            const target =
              \$assetGrid[0].scrollHeight - \$panel.height(); // scroll to bottom
            \$panel.animate({ scrollTop: target }, 800, 'swing'); // slow smooth scroll
          }, 150);
        }
        
JS;
$this->registerJs($js);
?>