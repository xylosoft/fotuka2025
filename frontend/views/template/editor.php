<?php
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;

$this->title = $template->isNewRecord ? 'New Template' : 'Edit Template: ' . $template->name;

$sectionTypes = [
    'header_image' => 'Highlighted Image',
    'image_carousel' => 'Image Carousel',
    'logo' => 'Logo',
    'company_name' => 'Company Name',
    'single_image' => 'Image Row',
    'gallery' => 'Gallery',
    'text_block' => 'Text Block',
];
?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@jaames/iro@5"></script>

<div class="template-editor-page">
    <div class="editor-shell">
        <div class="editor-topbar">
            <div>
                <h1><?= Html::encode($this->title) ?></h1>
                <p>Simple page building for folders, galleries, and published pages.</p>
            </div>
            <div>
                <a href="/templates" class="btn btn-default">Back</a>
            </div>
        </div>

        <?php $form = ActiveForm::begin(['id' => 'template-editor-form']); ?>

        <div class="editor-grid">
            <div class="editor-left">
                <div class="editor-card">
                    <h3>Template Settings</h3>

                    <?= $form->field($template, 'name')->textInput(['maxlength' => true]) ?>

                    <div class="checkbox-row" style="padding-top:20px">
                        <label>
                            <input type="checkbox" name="Template[password_enabled]" value="1" <?= $template->password_enabled ? 'checked' : '' ?>>
                            Password Protect Page
                        </label>
                    </div>

                    <div class="checkbox-row">
                        <label>
                            <input type="checkbox" name="Template[allow_downloads]" value="1" <?= $template->allow_downloads ? 'checked' : '' ?>>
                            Allow Downloads
                        </label>
                    </div>
                </div>

                <div class="editor-card">
                    <div class="card-title-row">
                        <h3>Theme Colors</h3>
                        <span class="small-muted">Applied instantly to the preview</span>
                    </div>

                    <div class="theme-grid">
                        <div class="theme-row">
                            <div class="theme-row-label">Page Background</div>
                            <button type="button" class="color-swatch js-color-swatch" data-scope="theme" data-key="page_background_color">
                                <span class="swatch-fill" style="background: <?= Html::encode($theme['page_background_color']) ?>;"></span>
                            </button>
                        </div>

                        <div class="theme-row">
                            <div class="theme-row-label">Page Text</div>
                            <button type="button" class="color-swatch js-color-swatch" data-scope="theme" data-key="page_text_color">
                                <span class="swatch-fill" style="background: <?= Html::encode($theme['page_text_color']) ?>;"></span>
                            </button>
                        </div>

                        <div class="theme-row">
                            <div class="theme-row-label">Accent</div>
                            <button type="button" class="color-swatch js-color-swatch" data-scope="theme" data-key="accent_color">
                                <span class="swatch-fill" style="background: <?= Html::encode($theme['accent_color']) ?>;"></span>
                            </button>
                        </div>

                        <div class="theme-row">
                            <div class="theme-row-label">Button</div>
                            <button type="button" class="color-swatch js-color-swatch" data-scope="theme" data-key="button_color">
                                <span class="swatch-fill" style="background: <?= Html::encode($theme['button_color']) ?>;"></span>
                            </button>
                        </div>

                        <div class="theme-row">
                            <div class="theme-row-label">Button Text</div>
                            <button type="button" class="color-swatch js-color-swatch" data-scope="theme" data-key="button_text_color">
                                <span class="swatch-fill" style="background: <?= Html::encode($theme['button_text_color']) ?>;"></span>
                            </button>
                        </div>

                        <div class="theme-row">
                            <div class="theme-row-label">Section Background</div>
                            <button type="button" class="color-swatch js-color-swatch" data-scope="theme" data-key="section_background_color">
                                <span class="swatch-fill" style="background: <?= Html::encode($theme['section_background_color']) ?>;"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="editor-card">
                    <h3>Add Sections</h3>
                    <div class="section-button-grid">
                        <?php foreach ($sectionTypes as $type => $label): ?>
                            <button type="button" class="btn btn-outline-primary add-section-btn" data-type="<?= Html::encode($type) ?>">
                                <?= Html::encode($label) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="section-list" class="section-list"></div>

                <div class="editor-card custom-fields-card">
                    <div class="card-title-row">
                        <h3>Custom Fields</h3>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="add-custom-field-btn">+ Add Custom Field</button>
                    </div>
                    <div id="custom-field-list"></div>
                </div>
            </div>

            <div class="editor-right">
                <div class="editor-card sticky-preview-card">
                    <div class="card-title-row">
                        <h3>Live Page Feel</h3>
                        <span class="small-muted">Always visible while editing</span>
                    </div>

                    <div id="template-stage" class="template-stage">
                        <div class="template-stage-inner" id="template-stage-inner"></div>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="theme_json" id="theme-json-input">
        <input type="hidden" name="sections_json" id="sections-json-input">
        <input type="hidden" name="custom_fields_json" id="custom-fields-json-input">

        <div class="editor-footer">
            <button type="submit" class="btn btn-primary btn-lg">Save Template</button>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<div id="shared-color-picker-popover" class="shared-color-picker-popover">
    <div id="shared-color-picker"></div>
</div>

<style>
    .template-editor-page {
        padding: 22px;
        background: #f8fafc;
    }
    .editor-shell {
        max-width: 1500px;
        margin: 0 auto;
    }
    .editor-topbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }
    .editor-topbar h1 {
        margin: 0 0 6px;
        font-size: 30px;
        font-weight: 700;
    }
    .editor-topbar p {
        margin: 0;
        color: #6b7280;
    }
    .editor-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 460px;
        gap: 18px;
        align-items: start;
    }
    .editor-left {
        min-width: 0;
    }
    .editor-right {
        position: sticky;
        top: 60px;
        align-self: start;
    }
    .editor-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 18px;
        margin-bottom: 18px;
        box-shadow: 0 12px 30px rgba(0,0,0,.04);
    }
    .editor-card h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
    }
    .card-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }
    .small-muted {
        color: #6b7280;
        font-size: 12px;
    }
    .checkbox-row {
        margin-bottom: 12px;
    }
    .theme-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .theme-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fafbfc;
    }
    .theme-row-label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }
    .color-swatch {
        width: 36px;
        height: 36px;
        border: 1px solid #d1d5db;
        background: #fff;
        border-radius: 999px;
        padding: 4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .color-swatch .swatch-fill {
        width: 100%;
        height: 100%;
        border-radius: 999px;
        display: block;
    }
    .shared-color-picker-popover {
        position: absolute;
        z-index: 3000;
        display: none;
        background: #fff;
        border: 1px solid #dbe3ee;
        border-radius: 16px;
        padding: 14px;
        box-shadow: 0 18px 40px rgba(0,0,0,.18);
    }
    .shared-color-picker-popover.open {
        display: block;
    }
    .section-button-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    .section-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 18px;
    }
    .section-card,
    .field-card {
        border: 1px solid #dbe3ee;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(0,0,0,.03);
        transition: box-shadow .2s ease, border-color .2s ease, transform .2s ease;
    }
    .section-card.flash-target {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(37,99,235,.16), 0 14px 30px rgba(0,0,0,.07);
    }
    .section-card-header,
    .field-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-bottom: 1px solid #eef2f7;
    }
    .section-card-title,
    .field-card-title {
        font-size: 15px;
        font-weight: 700;
    }
    .section-card-actions,
    .field-card-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .collapse-btn {
        width: 28px;
        height: 28px;
        border: 1px solid #dbe3ee;
        background: #fff;
        border-radius: 999px;
        font-size: 13px;
        line-height: 1;
        cursor: pointer;
    }
    .remove-btn-small {
        padding: 4px 9px;
        font-size: 11px;
        line-height: 1.2;
        border-radius: 999px;
        border: 1px solid #ef4444;
        background: #fff;
        color: #ef4444;
        cursor: pointer;
    }
    .remove-btn-small:hover {
        background: #fee2e2;
    }
    .section-card-body,
    .field-card-body {
        padding: 14px;
    }
    .section-card-body.collapsed,
    .field-card-body.collapsed {
        display: none;
    }
    .section-card-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .section-card-grid .full,
    .field-grid .full {
        grid-column: 1 / -1;
    }
    .section-card-grid label,
    .field-grid label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        color: #475569;
        font-weight: 600;
    }
    .field-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .helper-note {
        font-size: 12px;
        color: #64748b;
        margin-top: -4px;
        margin-bottom: 10px;
    }
    .sticky-preview-card {
        margin-bottom: 0;
    }
    .template-stage {
        border-radius: 18px;
        overflow: auto;
        border: 1px solid #e5e7eb;
        background: #fff;
        min-height: 620px;
        max-height: calc(100vh - 140px);
    }
    .template-stage-inner {
        min-height: 620px;
        padding: 18px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .stage-section {
        background: var(--stage-section-bg, #fff);
        color: var(--stage-text-color, #111827);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 26px rgba(0,0,0,.06);
    }
    .stage-highlighted-image {
        position: relative;
        min-height: 260px;
        background: linear-gradient(135deg, #cbd5e1, #94a3b8);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .stage-highlighted-image::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,.12), rgba(0,0,0,.38));
    }
    .stage-highlighted-text {
        position: relative;
        z-index: 2;
        color: #fff;
        font-weight: 700;
        padding: 28px;
        width: 100%;
        white-space: pre-line;
    }
    .stage-carousel {
        padding: 10px 12px;
        background: #f8fafc;
    }
    .stage-carousel-track {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        justify-content: flex-start;
        flex-wrap: nowrap;
    }
    .stage-carousel-slide {
        flex: 0 0 88px;
        width: 88px;
        height: 88px;
        min-height: 88px;
        border-radius: 16px;
        background: linear-gradient(135deg, #dbeafe, #c4b5fd);
    }
    .stage-brand-row {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 22px 24px;
        flex-wrap: wrap;
    }
    .stage-logo-box {
        height: 74px;
        border-radius: 16px;
        background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
        flex: 0 0 auto;
    }
    .stage-company-name {
        font-weight: 700;
        line-height: 1.2;
    }
    .stage-image-row {
        padding: 18px;
    }
    .stage-image-row-grid {
        display: grid;
        gap: 14px;
    }
    .stage-image-box {
        min-height: 160px;
        border-radius: 16px;
        background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
    }
    .stage-text-block {
        padding: 24px;
        white-space: pre-line;
    }
    .stage-gallery {
        padding: 18px;
    }
    .stage-gallery-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        grid-auto-rows: 16px;
        gap: 8px;
    }
    .stage-gallery-tile {
        border-radius: 14px;
        background: linear-gradient(135deg, #dbeafe, #e9d5ff);
    }
    .stage-gallery-tile.feature {
        grid-column: span 7;
        grid-row: span 5;
    }
    .stage-gallery-tile.tall {
        grid-column: span 5;
        grid-row: span 5;
    }
    .stage-gallery-tile.standard {
        grid-column: span 4;
        grid-row: span 3;
    }
    .stage-gallery-tile.wide {
        grid-column: span 6;
        grid-row: span 3;
    }
    .editor-footer {
        margin-top: 18px;
        display: flex;
        justify-content: flex-end;
    }

    @media (max-width: 1200px) {
        .editor-grid {
            grid-template-columns: 1fr;
        }

        .editor-right {
            position: static;
            top: auto;
        }

        .template-stage {
            max-height: none;
        }
    }

    @media (max-width: 900px) {
        .editor-grid {
            grid-template-columns: 1fr;
        }

        .editor-right {
            position: static;
            top: auto;
        }

        .template-stage {
            max-height: none;
        }
    }

    @media (max-width: 640px) {
        .theme-grid,
        .field-grid,
        .section-card-grid,
        .section-button-grid {
            grid-template-columns: 1fr;
        }
    }
    .field-template-name .help-block,
    .field-template-name .invalid-feedback,
    .has-error .help-block {
        color: #dc2626 !important;
        font-size: 13px;
        margin-top: 6px;
    }

    .field-template-name.has-error .form-control,
    .has-error .form-control {
        border-color: #dc2626 !important;
        box-shadow: none;
    }

    .field-template-name.has-error label,
    .has-error label {
        color: #dc2626;
    }
    .error-scroll-target {
        animation: errorPulse 1.2s ease;
    }

    @keyframes errorPulse {
        0%   { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.00); }
        30%  { box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.15); }
        100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.00); }
    }
</style>

<script>
    const initialSections = <?= Json::htmlEncode($sectionsPayload) ?>;
    const initialCustomFields = <?= Json::htmlEncode($customFieldsPayload) ?>;
    const initialTheme = <?= Json::htmlEncode($theme) ?>;

    const TYPE_LABELS = {
        header_image: 'Highlighted Image',
        image_carousel: 'Image Carousel',
        logo: 'Logo',
        company_name: 'Company Name',
        single_image: 'Image Row',
        gallery: 'Gallery',
        text_block: 'Text Block'
    };

    let sections = Array.isArray(initialSections) ? initialSections : [];
    let customFields = Array.isArray(initialCustomFields) ? initialCustomFields : [];
    let theme = Object.assign({}, initialTheme);

    let sharedPicker = null;
    let activeColorTarget = null;

    function scrollToFirstError() {
        const $form = $('#template-editor-form');
        const $firstError = $form.find('.has-error:visible').first();

        if (!$firstError.length) {
            return;
        }

        const top = Math.max($firstError.offset().top - 24, 0);

        $('html, body').stop(true).animate({
            scrollTop: top
        }, 300, function () {
            $firstError.addClass('error-scroll-target');

            const $input = $firstError.find('input, textarea, select').filter(':visible').first();
            if ($input.length) {
                $input.trigger('focus');
            }

            setTimeout(function () {
                $firstError.removeClass('error-scroll-target');
            }, 1400);
        });
    }

    function uuid(prefix) {
        return prefix + '_' + Math.random().toString(36).slice(2) + '_' + Date.now();
    }

    function typeLabel(type) {
        return TYPE_LABELS[type] || type;
    }

    function normalizeSection(section) {
        section.label = typeLabel(section.type);
        section.is_locked = 0;
        section.collapsed = !!section.collapsed;
        section.text = section.text || '';
        section.text_color = section.text_color || '#111827';
        section.settings = section.settings || {};
        section.settings.font_size = section.settings.font_size || (section.type === 'company_name' ? 32 : (section.type === 'text_block' ? 20 : 34));
        section.settings.text_align = section.settings.text_align || (section.type === 'text_block' ? 'left' : 'center');
        section.settings.text_mode = section.settings.text_mode || ((section.type === 'header_image' || section.type === 'text_block') ? 'static' : '');
        section.settings.logo_width = parseInt(section.settings.logo_width || 180, 10);

        if (section.type === 'company_name') {
            section.settings.own_line = !!section.settings.own_line;
        }

        if (section.type === 'header_image') {
            section.height = parseInt(section.height || 200, 10);
            section.image_count = 1;
            section.width = 12;
        }

        if (section.type === 'image_carousel') {
            section.height = parseInt(section.height || 140, 10);
            section.image_count = parseInt(section.image_count || 5, 10);
        }

        if (section.type === 'single_image') {
            section.image_count = parseInt(section.image_count || 3, 10);
        }
    }

    function normalizeCustomField(field) {
        field.collapsed = !!field.collapsed;
        field.name = field.name || '';
        field.slug = field.slug || slugify(field.name);
        field.text_color = field.text_color || '#111827';
        field.font_size = parseInt(field.font_size || 16, 10);
        field.font_weight = field.font_weight || '400';
        field.font_style = field.font_style || 'normal';
        field.text_align = field.text_align || 'left';
    }

    function sanitizeLoadedState() {
        sections.forEach(normalizeSection);
        customFields.forEach(normalizeCustomField);
    }

    function createDefaultSection(type) {
        const base = {
            section_key: uuid('section'),
            type: type,
            label: typeLabel(type),
            row_no: sections.length + 1,
            width: 12,
            height: '',
            sort_order: sections.length + 1,
            is_locked: 0,
            collapsed: false,
            text: '',
            custom_field_id: '',
            custom_field_key: '',
            background_color: '',
            text_color: '#111827',
            image_count: 0,
            settings: {
                text_mode: '',
                font_size: 34,
                text_align: 'center',
                logo_width: 180
            }
        };

        if (type === 'header_image') {
            base.height = 200;
            base.image_count = 1;
            base.text = 'A beautiful highlighted moment';
            base.text_color = '#ffffff';
            base.settings.text_mode = 'static';
            base.settings.font_size = 42;
            base.settings.text_align = 'center';
        }

        if (type === 'image_carousel') {
            base.height = 240;
            base.image_count = 5;
        }

        if (type === 'logo') {
            base.settings.logo_width = 180;
        }

        if (type === 'company_name') {
            base.settings.font_size = 32;
            base.settings.text_align = 'left';
            base.text_color = '#111827';
            base.settings.own_line = false;
        }

        if (type === 'single_image') {
            base.image_count = 3;
        }

        if (type === 'text_block') {
            base.text = 'Sample text block';
            base.settings.text_mode = 'static';
            base.settings.font_size = 20;
            base.settings.text_align = 'left';
        }

        return base;
    }

    function createDefaultField() {
        return {
            field_key: uuid('field'),
            name: '',
            slug: '',
            text_color: '#111827',
            font_size: 16,
            font_weight: '400',
            font_style: 'normal',
            text_align: 'left',
            collapsed: false
        };
    }

    function buildFieldOptions(selectedKey) {
        return customFields.map(field => {
            const selected = selectedKey === field.field_key ? 'selected' : '';
        return `<option value="${field.field_key}" ${selected}>${escapeHtml(field.name || '(Unnamed field)')}</option>`;
    }).join('');
    }

    function scrollToSectionCard(sectionKey) {
        const $card = $(`#section-list .section-card[data-key="${sectionKey}"]`);
        if (!$card.length) {
            return;
        }

        const top = Math.max($card.offset().top - 18, 0);

        $('html, body').stop(true).animate({
            scrollTop: top
        }, 280, function () {
            $card.addClass('flash-target');

            const $firstInput = $card.find('textarea, input, select').filter(':visible').first();
            if ($firstInput.length) {
                $firstInput.trigger('focus');
            }

            setTimeout(function () {
                $card.removeClass('flash-target');
            }, 1600);
        });
    }

    function renderSections() {
        hideColorPopover();

        const list = $('#section-list');
        list.empty();

        sections.forEach((section, index) => {
            normalizeSection(section);

        const supportsText = ['header_image', 'text_block'].includes(section.type);
        const supportsTextStyle = ['header_image', 'text_block', 'company_name'].includes(section.type);
        const showHeight = ['header_image', 'image_carousel'].includes(section.type);
        const showImageCountDropdown = ['image_carousel', 'single_image'].includes(section.type);
        const showLogoWidth = section.type === 'logo';

        const imageCountOptions = section.type === 'single_image'
            ? [1, 2, 3, 4, 5].map(n => `<option value="${n}" ${section.image_count == n ? 'selected' : ''}>${n}</option>`).join('')
    : [3, 4, 5, 6, 7, 8, 9, 10].map(n => `<option value="${n}" ${section.image_count == n ? 'selected' : ''}>${n}</option>`).join('');

        const bodyClass = section.collapsed ? 'section-card-body collapsed' : 'section-card-body';
        const toggleIcon = section.collapsed ? '▸' : '▾';

        const companyOwnLineHtml = section.type === 'company_name' ? `
            <div class="full">
                <label style="display:flex;align-items:center;gap:8px;margin-bottom:0;">
                    <input type="checkbox" class="section-company-own-line" ${section.settings.own_line ? 'checked' : ''}>
                    Display Company Name on its own line
                </label>
            </div>
        ` : '';

        const card = $(`
            <div class="section-card" data-key="${section.section_key}">
                <div class="section-card-header">
                    <div class="section-card-title">${escapeHtml(typeLabel(section.type))}</div>
                    <div class="section-card-actions">
                        <button type="button" class="collapse-btn toggle-section-btn">${toggleIcon}</button>
                        <button type="button" class="remove-btn-small remove-section-btn">Remove</button>
                    </div>
                </div>

                <div class="${bodyClass}">
                    ${section.type === 'header_image' ? `<div class="helper-note">Single image selected later during publishing. This section is always full width.</div>` : ''}
                    ${section.type === 'image_carousel' ? `<div class="helper-note">Images are selected during publishing. This section only controls height and how many images can be selected.</div>` : ''}
                    ${section.type === 'single_image' ? `<div class="helper-note">Images are selected during publishing. The preview shows the future row layout.</div>` : ''}
                    ${section.type === 'gallery' ? `<div class="helper-note">All folder images will be included automatically when the page is published.</div>` : ''}
                    ${section.type === 'logo' ? `<div class="helper-note">If Logo and Company Name are next to each other in the list, they will be rendered on the same row.</div>` : ''}

                    <div class="section-card-grid">
                        ${showHeight ? `
                        <div>
                            <label>Height (px)</label>
                            <input type="number" class="form-control section-height" value="${escapeAttr(section.height || '')}" min="120">
                        </div>
                        ` : ''}

                        ${showImageCountDropdown ? `
                        <div>
                            <label>${section.type === 'single_image' ? 'Images in Row' : 'Images in Carousel'}</label>
                            <select class="form-control section-image-count">
                                ${imageCountOptions}
                            </select>
                        </div>
                        ` : ''}

                        ${showLogoWidth ? `
                        <div>
                            <label>Logo Width (px)</label>
                            <input type="number" class="form-control section-logo-width" value="${escapeAttr(section.settings.logo_width || 180)}" min="60" max="600">
                        </div>
                        ` : ''}

                        ${supportsText ? `
                        <div>
                            <label>Text Source</label>
                            <select class="form-control section-text-mode">
                                <option value="static" ${(section.settings.text_mode || 'static') === 'static' ? 'selected' : ''}>Static Text</option>
                                <option value="custom_field" ${section.settings.text_mode === 'custom_field' ? 'selected' : ''}>Custom Field</option>
                            </select>
                        </div>
                        ` : ''}

                        ${(supportsText && section.settings.text_mode === 'custom_field') ? `
                        <div>
                            <label>Custom Field</label>
                            <select class="form-control section-custom-field-key">
                                <option value="">-- Select --</option>
                                ${buildFieldOptions(section.custom_field_key)}
                            </select>
                        </div>
                        ` : ''}

                        ${supportsTextStyle ? `
                        <div>
                            <label>Text Align</label>
                            <select class="form-control section-text-align">
                                <option value="left" ${section.settings.text_align === 'left' ? 'selected' : ''}>Left</option>
                                <option value="center" ${section.settings.text_align === 'center' ? 'selected' : ''}>Center</option>
                                <option value="right" ${section.settings.text_align === 'right' ? 'selected' : ''}>Right</option>
                            </select>
                        </div>
                        ` : ''}

                        ${companyOwnLineHtml}

                        ${supportsTextStyle ? `
                        <div>
                            <label>Font Size (px)</label>
                            <input type="number" class="form-control section-font-size" value="${escapeAttr(section.settings.font_size || 20)}" min="10" max="120">
                        </div>
                        ` : ''}

                        ${supportsTextStyle ? `
                        <div>
                            <label>Text Color</label>
                            <button type="button"
                                    class="color-swatch js-color-swatch"
                                    data-scope="section"
                                    data-index="${index}"
                                    data-key="text_color">
                                <span class="swatch-fill" style="background:${escapeAttr(section.text_color || '#111827')}"></span>
                            </button>
                        </div>
                        ` : ''}

                        ${(supportsText && section.settings.text_mode === 'static') ? `
                        <div class="full">
                            <label>Text</label>
                            <textarea class="form-control section-text" rows="${section.type === 'header_image' ? '3' : '4'}">${escapeHtml(section.text || '')}</textarea>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `);

        bindSectionCard(card, index);
        list.append(card);
    });

        renderStage();
        serializeState();
    }

    function bindSectionCard(card, index) {
        card.find('.toggle-section-btn').on('click', function() {
            sections[index].collapsed = !sections[index].collapsed;
            renderSections();
        });

        card.find('.remove-section-btn').on('click', function() {
            sections.splice(index, 1);
            renderSections();
        });

        card.find('.section-height').on('input', function() {
            sections[index].height = parseInt($(this).val() || 0, 10);
            renderStage();
            serializeState();
        });

        card.find('.section-image-count').on('change', function() {
            sections[index].image_count = parseInt($(this).val(), 10);
            renderStage();
            serializeState();
        });

        card.find('.section-logo-width').on('input', function() {
            sections[index].settings.logo_width = parseInt($(this).val() || 180, 10);
            renderStage();
            serializeState();
        });

        card.find('.section-text-mode').on('change', function() {
            sections[index].settings.text_mode = $(this).val();
            renderSections();
        });

        card.find('.section-custom-field-key').on('change', function() {
            sections[index].custom_field_key = $(this).val();
            renderStage();
            serializeState();
        });

        card.find('.section-text-align').on('change', function() {
            sections[index].settings.text_align = $(this).val();
            renderStage();
            serializeState();
        });

        card.find('.section-font-size').on('input', function() {
            sections[index].settings.font_size = parseInt($(this).val() || 20, 10);
            renderStage();
            serializeState();
        });

        card.find('.section-text').on('input', function() {
            sections[index].text = $(this).val();
            renderStage();
            serializeState();
        });

        card.find('.section-company-own-line').on('change', function() {
            sections[index].settings.own_line = $(this).is(':checked');
            renderStage();
            serializeState();
        });
    }

    function renderCustomFields() {
        hideColorPopover();

        const wrap = $('#custom-field-list');
        wrap.empty();

        customFields.forEach((field, index) => {
            normalizeCustomField(field);

        const bodyClass = field.collapsed ? 'field-card-body collapsed' : 'field-card-body';
        const toggleIcon = field.collapsed ? '▸' : '▾';

        const card = $(`
            <div class="field-card" data-key="${field.field_key}">
                <div class="field-card-header">
                    <div class="field-card-title">${escapeHtml(field.name || 'Custom Field')}</div>
                    <div class="field-card-actions">
                        <button type="button" class="collapse-btn toggle-field-btn">${toggleIcon}</button>
                        <button type="button" class="remove-btn-small remove-field-btn">Remove</button>
                    </div>
                </div>

                <div class="${bodyClass}">
                    <div class="field-grid">
                        <div class="full">
                            <label>Name</label>
                            <input type="text" class="form-control field-name" value="${escapeAttr(field.name || '')}" placeholder="Example: Address">
                        </div>

                        <div>
                            <label>Text Color</label>
                            <button type="button"
                                    class="color-swatch js-color-swatch"
                                    data-scope="field"
                                    data-index="${index}"
                                    data-key="text_color">
                                <span class="swatch-fill" style="background:${escapeAttr(field.text_color || '#111827')}"></span>
                            </button>
                        </div>

                        <div>
                            <label>Font Size (px)</label>
                            <input type="number" class="form-control field-font-size" value="${escapeAttr(field.font_size || 16)}" min="10" max="120">
                        </div>

                        <div>
                            <label>Weight</label>
                            <select class="form-control field-font-weight">
                                <option value="400" ${field.font_weight == '400' ? 'selected' : ''}>Normal</option>
                                <option value="600" ${field.font_weight == '600' ? 'selected' : ''}>Semi Bold</option>
                                <option value="700" ${field.font_weight == '700' ? 'selected' : ''}>Bold</option>
                            </select>
                        </div>

                        <div>
                            <label>Style</label>
                            <select class="form-control field-font-style">
                                <option value="normal" ${field.font_style === 'normal' ? 'selected' : ''}>Normal</option>
                                <option value="italic" ${field.font_style === 'italic' ? 'selected' : ''}>Italic</option>
                            </select>
                        </div>

                        <div>
                            <label>Align</label>
                            <select class="form-control field-text-align">
                                <option value="left" ${field.text_align === 'left' ? 'selected' : ''}>Left</option>
                                <option value="center" ${field.text_align === 'center' ? 'selected' : ''}>Center</option>
                                <option value="right" ${field.text_align === 'right' ? 'selected' : ''}>Right</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        `);

        card.find('.toggle-field-btn').on('click', function() {
            customFields[index].collapsed = !customFields[index].collapsed;
            renderCustomFields();
        });

        card.find('.field-name').on('input', function() {
            customFields[index].name = $(this).val();
            customFields[index].slug = slugify($(this).val());
            renderCustomFields();
            renderSections();
        });

        card.find('.field-font-size').on('input', function() {
            customFields[index].font_size = parseInt($(this).val() || 16, 10);
            serializeState();
            renderStage();
        });

        card.find('.field-font-weight').on('change', function() {
            customFields[index].font_weight = $(this).val();
            serializeState();
            renderStage();
        });

        card.find('.field-font-style').on('change', function() {
            customFields[index].font_style = $(this).val();
            serializeState();
            renderStage();
        });

        card.find('.field-text-align').on('change', function() {
            customFields[index].text_align = $(this).val();
            serializeState();
            renderStage();
        });

        card.find('.remove-field-btn').on('click', function() {
            const removedKey = field.field_key;
            customFields.splice(index, 1);

            sections.forEach(section => {
                if (section.custom_field_key === removedKey) {
                section.custom_field_key = '';
                if (section.settings && section.settings.text_mode === 'custom_field') {
                    section.settings.text_mode = 'static';
                }
            }
        });

            renderCustomFields();
            renderSections();
        });

        wrap.append(card);
    });

        serializeState();
    }

    function getSectionPreviewText(section) {
        const mode = section.settings && section.settings.text_mode ? section.settings.text_mode : 'static';

        if (section.type === 'company_name') {
            return 'Company Name';
        }

        if (mode === 'custom_field') {
            const field = customFields.find(item => item.field_key === section.custom_field_key);
            return field && field.name ? `{{${field.name}}}` : '{{Custom Field}}';
        }

        return section.text || typeLabel(section.type);
    }

    function buildStageBrandRow(logoSection, companySection, companyFirst = false) {
        const logoWidth = parseInt(logoSection.settings.logo_width || 180, 10);
        const companyText = 'Company Name';

        const brandRow = $(`
        <div class="stage-section">
            <div class="stage-brand-row">
                <div class="stage-logo-box"></div>
                <div class="stage-company-name">${escapeHtml(companyText)}</div>
            </div>
        </div>
    `);

        brandRow.find('.stage-logo-box').css({
            width: logoWidth + 'px'
        });

        brandRow.find('.stage-company-name').css({
            fontSize: (companySection.settings.font_size || 32) + 'px',
            color: companySection.text_color || theme.page_text_color,
            textAlign: companySection.settings.text_align || 'left'
        });

        if (companyFirst) {
            brandRow.find('.stage-brand-row').css({
                flexDirection: 'row-reverse',
                justifyContent: 'flex-end'
            });
        }

        return brandRow;
    }

    function renderStage() {
        const stage = $('#template-stage-inner');
        stage.empty();

        stage.css({
            background: theme.page_background_color,
            color: theme.page_text_color
        });

        for (let i = 0; i < sections.length; i++) {
            const section = sections[i];
            normalizeSection(section);

            const next = sections[i + 1] || null;

            if (
                section.type === 'logo' &&
                next &&
                next.type === 'company_name' &&
                !next.settings.own_line
            ) {
                stage.append(buildStageBrandRow(section, next, false));
                i++;
                continue;
            }

            if (
                section.type === 'company_name' &&
                next &&
                next.type === 'logo' &&
                !section.settings.own_line
            ) {
                stage.append(buildStageBrandRow(next, section, true));
                i++;
                continue;
            }

            if (section.type === 'header_image') {
                const box = $(`
                <div class="stage-section stage-highlighted-image">
                    <div class="stage-highlighted-text"></div>
                </div>
            `);

                box.css({
                    minHeight: (section.height || 420) + 'px'
                });

                box.find('.stage-highlighted-text').text(getSectionPreviewText(section)).css({
                    fontSize: (section.settings.font_size || 42) + 'px',
                    textAlign: section.settings.text_align || 'center',
                    color: section.text_color || '#ffffff'
                });

                stage.append(box);
                continue;
            }

            if (section.type === 'image_carousel') {
                const box = $(`
                    <div class="stage-section stage-carousel">
                        <div class="stage-carousel-track"></div>
                    </div>
                `);

                box.css({
                    minHeight: '0',
                    height: 'auto'
                });

                const track = box.find('.stage-carousel-track');
                const count = parseInt(section.image_count || 5, 10);

                for (let c = 0; c < Math.min(count, 8); c++) {
                    track.append('<div class="stage-carousel-slide"></div>');
                }

                stage.append(box);
                continue;
            }

            if (section.type === 'logo') {
                const box = $(`
                <div class="stage-section">
                    <div class="stage-brand-row">
                        <div class="stage-logo-box"></div>
                    </div>
                </div>
            `);

                box.find('.stage-logo-box').css({
                    width: (section.settings.logo_width || 180) + 'px'
                });

                stage.append(box);
                continue;
            }

            if (section.type === 'company_name') {
                const box = $(`
                <div class="stage-section">
                    <div class="stage-text-block stage-company-name-only">Company Name</div>
                </div>
            `);

                box.find('.stage-company-name-only').css({
                    fontSize: (section.settings.font_size || 32) + 'px',
                    textAlign: section.settings.text_align || 'left',
                    color: section.text_color || theme.page_text_color,
                    fontWeight: '700'
                });

                stage.append(box);
                continue;
            }

            if (section.type === 'single_image') {
                const count = parseInt(section.image_count || 3, 10);
                const box = $(`
                <div class="stage-section stage-image-row">
                    <div class="stage-image-row-grid"></div>
                </div>
            `);

                box.find('.stage-image-row-grid').css({
                    gridTemplateColumns: `repeat(${count}, minmax(0, 1fr))`
                });

                for (let c = 0; c < count; c++) {
                    box.find('.stage-image-row-grid').append('<div class="stage-image-box"></div>');
                }

                stage.append(box);
                continue;
            }

            if (section.type === 'gallery') {
                const box = $(`
                <div class="stage-section stage-gallery">
                    <div class="stage-gallery-grid">
                        <div class="stage-gallery-tile feature"></div>
                        <div class="stage-gallery-tile tall"></div>
                        <div class="stage-gallery-tile standard"></div>
                        <div class="stage-gallery-tile wide"></div>
                        <div class="stage-gallery-tile standard"></div>
                        <div class="stage-gallery-tile tall"></div>
                    </div>
                </div>
            `);
                stage.append(box);
                continue;
            }

            if (section.type === 'text_block') {
                const box = $(`
                <div class="stage-section">
                    <div class="stage-text-block"></div>
                </div>
            `);

                box.find('.stage-text-block').text(getSectionPreviewText(section)).css({
                    fontSize: (section.settings.font_size || 20) + 'px',
                    textAlign: section.settings.text_align || 'left',
                    color: section.text_color || theme.page_text_color
                });

                stage.append(box);
                continue;
            }
        }
    }

    function serializeState() {
        sections.forEach((section, idx) => {
            section.sort_order = idx + 1;
        section.label = typeLabel(section.type);

        if (section.type === 'header_image') {
            section.image_count = 1;
            section.width = 12;
        }
    });

        customFields.forEach((field, idx) => {
            field.sort_order = idx + 1;
        field.slug = slugify(field.name);
    });

        $('#theme-json-input').val(JSON.stringify(theme));
        $('#sections-json-input').val(JSON.stringify(sections));
        $('#custom-fields-json-input').val(JSON.stringify(customFields));
    }

    function slugify(value) {
        return (value || '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
    }

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function escapeAttr(value) {
        return escapeHtml(value).replace(/"/g, '&quot;');
    }

    function openColorPopover($btn) {
        const rect = $btn[0].getBoundingClientRect();
        const popover = $('#shared-color-picker-popover');

        popover.css({
            top: window.scrollY + rect.bottom + 8,
            left: window.scrollX + rect.left - 6
        });

        popover.show().addClass('open');
    }

    function hideColorPopover() {
        $('#shared-color-picker-popover').removeClass('open').hide();
        activeColorTarget = null;
    }

    function getTargetColor(target) {
        if (!target) return '#111827';

        if (target.scope === 'theme') {
            return theme[target.key] || '#111827';
        }

        if (target.scope === 'section') {
            const section = sections[parseInt(target.index, 10)];
            return section ? (section[target.key] || '#111827') : '#111827';
        }

        if (target.scope === 'field') {
            const field = customFields[parseInt(target.index, 10)];
            return field ? (field[target.key] || '#111827') : '#111827';
        }

        return '#111827';
    }

    function applyTargetColor(target, color) {
        if (!target) return;

        if (target.scope === 'theme') {
            theme[target.key] = color;
            $(`.js-color-swatch[data-scope="theme"][data-key="${target.key}"] .swatch-fill`).css('background', color);
            renderStage();
            serializeState();
            return;
        }

        if (target.scope === 'section') {
            const index = parseInt(target.index, 10);
            if (sections[index]) {
                sections[index][target.key] = color;
                $(`.js-color-swatch[data-scope="section"][data-index="${index}"][data-key="${target.key}"] .swatch-fill`).css('background', color);
                renderStage();
                serializeState();
            }
            return;
        }

        if (target.scope === 'field') {
            const index = parseInt(target.index, 10);
            if (customFields[index]) {
                customFields[index][target.key] = color;
                $(`.js-color-swatch[data-scope="field"][data-index="${index}"][data-key="${target.key}"] .swatch-fill`).css('background', color);
                renderStage();
                serializeState();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        sanitizeLoadedState();

        sharedPicker = new iro.ColorPicker('#shared-color-picker', {
            width: 180,
            color: theme.page_background_color || '#ffffff',
            borderWidth: 1,
            borderColor: '#ffffff'
        });

        sharedPicker.on('color:change', function(color) {
            if (!activeColorTarget) return;
            applyTargetColor(activeColorTarget, color.hexString);
        });

        sharedPicker.on('input:end', function(color) {
            if (!activeColorTarget) return;
            applyTargetColor(activeColorTarget, color.hexString);
            hideColorPopover();
        });

        $(document).on('click', '.js-color-swatch', function(e) {
            e.preventDefault();
            e.stopPropagation();

            activeColorTarget = {
                scope: $(this).data('scope'),
                key: $(this).data('key'),
                index: $(this).data('index')
            };

            const currentColor = getTargetColor(activeColorTarget);
            sharedPicker.color.hexString = currentColor;
            openColorPopover($(this));
        });

        $(document).on('click', function(e) {
            const popover = document.getElementById('shared-color-picker-popover');
            if (popover && !popover.contains(e.target) && !e.target.closest('.js-color-swatch')) {
                hideColorPopover();
            }
        });

        $('.add-section-btn').on('click', function() {
            const type = $(this).data('type');
            const newSection = createDefaultSection(type);

            sections.push(newSection);
            renderSections();

            setTimeout(function() {
                scrollToSectionCard(newSection.section_key);
            }, 60);
        });

        $('#add-custom-field-btn').on('click', function() {
            customFields.push(createDefaultField());
            renderCustomFields();
            renderSections();
        });

        new Sortable(document.getElementById('section-list'), {
            animation: 150,
            onEnd: function() {
                const keys = $('#section-list .section-card').map(function() {
                    return $(this).data('key');
                }).get();

                sections = keys.map(key => sections.find(section => section.section_key === key)).filter(Boolean);
                renderSections();
            }
        });

        setTimeout(function () {
            scrollToFirstError();
        }, 80);

        $('#template-editor-form').on('afterValidate', function (e, messages, errorAttributes) {
            if (errorAttributes && errorAttributes.length) {
                setTimeout(function () {
                    scrollToFirstError();
                }, 0);
            }
        });

        renderCustomFields();
        renderSections();
    });
</script>
