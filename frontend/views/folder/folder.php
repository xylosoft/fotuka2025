<?php
/** @var yii\web\View $this */
$this->title = 'Fotuka';

\yii\web\JqueryAsset::register($this);
?>
<div class="right-panel">
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
            <span class="section-count">8</span>
        </div>
        <div class="folder-section" id="subfolders"></div>

        <div class="section-header">
            <span class="section-title">Assets</span>
            <span class="section-count">7</span>
        </div>
        <div class="asset-grid" id="assetGrid"></div>

    </div>
</div>
<?php
$js = <<<JS
    console.log(typeof jQuery);

        \$(function () {
            // Toggle folder menu
            \$('#folderMenuBtn').on('click', function (e) {
                e.stopPropagation();
                \$('#folderMenu').toggle();
            });

            \$(document).on('click', function () {
                \$('#folderMenu').hide();
            });

            // Simulated AJAX folder load (replace with your API later)
            function loadFolder(folderId) {
              $('#currentFolderName').text('Folder ' + folderId);
            
              const subfolders = [
                  { id: 1, name: 'Subfolder 1', thumbnail: '/images/sample1.jpg' },
                  { id: 2, name: 'Subfolder 2', thumbnail: '/images/sample2.jpg' },
                  { id: 3, name: 'No Image Folder', thumbnail: null },
                  { id: 4, name: 'Folder 4', thumbnail: null },
                  { id: 1, name: 'Subfolder 3', thumbnail: '/images/sample3.jpg' },
                  { id: 2, name: 'Subfolder 4', thumbnail: '/images/sample4.jpg' },
                  { id: 3, name: 'No Image Folder 2', thumbnail: null },
                  { id: 4, name: 'Folder 7', thumbnail: null }
              ];
            
              const assets = [
                  '/images/sample1.jpg',
                  '/images/sample2.jpg',
                  '/images/sample3.jpg',
                  '/images/sample4.jpg',
                  '/images/sample5.jpg',
                  '/images/sample6.jpg',
                  '/images/sample7.jpg',
              ];
            
              renderSubfolders(subfolders);
              renderAssets(assets);
            }

            function renderSubfolders(folders) {
              const container = \$('#subfolders');
              container.empty();
            
              folders.forEach(f => {
                const safeName = f.name || 'Untitled';
                const short = safeName.length > 18 ? safeName.slice(0, 18) + '…' : safeName;
            
                const thumbHtml = f.thumbnail
                  ? `<div class="thumb">
                       <img src="\${f.thumbnail}"
                            alt="\${safeName}"
                            onerror="this.onerror=null;this.src='/icons/folder-placeholder.svg';">
                     </div>`
                  : `<div class="thumb thumb--placeholder" title="\${safeName}">
                       <span class="emoji">📁</span>
                     </div>`;
            
                const card = $(`
                  <div class="folder-card" title="\${safeName}">
                    \${thumbHtml}
                    <span>\${short}</span>
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
        });
JS;
$this->registerJs($js);
?>
