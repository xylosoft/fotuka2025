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
        <div class="folder-section" id="subfolders"></div>

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
            
            function loadFolder(folderId, append = false, loadAll = false) {
              // Reset pagination if this is a new folder (or not appending)
              if (folderPagination.folderId !== folderId || !append) {
                folderPagination = { folderId, offset: 0, limit: 20, allLoaded: false };
                $('#subfolders').empty(); // ✅ clear only on first load or when switching folders
              }
            
              const params = {
                offset: folderPagination.offset,
                limit: loadAll ? 0 : folderPagination.limit // 0 = load all
              };
            
              \$.ajax({
                url: '/json/folder/' + folderId,
                type: 'GET',
                data: params,
                dataType: 'json',
                success: function(res) {
                  if (res.ok) {
                    if (res.subfolders.length) {
                      renderSubfolders(res.subfolders, append);
                
                      // ✅ Scroll for both "Load More" and "Show All"
                      if (loadAll || append) {
                        scrollToAssetsPeek(60); // shows ~60px of assets
                      }
                    }
                
                    folderPagination.offset += res.subfolders.length;
                    folderPagination.allLoaded = res.allLoaded;
                
                    \$('#subfolderCount').text(res.total + ' total');
                    updateFolderButtons();
                  }
                },
                error: function() {
                  showBanner('Error loading subfolders', 'error');
                }
              });
            }

            function scrollToAssetsPeek(peekOffset) {
              const \$panel = \$('#rightPanel');
              const \$assetsStart = \$('#assetGrid');
            
              setTimeout(() => {
                const hasPanel = \$panel.length && \$panel[0];
                const panelEl = hasPanel ? \$panel[0] : null;
                const panelCanScroll = panelEl && panelEl.scrollHeight > panelEl.clientHeight;
            
                if (panelCanScroll) {
                  // Convert assets top to panel scroll space
                  const target =
                    \$assetsStart.offset().top      // assets top (page space)
                    - \$panel.offset().top          // minus panel top (page space)
                    + \$panel.scrollTop()           // plus panel current scroll
                    - (peekOffset || 60);           // small extra reveal
            
                  \$panel.animate({ scrollTop: target }, 1000);
                } else {
                  // Fallback: scroll the page (if rightPanel isn't the scroller)
                  const target = \$assetsStart.offset().top - (peekOffset || 60);
                  \$('html, body').animate({ scrollTop: target }, 400);
                }
              }, 100); // let DOM paint first
            }
                        
            function updateFolderButtons() {
              const \$controls = \$('#folderControls');
              \$controls.empty();
            
              // Show buttons only if not all loaded and there are results
              if (!folderPagination.allLoaded && folderPagination.folderId) {
                \$controls.append('<button id="loadMoreBtn">Load More</button>');
                \$controls.append('<button id="showAllBtn">Show All</button>');
              }
            
              $('#loadMoreBtn').on('click', function() {
                loadFolder(folderPagination.folderId, true);
              });
            
              $('#showAllBtn').on('click', function() {
                loadFolder(folderPagination.folderId, false, true);
              });
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
            }


            function renderAssets(assets) {
                const container = \$('#assetGrid');
                container.empty();
                assets.forEach(a => {
                    const img = \$(`<img class="asset" src="\${a}"/>`);
                    container.append(img);
                });
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
        
        
        // ASSET PAGINATION
        let assetPagination = {
          folderId: null,
          offset: 0,
          limit: 25,
          allLoaded: false
        };
        
        function loadAssets(folderId, append = false, loadAll = false) {
          // Reset pagination if new folder or not appending
          if (assetPagination.folderId !== folderId || !append) {
            assetPagination = { folderId, offset: 0, limit: 25, allLoaded: false };
            \$('#assetGrid').empty();
          }
        
          const params = {
            offset: assetPagination.offset,
            limit: loadAll ? 0 : assetPagination.limit
          };
        
          \$.ajax({
            url: '/json/assets/' + folderId,
            type: 'GET',
            data: params,
            dataType: 'json',
            success: function(res) {
              if (res.ok) {
                if (res.assets.length) {
                  renderAssets(res.assets, append);
        
                  assetPagination.offset += res.assets.length;
                  assetPagination.allLoaded = res.allLoaded;
        
                  \$('#assetCount').text(res.total + ' total');
                  updateAssetButtons();
                }
              } else {
                showBanner('Error loading assets', 'error');
              }
            },
            error: function() {
              showBanner('Server error while loading assets', 'error');
            }
          });
        }
        
        function renderAssets(assets, append = false) {
          const container = \$('#assetGrid');
          if (!append) container.empty();
        
          assets.forEach(a => {
            const card = \$(`
              <div class="asset-card" title="\${a.title}">
                <img src="\${a.thumbnail_url}" alt="\${a.title}" onerror="this.onerror=null;this.src='/images/no-thumbnail.png';">
                <span class="asset-title">\${a.title.length > 25 ? a.title.slice(0,25)+'…' : a.title}</span>
              </div>
            `);
            container.append(card);
          });
        }
        
        function updateAssetButtons() {
          const \$controls = \$('#assetControls');
          \$controls.empty();
        
          if (!assetPagination.allLoaded && assetPagination.folderId) {
            \$controls.append('<button id="loadMoreAssetsBtn">Load More</button>');
            \$controls.append('<button id="showAllAssetsBtn">Show All</button>');
          }
        
          \$('#loadMoreAssetsBtn').on('click', function() {
            loadAssets(assetPagination.folderId, true);
            scrollToAssetsEnd();
          });
        
          \$('#showAllAssetsBtn').on('click', function() {
            loadAssets(assetPagination.folderId, false, true);
            scrollToAssetsEnd();
          });
        }
        
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