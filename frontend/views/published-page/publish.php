<?php
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

$this->title = 'Publish Folder: ' . $folder->name;
?>

<div class="publish-page-wrap">
    <div class="publish-shell">

        <div class="publish-header">
            <div>
                <h1><?= Html::encode($this->title) ?></h1>
                <p>
                    Choose a template, complete the text fields, and assign folder images to the exact sections
                    and positions where they should appear on the published page.
                </p>
            </div>
        </div>

        <?php $form = ActiveForm::begin([
            'id' => 'publish-page-form',
            'options' => ['autocomplete' => 'off'],
        ]); ?>

        <div class="publish-grid">

            <!-- LEFT -->
            <div class="publish-main">

                <div class="publish-card">
                    <div class="card-title-row">
                        <h3>Page Settings</h3>
                        <span class="card-badge">Required</span>
                    </div>

                    <?= $form->field($page, 'template_id')->dropDownList(
                        ['' => '-- Select Template --'] + ArrayHelper::map($templates, 'template_id', 'name'),
                        ['id' => 'template-select']
                    ) ?>

                    <?= $form->field($page, 'uri')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'my-page-uri'
                    ])->hint('Public URL: https://fotuka.com/pages/<uri>') ?>

                    <?= $form->field($page, 'page_title')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Example: Spring Family Album'
                    ]) ?>

                    <div id="password-field-wrap" style="display:none;">
                        <?= $form->field($page, 'page_password')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Optional password'
                        ]) ?>
                    </div>
                </div>

                <div class="publish-card" id="custom-fields-wrap" style="display:none;">
                    <div class="card-title-row">
                        <h3>Text & Content Fields</h3>
                        <span class="card-badge soft">Template Driven</span>
                    </div>

                    <p class="section-intro">
                        Complete the text values required by the selected template.
                    </p>

                    <div id="custom-fields-container"></div>
                </div>

                <div class="publish-card" id="section-assets-wrap" style="display:none;">
                    <div class="card-title-row">
                        <h3>Image Assignment</h3>
                        <span class="card-badge soft">Drag & Drop</span>
                    </div>

                    <div class="assignment-help">
                        <div class="assignment-help-item">
                            <span class="help-dot"></span>
                            Drag images from the right panel into the exact slot where you want them.
                        </div>
                        <div class="assignment-help-item">
                            <span class="help-dot"></span>
                            Click a slot first, then click an image on the right to assign it quickly.
                        </div>
                        <div class="assignment-help-item">
                            <span class="help-dot"></span>
                            Drop another image on a slot to replace it.
                        </div>
                    </div>

                    <div id="section-assets-container"></div>
                </div>

            </div>

            <!-- RIGHT -->
            <aside class="publish-side">
                <div class="publish-card sticky-card">
                    <div class="card-title-row">
                        <h3>Folder Images</h3>
                        <span class="asset-counter" id="folder-asset-counter">0 images</span>
                    </div>

                    <p class="section-intro">
                        Use search to find images faster. Drag them into the assignment slots on the left.
                    </p>

                    <div class="asset-toolbar">
                        <input
                                type="text"
                                id="asset-search"
                                class="asset-search"
                                placeholder="Search by file name..."
                        >
                    </div>

                    <div class="asset-grid-preview" id="folder-assets-preview"></div>

                    <div id="folder-assets-empty" class="empty-mini" style="display:none;">
                        No matching images found.
                    </div>
                </div>
            </aside>

        </div>

        <div class="publish-footer">
            <div class="publish-footer-left">
                <div class="footer-summary" id="footer-summary">
                    Select a template to begin.
                </div>
            </div>

            <div class="publish-footer-right">
                <button type="submit" class="btn btn-primary btn-lg publish-btn">Publish Page</button>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>

<style>
    .publish-page-wrap{
        padding:24px;
        background:#f8fafc;
        min-height:100vh;
    }

    .publish-shell{
        max-width:1520px;
        margin:0 auto;
    }

    .publish-header{
        margin-bottom:18px;
    }

    .publish-header h1{
        margin:0 0 6px 0;
        font-size:30px;
        font-weight:700;
        color:#111827;
    }

    .publish-header p{
        margin:0;
        color:#6b7280;
        font-size:15px;
    }

    .publish-grid{
        display:grid;
        grid-template-columns:minmax(0, 1fr) 390px;
        gap:20px;
        align-items:start;
    }

    .publish-main,
    .publish-side{
        min-width:0;
    }

    .publish-card{
        background:#ffffff;
        border:1px solid #e5e7eb;
        border-radius:16px;
        padding:20px;
        box-shadow:0 4px 14px rgba(15, 23, 42, 0.04);
        margin-bottom:18px;
    }

    .sticky-card{
        position:sticky;
        top:20px;
    }

    .card-title-row{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        margin-bottom:12px;
    }

    .card-title-row h3{
        margin:0;
        font-size:18px;
        font-weight:700;
        color:#111827;
    }

    .card-badge{
        display:inline-flex;
        align-items:center;
        padding:6px 10px;
        border-radius:999px;
        font-size:12px;
        font-weight:600;
        background:#dbeafe;
        color:#1d4ed8;
        white-space:nowrap;
    }

    .card-badge.soft{
        background:#eef2ff;
        color:#4338ca;
    }

    .section-intro{
        margin:0 0 14px 0;
        color:#6b7280;
        font-size:14px;
    }

    .assignment-help{
        display:grid;
        gap:8px;
        background:#f8fafc;
        border:1px solid #e5e7eb;
        border-radius:14px;
        padding:14px;
        margin-bottom:16px;
    }

    .assignment-help-item{
        display:flex;
        align-items:flex-start;
        gap:10px;
        font-size:13px;
        color:#4b5563;
    }

    .help-dot{
        width:8px;
        height:8px;
        margin-top:5px;
        border-radius:50%;
        background:#3b82f6;
        flex:0 0 auto;
    }

    .asset-toolbar{
        margin-bottom:14px;
    }

    .asset-search{
        width:100%;
        border:1px solid #d1d5db;
        border-radius:12px;
        padding:10px 12px;
        font-size:14px;
        outline:none;
        transition:border-color .15s ease, box-shadow .15s ease;
    }

    .asset-search:focus{
        border-color:#60a5fa;
        box-shadow:0 0 0 4px rgba(59,130,246,0.10);
    }

    .asset-counter{
        font-size:12px;
        color:#6b7280;
        font-weight:600;
    }

    .asset-grid-preview{
        display:grid;
        grid-template-columns:repeat(3, 1fr);
        gap:10px;
    }

    .asset-thumb{
        position:relative;
        border:1px solid #e5e7eb;
        border-radius:12px;
        overflow:hidden;
        background:#ffffff;
        cursor:grab;
        transition:transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }

    .asset-thumb:hover{
        transform:translateY(-1px);
        box-shadow:0 8px 16px rgba(15,23,42,0.08);
        border-color:#bfdbfe;
    }

    .asset-thumb:active{
        cursor:grabbing;
    }

    .asset-thumb img{
        display:block;
        width:100%;
        height:92px;
        object-fit:cover;
        background:#f3f4f6;
    }

    .asset-thumb-name{
        padding:8px;
        font-size:11px;
        line-height:1.35;
        color:#374151;
        border-top:1px solid #f3f4f6;
        height:42px;
        overflow:hidden;
        word-break:break-word;
    }

    .asset-thumb.active-target-match{
        border-color:#3b82f6;
        box-shadow:0 0 0 4px rgba(59,130,246,.10);
    }

    .empty-mini{
        margin-top:10px;
        padding:14px;
        border:1px dashed #d1d5db;
        border-radius:12px;
        color:#6b7280;
        font-size:13px;
        text-align:center;
        background:#fafafa;
    }

    .template-section-card{
        border:1px solid #e5e7eb;
        border-radius:16px;
        padding:16px;
        background:#fcfcfd;
        margin-bottom:16px;
    }

    .template-section-head{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:12px;
        margin-bottom:12px;
    }

    .template-section-title{
        margin:0 0 4px 0;
        font-size:16px;
        font-weight:700;
        color:#111827;
    }

    .template-section-meta{
        color:#6b7280;
        font-size:13px;
    }

    .template-section-count{
        display:inline-flex;
        align-items:center;
        padding:6px 10px;
        border-radius:999px;
        background:#eef2ff;
        color:#4338ca;
        font-size:12px;
        font-weight:700;
        white-space:nowrap;
    }

    .section-slots-grid{
        display:grid;
        grid-template-columns:repeat(5, minmax(0, 1fr));
        gap:12px;
    }

    .image-slot{
        position:relative;
        min-height:170px;
        border:2px dashed #cbd5e1;
        border-radius:14px;
        background:#f8fafc;
        overflow:hidden;
        transition:border-color .15s ease, background .15s ease, box-shadow .15s ease;
    }

    .image-slot.is-over{
        border-color:#3b82f6;
        background:#eff6ff;
        box-shadow:0 0 0 4px rgba(59,130,246,.08);
    }

    .image-slot.is-active{
        border-color:#2563eb;
        background:#eff6ff;
        box-shadow:0 0 0 4px rgba(37,99,235,.10);
    }

    .image-slot-inner{
        position:relative;
        width:100%;
        height:100%;
        min-height:170px;
    }

    .image-slot-empty{
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        min-height:170px;
        text-align:center;
        padding:14px;
        color:#6b7280;
    }

    .image-slot-empty .slot-index{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:32px;
        height:32px;
        border-radius:999px;
        background:#e5e7eb;
        color:#374151;
        font-size:12px;
        font-weight:700;
        margin-bottom:10px;
    }

    .image-slot-empty .slot-title{
        font-size:13px;
        font-weight:700;
        color:#374151;
        margin-bottom:4px;
    }

    .image-slot-empty .slot-help{
        font-size:12px;
        color:#6b7280;
        line-height:1.35;
    }

    .image-slot-filled{
        position:relative;
        width:100%;
        min-height:170px;
    }

    .image-slot-filled img{
        width:100%;
        height:170px;
        object-fit:cover;
        display:block;
        background:#f3f4f6;
    }

    .slot-overlay{
        position:absolute;
        left:0;
        right:0;
        bottom:0;
        background:linear-gradient(to top, rgba(0,0,0,.75), rgba(0,0,0,.15));
        color:#fff;
        padding:10px;
    }

    .slot-overlay .slot-label{
        font-size:11px;
        font-weight:700;
        opacity:.92;
        margin-bottom:2px;
    }

    .slot-overlay .slot-filename{
        font-size:11px;
        line-height:1.3;
        word-break:break-word;
    }

    .slot-remove-btn{
        position:absolute;
        top:8px;
        right:8px;
        width:28px;
        height:28px;
        border:none;
        border-radius:999px;
        background:rgba(17,24,39,.85);
        color:#fff;
        font-size:16px;
        line-height:28px;
        cursor:pointer;
        z-index:3;
    }

    .slot-remove-btn:hover{
        background:#111827;
    }

    .slot-replace-hint{
        position:absolute;
        top:8px;
        left:8px;
        background:rgba(255,255,255,.9);
        color:#111827;
        font-size:10px;
        font-weight:700;
        border-radius:999px;
        padding:4px 8px;
        z-index:3;
    }

    .field-grid{
        display:grid;
        grid-template-columns:repeat(2, minmax(0, 1fr));
        gap:16px;
    }

    .field-grid .field-block.full{
        grid-column:1 / -1;
    }

    .field-block{
        min-width:0;
    }

    .field-label{
        display:block;
        font-size:13px;
        font-weight:700;
        color:#374151;
        margin-bottom:8px;
    }

    .field-help{
        display:block;
        margin-top:6px;
        color:#6b7280;
        font-size:12px;
    }

    .custom-input,
    .custom-textarea,
    .custom-select{
        width:100%;
        border:1px solid #d1d5db;
        border-radius:12px;
        padding:10px 12px;
        font-size:14px;
        outline:none;
        transition:border-color .15s ease, box-shadow .15s ease;
        background:#fff;
    }

    .custom-input:focus,
    .custom-textarea:focus,
    .custom-select:focus{
        border-color:#60a5fa;
        box-shadow:0 0 0 4px rgba(59,130,246,0.10);
    }

    .custom-textarea{
        min-height:110px;
        resize:vertical;
    }

    .publish-footer{
        margin-top:6px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:16px;
        padding:18px 4px 8px 4px;
    }

    .footer-summary{
        color:#4b5563;
        font-size:14px;
    }

    .publish-btn{
        min-width:170px;
        border-radius:12px;
        font-weight:700;
    }

    .template-empty-state{
        border:1px dashed #d1d5db;
        border-radius:14px;
        background:#fafafa;
        padding:22px;
        text-align:center;
        color:#6b7280;
        font-size:14px;
    }

    @media (max-width: 1380px){
        .section-slots-grid{
            grid-template-columns:repeat(4, minmax(0, 1fr));
        }
    }

    @media (max-width: 1200px){
        .publish-grid{
            grid-template-columns:1fr;
        }

        .sticky-card{
            position:static;
        }
    }

    @media (max-width: 900px){
        .field-grid{
            grid-template-columns:1fr;
        }

        .section-slots-grid{
            grid-template-columns:repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px){
        .asset-grid-preview{
            grid-template-columns:repeat(2, 1fr);
        }

        .section-slots-grid{
            grid-template-columns:repeat(2, minmax(0, 1fr));
        }

        .publish-footer{
            flex-direction:column;
            align-items:stretch;
        }

        .publish-footer-right{
            width:100%;
        }

        .publish-btn{
            width:100%;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const templatesPayload = <?= Json::htmlEncode($templatesPayload) ?>;
        const assetsPayload = <?= Json::htmlEncode($assetsPayload) ?>;

        const state = {
            activeSlot: null,
            assetMap: {},
        };

        const folderAssetsPreview = document.getElementById('folder-assets-preview');
        const folderAssetsEmpty = document.getElementById('folder-assets-empty');
        const assetCounter = document.getElementById('folder-asset-counter');
        const assetSearch = document.getElementById('asset-search');
        const templateSelect = document.getElementById('template-select');
        const customFieldsWrap = document.getElementById('custom-fields-wrap');
        const customFieldsContainer = document.getElementById('custom-fields-container');
        const sectionAssetsWrap = document.getElementById('section-assets-wrap');
        const sectionAssetsContainer = document.getElementById('section-assets-container');
        const passwordWrap = document.getElementById('password-field-wrap');
        const passwordInput = document.getElementById('publishedpage-page_password');
        const footerSummary = document.getElementById('footer-summary');

        assetsPayload.forEach(function (asset) {
            state.assetMap[String(asset.id)] = asset;
        });

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value || '';
            return div.innerHTML;
        }

        function getAssetImage(asset) {
            return asset.thumbnail_url || asset.preview_url || '';
        }

        function normalizeFieldType(field) {
            const type = String(field.type || field.field_type || '').toLowerCase();

            if (type === 'text' || type === 'input' || type === 'string') {
                return 'text';
            }

            if (type === 'textarea' || type === 'longtext' || type === 'multiline') {
                return 'textarea';
            }

            if (type === 'number') {
                return 'number';
            }

            if (type === 'select' || type === 'dropdown') {
                return 'select';
            }

            return 'textarea';
        }

        function getFieldPlaceholder(field) {
            return field.placeholder || field.label || field.name || '';
        }

        function getFieldHelp(field) {
            return field.help_text || field.description || '';
        }

        function renderFolderPreview(filterTerm) {
            if (!folderAssetsPreview) {
                return;
            }

            const term = String(filterTerm || '').trim().toLowerCase();

            folderAssetsPreview.innerHTML = '';

            const filtered = assetsPayload.filter(function (asset) {
                if (!term) {
                    return true;
                }

                return String(asset.filename || '').toLowerCase().indexOf(term) !== -1;
            });

            assetCounter.textContent = filtered.length + ' image' + (filtered.length === 1 ? '' : 's');

            if (!filtered.length) {
                folderAssetsEmpty.style.display = '';
                return;
            }

            folderAssetsEmpty.style.display = 'none';

            filtered.forEach(function (asset) {
                const thumb = document.createElement('div');
                thumb.className = 'asset-thumb draggable-asset';
                thumb.setAttribute('draggable', 'true');
                thumb.dataset.assetId = asset.id;
                thumb.title = asset.filename || '';

                const img = document.createElement('img');
                img.src = getAssetImage(asset);
                img.alt = asset.filename || '';

                const name = document.createElement('div');
                name.className = 'asset-thumb-name';
                name.textContent = asset.filename || ('Asset #' + asset.id);

                thumb.appendChild(img);
                thumb.appendChild(name);
                folderAssetsPreview.appendChild(thumb);
            });

            refreshActiveSlotHighlight();
        }

        function renderCustomFields(template) {
            customFieldsContainer.innerHTML = '';

            if (!template.custom_fields || !template.custom_fields.length) {
                customFieldsWrap.style.display = 'none';
                return;
            }

            const grid = document.createElement('div');
            grid.className = 'field-grid';

            template.custom_fields.forEach(function (field) {
                const block = document.createElement('div');
                block.className = 'field-block';

                const fieldType = normalizeFieldType(field);
                const fullWidth = fieldType === 'textarea';
                if (fullWidth) {
                    block.classList.add('full');
                }

                const label = document.createElement('label');
                label.className = 'field-label';
                label.textContent = field.name || field.label || 'Field';

                let input;

                if (fieldType === 'text' || fieldType === 'number') {
                    input = document.createElement('input');
                    input.type = fieldType === 'number' ? 'number' : 'text';
                    input.className = 'custom-input';
                    input.name = 'custom_field_values[' + field.custom_field_id + ']';
                    input.placeholder = getFieldPlaceholder(field);
                } else if (fieldType === 'select' && Array.isArray(field.options) && field.options.length) {
                    input = document.createElement('select');
                    input.className = 'custom-select';
                    input.name = 'custom_field_values[' + field.custom_field_id + ']';

                    const emptyOption = document.createElement('option');
                    emptyOption.value = '';
                    emptyOption.textContent = '-- Select --';
                    input.appendChild(emptyOption);

                    field.options.forEach(function (option) {
                        const optionEl = document.createElement('option');

                        if (typeof option === 'object') {
                            optionEl.value = option.value || option.label || '';
                            optionEl.textContent = option.label || option.value || '';
                        } else {
                            optionEl.value = option;
                            optionEl.textContent = option;
                        }

                        input.appendChild(optionEl);
                    });
                } else {
                    input = document.createElement('textarea');
                    input.className = 'custom-textarea';
                    input.name = 'custom_field_values[' + field.custom_field_id + ']';
                    input.rows = 4;
                    input.placeholder = getFieldPlaceholder(field);
                }

                block.appendChild(label);
                block.appendChild(input);

                const helpText = getFieldHelp(field);
                if (helpText) {
                    const help = document.createElement('span');
                    help.className = 'field-help';
                    help.textContent = helpText;
                    block.appendChild(help);
                }

                grid.appendChild(block);
            });

            customFieldsContainer.appendChild(grid);
            customFieldsWrap.style.display = '';
        }

        function getSectionAssetCount(section) {
            if (parseInt(section.image_count || 0, 10) > 0) {
                return parseInt(section.image_count, 10);
            }

            if (String(section.type || '').toLowerCase() === 'header_image') {
                return 1;
            }

            return 1;
        }

        function getSectionTypeLabel(section) {
            const type = String(section.type || '').replace(/_/g, ' ').trim();
            if (!type) {
                return 'Image Section';
            }

            return type.charAt(0).toUpperCase() + type.slice(1);
        }

        function createEmptySlotMarkup(slotIndex, slotLabel) {
            return ''
                + '<div class="image-slot-empty">'
                + '  <div class="slot-index">' + slotIndex + '</div>'
                + '  <div class="slot-title">' + escapeHtml(slotLabel) + '</div>'
                + '  <div class="slot-help">Drop an image here or click this slot and then click an image on the right.</div>'
                + '</div>';
        }

        function fillSlot(slot, assetId) {
            const asset = state.assetMap[String(assetId)];
            if (!asset) {
                return;
            }

            const input = slot.querySelector('input[type="hidden"]');
            if (!input) {
                return;
            }

            input.value = asset.id;

            const slotIndex = parseInt(slot.dataset.slotIndex || '1', 10);
            const slotLabel = slot.dataset.slotLabel || ('Image ' + slotIndex);

            slot.querySelector('.image-slot-inner').innerHTML = ''
                + '<div class="image-slot-filled">'
                + '  <button type="button" class="slot-remove-btn" title="Remove image">&times;</button>'
                + '  <div class="slot-replace-hint">Drop to replace</div>'
                + '  <img src="' + escapeHtml(getAssetImage(asset)) + '" alt="' + escapeHtml(asset.filename || '') + '">'
                + '  <div class="slot-overlay">'
                + '      <div class="slot-label">' + escapeHtml(slotLabel) + '</div>'
                + '      <div class="slot-filename">' + escapeHtml(asset.filename || ('Asset #' + asset.id)) + '</div>'
                + '  </div>'
                + '</div>';

            slot.dataset.assetId = String(asset.id);

            refreshSummary();
        }

        function clearSlot(slot) {
            const input = slot.querySelector('input[type="hidden"]');
            const slotIndex = parseInt(slot.dataset.slotIndex || '1', 10);
            const slotLabel = slot.dataset.slotLabel || ('Image ' + slotIndex);

            if (input) {
                input.value = '';
            }

            slot.dataset.assetId = '';
            slot.querySelector('.image-slot-inner').innerHTML = createEmptySlotMarkup(slotIndex, slotLabel);

            refreshSummary();
        }

        function createSlot(sectionId, slotIndex, slotLabel) {
            const slot = document.createElement('div');
            slot.className = 'image-slot';
            slot.dataset.sectionId = sectionId;
            slot.dataset.slotIndex = slotIndex;
            slot.dataset.slotLabel = slotLabel;
            slot.dataset.assetId = '';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'section_assets[' + sectionId + '][' + slotIndex + ']';
            input.value = '';

            const inner = document.createElement('div');
            inner.className = 'image-slot-inner';
            inner.innerHTML = createEmptySlotMarkup(slotIndex, slotLabel);

            slot.appendChild(input);
            slot.appendChild(inner);

            return slot;
        }

        function renderSectionAssets(template) {
            sectionAssetsContainer.innerHTML = '';

            const selectableSections = (template.sections || []).filter(function (section) {
                return !!section.requires_assets;
            });

            if (!selectableSections.length) {
                sectionAssetsWrap.style.display = 'none';
                refreshSummary();
                return;
            }

            selectableSections.forEach(function (section) {
                const sectionId = section.section_id;
                const count = getSectionAssetCount(section);

                const card = document.createElement('div');
                card.className = 'template-section-card';

                const header = document.createElement('div');
                header.className = 'template-section-head';

                const headLeft = document.createElement('div');

                const title = document.createElement('h4');
                title.className = 'template-section-title';
                title.textContent = section.label || section.name || ('Section #' + sectionId);

                const meta = document.createElement('div');
                meta.className = 'template-section-meta';
                meta.textContent = getSectionTypeLabel(section) + ' • Assign ' + count + ' image' + (count === 1 ? '' : 's');

                headLeft.appendChild(title);
                headLeft.appendChild(meta);

                const countBadge = document.createElement('div');
                countBadge.className = 'template-section-count';
                countBadge.textContent = count + ' slot' + (count === 1 ? '' : 's');

                header.appendChild(headLeft);
                header.appendChild(countBadge);

                const grid = document.createElement('div');
                grid.className = 'section-slots-grid';

                for (let i = 1; i <= count; i++) {
                    let slotLabel = 'Image ' + i;

                    if (count === 1) {
                        slotLabel = 'Highlighted Image';
                    } else if (String(section.type || '').toLowerCase() === 'carousel') {
                        slotLabel = 'Carousel Image ' + i;
                    }

                    grid.appendChild(createSlot(sectionId, i, slotLabel));
                }

                card.appendChild(header);
                card.appendChild(grid);
                sectionAssetsContainer.appendChild(card);
            });

            sectionAssetsWrap.style.display = '';
            refreshSummary();
        }

        function renderTemplateDependentUi(templateId) {
            const template = templatesPayload[templateId];

            customFieldsContainer.innerHTML = '';
            sectionAssetsContainer.innerHTML = '';
            state.activeSlot = null;

            if (!template) {
                customFieldsWrap.style.display = 'none';
                sectionAssetsWrap.style.display = 'none';
                passwordWrap.style.display = 'none';
                footerSummary.textContent = 'Select a template to begin.';
                return;
            }

            if (parseInt(template.password_enabled, 10) === 1) {
                passwordWrap.style.display = '';
            } else {
                passwordWrap.style.display = 'none';
                if (passwordInput) {
                    passwordInput.value = '';
                }
            }

            renderCustomFields(template);
            renderSectionAssets(template);
        }

        function refreshActiveSlotHighlight() {
            document.querySelectorAll('.image-slot').forEach(function (slot) {
                slot.classList.remove('is-active');
            });

            document.querySelectorAll('.asset-thumb').forEach(function (thumb) {
                thumb.classList.remove('active-target-match');
            });

            if (state.activeSlot) {
                state.activeSlot.classList.add('is-active');

                document.querySelectorAll('.asset-thumb').forEach(function (thumb) {
                    thumb.classList.add('active-target-match');
                });
            }
        }

        function setActiveSlot(slot) {
            state.activeSlot = slot;
            refreshActiveSlotHighlight();
        }

        function refreshSummary() {
            const templateId = templateSelect ? templateSelect.value : '';
            const template = templateId ? templatesPayload[templateId] : null;

            if (!template) {
                footerSummary.textContent = 'Select a template to begin.';
                return;
            }

            const slots = sectionAssetsContainer.querySelectorAll('.image-slot input[type="hidden"]');
            const filled = Array.from(slots).filter(function (input) {
                return !!String(input.value || '').trim();
            }).length;

            const customFields = customFieldsContainer.querySelectorAll('[name^="custom_field_values["]').length;

            let message = filled + ' of ' + slots.length + ' image slot' + (slots.length === 1 ? '' : 's') + ' assigned';
            if (customFields > 0) {
                message += ' • ' + customFields + ' text field' + (customFields === 1 ? '' : 's') + ' available';
            }

            footerSummary.textContent = message;
        }

        function assignAssetToSlot(slot, assetId) {
            if (!slot || !assetId) {
                return;
            }

            fillSlot(slot, assetId);
            setActiveSlot(slot);
        }

        renderFolderPreview('');

        if (assetSearch) {
            assetSearch.addEventListener('input', function () {
                renderFolderPreview(this.value);
            });
        }

        if (templateSelect) {
            templateSelect.addEventListener('change', function () {
                renderTemplateDependentUi(this.value);
            });

            if (templateSelect.value) {
                renderTemplateDependentUi(templateSelect.value);
            }
        }

        document.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.slot-remove-btn');
            if (removeBtn) {
                const slot = removeBtn.closest('.image-slot');
                if (slot) {
                    clearSlot(slot);
                    setActiveSlot(slot);
                }
                return;
            }

            const slot = e.target.closest('.image-slot');
            if (slot) {
                setActiveSlot(slot);
                return;
            }

            const assetThumb = e.target.closest('.asset-thumb');
            if (assetThumb && state.activeSlot) {
                assignAssetToSlot(state.activeSlot, assetThumb.dataset.assetId);
            }
        });

        document.addEventListener('dragstart', function (e) {
            const item = e.target.closest('.draggable-asset');
            if (!item) {
                return;
            }

            e.dataTransfer.setData('assetId', item.dataset.assetId);
            e.dataTransfer.effectAllowed = 'copy';
        });

        document.addEventListener('dragover', function (e) {
            const slot = e.target.closest('.image-slot');
            if (!slot) {
                return;
            }

            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            slot.classList.add('is-over');
        });

        document.addEventListener('dragleave', function (e) {
            const slot = e.target.closest('.image-slot');
            if (!slot) {
                return;
            }

            slot.classList.remove('is-over');
        });

        document.addEventListener('drop', function (e) {
            const slot = e.target.closest('.image-slot');
            if (!slot) {
                return;
            }

            e.preventDefault();
            slot.classList.remove('is-over');

            const assetId = e.dataTransfer.getData('assetId');
            if (!assetId) {
                return;
            }

            assignAssetToSlot(slot, assetId);
        });

        document.addEventListener('change', function (e) {
            if (
                e.target.matches('[name^="custom_field_values["]') ||
                e.target.matches('[name^="section_assets["]')
            ) {
                refreshSummary();
            }
        });

        const publishForm = document.getElementById('publish-page-form');
        if (publishForm) {
            publishForm.addEventListener('submit', function () {
                state.activeSlot = null;
                refreshActiveSlotHighlight();
            });
        }
    });
</script>