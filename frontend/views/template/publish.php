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
$initialDefinition = $definition ?: ($template ? $template->getDefinitionArray() : WebsiteTemplate::defaultDefinition());
$initialValues = $publication->values_json
    ? $publication->getValuesArray()
    : ['components' => []];

$initialTemplateId = !$publication->isNewRecord
    ? (int) $publication->template_id
    : (int) ($template->id ?? 0);

$pageTitleValue = (string) $publication->page_title;
if ($pageTitleValue === '' && !$publication->hasErrors('page_title')) {
    $pageTitleValue = $folderName;
}

$uriValue = (string) $publication->uri;
if ($uriValue === '' && !$publication->hasErrors('uri')) {
    $uriValue = $folderDefaultSlug;
}

$templateMap = [];
foreach ($templates as $tpl) {
    $templateMap[$tpl->id] = [
        'id' => (int) $tpl->id,
        'name' => $tpl->name,
        'definition' => $tpl->getDefinitionArray(),
    ];
}
$assetPickerLabelPartialMatch = (bool) (Yii::$app->params['publishAssetPickerLabelPartialMatch'] ?? true);

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
            max-width:1200px;
            margin:0 auto;
            padding:0 24px;
        }

        .tpl-publish-grid {
            display:block;
        }

        .tpl-main-col {
            width:100%;
        }

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
            margin-bottom:5px;
        }

        .tpl-hero-copy h1 {
            margin:0 0 8px;
            font-size:30px;
            font-weight:800;
        }

        .tpl-hero-settings {
            display:grid;
            grid-template-columns:minmax(220px,280px) minmax(180px,240px) minmax(280px,1fr) auto;
            gap:12px;
            align-items:start;
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
            width:100%;
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
            align-self:end;
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

        .btn-fotuka:hover {
            color:#fff;
            text-decoration:none;
            background:#1d4ed8;
        }

        .btn-fotuka-secondary {
            background:#fff;
            color:#15355e;
            border:1px solid #d5e2f1;
            box-shadow:none;
        }

        .btn-fotuka-secondary:hover {
            background:#f7fbff;
        }

        .btn-fotuka-danger {
            background:#fff4f4;
            color:#b42318;
            border:1px solid #fecaca;
            box-shadow:none;
        }

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

        .tpl-card-header {
            padding:18px 22px 12px;
            border-bottom:1px solid #ebf1f8;
        }

        .tpl-card-header h2 {
            margin:0;
            font-size:20px;
            font-weight:800;
        }

        .tpl-card-header p {
            margin:6px 0 0;
            color:#69809f;
            line-height:1.5;
        }

        .tpl-card-body { padding:18px 22px 22px; }

        .tpl-preview-card {
            position:relative;
            top:0;
        }

        .tpl-preview-stage {
            overflow:hidden;
            padding:22px 22px 5px;
            background:#fff;
        }

        .tpl-preview-canvas-wrap {
            width:100%;
            display:flex;
            justify-content:center;
            align-items:flex-start;
            overflow:hidden
        }

        .tpl-preview-canvas-scale {
            transform-origin:top center;
        }

        .tpl-preview-canvas {
            position:relative;
            background:#fff;
            border:none;
            border-radius:18px;
            overflow:hidden;
            box-shadow:none;
        }

        .tpl-public-item {
            position:absolute;
            overflow:hidden;
        }

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
            cursor:pointer;
            transition:border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }

        .tpl-public-media:hover,
        .tpl-public-gallery:hover {
            border-color:#97b7dc;
            box-shadow:0 0 0 4px rgba(37,99,235,.08);
        }

        .tpl-public-media.is-filled,
        .tpl-public-gallery.is-filled {
            border-style:solid;
            border-color:#dbe6f3;
            background:#fff;
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
            font-size:24px;
        }

        .tpl-public-placeholder small {
            display:block;
            margin-top:6px;
            font-size:12px;
            font-weight:700;
        }

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

        .tpl-preview-remove,
        .tpl-preview-thumb-remove {
            pointer-events:auto;
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
            font-size:18px;
            font-weight:800;
        }

        .tpl-preview-carousel-grid,
        .tpl-preview-gallery-grid {
            width:100%;
            display:grid;
            grid-template-columns:repeat(6,minmax(0,1fr));
            gap:8px;
            padding:50px 8px 40px;
            align-content:start;
            box-sizing:border-box;
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

        .tpl-lightbox {
            position:fixed;
            inset:0;
            z-index:10010;
            background:rgba(10,18,31,.68);
            display:none;
            align-items:center;
            justify-content:center;
            padding:24px;
        }

        .tpl-lightbox.is-open {
            display:flex;
        }

        .tpl-lightbox-dialog {
            position:relative;
            border:1px solid #dbe6f3;
            border-radius:22px;
            box-shadow:0 24px 60px rgba(17,40,74,.20);
            overflow:hidden;
            display:flex;
            flex-direction:column;
        }

        .tpl-lightbox-btn.close {
            position:absolute;
            top:14px;
            right:14px;
            z-index:4;
            width:34px;
            height:34px;
            border:none;
            border-radius:999px;
            display:flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            font-size:20px;
            font-weight:800;
            line-height:1;
        }

        .tpl-picker-modal {
            position:fixed;
            inset:0;
            z-index:10020;
            background:rgba(10,18,31,.58);
            display:none;
            align-items:center;
            justify-content:center;
            padding:24px;
        }

        .tpl-picker-modal.is-open {
            display:flex;
        }

        .tpl-picker-dialog {
            width:min(1040px, 92vw);
            height:min(76vh, 660px);
            background:#fff;
            border:1px solid #dbe6f3;
            border-radius:22px;
            box-shadow:0 24px 60px rgba(17,40,74,.18);
            display:flex;
            flex-direction:column;
            overflow:hidden;
        }

        .tpl-picker-header {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
            padding:18px 22px 12px;
            border-bottom:1px solid #e7eef8;
        }

        .tpl-picker-header h2 {
            margin:0;
            font-size:26px;
            font-weight:800;
            color:#14345c;
        }

        .tpl-picker-close {
            width:36px;
            height:36px;
            border:none;
            border-radius:999px;
            background:#edf4fb;
            color:#16355c;
            font-size:19px;
            font-weight:800;
            cursor:pointer;
            line-height:1;
        }

        .tpl-picker-toolbar {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:14px;
            padding:14px 22px 8px;
        }

        .tpl-picker-search-wrap {
            width:min(360px, 100%);
            margin:0 auto;
            position:relative;
        }

        .tpl-picker-search-icon {
            position:absolute;
            left:110px;
            top:19px;
            transform:translateY(-50%);
            color:#94a3b8;
            font-size:24px;
            line-height:1;
            pointer-events:none;
        }

        .tpl-picker-search-input {
            margin-left: 100px;
            width:100%;
            height:48px;
            padding:0 18px 0 35px;
            border:1px solid #d8e2ef;
            border-radius:14px;
            background:#fff;
            color:#10233f;
            font-size:16px;
            outline:none;
            box-shadow:inset 0 1px 2px rgba(16,35,63,.03);
        }

        .tpl-picker-search-input:focus {
            border-color:#2563eb;
            box-shadow:0 0 0 4px rgba(37,99,235,.12);
        }

        .tpl-picker-bulk-actions {
            display:flex;
            gap:10px;
            flex-shrink:0;
        }

        .tpl-picker-bulk-btn {
            min-height:34px;
            padding:0 16px;
            border:2px solid #7c8293;
            border-radius:14px;
            background:#fff;
            color:#4a5568;
            font-size:14px;
            font-weight:700;
            cursor:pointer;
            line-height:1;
        }

        .tpl-picker-bulk-btn[disabled] {
            opacity:.45;
            cursor:default;
        }

        .tpl-picker-meta {
            min-height:22px;
            padding:0 22px 10px;
            color:#64748b;
            font-size:14px;
            font-weight:700;
        }

        .tpl-picker-body {
            flex:1;
            min-height:0;
            overflow-y:auto;
            overflow-x:hidden;
            padding:0 22px 18px;
        }

        .tpl-picker-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(126px, 126px));
            gap:14px;
            align-content:start;
            justify-content:start;
        }

        .tpl-picker-card {
            position:relative;
            width:126px;
            border:1px solid #dbe6f3;
            border-radius:20px;
            background:#fff;
            padding:10px;
            cursor:pointer;
            transition:border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        }

        .tpl-picker-card:hover {
            transform:translateY(-1px);
            box-shadow:0 14px 26px rgba(16,35,63,.08);
        }

        .tpl-picker-card.is-selected {
            border-color:#8fb1f0;
            box-shadow:0 0 0 4px rgba(37,99,235,.18);
        }

        .tpl-picker-check {
            position:absolute;
            top:10px;
            left:10px;
            width:18px;
            height:18px;
            border-radius:6px;
            border:2px solid rgba(121,128,147,.66);
            background:#fff;
            box-shadow:0 6px 14px rgba(17,40,74,.12);
            display:flex;
            align-items:center;
            justify-content:center;
            z-index:2;
        }

        .tpl-picker-card.is-selected .tpl-picker-check {
            border-color:#2563eb;
            background:#2563eb;
            color:#fff;
        }

        .tpl-picker-check svg {
            width:10px;
            height:10px;
            display:block;
        }

        .tpl-picker-thumb {
            width:100%;
            aspect-ratio:1 / 1;
            border-radius:18px;
            overflow:hidden;
            background:#edf3fb;
        }

        .tpl-picker-thumb img {
            width:100%;
            height:100%;
            object-fit:cover;
            display:block;
        }

        .tpl-picker-empty-thumb {
            width:100%;
            height:100%;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#6b819d;
            font-size:12px;
            font-weight:700;
            text-align:center;
            padding:8px;
        }

        .tpl-picker-empty {
            min-height:280px;
            display:flex;
            align-items:center;
            justify-content:center;
            text-align:center;
            color:#6b819d;
            font-size:18px;
            font-weight:700;
            border:1px dashed #cfdbeb;
            border-radius:18px;
            background:#fafcff;
        }

        .tpl-picker-footer {
            display:flex;
            justify-content:flex-end;
            gap:10px;
            padding:14px 22px 18px;
            border-top:1px solid #e7eef8;
        }

        .tpl-picker-modal .btn-fotuka,
        .tpl-picker-modal .btn-fotuka-secondary {
            min-height:38px;
            padding:9px 15px;
            border-radius:12px;
            box-shadow:none;
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
        .tpl-public-gallery > .tpl-preview-count,
        .tpl-public-gallery > .tpl-preview-gallery-grid {
            pointer-events:none;
        }

        @media (max-width:1400px) {
            .tpl-hero-settings {
                grid-template-columns:repeat(2,minmax(240px,1fr));
            }

            .tpl-hero-actions {
                justify-content:flex-start;
            }
        }

        @media (max-width:900px) {
            .tpl-publish-shell { padding:0 16px; }
            .tpl-publish-hero { padding:18px; }
            .tpl-hero-top { flex-direction:column; }
            .tpl-hero-settings { grid-template-columns:1fr; }
        }

        .tpl-protect-toggle {
            grid-column: 1;
            grid-row: 2;
        }

        .tpl-password-row {
            grid-column: 2;
            grid-row: 2;
            width: 100%;
            justify-self: start;
        }

        .tpl-download-toggle {
            grid-column: 3;
            grid-row: 2;
            width: 210px;
            max-width: none;
            justify-self: stretch;
        }
        .tpl-form-error {
            display: none;
            margin-top: 6px;
            color: #dc2626;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.3;
        }

        .tpl-input.is-invalid,
        .tpl-select.is-invalid,
        .tpl-domain.is-invalid {
            border-color: #dc2626;
        }

        .tpl-input.is-invalid:focus,
        .tpl-select.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(220, 38, 38, .10);
        }
    </style>
    <div class="tpl-publish-shell">
        <a class="breadcrum-link" href="/folders">Folders</a>
        &nbsp;&nbsp;/&nbsp;&nbsp;
        <span class="breadcrum-static">Folder Publishing</span>

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
                        <select  class="tpl-select<?= $publication->hasErrors('template_id') ? ' is-invalid' : '' ?>"
                                id="publicationTemplateId"
                                name="WebsitePublication[template_id]" >
                            <option value="">Select a template…</option>
                            <?php foreach ($templates as $tpl): ?>
                                <option value="<?= (int) $tpl->id ?>" <?= $initialTemplateId === (int) $tpl->id ? 'selected' : '' ?>>
                                    <?= Html::encode($tpl->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="publicationTemplateIdError"  class="tpl-form-error"
                            <?= $publication->hasErrors('template_id') ? '' : 'style="display:none;"' ?> >
                            <?= Html::encode($publication->getFirstError('template_id')) ?>
                        </div>
                    </div>

                    <div class="tpl-form-row">
                        <label for="publicationUri">Public URI</label>
                        <div class="tpl-inline-url">
                            <div
                                style="width:240px;"
                                id="publicationUriDomain"
                                class="tpl-domain<?= $publication->hasErrors('uri') ? ' is-invalid' : '' ?>">
                                https://fotuka.com/page/
                            </div>
                            <input  class="tpl-input<?= $publication->hasErrors('uri') ? ' is-invalid' : '' ?>"
                                    type="text"
                                    style="width:213px;"
                                    id="publicationUri"
                                    name="WebsitePublication[uri]"
                                    value="<?= Html::encode($uriValue) ?>"
                                    maxlength="255"
                                    placeholder="<?= Html::encode($folderDefaultSlug) ?>" >
                        </div>
                        <div  id="publicationUriError"
                                class="tpl-form-error"
                            <?= $publication->hasErrors('uri') ? '' : 'style="display:none;"' ?> >
                            <?= Html::encode($publication->getFirstError('uri')) ?>
                        </div>
                    </div>

                    <div class="tpl-hero-actions">
                        <button type="submit" class="btn-fotuka">Publish Folder</button>
                    </div>

                    <label class="tpl-check-inline tpl-protect-toggle">
                        <input type="checkbox" id="publicationProtected" name="WebsitePublication[is_password_protected]" value="1" <?= (int) $publication->is_password_protected === 1 ? 'checked' : '' ?>>
                        <span>Password protect this page</span>
                    </label>

                    <div class="tpl-form-row tpl-password-row" id="passwordRow" style="<?= (int) $publication->is_password_protected === 1 ? '' : 'display:none;' ?>">
                        <input  class="tpl-input<?= $publication->hasErrors('plain_password') ? ' is-invalid' : '' ?>"
                                type="password"
                                id="publicationPassword"
                                name="WebsitePublication[plain_password]"
                                value=""
                                placeholder="<?= $publication->isNewRecord ? 'Password' : '********' ?>" >
                        <div id="publicationPasswordError"
                                class="tpl-form-error"
                            <?= $publication->hasErrors('plain_password') ? '' : 'style="display:none;"' ?> >
                            <?= Html::encode($publication->getFirstError('plain_password')) ?>
                        </div>
                    </div>

                    <label class="tpl-check-inline tpl-download-toggle">
                        <input type="checkbox" name="WebsitePublication[allow_download_all]" value="1" <?= (int) $publication->allow_download_all === 1 ? 'checked' : '' ?>>
                        <span>Allow Downloads</span>
                    </label>

                </div>
            </div>

            <div class="tpl-publish-grid">
                <div class="tpl-main-col">
                    <div class="tpl-card tpl-preview-card">
                        <div class="tpl-card-header">
                            <h2>Live Preview</h2>
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
            </div>
        </form>
    </div>

    <div id="publishTextModal" class="tpl-lightbox">
        <div class="tpl-lightbox-dialog" style="background:#fff; height:420px; width:800px;">
            <button type="button" class="tpl-lightbox-btn close" id="closeTextModal" style="color:#16355c; background:rgba(11,38,73,.08);">✕</button>
            <div style="padding:22px 24px 16px; border-bottom:1px solid #e7eef8;">
                <div class="tpl-pill" id="textModalType">Publish-Time Text</div>
                <h2 id="textModalTitle" style="margin:12px 0 0; font-size:28px; font-weight:800; color:#14345c;"></h2>
                <p id="textModalSubtitle" class="tpl-muted" style="margin:8px 0 0;"></p>
            </div>
            <div style="padding:16px 22px 18px; height:calc(100% - 106px); display:flex; flex-direction:column; gap:12px;">
                <textarea id="publishRichTextEditor"></textarea>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn-fotuka btn-fotuka-secondary" id="cancelTextModal">Cancel</button>
                    <button type="button" class="btn-fotuka" id="saveTextModal">Save Text</button>
                </div>
            </div>
        </div>
    </div>

    <div id="assetPickerModal" class="tpl-picker-modal">
        <div class="tpl-picker-dialog" role="dialog" aria-modal="true" aria-labelledby="assetPickerTitle">
            <div class="tpl-picker-header">
                <h2 id="assetPickerTitle">Asset Selection</h2>
                <button type="button" class="tpl-picker-close" id="assetPickerClose" aria-label="Close">✕</button>
            </div>

            <div class="tpl-picker-toolbar">
                <div class="tpl-picker-search-wrap">
                    <span class="tpl-picker-search-icon" aria-hidden="true">⌕</span>
                    <input
                            type="text"
                            id="assetPickerSearch"
                            class="tpl-picker-search-input"
                            placeholder="Search Images"
                            autocomplete="off"
                    >
                </div>

                <div class="tpl-picker-bulk-actions" id="assetPickerBulkActions">
                    <button type="button" class="tpl-picker-bulk-btn" id="assetPickerSelectAll">Select All</button>
                    <button type="button" class="tpl-picker-bulk-btn" id="assetPickerUnselectAll">Unselect All</button>
                </div>
            </div>

            <div class="tpl-picker-meta" id="assetPickerMeta"></div>

            <div class="tpl-picker-body">
                <div id="assetPickerEmpty" class="tpl-picker-empty" style="display:none;">
                    No image assets matched your search.
                </div>
                <div id="assetPickerGrid" class="tpl-picker-grid"></div>
            </div>

            <div class="tpl-picker-footer">
                <button type="button" class="btn-fotuka btn-fotuka-secondary" id="assetPickerCancel">Cancel</button>
                <button type="button" class="btn-fotuka" id="assetPickerInsert">Insert</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.tiny.cloud/1/cbcqlkpvavpfb1f48f22qrybc82x9c8rv604z1jupes12uub/tinymce/6/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const templates = <?= Json::htmlEncode($templateMap) ?>;
            const rawAssets = <?= Json::htmlEncode(array_values($assets)) ?>;
            const assetPickerLabelPartialMatch = <?= $assetPickerLabelPartialMatch ? 'true' : 'false' ?>;
            const initialDefinition = <?= Json::htmlEncode($initialDefinition) ?>;

            const PREVIEW_GRID_COLS = 6;
            const PREVIEW_GRID_GAP = 8;
            const PREVIEW_GRID_PAD_X = 8;
            const PREVIEW_MULTI_TOP_PAD = 34;
            const PREVIEW_MULTI_BOTTOM_PAD = 40;
            const PREVIEW_ROW_SPACING = 18;

            const state = {
                selectedTemplateId: <?= Json::htmlEncode((string) $initialTemplateId) ?>,
                values: <?= Json::htmlEncode($initialValues) ?>,
                definition: initialDefinition,
                textEditorField: null,
                assetPicker: {
                    isOpen: false,
                    componentId: null,
                    mode: 'multiple',
                    query: '',
                    allAssets: [],
                    visibleAssets: [],
                    selectedIds: new Set()
                }
            };

            const templateSelect = document.getElementById('publicationTemplateId');
            const valuesJsonInput = document.getElementById('publicationValuesJson');
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

            const assetPickerModal = document.getElementById('assetPickerModal');
            const assetPickerClose = document.getElementById('assetPickerClose');
            const assetPickerCancel = document.getElementById('assetPickerCancel');
            const assetPickerInsert = document.getElementById('assetPickerInsert');
            const assetPickerSearch = document.getElementById('assetPickerSearch');
            const assetPickerGrid = document.getElementById('assetPickerGrid');
            const assetPickerEmpty = document.getElementById('assetPickerEmpty');
            const assetPickerMeta = document.getElementById('assetPickerMeta');
            const assetPickerSelectAll = document.getElementById('assetPickerSelectAll');
            const assetPickerUnselectAll = document.getElementById('assetPickerUnselectAll');
            const assetPickerBulkActions = document.getElementById('assetPickerBulkActions');

            const publishForm = document.getElementById('publishForm');
            const uriInput = document.getElementById('publicationUri');
            const uriDomain = document.getElementById('publicationUriDomain');
            const passwordInput = document.getElementById('publicationPassword');

            const templateError = document.getElementById('publicationTemplateIdError');
            const uriError = document.getElementById('publicationUriError');
            const passwordError = document.getElementById('publicationPasswordError');


            function deepClone(obj) {
                return JSON.parse(JSON.stringify(obj));
            }

            function componentKey(component) {
                return String(component && component.id ? component.id : '');
            }

            function componentLabel(component) {
                return component?.label || component?.field_name || component?.type || 'component';
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

                    components.forEach(function (component) {
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

            function syncHidden() {
                valuesJsonInput.value = JSON.stringify(state.values);
            }

            function normalizeAsset(asset) {
                if (!asset) return null;

                const assetId = asset.asset_id ?? asset.id ?? null;
                if (!assetId) return null;

                const width = parseInt(asset.width || 0, 10) || 0;
                const height = parseInt(asset.height || 0, 10) || 0;

                return {
                    asset_id: String(assetId),
                    title: String(asset.title || ''),
                    description: String(asset.description || ''),
                    filename: String(asset.filename || ''),
                    extension: String(asset.extension || ''),
                    orientation: String(asset.orientation || '').toLowerCase(),
                    width: width,
                    height: height,
                    thumbnail_url: String(asset.thumbnail_url || ''),
                    preview_url: String(asset.preview_url || ''),
                    labels: Array.isArray(asset.labels) ? asset.labels.map(function (v) { return String(v || ''); }) : []
                };
            }

            function getAssetKey(asset) {
                return String(asset?.asset_id ?? asset?.id ?? '');
            }

            function normalizeAllAssets() {
                state.assetPicker.allAssets = rawAssets
                    .map(normalizeAsset)
                    .filter(Boolean)
                    .filter(function (asset) {
                        return !!getAssetKey(asset);
                    });

                state.assetPicker.visibleAssets = state.assetPicker.allAssets.slice();
            }

            function componentPickerMode(component) {
                return component?.type === 'image' ? 'single' : 'multiple';
            }

            function getSelectedAssetIdsForComponent(componentId) {
                const bucket = getComponentValue(componentId) || null;
                if (!bucket) return [];

                if (bucket.type === 'image' && bucket.asset) {
                    const assetId = getAssetKey(bucket.asset);
                    return assetId ? [assetId] : [];
                }

                if ((bucket.type === 'carousel' || bucket.type === 'gallery') && Array.isArray(bucket.items)) {
                    return bucket.items
                        .map(function (item) { return getAssetKey(item); })
                        .filter(Boolean);
                }

                return [];
            }

            function searchTokens(query) {
                return String(query || '')
                    .toLowerCase()
                    .trim()
                    .split(/\s+/)
                    .filter(Boolean);
            }

            function assetMatchesToken(asset, token) {
                const width = parseInt(asset.width || 0, 10) || 0;
                const height = parseInt(asset.height || 0, 10) || 0;

                const searchableFields = [
                    String(asset.title || '').toLowerCase(),
                    String(asset.description || '').toLowerCase(),
                    String(asset.filename || '').toLowerCase(),
                    String(asset.extension || '').toLowerCase(),
                    String(asset.orientation || '').toLowerCase()
                ];

                const searchableLabels = Array.isArray(asset.labels)
                    ? asset.labels.map(function (label) { return String(label || '').toLowerCase(); })
                    : [];

                if (token === 'portrait' || token === 'vertical') {
                    if (height > width && width > 0 && height > 0) {
                        return true;
                    }
                }

                if (token === 'landscape' || token === 'horizontal') {
                    if (width >= height && width > 0 && height > 0) {
                        return true;
                    }
                }

                if (token === 'square') {
                    if (width === height && width > 0) {
                        return true;
                    }
                }

                if (searchableFields.some(function (field) { return field.indexOf(token) !== -1; })) {
                    return true;
                }

                if (assetPickerLabelPartialMatch) {
                    if (searchableLabels.some(function (label) { return label.indexOf(token) !== -1; })) {
                        return true;
                    }
                } else {
                    if (searchableLabels.some(function (label) { return label === token; })) {
                        return true;
                    }
                }

                return false;
            }

            function filterPickerAssets(query) {
                const tokens = searchTokens(query);

                if (!tokens.length) {
                    return state.assetPicker.allAssets.slice();
                }

                return state.assetPicker.allAssets.filter(function (asset) {
                    return tokens.some(function (token) {
                        return assetMatchesToken(asset, token);
                    });
                });
            }

            function assetPickerCardMarkup(asset) {
                const assetId = getAssetKey(asset);
                const selected = state.assetPicker.selectedIds.has(assetId);
                const thumbUrl = asset.thumbnail_url || asset.preview_url || '';

                return `
                    <button
                        type="button"
                        class="tpl-picker-card ${selected ? 'is-selected' : ''}"
                        data-asset-id="${escapeHtml(assetId)}"
                    >
                        <span class="tpl-picker-check">
                            ${selected ? `
                                <svg viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M7.8 13.9 4.2 10.3l-1.4 1.4 5 5 9-9-1.4-1.4z" fill="currentColor"></path>
                                </svg>
                            ` : ''}
                        </span>
                        <div class="tpl-picker-thumb">
                            ${thumbUrl
                    ? `<img src="${escapeHtml(thumbUrl)}" alt="">`
                    : `<div class="tpl-picker-empty-thumb">Thumbnail not available</div>`
                    }
                        </div>
                    </button>
                `;
            }

            function renderAssetPicker() {
                const visibleAssets = state.assetPicker.visibleAssets;
                const selectedCount = state.assetPicker.selectedIds.size;
                const isSingle = state.assetPicker.mode === 'single';

                assetPickerBulkActions.style.display = isSingle ? 'none' : 'flex';
                assetPickerSelectAll.disabled = isSingle || visibleAssets.length === 0;
                assetPickerUnselectAll.disabled = selectedCount === 0;

                assetPickerMeta.textContent = `${selectedCount} selected · ${visibleAssets.length} shown`;

                if (!visibleAssets.length) {
                    assetPickerGrid.innerHTML = '';
                    assetPickerEmpty.style.display = 'flex';
                    return;
                }

                assetPickerEmpty.style.display = 'none';
                assetPickerGrid.innerHTML = visibleAssets.map(assetPickerCardMarkup).join('');

                assetPickerGrid.querySelectorAll('.tpl-picker-card').forEach(function (card) {
                    card.addEventListener('click', function () {
                        const assetId = card.getAttribute('data-asset-id');
                        togglePickerAsset(assetId);
                    });
                });
            }

            function openAssetPicker(componentId) {
                ensureValues();

                const definition = getDefinition();
                const component = (definition.components || []).find(function (c) {
                    return componentKey(c) === componentId;
                });

                if (!component) return;
                if (!['image', 'carousel', 'gallery'].includes(component.type)) return;

                state.assetPicker.isOpen = true;
                state.assetPicker.componentId = componentId;
                state.assetPicker.mode = componentPickerMode(component);
                state.assetPicker.query = '';
                state.assetPicker.visibleAssets = state.assetPicker.allAssets.slice();
                state.assetPicker.selectedIds = new Set(getSelectedAssetIdsForComponent(componentId));

                assetPickerSearch.value = '';
                assetPickerModal.classList.add('is-open');
                renderAssetPicker();

                setTimeout(function () {
                    assetPickerSearch.focus();
                }, 0);
            }

            function closeAssetPicker() {
                state.assetPicker.isOpen = false;
                state.assetPicker.componentId = null;
                state.assetPicker.mode = 'multiple';
                state.assetPicker.query = '';
                state.assetPicker.visibleAssets = state.assetPicker.allAssets.slice();
                state.assetPicker.selectedIds = new Set();

                assetPickerSearch.value = '';
                assetPickerModal.classList.remove('is-open');
            }

            function togglePickerAsset(assetId) {
                const id = String(assetId);

                if (state.assetPicker.mode === 'single') {
                    if (state.assetPicker.selectedIds.has(id)) {
                        state.assetPicker.selectedIds.clear();
                    } else {
                        state.assetPicker.selectedIds = new Set([id]);
                    }
                } else {
                    if (state.assetPicker.selectedIds.has(id)) {
                        state.assetPicker.selectedIds.delete(id);
                    } else {
                        state.assetPicker.selectedIds.add(id);
                    }
                }

                renderAssetPicker();
            }

            function selectAllVisibleAssets() {
                if (state.assetPicker.mode === 'single') return;

                state.assetPicker.visibleAssets.forEach(function (asset) {
                    const assetId = getAssetKey(asset);
                    if (assetId) {
                        state.assetPicker.selectedIds.add(assetId);
                    }
                });

                renderAssetPicker();
            }

            function unselectAllAssets() {
                state.assetPicker.selectedIds.clear();
                renderAssetPicker();
            }

            function runPickerSearch() {
                state.assetPicker.query = assetPickerSearch.value || '';
                state.assetPicker.visibleAssets = filterPickerAssets(state.assetPicker.query);
                renderAssetPicker();
            }

            function applyAssetPickerSelection() {
                const componentId = state.assetPicker.componentId;
                if (!componentId) return;

                const definition = getDefinition();
                const component = (definition.components || []).find(function (c) {
                    return componentKey(c) === componentId;
                });

                if (!component) {
                    closeAssetPicker();
                    return;
                }

                const selectedAssets = state.assetPicker.allAssets
                    .filter(function (asset) {
                        return state.assetPicker.selectedIds.has(getAssetKey(asset));
                    })
                    .map(function (asset) {
                        return deepClone(asset);
                    });

                if (component.type === 'image') {
                    if (selectedAssets.length) {
                        setComponentValue(componentId, {
                            type: 'image',
                            asset: selectedAssets[0]
                        });
                    } else {
                        deleteComponentValue(componentId);
                    }
                }

                if (component.type === 'carousel') {
                    setComponentValue(componentId, {
                        type: 'carousel',
                        items: selectedAssets
                    });
                }

                if (component.type === 'gallery') {
                    setComponentValue(componentId, {
                        type: 'gallery',
                        items: selectedAssets
                    });
                }

                syncHidden();
                closeAssetPicker();
                renderPreview();
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

                if (component.type !== 'carousel' && component.type !== 'gallery') {
                    return baseHeight;
                }

                const items = getPreviewItemsForComponent(component);
                if (!items.length) {
                    return baseHeight;
                }

                const width = Math.max(120, Math.round(component.w || 300));
                const columns = PREVIEW_GRID_COLS;
                const innerWidth = width - (PREVIEW_GRID_PAD_X * 2) - ((columns - 1) * PREVIEW_GRID_GAP);
                const thumbSize = Math.max(44, Math.floor(innerWidth / columns));
                const rows = Math.max(1, Math.ceil(items.length / columns));
                const gridHeight = (rows * thumbSize) + (Math.max(0, rows - 1) * PREVIEW_GRID_GAP);

                return PREVIEW_MULTI_TOP_PAD + gridHeight + PREVIEW_MULTI_BOTTOM_PAD;
            }

            function buildPreviewLayout(definition) {
                const source = Array.isArray(definition.components) ? definition.components.map(deepClone) : [];
                const rows = new Map();

                source.forEach(function (component) {
                    const rowY = Math.round(component.y || 0);
                    if (!rows.has(rowY)) {
                        rows.set(rowY, []);
                    }
                    rows.get(rowY).push(component);
                });

                const sortedRowYs = Array.from(rows.keys()).sort(function (a, b) {
                    return a - b;
                });

                const laidOut = [];
                let carry = 0;
                let maxBottom = 0;

                sortedRowYs.forEach(function (rowY) {
                    const rowComponents = rows.get(rowY).sort(function (a, b) {
                        const ax = Math.round(a.x || 0);
                        const bx = Math.round(b.x || 0);
                        if (ax !== bx) return ax - bx;
                        return Math.round((a.z || 0) - (b.z || 0));
                    });

                    const previewY = rowY + carry;
                    let originalRowBottom = rowY;
                    let previewRowBottom = previewY;

                    rowComponents.forEach(function (component) {
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

                laidOut.sort(function (a, b) {
                    return Math.round((a.z || 0) - (b.z || 0));
                });

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
                    <div class="tpl-public-item tpl-public-text js-preview-edit-text" data-component-id="${escapeHtml(componentId)}" style="${previewBoxStyle(component)}" title="Click to edit">
                        <div class="tpl-preview-edit-tag">Click to edit</div>
                        <div class="tpl-preview-text-inner">${html}</div>
                    </div>
                `;
            }

            function previewImageMarkup(component) {
                const componentId = componentKey(component);
                const bucket = getComponentValue(componentId) || null;
                const asset = bucket && bucket.asset ? bucket.asset : null;
                const imageUrl = asset ? (asset.preview_url || asset.thumbnail_url || '') : '';

                if (imageUrl) {
                    return `
                        <div class="tpl-public-item tpl-public-media is-filled js-open-asset-picker" data-component-id="${escapeHtml(componentId)}" style="${previewBoxStyle(component)}">
                            <img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(asset.title || componentLabel(component))}">
                            <button type="button" class="tpl-preview-remove js-preview-clear-single" data-component-id="${escapeHtml(componentId)}" title="Remove image">×</button>
                        </div>`;
                }

                return `
                    <div class="tpl-public-item tpl-public-media js-open-asset-picker" data-component-id="${escapeHtml(componentId)}" style="${previewBoxStyle(component)}">
                        <div class="tpl-public-placeholder">
                            <div>
                                Click to select one image
                                <small>${escapeHtml(componentLabel(component))}</small>
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
                        <div class="tpl-public-item tpl-public-media js-open-asset-picker" data-component-id="${escapeHtml(componentId)}" style="${previewBoxStyle(component)}">
                            <div class="tpl-public-placeholder">
                                <div>
                                    Click to select carousel images
                                    <small>${escapeHtml(componentLabel(component))}</small>
                                </div>
                            </div>
                        </div>`;
                }

                return `
                    <div class="tpl-public-item tpl-public-media is-filled js-open-asset-picker" data-component-id="${escapeHtml(componentId)}" style="${previewBoxStyle(component)}">
                        <div class="tpl-preview-count">${items.length} image${items.length === 1 ? '' : 's'}</div>
                        <div class="tpl-preview-carousel-grid">
                            ${items.map(function (item, index) {
                                return `
                                    <div class="tpl-preview-thumb">
                                        <img src="${escapeHtml(item.thumbnail_url || item.preview_url || '')}" alt="">
                                        <button
                                            type="button"
                                            class="tpl-preview-thumb-remove js-preview-remove-carousel"
                                            data-component-id="${escapeHtml(componentId)}"
                                            data-item-index="${index}"
                                            title="Remove image"
                                        >×</button>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>`;
            }

            function previewGalleryMarkup(component) {
                const componentId = componentKey(component);
                const bucket = getComponentValue(componentId) || { type: 'gallery', items: [] };
                const items = Array.isArray(bucket.items) ? bucket.items.filter(Boolean) : [];

                if (!items.length) {
                    return `
                        <div class="tpl-public-item tpl-public-gallery js-open-asset-picker" data-component-id="${escapeHtml(componentId)}" style="${previewBoxStyle(component)}">
                            <div class="tpl-public-placeholder">
                                <div>
                                    Click to select gallery images
                                    <small>${escapeHtml(componentLabel(component))}</small>
                                </div>
                            </div>
                        </div>`;
                }

                return `
                    <div class="tpl-public-item tpl-public-gallery is-filled js-open-asset-picker" data-component-id="${escapeHtml(componentId)}" style="${previewBoxStyle(component)}">
                        <div class="tpl-preview-count">${items.length} image${items.length === 1 ? '' : 's'}</div>
                        <div class="tpl-preview-gallery-grid">
                            ${items.map(function (item, index) {
                                return `
                                    <div class="tpl-preview-thumb">
                                        <img src="${escapeHtml(item.thumbnail_url || item.preview_url || '')}" alt="">
                                        <button
                                            type="button"
                                            class="tpl-preview-thumb-remove js-preview-remove-gallery"
                                            data-component-id="${escapeHtml(componentId)}"
                                            data-item-index="${index}"
                                            title="Remove image"
                                        >×</button>
                                    </div>
                                `;
                            }).join('')}
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
                publishPreviewCanvas.querySelectorAll('.js-preview-edit-text').forEach(function (node) {
                    node.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        openTextEditor(node.getAttribute('data-component-id'));
                    });
                });

                publishPreviewCanvas.querySelectorAll('.js-open-asset-picker').forEach(function (node) {
                    node.addEventListener('click', function (e) {
                        if (e.target.closest('.js-preview-clear-single') ||
                            e.target.closest('.js-preview-remove-carousel') ||
                            e.target.closest('.js-preview-remove-gallery')) {
                            return;
                        }

                        e.preventDefault();
                        e.stopPropagation();
                        openAssetPicker(node.getAttribute('data-component-id'));
                    });
                });

                publishPreviewCanvas.querySelectorAll('.js-preview-clear-single').forEach(function (btn) {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();

                        deleteComponentValue(btn.getAttribute('data-component-id'));
                        syncHidden();
                        renderPreview();
                    });
                });

                publishPreviewCanvas.querySelectorAll('.js-preview-remove-carousel').forEach(function (btn) {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();

                        const componentId = btn.getAttribute('data-component-id');
                        const index = parseInt(btn.getAttribute('data-item-index'), 10);
                        removeItemFromMultiComponent(componentId, index, 'carousel');
                    });
                });

                publishPreviewCanvas.querySelectorAll('.js-preview-remove-gallery').forEach(function (btn) {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();

                        const componentId = btn.getAttribute('data-component-id');
                        const index = parseInt(btn.getAttribute('data-item-index'), 10);
                        removeItemFromMultiComponent(componentId, index, 'gallery');
                    });
                });
            }

            function renderPreview() {
                ensureValues();

                const definition = getDefinition();
                const page = definition.page || {};
                const layout = buildPreviewLayout(definition);
                const components = layout.components;
                const canvasWidth = Math.max(900, parseInt(page.canvas_width || 1300, 10));
                const canvasHeight = Math.max(
                    900,
                    parseInt(page.canvas_min_height || 1400, 10),
                    Math.ceil(layout.bottom + 24)
                );

                publishPreviewCanvas.style.width = canvasWidth + 'px';
                publishPreviewCanvas.style.height = canvasHeight + 'px';
                publishPreviewCanvas.style.background = page.background_color || '#ffffff';

                publishPreviewCanvas.innerHTML = components.length
                    ? components.map(previewComponentMarkup).join('')
                    : '<div style="padding:40px;text-align:center;color:#6b819d;font-weight:700;">No template components found for this preview.</div>';

                previewScale.style.width = canvasWidth + 'px';
                previewScale.style.height = canvasHeight + 'px';

                const stageWidth = Math.max(320, previewStage.clientWidth - 44);
                const preferredScale = 0.84;
                const scale = Math.min(1, preferredScale, stageWidth / canvasWidth);

                previewScale.style.transform = `scale(${scale})`;
                previewCanvasWrap.style.height = Math.max(240, canvasHeight * scale) + 'px';

                bindPreviewInteractions();
            }

            function openTextEditor(componentId) {
                ensureValues();
                state.textEditorField = componentId;

                const definition = getDefinition();
                const component = (definition.components || []).find(function (c) {
                    return componentKey(c) === componentId;
                });

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

                setTimeout(function () {
                    tinymce.init({
                        target: textarea,
                        height: 300,
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
                const editor = tinymce.get('publishRichTextEditor');
                if (editor) {
                    editor.remove();
                }
            }

            function removeItemFromMultiComponent(componentId, index, type) {
                const existing = getComponentValue(componentId) || { type: type, items: [] };
                const items = Array.isArray(existing.items) ? existing.items.slice() : [];

                if (index >= 0 && index < items.length) {
                    items.splice(index, 1);
                }

                setComponentValue(componentId, {
                    type: type,
                    items: items
                });

                syncHidden();
                renderPreview();
            }

            function renderAll() {
                ensureValues();
                renderPreview();
                syncHidden();
            }

            templateSelect.addEventListener('change', function () {
                state.selectedTemplateId = templateSelect.value;

                const tpl = getSelectedTemplate();
                if (tpl && tpl.definition) {
                    state.definition = deepClone(tpl.definition);
                }

                renderAll();
            });

            protectedCheckbox.addEventListener('change', function () {
                clearFieldError(passwordInput, passwordError);
                passwordRow.style.display = protectedCheckbox.checked ? '' : 'none';
            });

            passwordInput.addEventListener('input', function () {
                if (passwordError.style.display !== 'none') {
                    validatePasswordField();
                }
            });

            passwordInput.addEventListener('blur', validatePasswordField);

            closeTextModal.addEventListener('click', closeTextEditor);
            cancelTextModal.addEventListener('click', closeTextEditor);

            saveTextModal.addEventListener('click', function () {
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

            assetPickerClose.addEventListener('click', closeAssetPicker);
            assetPickerCancel.addEventListener('click', closeAssetPicker);
            assetPickerInsert.addEventListener('click', applyAssetPickerSelection);
            assetPickerSelectAll.addEventListener('click', selectAllVisibleAssets);
            assetPickerUnselectAll.addEventListener('click', unselectAllAssets);

            assetPickerSearch.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    runPickerSearch();
                }
            });

            assetPickerModal.addEventListener('click', function (e) {
                if (e.target === assetPickerModal) {
                    closeAssetPicker();
                }
            });

            textModal.addEventListener('click', function (e) {
                if (e.target === textModal) {
                    closeTextEditor();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') return;

                if (assetPickerModal.classList.contains('is-open')) {
                    closeAssetPicker();
                    return;
                }

                if (textModal.classList.contains('is-open')) {
                    closeTextEditor();
                }
            });

            publishForm.addEventListener('submit', function (e) {
                const result = validatePublishForm();

                if (result.valid) {
                    return;
                }

                e.preventDefault();

                if (result.firstInvalidField) {
                    result.firstInvalidField.focus();
                }
            });

            function setFieldError(field, errorNode, message, extraNodes = []) {
                if (field) {
                    field.classList.add('is-invalid');
                }

                extraNodes.forEach(function (node) {
                    if (node) {
                        node.classList.add('is-invalid');
                    }
                });

                if (errorNode) {
                    errorNode.textContent = message;
                    errorNode.style.display = 'block';
                }
            }

            function clearFieldError(field, errorNode, extraNodes = []) {
                if (field) {
                    field.classList.remove('is-invalid');
                }

                extraNodes.forEach(function (node) {
                    if (node) {
                        node.classList.remove('is-invalid');
                    }
                });

                if (errorNode) {
                    errorNode.textContent = '';
                    errorNode.style.display = 'none';
                }
            }

            function validateRequiredField(field, errorNode, message, extraNodes = []) {
                const value = String(field?.value || '').trim();

                if (value === '') {
                    setFieldError(field, errorNode, message, extraNodes);
                    return false;
                }

                clearFieldError(field, errorNode, extraNodes);
                return true;
            }

            function validatePasswordField() {
                if (!protectedCheckbox.checked) {
                    clearFieldError(passwordInput, passwordError);
                    return true;
                }

                return validateRequiredField(
                    passwordInput,
                    passwordError,
                    'Please enter a Password'
                );
            }

            function validatePublishForm() {
                const checks = [
                    {
                        valid: validateRequiredField(
                            templateSelect,
                            templateError,
                            'Please select a Template'
                        ),
                        field: templateSelect
                    },
                    {
                        valid: validateRequiredField(
                            uriInput,
                            uriError,
                            'Please select a Public URI for your page',
                            [uriDomain]
                        ),
                        field: uriInput
                    },
                    {
                        valid: validatePasswordField(),
                        field: passwordInput
                    }
                ];

                const failed = checks.find(function (item) {
                    return !item.valid;
                });

                return {
                    valid: !failed,
                    firstInvalidField: failed ? failed.field : null
                };
            }

            normalizeAllAssets();
            renderAll();
        });
    </script>
</div>

