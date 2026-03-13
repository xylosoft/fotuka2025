<?php

use common\models\WebsiteTemplate;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array|null $folder */
/** @var string $folderName */
/** @var string $folderDefaultSlug */
/** @var common\models\WebsitePublication $publication */
/** @var common\models\WebsiteTemplate|null $template */
/** @var array|null $definition */
/** @var common\models\WebsiteTemplate[] $templates */
/** @var array $assets */

$this->title = 'Publish Folder';

$initialDefinition = $definition ?: ($template ? $template->getDefinitionArray() : WebsiteTemplate::defaultDefinition());
$initialValues = (!$publication->isNewRecord && $publication->values_json)
    ? $publication->getValuesArray()
    : ['components' => []];

$initialTemplateId = !$publication->isNewRecord
    ? (int) $publication->template_id
    : (int) ($template->id ?? 0);

$templateMap = [];
foreach ($templates as $tpl) {
    $templateMap[$tpl->id] = [
        'id' => (int) $tpl->id,
        'name' => $tpl->name,
        'definition' => $tpl->getDefinitionArray(),
    ];
}
?>
<div class="tpl-publish-page">
    <style>
        html, body { background-color: #f4f8fc; }
        .tpl-publish-page {
            color:#10233f;
            min-height:100vh;
            width:auto;
            margin:0;
            padding:24px 0 40px;
        }

        .tpl-publish-shell {
            max-width:1380px;
            margin:0 auto;
            padding:0 24px;
        }
        .flash-wrap .alert { border:none; border-radius:14px; padding:14px 16px; margin-bottom:12px; }

        .tpl-card {
            background:#fff;
            border:1px solid #dbe6f3;
            border-radius:22px;
            box-shadow:0 16px 40px rgba(17,40,74,.06);
            overflow:hidden;
        }

        .tpl-publish-hero {
            margin-bottom:20px;
            padding:22px 24px 20px;
        }
        .tpl-hero-top {
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:20px;
            margin-bottom:18px;
        }
        .tpl-hero-copy h1 { margin:0 0 8px; font-size:30px; font-weight:800; }

        .tpl-hero-settings {
            display:grid;
            grid-template-columns:minmax(220px,280px) minmax(180px,240px) minmax(280px,1fr) auto;
            gap:12px;
            align-items:end;
        }
        .tpl-form-row { margin:0; }
        .tpl-form-row label {
            display:block;
            margin-bottom:6px;
            font-size:12px;
            font-weight:800;
            letter-spacing:.02em;
            color:#25476f;
            text-transform:uppercase;
        }
        .tpl-input,
        .tpl-select {
            width:100%;
            border:1px solid #cfdded;
            border-radius:12px;
            min-height:42px;
            padding:8px 12px;
            font-size:14px;
            color:#10233f;
            background:#fff;
            outline:none;
            box-shadow:inset 0 1px 2px rgba(16,35,63,.03);
        }
        .tpl-input:focus,
        .tpl-select:focus {
            border-color:#2563eb;
            box-shadow:0 0 0 4px rgba(37,99,235,.12);
        }
        .tpl-inline-url {
            display:grid;
            grid-template-columns:auto 1fr;
            gap:10px;
            align-items:center;
        }
        .tpl-inline-url .tpl-domain {
            padding:10px 12px;
            border:1px solid #cfdded;
            border-radius:12px;
            background:#f7fbff;
            color:#25476f;
            font-weight:700;
            white-space:nowrap;
            min-height:42px;
            display:flex;
            align-items:center;
        }
        .tpl-check-inline {
            display:inline-flex;
            align-items:center;
            gap:8px;
            min-height:42px;
            padding:9px 12px;
            border:1px solid #dbe6f3;
            border-radius:12px;
            background:#f9fbfe;
            color:#17345c;
            font-size:13px;
            font-weight:700;
            justify-self:start;
            width:auto;
            max-width:320px;
        }
        .tpl-check-inline input { margin:0; }
        .tpl-password-row {
            min-width:180px;
            max-width:260px;
        }
        .tpl-hero-actions {
            display:flex;
            justify-content:flex-end;
            align-items:end;
        }
        .btn-fotuka {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            background:#2563eb;
            color:#fff;
            border:none;
            border-radius:12px;
            padding:11px 18px;
            min-height:42px;
            font-weight:800;
            text-decoration:none;
            box-shadow:0 14px 26px rgba(37,99,235,.18);
            cursor:pointer;
        }
        .btn-fotuka:hover { color:#fff; text-decoration:none; background:#1d4ed8; }
        .btn-fotuka-secondary { background:#fff; color:#15355e; border:1px solid #d5e2f1; box-shadow:none; }
        .btn-fotuka-secondary:hover { background:#f7fbff; }
        .btn-fotuka-danger { background:#fff4f4; color:#b42318; border:1px solid #fecaca; box-shadow:none; }
        .tpl-pill {
            display:inline-flex;
            align-items:center;
            padding:6px 10px;
            border-radius:999px;
            background:#eff6ff;
            color:#1d4ed8;
            font-size:12px;
            font-weight:800;
        }
        .tpl-muted { color:#6a7d98; }

        .tpl-publish-grid {
            display:grid;
            grid-template-columns:minmax(0,1fr) 420px;
            gap:20px;
            align-items:start;
        }

        .tpl-card-header { padding:18px 22px 12px; border-bottom:1px solid #ebf1f8; }
        .tpl-card-header h2 { margin:0; font-size:20px; font-weight:800; }
        .tpl-card-header p { margin:6px 0 0; color:#69809f; line-height:1.5; }
        .tpl-card-body { padding:18px 22px 22px; }

        .tpl-preview-card { position:sticky; top:20px; }
        .tpl-preview-stage {
            overflow:hidden;
            padding:18px;
            background-color: #FFFFFF;
        }
        .tpl-preview-canvas-wrap {
            width:100%;
            display:flex;
            justify-content:center;
            align-items:flex-start;
            overflow:hidden;
        }
        .tpl-preview-canvas-scale { transform-origin:top center; }
        .tpl-preview-canvas {
            position:relative;
            background:#fff;
            border:1px solid #dbe6f3;
            border-radius:18px;
            overflow:hidden;
            box-shadow:0 20px 44px rgba(17,40,74,.08);
        }

        .tpl-public-item { position:absolute; overflow:hidden; }
        .tpl-public-static,
        .tpl-public-text {
            background:transparent;
            border-radius:14px;
            cursor:pointer;
        }
        .tpl-public-text {
            cursor:pointer;
            outline:2px dashed transparent;
            outline-offset:-6px;
            transition:outline-color .15s ease, background .15s ease;
        }
        .tpl-public-text:hover {
            outline-color:#9bb9df;
            background:rgba(255,255,255,.55);
        }
        .tpl-preview-text-inner {
            width:100%;
            height:100%;
            overflow:auto;
            padding:6px;
            pointer-events:none;
        }
        .tpl-preview-edit-tag {
            position:absolute;
            top:10px;
            right:10px;
            z-index:3;
            padding:4px 8px;
            border-radius:999px;
            background:rgba(37,99,235,.12);
            color:#1d4ed8;
            font-size:11px;
            font-weight:800;
            pointer-events:none;
        }

        .tpl-public-media,
        .tpl-public-gallery {
            border:2px dashed #bdd0e7;
            border-radius:16px;
            background:#f8fbff;
            overflow:hidden;
        }
        .tpl-public-media.is-filled,
        .tpl-public-gallery.is-filled {
            border-style:solid;
            border-color:#dbe6f3;
            background:#fff;
        }
        .tpl-public-media.is-over {
            border-color:#2563eb;
            background:#eef5ff;
            box-shadow:0 0 0 4px rgba(37,99,235,.12);
        }
        .tpl-public-placeholder {
            width:100%;
            height:100%;
            display:flex;
            align-items:center;
            justify-content:center;
            text-align:center;
            color:#6c819d;
            padding:16px;
            font-weight:700;
            line-height:1.5;
            font-size: 24px;
        }
        .tpl-public-placeholder small { display:block; margin-top:6px; font-size:12px; font-weight:700; }
        .tpl-preview-remove {
            position:absolute;
            top:10px;
            right:10px;
            z-index:4;
            width:28px;
            height:28px;
            border:none;
            border-radius:999px;
            background:rgba(180,35,24,.92);
            color:#fff;
            font-size:16px;
            font-weight:800;
            line-height:1;
            cursor:pointer;
            box-shadow:0 8px 16px rgba(0,0,0,.15);
        }

        .tpl-public-media > img,
        .tpl-public-gallery > img {
            width:100%;
            height:100%;
            object-fit:cover;
            display:block;
        }
        .tpl-preview-thumb-remove {
            position:absolute;
            top:5px;
            right:5px;
            z-index:2;
            width:20px;
            height:20px;
            border:none;
            border-radius:999px;
            background:rgba(180,35,24,.92);
            color:#fff;
            font-size:12px;
            font-weight:800;
            line-height:1;
            cursor:pointer;
        }
        .tpl-preview-count {
            position:absolute;
            top:10px;
            left:10px;
            z-index:3;
            padding:4px 8px;
            border-radius:999px;
            background:rgba(37,99,235,.12);
            color:#1d4ed8;
            font-size:11px;
            font-weight:800;
        }

        .tpl-preview-carousel-grid {
            width:100%;
            height:100%;
            display:grid;
            grid-template-columns:repeat(6,minmax(0,1fr));
            gap:8px;
            padding:8px 8px 40px;
            overflow:auto;
            align-content:start;
        }

        .tpl-preview-thumb {
            position:relative;
            border-radius:10px;
            overflow:hidden;
            aspect-ratio:1 / 1;
            min-height:0;
            background:#edf3fb;
        }

        .tpl-preview-thumb img {
            width:100%;
            height:100%;
            object-fit:cover;
            display:block;
        }

        .tpl-preview-gallery-summary {
            position:absolute;
            inset:0;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            text-align:center;
            padding:16px 22px 14px;
            gap:8px;
            color:#4b6485;
        }

        .tpl-preview-gallery-summary-subtitle {
            max-width:560px;
            font-size:24px;
            line-height:1.45;
            color:#6b7f99;
            font-weight:700;
        }

        .tpl-preview-gallery-summary-subtitle small {
            display:block;
            margin-top:8px;
            font-size:12px;
            font-weight:800;
            color:#527199;
        }

        .tpl-asset-list {
            display:grid;
            grid-template-columns:repeat(3,minmax(0,1fr));
            gap:10px;
        }
        .tpl-asset-card {
            border:1px solid #dbe6f3;
            border-radius:14px;
            background:#fff;
            overflow:hidden;
            cursor:grab;
            transition:transform .14s ease, box-shadow .14s ease, border-color .14s ease;
            user-select:none;
            -webkit-user-select:none;
        }
        .tpl-asset-card:active {
            cursor:grabbing;
        }
        .tpl-asset-card:hover { transform:translateY(-1px); box-shadow:0 14px 26px rgba(16,35,63,.08); }
        .tpl-asset-card.is-selected { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
        .tpl-asset-thumb {
            height:110px;
            pointer-events:none;
        }
        .tpl-asset-thumb img {
            width:100%;
            height:100%;
            object-fit:cover;
            display:block;
            pointer-events:none;
            user-select:none;
            -webkit-user-drag:none;
        }
        .tpl-asset-empty {
            font-size:12px;
            color:#6e83a0;
            padding:12px;
            text-align:center;
            line-height:1.35;
        }
        .tpl-lightbox {
            position:fixed;
            inset:0;
            background:rgba(6,16,29,.84);
            z-index:9999;
            display:none;
            align-items:center;
            justify-content:center;
            padding:42px;
        }
        .tpl-lightbox.is-open { display:flex; }
        .tpl-lightbox-dialog {
            position:relative;
            width:min(1100px,92vw);
            height:min(80vh,780px);
            border-radius:24px;
            background:#09121f;
            box-shadow:0 24px 70px rgba(0,0,0,.35);
            overflow:hidden;
        }
        .tpl-lightbox-dialog img {
            width:100%;
            height:100%;
            object-fit:contain;
            display:block;
            background:#09121f;
        }
        .tpl-lightbox-btn {
            position:absolute;
            top:18px;
            width:44px;
            height:44px;
            border:none;
            border-radius:999px;
            background:rgba(255,255,255,.13);
            color:#fff;
            font-size:20px;
            font-weight:800;
            cursor:pointer;
            backdrop-filter:blur(10px);
        }
        .tpl-lightbox-btn.close { right:18px; }
        .tpl-lightbox-btn.prev { top:50%; left:18px; transform:translateY(-50%); }
        .tpl-lightbox-btn.next { top:50%; right:18px; transform:translateY(-50%); }
        .tpl-lightbox-caption {
            position:absolute;
            left:22px;
            right:82px;
            bottom:18px;
            padding:12px 14px;
            border-radius:14px;
            background:rgba(255,255,255,.12);
            color:#fff;
            font-weight:700;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .tpl-empty-state {
            padding:22px;
            border:1px dashed #c8d7ea;
            border-radius:18px;
            text-align:center;
            color:#6b819d;
            background:#fafcff;
        }
        .tpl-public-media > img,
        .tpl-public-media > .tpl-public-placeholder,
        .tpl-public-media > .tpl-preview-count,
        .tpl-public-media > .tpl-preview-carousel-grid,
        .tpl-public-gallery > .tpl-public-placeholder,
        .tpl-public-gallery > .tpl-preview-gallery-summary {
            pointer-events:none;
        }

        .tpl-preview-remove,
        .tpl-preview-thumb-remove {
            pointer-events:auto;
        }

        @media (max-width:1400px) {
            .tpl-publish-grid { grid-template-columns:minmax(0,1fr) 380px; }
            .tpl-hero-settings { grid-template-columns:repeat(2,minmax(240px,1fr)); }
            .tpl-hero-actions { justify-content:flex-start; }
        }
        @media (max-width:1180px) {
            .tpl-publish-grid { grid-template-columns:1fr; }
            .tpl-preview-card { position:relative; top:0; }
        }
        @media (max-width:900px) {
            .tpl-publish-shell { padding:0 16px; }
            .tpl-publish-hero { padding:18px; }
            .tpl-hero-top { flex-direction:column; }
            .tpl-hero-settings { grid-template-columns:1fr; }
            .tpl-asset-list { grid-template-columns:repeat(2,minmax(0,1fr)); }
        }
        .tpl-public-media.is-over {
            border-color:#2563eb;
            background:#eef5ff;
            box-shadow:0 0 0 4px rgba(37,99,235,.12);
        }
    </style>

    <div class="tpl-publish-shell">
        <a class="breadcrum-link" href="/folders">Folders</a>
        &nbsp;&nbsp;/&nbsp;&nbsp;
        <span class="breadcrum-static">Folder Publishing</span>
        <div class="flash-wrap">
            <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
                <div class="alert alert-<?= Html::encode($type) ?>"><?= Html::encode($message) ?></div>
            <?php endforeach; ?>
        </div>

        <form method="post" id="publishForm" action="<?= Url::to(['publish', 'id' => (int)($folder['id'] ?? Yii::$app->request->get('id'))]) ?>">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
            <?= Html::hiddenInput('WebsitePublication[values_json]', $publication->values_json ?: Json::encode($initialValues, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ['id' => 'publicationValuesJson']) ?>

            <div class="tpl-card tpl-publish-hero">
                <div class="tpl-hero-top">
                    <div class="tpl-hero-copy">
                        <h1>Publish “<?= Html::encode($folderName) ?>”</h1>
                    </div>
                </div>

                <div class="tpl-hero-settings">
                    <div class="tpl-form-row">
                        <label for="publicationTemplateId">Template</label>
                        <select class="tpl-select" id="publicationTemplateId" name="WebsitePublication[template_id]">
                            <option value="">Select a template…</option>
                            <?php foreach ($templates as $tpl): ?>
                                <option value="<?= (int) $tpl->id ?>" <?= $initialTemplateId === (int) $tpl->id ? 'selected' : '' ?>><?= Html::encode($tpl->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="tpl-form-row">
                        <label for="publicationPageTitle">Page Title</label>
                        <input class="tpl-input" type="text" id="publicationPageTitle" name="WebsitePublication[page_title]" value="<?= Html::encode($publication->page_title ?: $folderName) ?>" maxlength="255">
                    </div>

                    <div class="tpl-form-row">
                        <label for="publicationUri">Public URI</label>
                        <div class="tpl-inline-url">
                            <div class="tpl-domain">https://fotuka.com/page/</div>
                            <input class="tpl-input" type="text" id="publicationUri" name="WebsitePublication[uri]" value="<?= Html::encode($publication->uri ?: $folderDefaultSlug) ?>" maxlength="255" placeholder="<?= Html::encode($folderDefaultSlug) ?>">
                        </div>
                    </div>

                    <div class="tpl-hero-actions">
                        <button type="submit" class="btn-fotuka">Publish Folder</button>
                    </div>

                    <label class="tpl-check-inline">
                        <input type="checkbox" id="publicationProtected" name="WebsitePublication[is_password_protected]" value="1" <?= (int) $publication->is_password_protected === 1 ? 'checked' : '' ?>>
                        <span>Password protect this page</span>
                    </label>

                    <div class="tpl-form-row tpl-password-row" id="passwordRow" style="<?= (int) $publication->is_password_protected === 1 ? '' : 'display:none;' ?>">
                        <label for="publicationPassword">Password</label>
                        <input class="tpl-input" type="text" id="publicationPassword" name="WebsitePublication[plain_password]" value="" placeholder="<?= $publication->isNewRecord ? 'Enter a password' : 'Leave blank to keep current password' ?>">
                    </div>

                    <label class="tpl-check-inline">
                        <input type="checkbox" name="WebsitePublication[allow_download_all]" value="1" <?= (int) $publication->allow_download_all === 1 ? 'checked' : '' ?>>
                        <span>Show “Download All” button</span>
                    </label>

                    <div></div>
                </div>
            </div>

            <div class="tpl-publish-grid">
                <div class="tpl-main-col">
                    <div class="tpl-card tpl-preview-card">
                        <div class="tpl-card-header">
                            <h2>Live Preview</h2>
                            <p>Drop onto images from the Folder Assets to the desired component.<br/> Click editable text blocks to open the editor.</p>
                        </div>
                        <div class="tpl-preview-stage" id="previewStage">
                            <div id="previewCanvasWrap" class="tpl-preview-canvas-wrap">
                                <div id="previewScale" class="tpl-preview-canvas-scale">
                                    <div id="publishPreviewCanvas" class="tpl-preview-canvas"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tpl-right-col">
                    <div class="tpl-card">
                        <div class="tpl-card-header">
                            <h2>Folder Assets</h2>
                        </div>
                        <div class="tpl-card-body">
                            <div id="assetGallery" class="tpl-asset-list"></div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="publishTextModal" class="tpl-lightbox" style="background:rgba(10,18,31,.68);">
        <div class="tpl-lightbox-dialog" style="background:#fff; height:min(86vh,860px); width:min(1100px,96vw);">
            <button type="button" class="tpl-lightbox-btn close" id="closeTextModal" style="color:#16355c; background:rgba(11,38,73,.08);">✕</button>
            <div style="padding:24px 26px 18px; border-bottom:1px solid #e7eef8;">
                <div class="tpl-pill" id="textModalType">Publish-Time Text</div>
                <h2 id="textModalTitle" style="margin:12px 0 0; font-size:28px; font-weight:800; color:#14345c;"></h2>
                <p id="textModalSubtitle" class="tpl-muted" style="margin:8px 0 0;"></p>
            </div>
            <div style="padding:20px 26px 24px; height:calc(100% - 120px); display:flex; flex-direction:column; gap:16px;">
                <textarea id="publishRichTextEditor"></textarea>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn-fotuka btn-fotuka-secondary" id="cancelTextModal">Cancel</button>
                    <button type="button" class="btn-fotuka" id="saveTextModal">Save Text</button>
                </div>
            </div>
        </div>
    </div>

    <div id="assetLightbox" class="tpl-lightbox">
        <div class="tpl-lightbox-dialog">
            <button type="button" class="tpl-lightbox-btn close" id="lightboxClose">✕</button>
            <button type="button" class="tpl-lightbox-btn prev" id="lightboxPrev">‹</button>
            <button type="button" class="tpl-lightbox-btn next" id="lightboxNext">›</button>
            <img id="lightboxImage" src="" alt="">
            <div id="lightboxCaption" class="tpl-lightbox-caption"></div>
        </div>
    </div>

    <script src="https://cdn.tiny.cloud/1/cbcqlkpvavpfb1f48f22qrybc82x9c8rv604z1jupes12uub/tinymce/6/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>

    <script>
        function dndLog(label, data) {
            console.log('[PUBLISH DND] ' + label, data);
        }

        (function () {
            const templates = <?= Json::htmlEncode($templateMap) ?>;
            const assets = <?= Json::htmlEncode(array_values($assets)) ?>;
            const initialDefinition = <?= Json::htmlEncode($initialDefinition) ?>;
            const state = {
                selectedTemplateId: <?= Json::htmlEncode((string) $initialTemplateId) ?>,
                values: <?= Json::htmlEncode($initialValues) ?>,
                selectedAssetId: null,
                draggingAssetId: null,
                draggingAsset: null,
                previewScale: 1,
                textEditorField: null,
                lightboxItems: assets.filter(a => (a.preview_url || a.thumbnail_url)),
                lightboxIndex: 0
            };
            const templateSelect = document.getElementById('publicationTemplateId');
            const valuesJsonInput = document.getElementById('publicationValuesJson');
            const assetGallery = document.getElementById('assetGallery');
            const publishPreviewCanvas = document.getElementById('publishPreviewCanvas');
            const previewScale = document.getElementById('previewScale');
            const previewStage = document.getElementById('previewStage');
            const previewCanvasWrap = document.getElementById('previewCanvasWrap');
            const protectedCheckbox = document.getElementById('publicationProtected');
            const passwordRow = document.getElementById('passwordRow');
            const textModal = document.getElementById('publishTextModal');
            const closeTextModal = document.getElementById('closeTextModal');
            const cancelTextModal = document.getElementById('cancelTextModal');
            const saveTextModal = document.getElementById('saveTextModal');
            const textModalTitle = document.getElementById('textModalTitle');
            const textModalSubtitle = document.getElementById('textModalSubtitle');
            const lightbox = document.getElementById('assetLightbox');
            const lightboxImage = document.getElementById('lightboxImage');
            const lightboxCaption = document.getElementById('lightboxCaption');
            const lightboxClose = document.getElementById('lightboxClose');
            const lightboxPrev = document.getElementById('lightboxPrev');
            const lightboxNext = document.getElementById('lightboxNext');
            const PREVIEW_GRID_COLS = 6;
            const PREVIEW_GRID_GAP = 8;
            const PREVIEW_GRID_PAD_X = 8;
            const PREVIEW_CAROUSEL_PAD_TOP = 42;
            const PREVIEW_GALLERY_PAD_TOP = 8;
            const PREVIEW_GRID_PAD_BOTTOM = 44;
            const PREVIEW_ROW_SPACING = 18;
            const PREVIEW_GALLERY_FIXED_HEIGHT = 450;

            function escapeHtml(value) {
                return String(value ?? '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
            }

            function slugify(value) {
                return String(value || '')
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9-_]+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
            }

            function deepClone(obj) {
                return JSON.parse(JSON.stringify(obj));
            }

            function getSelectedTemplate() {
                return templates[state.selectedTemplateId] || null;
            }

            function getDefinition() {
                const tpl = getSelectedTemplate();

                if (tpl && tpl.definition) {
                    state.definition = deepClone(tpl.definition);
                    return state.definition;
                }

                if (state.definition && Array.isArray(state.definition.components)) {
                    return deepClone(state.definition);
                }

                return {
                    page: {
                        canvas_width: 1200,
                        canvas_min_height: 1400,
                        background_color: '#ffffff',
                        button_color: '#2563eb'
                    },
                    components: []
                };
            }

            function componentKey(component) {
                return String(component && component.id ? component.id : '');
            }

            function componentLabel(component) {
                return component.label || component.field_name || component.type || 'component';
            }

            function getComponentValue(componentOrId) {
                ensureValues();
                const key = typeof componentOrId === 'string' ? componentOrId : componentKey(componentOrId);
                return key && state.values.components[key] ? state.values.components[key] : null;
            }

            function setComponentValue(componentOrId, payload) {
                ensureValues();
                const key = typeof componentOrId === 'string' ? componentOrId : componentKey(componentOrId);
                if (!key) return;
                state.values.components[key] = payload;
            }

            function deleteComponentValue(componentOrId) {
                ensureValues();
                const key = typeof componentOrId === 'string' ? componentOrId : componentKey(componentOrId);
                if (!key) return;
                delete state.values.components[key];
            }

            function isLikelyImage(asset) {
                const type = String(asset.file_type || asset.mime_type || '').toLowerCase();
                if (!type) return !!(asset.preview_url || asset.thumbnail_url);
                return type.indexOf('image/') === 0;
            }

            function getAssetKey(asset) {
                if (!asset) return '';
                return String(asset.asset_id ?? asset.id ?? '');
            }

            function normalizeAsset(asset) {
                if (!asset) return null;

                const key = asset.asset_id ?? asset.id ?? null;

                return {
                    asset_id: key,
                    title: asset.title || asset.filename || ('Asset #' + (key || '')),
                    filename: asset.filename || '',
                    preview_url: asset.preview_url || asset.thumbnail_url || '',
                    thumbnail_url: asset.thumbnail_url || asset.preview_url || '',
                    file_type: asset.file_type || asset.mime_type || ''
                };
            }

            function assetById(assetId) {
                return assets.find(a => String(a.asset_id ?? a.id ?? '') === String(assetId)) || null;
            }

            function galleryAssets() {
                return assets
                    .filter(isLikelyImage)
                    .map(normalizeAsset)
                    .filter(Boolean)
                    .filter(a => a.preview_url || a.thumbnail_url);
            }

            function ensureValues() {
                if (!state.values || typeof state.values !== 'object' || Array.isArray(state.values)) {
                    state.values = {};
                }

                if (!state.values.components || typeof state.values.components !== 'object' || Array.isArray(state.values.components)) {
                    const legacy = state.values;
                    const migrated = {};
                    const definition = getDefinition();
                    const components = Array.isArray(definition.components) ? definition.components : [];

                    const legacyDynamicText = legacy.dynamic_text && typeof legacy.dynamic_text === 'object' && !Array.isArray(legacy.dynamic_text)
                        ? legacy.dynamic_text
                        : {};

                    const legacyImage = legacy.image && typeof legacy.image === 'object' && !Array.isArray(legacy.image)
                        ? legacy.image
                        : {};

                    const legacyCarousel = legacy.carousel && typeof legacy.carousel === 'object' && !Array.isArray(legacy.carousel)
                        ? legacy.carousel
                        : {};

                    const legacyGallery = legacy.gallery && typeof legacy.gallery === 'object' && !Array.isArray(legacy.gallery)
                        ? legacy.gallery
                        : {};

                    components.forEach(component => {
                        const key = componentKey(component);
                        const field = String(component.field_name || '');

                        if (!key || !field) {
                            return;
                        }

                        if (component.type === 'dynamic_text' && legacyDynamicText[field]) {
                            migrated[key] = {
                                type: 'dynamic_text',
                                html: legacyDynamicText[field].html || component.default_html || '<p></p>'
                            };
                            return;
                        }

                        if (component.type === 'image' && legacyImage[field]) {
                            migrated[key] = {
                                type: 'image',
                                asset: deepClone(legacyImage[field])
                            };
                            return;
                        }

                        if (component.type === 'carousel' && legacyCarousel[field]) {
                            migrated[key] = {
                                type: 'carousel',
                                items: Array.isArray(legacyCarousel[field].items)
                                    ? deepClone(legacyCarousel[field].items.filter(Boolean))
                                    : []
                            };
                            return;
                        }

                        if (component.type === 'gallery' && legacyGallery[field]) {
                            migrated[key] = {
                                type: 'gallery',
                                auto_folder_gallery: legacyGallery[field].auto_folder_gallery ? 1 : 0,
                                items: Array.isArray(legacyGallery[field].items)
                                    ? deepClone(legacyGallery[field].items.filter(Boolean))
                                    : []
                            };
                        }
                    });

                    state.values = {
                        components: migrated
                    };
                }

                syncAutoGalleryValues();
            }

            function syncAutoGalleryValues() {
                if (syncingAutoGalleryValues) {
                    return;
                }

                syncingAutoGalleryValues = true;

                try {
                    if (!state.values || typeof state.values !== 'object' || Array.isArray(state.values)) {
                        state.values = {};
                    }

                    if (!state.values.components || typeof state.values.components !== 'object' || Array.isArray(state.values.components)) {
                        state.values.components = {};
                    }

                    const definition = getDefinition();
                    const galleryComponents = Array.isArray(definition.components) ? definition.components.filter(component => component.type === 'gallery'): [];
                    const items = galleryAssets();

                    galleryComponents.forEach(component => {
                        const key = componentKey(component);
                        if (!key) return;

                        state.values.components[key] = {
                            type: 'gallery',
                            auto_folder_gallery: 1,
                            items: deepClone(items)
                        };
                    });
                } finally {
                    syncingAutoGalleryValues = false;
                }
            }

            function syncHidden() {
                valuesJsonInput.value = JSON.stringify(state.values);
            }


            function openLightboxByAssetId(assetId) {
                const index = state.lightboxItems.findIndex(item => String(item.asset_id) === String(assetId));
                if (index === -1) return;
                state.lightboxIndex = index;
                renderLightbox();
                lightbox.classList.add('is-open');
            }

            function renderLightbox() {
                const item = state.lightboxItems[state.lightboxIndex];
                if (!item) return;
                lightboxImage.src = item.preview_url || item.thumbnail_url || '';
                lightboxCaption.textContent = item.title || item.filename || '';
            }

            function closeLightbox() {
                lightbox.classList.remove('is-open');
            }

            function previousLightbox() {
                if (!state.lightboxItems.length) return;
                state.lightboxIndex = (state.lightboxIndex - 1 + state.lightboxItems.length) % state.lightboxItems.length;
                renderLightbox();
            }

            function nextLightbox() {
                if (!state.lightboxItems.length) return;
                state.lightboxIndex = (state.lightboxIndex + 1) % state.lightboxItems.length;
                renderLightbox();
            }

            function assetCardMarkup(asset) {
                const thumbUrl = asset.thumbnail_url || asset.preview_url || '';
                const assetKey = getAssetKey(asset);

                return `
                    <div class="tpl-asset-card ${String(state.selectedAssetId) === assetKey ? 'is-selected' : ''}" data-asset-id="${escapeHtml(assetKey)}" draggable="true">
                        <div class="tpl-asset-thumb">
                            ${thumbUrl ? `<img src="${escapeHtml(thumbUrl)}" alt="" draggable="false">` : `<div class="tpl-asset-empty">Thumbnail not available</div>`}
                        </div>
                    </div>`;
            }

            function renderAssetGallery() {
                if (!assets.length) {
                    assetGallery.innerHTML = '<div class="tpl-empty-state" style="grid-column:1 / -1;">No assets were found for this folder.</div>';
                    return;
                }

                assetGallery.innerHTML = assets.map(assetCardMarkup).join('');

                assetGallery.querySelectorAll('.tpl-asset-card').forEach(card => {
                    const assetId = card.getAttribute('data-asset-id');
                    const asset = assetById(assetId);

                    card.setAttribute('draggable', 'true');

                    card.addEventListener('dragstart', e => {
                        state.draggingAssetId = String(assetId);
                        state.draggingAsset = normalizeAsset(asset);
                        state.selectedAssetId = String(assetId);

                        e.dataTransfer.clearData();
                        e.dataTransfer.setData('text/plain', String(assetId));
                        e.dataTransfer.effectAllowed = 'copy';

                        dndLog('dragstart', {
                            assetId: String(assetId),
                            asset: asset,
                            normalized: normalizeAsset(asset),
                            dataTransferText: e.dataTransfer.getData('text/plain')
                        });
                    });

                    card.addEventListener('dragend', () => {
                        dndLog('dragend', {
                            draggingAssetId: state.draggingAssetId,
                            draggingAsset: state.draggingAsset,
                            selectedAssetId: state.selectedAssetId
                        });

                        setTimeout(() => {
                            state.draggingAssetId = null;
                            state.draggingAsset = null;
                        }, 0);
                    });

                    card.addEventListener('click', () => {
                        state.selectedAssetId = assetId;
                        renderAssetGallery();

                        if (asset && (asset.preview_url || asset.thumbnail_url)) {
                            openLightboxByAssetId(assetId);
                        }
                    });
                });
            }

            function attachAssetDropZone(zone, onAssign) {
                zone.addEventListener('dragenter', e => {
                    e.preventDefault();
                    zone.classList.add('is-over');

                    dndLog('ZONE dragenter', {
                        componentId: zone.getAttribute('data-component-id'),
                        zoneClass: zone.className,
                        targetClass: e.target && e.target.className
                    });
                });

                zone.addEventListener('dragover', e => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'copy';
                    zone.classList.add('is-over');

                    dndLog('ZONE dragover', {
                        componentId: zone.getAttribute('data-component-id'),
                        zoneClass: zone.className,
                        dataTransferText: e.dataTransfer.getData('text/plain'),
                        draggingAssetId: state.draggingAssetId,
                        draggingAsset: state.draggingAsset,
                        selectedAssetId: state.selectedAssetId
                    });
                });

                zone.addEventListener('dragleave', e => {
                    if (!zone.contains(e.relatedTarget)) {
                        zone.classList.remove('is-over');
                    }
                });

                zone.addEventListener('drop', e => {
                    e.preventDefault();
                    e.stopPropagation();
                    zone.classList.remove('is-over');

                    const rawDataTransfer = e.dataTransfer.getData('text/plain');
                    const assetId = rawDataTransfer || state.draggingAssetId || state.selectedAssetId;
                    const assetFromId = assetById(assetId);
                    const droppedAsset = state.draggingAsset || normalizeAsset(assetFromId);

                    dndLog('ZONE drop BEFORE assign', {
                        componentId: zone.getAttribute('data-component-id'),
                        rawDataTransfer: rawDataTransfer,
                        resolvedAssetId: assetId,
                        assetFromId: assetFromId,
                        droppedAsset: droppedAsset,
                        targetClass: e.target && e.target.className,
                        currentTargetClass: e.currentTarget && e.currentTarget.className
                    });

                    state.draggingAssetId = null;
                    state.draggingAsset = null;

                    if (!droppedAsset) {
                        dndLog('ZONE drop ABORT no droppedAsset', {
                            componentId: zone.getAttribute('data-component-id')
                        });
                        return;
                    }

                    onAssign(droppedAsset);

                    dndLog('ZONE drop AFTER assign callback', {
                        componentId: zone.getAttribute('data-component-id'),
                        componentValue: getComponentValue(zone.getAttribute('data-component-id'))
                    });
                });

                zone.addEventListener('click', e => {
                    if (e.target.closest('.tpl-preview-remove') || e.target.closest('.tpl-preview-thumb-remove')) return;
                    if (!state.selectedAssetId) return;

                    const asset = assetById(state.selectedAssetId);

                    dndLog('ZONE click assign', {
                        componentId: zone.getAttribute('data-component-id'),
                        selectedAssetId: state.selectedAssetId,
                        asset: asset
                    });

                    if (asset) onAssign(normalizeAsset(asset));
                });
            }

            let activePreviewDropZone = null;
            let syncingAutoGalleryValues = false;

            function clearActivePreviewDropZone() {
                if (activePreviewDropZone) {
                    activePreviewDropZone.classList.remove('is-over');
                    activePreviewDropZone = null;
                }
            }

            function findPreviewDropZoneFromPoint(clientX, clientY) {
                const el = document.elementFromPoint(clientX, clientY);
                if (!el) return null;
                return el.closest('.js-preview-drop-single, .js-preview-drop-carousel');
            }

            function getDraggedAssetFromEvent(e) {
                const assetId =
                    (e.dataTransfer && e.dataTransfer.getData('text/plain')) ||
                    state.draggingAssetId ||
                    state.selectedAssetId;

                return state.draggingAsset || normalizeAsset(assetById(assetId));
            }

            function bindPreviewCanvasDnD() {
                if (publishPreviewCanvas.dataset.dndBound === '1') return;
                publishPreviewCanvas.dataset.dndBound = '1';

                publishPreviewCanvas.addEventListener('dragover', e => {
                    e.preventDefault();
                if (e.dataTransfer) e.dataTransfer.dropEffect = 'copy';

                const zone = findPreviewDropZoneFromPoint(e.clientX, e.clientY);

                dndLog('CANVAS dragover', {
                    clientX: e.clientX,
                    clientY: e.clientY,
                    foundZoneComponentId: zone ? zone.getAttribute('data-component-id') : null,
                    foundZoneClass: zone ? zone.className : null,
                    draggingAssetId: state.draggingAssetId,
                    draggingAsset: state.draggingAsset,
                    selectedAssetId: state.selectedAssetId
                });

                if (zone !== activePreviewDropZone) {
                    clearActivePreviewDropZone();
                    if (zone) {
                        zone.classList.add('is-over');
                        activePreviewDropZone = zone;
                    }
                }
            });

                publishPreviewCanvas.addEventListener('dragleave', e => {
                    if (!publishPreviewCanvas.contains(e.relatedTarget)) {
                    clearActivePreviewDropZone();
                }
            });

                publishPreviewCanvas.addEventListener('drop', e => {
                    e.preventDefault();
                    e.stopPropagation();

                    const zone = findPreviewDropZoneFromPoint(e.clientX, e.clientY) || activePreviewDropZone;
                    const droppedAsset = getDraggedAssetFromEvent(e);

                    dndLog('CANVAS drop BEFORE assign', {
                        clientX: e.clientX,
                        clientY: e.clientY,
                        zoneComponentId: zone ? zone.getAttribute('data-component-id') : null,
                        zoneClass: zone ? zone.className : null,
                        droppedAsset: droppedAsset,
                        draggingAssetId: state.draggingAssetId,
                        draggingAsset: state.draggingAsset,
                        selectedAssetId: state.selectedAssetId
                    });

                    clearActivePreviewDropZone();
                    state.draggingAssetId = null;
                    state.draggingAsset = null;

                    if (!zone || !droppedAsset) {
                        dndLog('CANVAS drop ABORT', {
                            zoneExists: !!zone,
                            droppedAssetExists: !!droppedAsset
                        });
                        return;
                    }

                    const componentId = zone.getAttribute('data-component-id');
                    if (!componentId) {
                        dndLog('CANVAS drop ABORT no componentId', {});
                        return;
                    }

                    if (zone.classList.contains('js-preview-drop-single')) {
                        setComponentValue(componentId, {
                            type: 'image',
                            asset: deepClone(droppedAsset)
                        });
                    } else if (zone.classList.contains('js-preview-drop-carousel')) {
                        const existing = getComponentValue(componentId) || { type: 'carousel', items: [] };
                        const items = Array.isArray(existing.items) ? existing.items.slice() : [];
                        items.push(deepClone(droppedAsset));

                        setComponentValue(componentId, {
                            type: 'carousel',
                            items: items
                        });
                    }

                    dndLog('CANVAS drop AFTER assign BEFORE render', {
                        componentId: componentId,
                        componentValue: getComponentValue(componentId)
                    });

                    state.selectedAssetId = String(droppedAsset.asset_id || '');
                    syncHidden();
                    renderAssetGallery();
                    renderPreview();

                    dndLog('CANVAS drop AFTER render', {
                        componentId: componentId,
                        hiddenJson: valuesJsonInput.value,
                        previewHtmlSnippet: publishPreviewCanvas.innerHTML.slice(0, 400)
                    });
                });
            }

            function getPreviewItemsForComponent(component) {
                const bucket = getComponentValue(componentKey(component)) || null;
                if (!bucket) return [];

                if (component.type === 'carousel' || component.type === 'gallery') {
                    return Array.isArray(bucket.items) ? bucket.items.filter(Boolean) : [];
                }

                return [];
            }

            function getExpandedPreviewHeight(component) {
                const baseHeight = Math.max(80, Math.round(component.h || 180));

                if (component.type === 'gallery') {
                    return PREVIEW_GALLERY_FIXED_HEIGHT;
                }

                if (component.type !== 'carousel') {
                    return baseHeight;
                }

                const items = getPreviewItemsForComponent(component);
                if (!items.length) {
                    return baseHeight;
                }

                const width = Math.max(120, Math.round(component.w || 300));
                const columns = Math.min(PREVIEW_GRID_COLS, Math.max(1, items.length));
                const innerWidth = width - (PREVIEW_GRID_PAD_X * 2) - ((columns - 1) * PREVIEW_GRID_GAP);
                const thumbSize = Math.max(44, Math.floor(innerWidth / columns));
                const rows = Math.ceil(items.length / PREVIEW_GRID_COLS);

                const topPad = PREVIEW_CAROUSEL_PAD_TOP;
                const gridHeight = (rows * thumbSize) + (Math.max(0, rows - 1) * PREVIEW_GRID_GAP);
                const neededHeight = topPad + gridHeight + PREVIEW_GRID_PAD_BOTTOM + 8;

                return Math.max(baseHeight, neededHeight);
            }

            function buildPreviewLayout(definition) {
                const source = Array.isArray(definition.components) ? definition.components.map(deepClone) : [];
                const rows = new Map();

                source.forEach(component => {
                    const rowY = Math.round(component.y || 0);
                    if (!rows.has(rowY)) {
                        rows.set(rowY, []);
                    }
                    rows.get(rowY).push(component);
                });

                const sortedRowYs = Array.from(rows.keys()).sort((a, b) => a - b);
                const laidOut = [];
                let carry = 0;
                let maxBottom = 0;

                sortedRowYs.forEach(rowY => {
                    const rowComponents = rows.get(rowY).sort((a, b) => {
                        const ax = Math.round(a.x || 0);
                        const bx = Math.round(b.x || 0);
                        if (ax !== bx) return ax - bx;
                        return Math.round((a.z || 0) - (b.z || 0));
                    });

                    const previewY = rowY + carry;
                    let originalRowBottom = rowY;
                    let previewRowBottom = previewY;

                    rowComponents.forEach(component => {
                        component.preview_x = Math.round(component.x || 0);
                        component.preview_y = previewY;
                        component.preview_w = Math.round(component.w || 300);
                        component.preview_h = getExpandedPreviewHeight(component);

                        originalRowBottom = Math.max(originalRowBottom, rowY + Math.round(component.h || 180));
                        previewRowBottom = Math.max(previewRowBottom, previewY + component.preview_h);

                        laidOut.push(component);
                    });

                    const rowExtra = Math.max(0, previewRowBottom - (originalRowBottom + carry));
                    if (rowExtra > 0) {
                        carry += rowExtra + PREVIEW_ROW_SPACING;
                    }

                    maxBottom = Math.max(maxBottom, previewRowBottom);
                });

                laidOut.sort((a, b) => Math.round((a.z || 0) - (b.z || 0)));

                return {
                    components: laidOut,
                    bottom: maxBottom
                };
            }

            function previewBoxStyle(component) {
                const left = Math.round(component.preview_x ?? component.x ?? 0);
                const top = Math.round(component.preview_y ?? component.y ?? 0);
                const width = Math.round(component.preview_w ?? component.w ?? 300);
                const height = Math.round(component.preview_h ?? component.h ?? 180);
                const zIndex = Math.round(component.z || 1);

                return `left:${left}px;top:${top}px;width:${width}px;height:${height}px;z-index:${zIndex};`;
            }

            function previewStaticTextMarkup(component) {
                return `<div class="tpl-public-item tpl-public-static" style="${previewBoxStyle(component)}">${component.html || '<p></p>'}</div>`;
            }

            function previewDynamicTextMarkup(component) {
                const componentId = componentKey(component);
                const bucket = getComponentValue(componentId) || {};
                const html = bucket.html || component.default_html || '<p></p>';

                return `
                    <div class="tpl-public-item tpl-public-text js-preview-edit-text" data-component-id="${escapeHtml(componentId)}" style="${previewBoxStyle(component)}" title="Double-click to edit">
                        <div class="tpl-preview-edit-tag">Double-click to edit</div>
                        <div class="tpl-preview-text-inner">${html}</div>
                    </div>`;
            }

            function previewImageMarkup(component) {
                const componentId = componentKey(component);
                const bucket = getComponentValue(componentId) || null;
                const asset = bucket && bucket.asset ? bucket.asset : null;
                const imageUrl = asset ? (asset.preview_url || asset.thumbnail_url || '') : '';

                if (imageUrl) {
                    return `
                        <div class="tpl-public-item tpl-public-media is-filled js-preview-drop-single" data-component-id="${escapeHtml(componentId)}" style="${previewBoxStyle(component)}">
                            <img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(asset.title || componentLabel(component))}">
                            <button type="button" class="tpl-preview-remove js-preview-clear-single" data-component-id="${escapeHtml(componentId)}" title="Clear image">×</button>
                        </div>`;
                }

                return `
                    <div class="tpl-public-item tpl-public-media js-preview-drop-single" data-component-id="${escapeHtml(componentId)}" style="${previewBoxStyle(component)}">
                        <div class="tpl-public-placeholder">
                            <div>
                                Drop one image here
                            </div>
                        </div>
                    </div>`;
            }

            function previewCarouselMarkup(component) {
                const componentId = componentKey(component);
                const bucket = getComponentValue(componentId) || { type: 'carousel', items: [] };
                const items = Array.isArray(bucket.items) ? bucket.items.filter(Boolean) : [];

                if (!items.length) {
                    return `
                        <div class="tpl-public-item tpl-public-media js-preview-drop-carousel" data-component-id="${escapeHtml(componentId)}" style="${previewBoxStyle(component)}">
                            <div class="tpl-public-placeholder">
                                <div>
                                    Drop images here to build the carousel
                                </div>
                            </div>
                        </div>`;
                }

                return `
                    <div class="tpl-public-item tpl-public-media is-filled js-preview-drop-carousel" data-component-id="${escapeHtml(componentId)}" style="${previewBoxStyle(component)}">
                        <div class="tpl-preview-count">${items.length} image${items.length === 1 ? '' : 's'}</div>
                        <div class="tpl-preview-carousel-grid">
                            ${items.map((item, index) => `
                                <div class="tpl-preview-thumb">
                                    <img src="${escapeHtml(item.thumbnail_url || item.preview_url || '')}" alt="${escapeHtml(item.title || '')}">
                                    <button type="button" class="tpl-preview-thumb-remove js-preview-remove-carousel" data-component-id="${escapeHtml(componentId)}" data-item-index="${index}" title="Remove image">×</button>
                                </div>
                            `).join('')}
                        </div>
                    </div>`;
            }

            function previewGalleryMarkup(component) {
                const componentId = componentKey(component);
                const bucket = getComponentValue(componentId) || { type: 'gallery', items: [] };
                const items = Array.isArray(bucket.items) ? bucket.items.filter(Boolean) : [];
                const count = items.length;

                return `
                    <div class="tpl-public-item tpl-public-gallery is-filled" data-component-id="${escapeHtml(componentId)}" style="${previewBoxStyle(component)}">
                        <div class="tpl-preview-gallery-summary">
                            <div class="tpl-preview-gallery-summary-subtitle">
                                This gallery is automatic, so there is nothing to drag into this section.
                                The published page will pull ${count} image${count === 1 ? '' : 's'} directly from this folder.
                            </div>
                        </div>
                    </div>`;
            }



            function previewComponentMarkup(component) {
                if (component.type === 'static_text') return previewStaticTextMarkup(component);
                if (component.type === 'dynamic_text') return previewDynamicTextMarkup(component);
                if (component.type === 'image') return previewImageMarkup(component);
                if (component.type === 'carousel') return previewCarouselMarkup(component);
                if (component.type === 'gallery') return previewGalleryMarkup(component);
                return '';
            }

            function bindPreviewInteractions() {
                publishPreviewCanvas.querySelectorAll('.js-preview-edit-text').forEach(node => {
                    node.addEventListener('click', e => {
                        e.preventDefault();
                        e.stopPropagation();

                        const componentId = node.getAttribute('data-component-id');

                        dndLog('TEXT click', {
                            componentId: componentId,
                            className: node.className
                        });

                        openTextEditor(componentId);
                    });
                });

                publishPreviewCanvas.querySelectorAll('.js-preview-drop-single').forEach(zone => {
                    attachAssetDropZone(zone, asset => {
                        const componentId = zone.getAttribute('data-component-id');

                        dndLog('ASSIGN single BEFORE', {
                            componentId: componentId,
                            asset: asset,
                            existingValue: getComponentValue(componentId)
                        });

                        setComponentValue(componentId, {
                            type: 'image',
                            asset: deepClone(asset)
                        });

                        state.selectedAssetId = String(asset.asset_id || '');
                        syncHidden();

                        dndLog('ASSIGN single AFTER syncHidden', {
                            componentId: componentId,
                            savedValue: getComponentValue(componentId),
                            hiddenJson: valuesJsonInput.value
                        });

                        renderAssetGallery();
                        renderPreview();

                        dndLog('ASSIGN single AFTER render', {
                            componentId: componentId,
                            previewHtmlSnippet: publishPreviewCanvas.innerHTML.slice(0, 400)
                        });
                    });
                });

                publishPreviewCanvas.querySelectorAll('.js-preview-clear-single').forEach(btn => {
                    btn.addEventListener('click', e => {
                        e.preventDefault();
                        e.stopPropagation();

                        deleteComponentValue(btn.getAttribute('data-component-id'));
                        syncHidden();
                        renderPreview();
                    });
                });

                publishPreviewCanvas.querySelectorAll('.js-preview-drop-carousel').forEach(zone => {
                    attachAssetDropZone(zone, asset => {
                        const componentId = zone.getAttribute('data-component-id');
                        const existing = getComponentValue(componentId) || { type: 'carousel', items: [] };
                        const items = Array.isArray(existing.items) ? existing.items.slice() : [];

                        dndLog('ASSIGN carousel BEFORE', {
                            componentId: componentId,
                            asset: asset,
                            existingBucket: existing
                        });

                        items.push(deepClone(asset));

                        setComponentValue(componentId, {
                            type: 'carousel',
                            items: items
                        });

                        state.selectedAssetId = String(asset.asset_id || '');
                        syncHidden();

                        dndLog('ASSIGN carousel AFTER syncHidden', {
                            componentId: componentId,
                            savedBucket: getComponentValue(componentId),
                            hiddenJson: valuesJsonInput.value
                        });

                        renderAssetGallery();
                        renderPreview();

                        dndLog('ASSIGN carousel AFTER render', {
                            componentId: componentId,
                            previewHtmlSnippet: publishPreviewCanvas.innerHTML.slice(0, 400)
                        });
                    });
                });

                publishPreviewCanvas.querySelectorAll('.js-preview-remove-carousel').forEach(btn => {
                    btn.addEventListener('click', e => {
                        e.preventDefault();
                        e.stopPropagation();

                        const componentId = btn.getAttribute('data-component-id');
                        const index = parseInt(btn.getAttribute('data-item-index'), 10);
                        const existing = getComponentValue(componentId) || { type: 'carousel', items: [] };
                        const items = Array.isArray(existing.items) ? existing.items.slice() : [];

                        if (index >= 0) {
                            items.splice(index, 1);
                        }

                        setComponentValue(componentId, {
                            type: 'carousel',
                            items: items
                        });

                        syncHidden();
                        renderPreview();
                    });
                });
            }

            function renderPreview() {
                ensureValues();

                const definition = getDefinition();
                const page = definition.page || {};
                const canvasWidth = Math.max(900, parseInt(page.canvas_width || 1200, 10));

                const layout = buildPreviewLayout(definition);
                const components = layout.components;
                const canvasHeight = Math.max(
                    900,
                    parseInt(page.canvas_min_height || 1400, 10),
                    Math.ceil(layout.bottom + 24)
                );

                publishPreviewCanvas.style.width = canvasWidth + 'px';
                publishPreviewCanvas.style.height = canvasHeight + 'px';
                publishPreviewCanvas.style.background = page.background_color || '#ffffff';

                dndLog('renderPreview START', {
                    componentValues: state.values.components,
                    components: components.map(c => ({
                        type: c.type,
                        componentId: componentKey(c),
                        x: c.preview_x ?? c.x,
                        y: c.preview_y ?? c.y,
                        w: c.preview_w ?? c.w,
                        h: c.preview_h ?? c.h
                    }))
                });

                publishPreviewCanvas.innerHTML = components.length
                    ? components.map(previewComponentMarkup).join('')
                    : '<div style="padding:40px;text-align:center;color:#6b819d;font-weight:700;">No template components found for this preview.</div>';

                previewScale.style.width = canvasWidth + 'px';
                previewScale.style.height = canvasHeight + 'px';

                const stageWidth = Math.max(320, previewStage.clientWidth - 36);
                const preferredScale = 0.72;
                const scale = Math.min(1, preferredScale, stageWidth / canvasWidth);

                previewScale.style.transform = `scale(${scale})`;
                previewCanvasWrap.style.height = Math.max(240, canvasHeight * scale) + 'px';

                bindPreviewInteractions();

                dndLog('renderPreview END', {
                    singleZones: publishPreviewCanvas.querySelectorAll('.js-preview-drop-single').length,
                    carouselZones: publishPreviewCanvas.querySelectorAll('.js-preview-drop-carousel').length,
                    imageTagsInSingleZones: publishPreviewCanvas.querySelectorAll('.js-preview-drop-single img').length,
                    imageTagsInCarouselZones: publishPreviewCanvas.querySelectorAll('.js-preview-drop-carousel img').length
                });
            }

            function openTextEditor(componentId) {
                dndLog('openTextEditor', {
                    componentId: componentId,
                    dynamicValue: getComponentValue(componentId)
                });

                ensureValues();
                state.textEditorField = componentId;

                const definition = getDefinition();
                const component = (definition.components || []).find(c => componentKey(c) === componentId);

                textModalTitle.textContent = componentLabel(component || { type: 'dynamic_text', label: 'Dynamic Text' });
                textModalSubtitle.textContent = 'Component ID: ' + componentId;

                textModal.classList.add('is-open');

                const existingValue = getComponentValue(componentId) || {};
                const initialHtml = existingValue.html || component?.default_html || '<p></p>';

                const existingEditor = tinymce.get('publishRichTextEditor');
                if (existingEditor) {
                    existingEditor.remove();
                }

                const textarea = document.getElementById('publishRichTextEditor');
                textarea.value = initialHtml;

                setTimeout(() => {
                    tinymce.init({
                        target: textarea,
                        height: 500,
                        menubar: false,
                        inline: false,
                        plugins: 'link lists code table autoresize',
                        toolbar: 'undo redo | blocks fontsize bold italic forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link table | code'
                    });
                }, 0);
            }

            function closeTextEditor() {
                state.textEditorField = null;
                textModal.classList.remove('is-open');
                tinymce.get('publishRichTextEditor')?.remove();
            }

            function renderAll() {
                ensureValues();
                bindPreviewCanvasDnD();
                renderAssetGallery();
                renderPreview();
                syncHidden();
            }

            templateSelect.addEventListener('change', () => {
                state.selectedTemplateId = templateSelect.value;

                const tpl = getSelectedTemplate();
                if (tpl && tpl.definition) {
                    state.definition = deepClone(tpl.definition);
                }

                renderAll();
            });

            protectedCheckbox.addEventListener('change', () => {
                passwordRow.style.display = protectedCheckbox.checked ? '' : 'none';
            });

            closeTextModal.addEventListener('click', closeTextEditor);
            cancelTextModal.addEventListener('click', closeTextEditor);

            saveTextModal.addEventListener('click', () => {
                const editor = tinymce.get('publishRichTextEditor');
                if (!editor || !state.textEditorField) return;

                setComponentValue(state.textEditorField, {
                    type: 'dynamic_text',
                    html: editor.getContent()
                });

                closeTextEditor();
                syncHidden();
                renderPreview();
            });

            textModal.addEventListener('click', e => {
                if (e.target === textModal) closeTextEditor();
            });

            lightboxClose.addEventListener('click', closeLightbox);
            lightboxPrev.addEventListener('click', previousLightbox);
            lightboxNext.addEventListener('click', nextLightbox);

            lightbox.addEventListener('click', e => {
                if (e.target === lightbox) closeLightbox();
            });

            document.addEventListener('keydown', e => {
                if (textModal.classList.contains('is-open') && e.key === 'Escape') {
                    closeTextEditor();
                    return;
                }

                if (!lightbox.classList.contains('is-open')) return;
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowLeft') previousLightbox();
                if (e.key === 'ArrowRight') nextLightbox();
            });

            window.addEventListener('resize', renderPreview);

            ensureValues();
            renderAll();
        })();
    </script>
</div>