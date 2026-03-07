<?php
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;

$this->title = $template->isNewRecord ? 'New Template' : 'Edit Template: ' . $template->name;

$sectionTypes = [
    'header_image'   => 'Highlighted Image',
    'image_carousel' => 'Image Carousel',
    'logo'           => 'Logo',
    'company_name'   => 'Company Name',
    'single_image'   => 'Image Row',
    'gallery'        => 'Gallery',
    'text_block'     => 'Text Block',
];
?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@jaames/iro@5"></script>

<div class="template-editor-page">
    <div class="editor-shell">
        <div class="editor-topbar">
            <div>
                <h1><?= Html::encode($this->title) ?></h1>
                <p>Template Editor for publishing your folder photos to a custom webpage.</p>
            </div>
            <div class="editor-topbar-actions">
                <a href="/templates" class="btn btn-default">Back</a>
            </div>
        </div>

        <?php $form = ActiveForm::begin(['id' => 'template-editor-form']); ?>

        <div class="editor-grid">
            <div class="editor-left">
                <div class="editor-card">
                    <h3>Template Settings</h3>

                    <?= $form->field($template, 'name')->textInput(['maxlength' => true]) ?>

                    <div class="checkbox-row checkbox-row-spaced">
                        <label>
                            <input type="checkbox" name="Template[password_enabled]" value="1" <?= $template->password_enabled ? 'checked' : '' ?>>
                            Password Protect Page
                        </label>
                    </div>

                    <div class="checkbox-row">
                        <label>
                            <input type="checkbox" id="allow-downloads-checkbox" name="Template[allow_downloads]" value="1" <?= $template->allow_downloads ? 'checked' : '' ?>>
                            Allow Downloads
                        </label>
                    </div>
                </div>

                <div class="editor-card">
                    <div class="card-title-row">
                        <h3>Theme Colors</h3>
                    </div>

                    <div class="theme-grid">
                        <div class="theme-row">
                            <div class="theme-row-label">Page Background</div>
                            <button type="button" class="color-swatch js-color-swatch" data-scope="theme" data-key="page_background_color">
                                <span class="swatch-fill" style="background: <?= Html::encode($theme['page_background_color'] ?? '#edf1f5') ?>;"></span>
                            </button>
                        </div>

                        <div class="theme-row">
                            <div class="theme-row-label">Page Text</div>
                            <button type="button" class="color-swatch js-color-swatch" data-scope="theme" data-key="page_text_color">
                                <span class="swatch-fill" style="background: <?= Html::encode($theme['page_text_color'] ?? '#111827') ?>;"></span>
                            </button>
                        </div>

                        <div class="theme-row">
                            <div class="theme-row-label">Button</div>
                            <button type="button" class="color-swatch js-color-swatch" data-scope="theme" data-key="button_color">
                                <span class="swatch-fill" style="background: <?= Html::encode($theme['button_color'] ?? '#2563eb') ?>;"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="editor-card">
                    <h3>Add Sections</h3>
                    <div class="section-button-grid" id="section-button-grid">
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
                        <h3>Live Preview</h3>
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
            <button type="submit" class="btn btn-primary btn-save-template">Save Template</button>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<div id="shared-color-picker-popover" class="shared-color-picker-popover">
    <div id="shared-color-picker"></div>
</div>

<style>
    html, body {
        background: #edf1f5 !important;
    }

    .template-editor-page {
        min-height: 100vh;
        padding: 22px;
        background: #edf1f5;
    }

    .editor-shell {
        max-width: 1500px;
        margin: 0 auto;
    }

    .editor-topbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 20px;
    }

    .editor-topbar h1 {
        margin: 0 0 6px;
        font-size: 30px;
        font-weight: 700;
        color: #111827;
    }

    .editor-topbar p {
        margin: 0;
        color: #6b7280;
        font-size: 15px;
    }

    .editor-topbar-actions {
        flex: 0 0 auto;
    }

    .editor-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 500px;
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
        color: #111827;
    }

    .card-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .checkbox-row {
        margin-bottom: 12px;
    }

    .checkbox-row-spaced {
        padding-top: 20px;
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
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .color-swatch:hover {
        transform: scale(1.03);
        box-shadow: 0 6px 16px rgba(0,0,0,.08);
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

    .add-section-btn {
        min-height: 42px;
        font-size: 13px;
        font-weight: 600;
    }

    .add-section-btn.is-disabled,
    .add-section-btn:disabled {
        background: #e5e7eb !important;
        border-color: #d1d5db !important;
        color: #9ca3af !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
        opacity: 1;
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
        color: #111827;
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

    .section-card-grid.header-image-grid {
        grid-template-columns: 110px 150px 140px 110px auto;
        align-items: end;
    }

    .field-grid {
        display: grid;
        grid-template-columns: 1.6fr .8fr .8fr auto;
        gap: 12px;
        align-items: end;
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

    .field-token-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px;
        border: 1px dashed #dbe3ee;
        border-radius: 12px;
        background: #fafbfc;
        font-size: 12px;
        color: #64748b;
    }

    .field-token-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        font-weight: 700;
        font-size: 12px;
    }

    .section-color-inline {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fafbfc;
        height: 42px;
    }

    .section-color-inline span {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
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
        border: 1px solid #d9e0e8;
        background: #eef2f6;
        min-height: 620px;
        max-height: calc(100vh - 140px);
        padding: 14px;
    }

    .template-stage-inner {
        min-height: 590px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        border-radius: 16px;
        background: #eef2f6;
    }

    .stage-sortable {
        position: relative;
        border-radius: 18px;
        cursor: grab;
    }

    .stage-sortable:active {
        cursor: grabbing;
    }

    .stage-sortable.stage-sortable-active .stage-section {
        box-shadow: 0 0 0 3px rgba(37,99,235,.12), 0 18px 40px rgba(0,0,0,.09);
    }

    .stage-section {
        position: relative;
        background: rgba(255,255,255,.96);
        color: var(--stage-text-color, #111827);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 10px 24px rgba(15,23,42,.06);
        border: 1px solid rgba(203,213,225,.75);
        padding-top: 30px;
    }

    .stage-section-label {
        position: absolute;
        top: 10px;
        left: 14px;
        z-index: 5;
        color: #6b7280;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .02em;
        background: transparent;
        box-shadow: none;
        border-radius: 0;
        padding: 0;
        user-select: none;
        pointer-events: none;
    }

    .stage-page-header {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        min-height: 18px;
    }

    .stage-download-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 14px;
        border-radius: 999px;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        box-shadow: 0 8px 18px rgba(0,0,0,.10);
    }

    .stage-highlighted-image {
        padding: 38px 16px 16px;
        background: rgba(255,255,255,.96);
    }

    .stage-highlighted-inner {
        width: 100%;
        height: 100%;
        border-radius: 18px;
        background: #ffffff;
        border: 3px dashed #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .stage-highlighted-text {
        position: absolute;
        inset: 0;
        z-index: 2;
        font-weight: 700;
        padding: 20px 26px;
        width: 100%;
        white-space: pre-line;
        background: transparent !important;
    }

    .stage-carousel {
        padding: 38px 16px 16px;
        background: rgba(255,255,255,.92);
    }

    .stage-carousel-track {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        align-items: stretch;
    }

    .stage-brand-row {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 38px 24px 22px;
        flex-wrap: wrap;
    }

    .stage-logo-box {
        border-radius: 16px;
        background: #f8fafc;
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stage-company-name {
        font-weight: 700;
        line-height: 1.2;
    }

    .stage-company-name-only {
        padding-top: 14px;
    }

    .stage-image-row {
        padding: 38px 16px 16px;
    }

    .stage-image-row-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-start;
    }

    .stage-image-row-grid .image-placeholder.square {
        width: 88px;
        height: 88px;
        min-height: 88px;
        flex: 0 0 88px;
    }

    .stage-text-block {
        padding: 14px 24px 24px;
        white-space: pre-line;
    }

    .stage-gallery {
        padding: 38px 16px 16px;
    }

    .stage-gallery-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        align-items: stretch;
        justify-items: stretch;
    }

    .stage-gallery-grid .gallery-span-2 {
        grid-column: span 2;
    }

    .image-placeholder {
        border: 3px dashed #cbd5e1;
        background: #f3f4f6;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #374151;
        font-weight: 700;
        text-align: center;
        box-sizing: border-box;
    }

    .image-placeholder.small {
        min-height: 74px;
        font-size: 12px;
        border-radius: 16px;
    }

    .image-placeholder.square {
        aspect-ratio: 1 / 1;
        min-height: 74px;
        font-size: 12px;
        border-radius: 16px;
    }

    .image-placeholder.gallery-square {
        aspect-ratio: 1 / 1;
        min-height: 96px;
        font-size: 12px;
    }

    .image-placeholder.gallery-wide {
        aspect-ratio: 2 / 1;
        min-height: 96px;
        font-size: 12px;
    }

    .editor-footer {
        margin-top: 18px;
        display: flex;
        justify-content: flex-end;
    }

    .btn-save-template {
        font-size: 14px;
        font-weight: 700;
        padding: 9px 18px;
        border-radius: 10px;
    }

    .preview-empty-state {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 260px;
        border: 2px dashed #d5dce5;
        border-radius: 18px;
        background: rgba(255,255,255,.65);
        color: #64748b;
        font-weight: 600;
        text-align: center;
        padding: 30px;
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

    @media (max-width: 1280px) {
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

    @media (max-width: 980px) {
        .theme-grid,
        .section-button-grid {
            grid-template-columns: 1fr;
        }

        .section-card-grid,
        .section-card-grid.header-image-grid,
        .field-grid {
            grid-template-columns: 1fr;
        }

        .field-token-row {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 640px) {
        .template-editor-page {
            padding: 14px;
        }

        .editor-topbar {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
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

        const UNIQUE_SECTION_TYPES = ['header_image', 'image_carousel', 'logo', 'company_name', 'gallery'];

        let sections = Array.isArray(initialSections) ? initialSections : [];
        let customFields = Array.isArray(initialCustomFields) ? initialCustomFields : [];
        let theme = Object.assign({
            page_background_color: '#edf1f5',
            page_text_color: '#111827',
            button_color: '#2563eb'
        }, initialTheme || {});

        let sharedPicker = null;
        let activeColorTarget = null;
        let previewSortable = null;

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

        function isUniqueType(type) {
            return UNIQUE_SECTION_TYPES.includes(type);
        }

        function canAddSectionType(type) {
            if (!isUniqueType(type)) {
                return true;
            }
            return !sections.some(section => section.type === type);
        }

        function normalizeSection(section) {
            section.label = typeLabel(section.type);
            section.is_locked = 0;
            section.collapsed = !!section.collapsed;
            section.text = typeof section.text === 'string' ? section.text : '';
            section.text_color = section.text_color || (section.type === 'header_image' ? '#111827' : '#111827');
            section.settings = section.settings || {};
            section.settings.text_mode = section.settings.text_mode || ((section.type === 'header_image' || section.type === 'text_block') ? 'static' : '');
            section.settings.text_align = section.settings.text_align || (section.type === 'text_block' ? 'left' : (section.type === 'company_name' ? 'left' : 'center'));
            section.settings.logo_width = parseInt(section.settings.logo_width || 180, 10);

            if (section.type === 'company_name') {
                section.settings.font_size = parseInt(section.settings.font_size || 20, 10);
                section.settings.own_line = !!section.settings.own_line;
            } else if (section.type === 'header_image') {
                section.settings.font_size = parseInt(section.settings.font_size || 24, 10);
                section.height = parseInt(section.height || 300, 10);
                section.image_count = 1;
                section.width = 12;
            } else if (section.type === 'text_block') {
                section.settings.font_size = parseInt(section.settings.font_size || 20, 10);
            } else {
                section.settings.font_size = parseInt(section.settings.font_size || 20, 10);
            }

            if (section.type === 'image_carousel') {
                section.height = parseInt(section.height || 140, 10);
                section.image_count = parseInt(section.image_count || 5, 10);
            }

            if (section.type === 'single_image') {
                section.image_count = parseInt(section.image_count || 3, 10);
            }

            if (section.type === 'gallery') {
                section.image_count = 0;
            }
        }

        function normalizeCustomField(field) {
            field.collapsed = !!field.collapsed;
            field.name = field.name || '';
            field.slug = field.slug || slugify(field.name);
            field.text_color = field.text_color || '#111827';
            field.font_size = parseInt(field.font_size || 16, 10);
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
                    font_size: 20,
                    text_align: 'center',
                    logo_width: 180
                }
            };

            if (type === 'header_image') {
                base.height = 300;
                base.image_count = 1;
                base.text = '';
                base.text_color = '#111827';
                base.settings.text_mode = 'static';
                base.settings.font_size = 24;
                base.settings.text_align = 'center';
            }

            if (type === 'image_carousel') {
                base.height = 140;
                base.image_count = 5;
            }

            if (type === 'logo') {
                base.settings.logo_width = 180;
            }

            if (type === 'company_name') {
                base.settings.font_size = 20;
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
                base.text_color = '#111827';
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

        function getPreviewHeight(realHeight) {
            const parsed = parseInt(realHeight || 300, 10);
            const scaled = Math.round(parsed * 0.52);
            return Math.max(140, Math.min(300, scaled));
        }

        function getPreviewLogoSize(realWidth) {
            const parsed = parseInt(realWidth || 180, 10);
            const scaled = Math.round(parsed * 0.42);
            return Math.max(54, Math.min(120, scaled));
        }

        function renderAddSectionButtons() {
            $('#section-button-grid .add-section-btn').each(function () {
                const type = $(this).data('type');
                const disabled = !canAddSectionType(type);

                $(this).prop('disabled', disabled);
                $(this).toggleClass('is-disabled', disabled);
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

            const colorControlHtml = supportsTextStyle ? `
                <div class="section-color-inline">
                    <span>Color</span>
                    <button type="button"
                            class="color-swatch js-color-swatch"
                            data-scope="section"
                            data-index="${index}"
                            data-key="text_color">
                        <span class="swatch-fill" style="background:${escapeAttr(section.text_color || '#111827')}"></span>
                    </button>
                </div>
            ` : '';

            const gridClass = section.type === 'header_image' ? 'section-card-grid header-image-grid' : 'section-card-grid';

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
                        ${section.type === 'header_image' ? `<div class="helper-note">Single highlighted image selected later during publishing.</div>` : ''}
                        ${section.type === 'image_carousel' ? `<div class="helper-note">Images are selected during publishing. Preview always shows 4 sample items.</div>` : ''}
                        ${section.type === 'single_image' ? `<div class="helper-note">Images are selected during publishing. The preview shows a sample row layout.</div>` : ''}
                        ${section.type === 'gallery' ? `<div class="helper-note">All folder images will be included automatically when the page is published.</div>` : ''}

                        <div class="${gridClass}">
                            ${showHeight ? `
                            <div>
                                <label>Height (px)</label>
                                <input type="number" class="form-control section-height" value="${escapeAttr(section.height || '')}" min="120">
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

                            ${supportsTextStyle ? `
                            <div>
                                <label>Alignment</label>
                                <select class="form-control section-text-align">
                                    <option value="left" ${section.settings.text_align === 'left' ? 'selected' : ''}>Left</option>
                                    <option value="center" ${section.settings.text_align === 'center' ? 'selected' : ''}>Center</option>
                                    <option value="right" ${section.settings.text_align === 'right' ? 'selected' : ''}>Right</option>
                                </select>
                            </div>
                            ` : ''}

                            ${supportsTextStyle ? `
                            <div>
                                <label>Font Size (px)</label>
                                <input type="number" class="form-control section-font-size" value="${escapeAttr(section.settings.font_size || 20)}" min="10" max="120">
                            </div>
                            ` : ''}

                            ${section.type === 'header_image' ? `
                            <div>
                                <label>Color</label>
                                ${colorControlHtml}
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

                            ${(supportsText && section.settings.text_mode === 'custom_field') ? `
                            <div>
                                <label>Custom Field</label>
                                <select class="form-control section-custom-field-key">
                                    <option value="">-- Select --</option>
                                    ${buildFieldOptions(section.custom_field_key)}
                                </select>
                            </div>
                            ` : ''}

                            ${(supportsTextStyle && section.type !== 'header_image') ? `
                            <div>
                                <label>Color</label>
                                ${colorControlHtml}
                            </div>
                            ` : ''}

                            ${companyOwnLineHtml}

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

            renderAddSectionButtons();
            renderStage();
            serializeState();
        }

        function bindSectionCard(card, index) {
            card.find('.toggle-section-btn').on('click', function () {
                sections[index].collapsed = !sections[index].collapsed;
                renderSections();
            });

            card.find('.remove-section-btn').on('click', function () {
                sections.splice(index, 1);
                renderSections();
                renderCustomFields();
            });

            card.find('.section-height').on('input', function () {
                sections[index].height = parseInt($(this).val() || 0, 10);
                renderStage();
                serializeState();
            });

            card.find('.section-image-count').on('change', function () {
                sections[index].image_count = parseInt($(this).val(), 10);
                renderStage();
                serializeState();
            });

            card.find('.section-logo-width').on('input', function () {
                sections[index].settings.logo_width = parseInt($(this).val() || 180, 10);
                renderStage();
                serializeState();
            });

            card.find('.section-text-mode').on('change', function () {
                sections[index].settings.text_mode = $(this).val();
                renderSections();
            });

            card.find('.section-custom-field-key').on('change', function () {
                sections[index].custom_field_key = $(this).val();
                renderStage();
                serializeState();
            });

            card.find('.section-text-align').on('change', function () {
                sections[index].settings.text_align = $(this).val();
                renderStage();
                serializeState();
            });

            card.find('.section-font-size').on('input', function () {
                sections[index].settings.font_size = parseInt($(this).val() || 20, 10);
                renderStage();
                serializeState();
            });

            card.find('.section-text').on('input', function () {
                sections[index].text = $(this).val();
                renderStage();
                serializeState();
            });

            card.find('.section-company-own-line').on('change', function () {
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
            const tokenText = field.name ? '{{' + field.name + '}}' : '{{custom_field}}';

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
                            <div>
                                <label>Name</label>
                                <input type="text" class="form-control field-name" value="${escapeAttr(field.name || '')}" placeholder="Example: Address">
                            </div>

                            <div>
                                <label>Font Size (px)</label>
                                <input type="number" class="form-control field-font-size" value="${escapeAttr(field.font_size || 16)}" min="10" max="120">
                            </div>

                            <div>
                                <label>Align</label>
                                <select class="form-control field-text-align">
                                    <option value="left" ${field.text_align === 'left' ? 'selected' : ''}>Left</option>
                                    <option value="center" ${field.text_align === 'center' ? 'selected' : ''}>Center</option>
                                    <option value="right" ${field.text_align === 'right' ? 'selected' : ''}>Right</option>
                                </select>
                            </div>

                            <div>
                                <label>Color</label>
                                <button type="button"
                                        class="color-swatch js-color-swatch"
                                        data-scope="field"
                                        data-index="${index}"
                                        data-key="text_color">
                                    <span class="swatch-fill" style="background:${escapeAttr(field.text_color || '#111827')}"></span>
                                </button>
                            </div>

                            <div class="full">
                                <div class="field-token-row">
                                    <span>Reference token for text sections</span>
                                    <span class="field-token-pill">${escapeHtml(tokenText)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);

            card.find('.toggle-field-btn').on('click', function () {
                customFields[index].collapsed = !customFields[index].collapsed;
                renderCustomFields();
            });

            card.find('.field-name').on('input', function () {
                customFields[index].name = $(this).val();
                customFields[index].slug = slugify($(this).val());
                renderCustomFields();
                renderSections();
            });

            card.find('.field-font-size').on('input', function () {
                customFields[index].font_size = parseInt($(this).val() || 16, 10);
                serializeState();
                renderStage();
            });

            card.find('.field-text-align').on('change', function () {
                customFields[index].text_align = $(this).val();
                serializeState();
                renderStage();
            });

            card.find('.remove-field-btn').on('click', function () {
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

            return section.text || '';
        }

        function buildImagePlaceholder(sizeClass, extraClasses = '') {
            return `<div class="image-placeholder ${sizeClass} ${extraClasses}">Image</div>`;
        }

        function buildEmptyDashedArea() {
            return `<div class="stage-highlighted-inner"></div>`;
        }

        function buildSectionLabel(text) {
            return `<div class="stage-section-label">${escapeHtml(text)}</div>`;
        }

        function renderStage() {
            const stage = $('#template-stage-inner');
            stage.empty();

            stage.css({
                background: theme.page_background_color,
                color: theme.page_text_color
            });

            if ($('#allow-downloads-checkbox').is(':checked')) {
                const header = $(`
                <div class="stage-page-header">
                    <div class="stage-download-btn">Download All</div>
                </div>
            `);

                header.find('.stage-download-btn').css({
                    background: theme.button_color || '#2563eb'
                });

                stage.append(header);
            }

            if (!sections.length) {
                stage.append(`
                <div class="preview-empty-state">
                    Add sections on the left to start building your page preview.
                </div>
            `);
                initPreviewSortable();
                serializeState();
                return;
            }

            sections.forEach((section) => {
                normalizeSection(section);

            if (section.type === 'header_image') {
                const previewHeight = getPreviewHeight(section.height || 300);
                const text = getSectionPreviewText(section);

                const box = $(`
                    <div class="stage-sortable" data-key="${section.section_key}">
                        <div class="stage-section stage-highlighted-image">
                            ${buildSectionLabel('Highlighted Image')}
                            ${buildEmptyDashedArea()}
                            <div class="stage-highlighted-text"></div>
                        </div>
                    </div>
                `);

                box.find('.stage-highlighted-inner').css({
                    minHeight: previewHeight + 'px'
                });

                box.find('.stage-highlighted-text').text(text).css({
                    fontSize: Math.max(16, Math.round((section.settings.font_size || 24) * 0.82)) + 'px',
                    textAlign: section.settings.text_align || 'center',
                    color: section.text_color || '#111827'
                });

                box.on('click', function () {
                    scrollToSectionCard(section.section_key);
                });

                stage.append(box);
                return;
            }

            if (section.type === 'image_carousel') {
                const box = $(`
                    <div class="stage-sortable" data-key="${section.section_key}">
                        <div class="stage-section stage-carousel">
                            ${buildSectionLabel('Image Carousel')}
                            <div class="stage-carousel-track"></div>
                        </div>
                    </div>
                `);

                const track = box.find('.stage-carousel-track');

                for (let c = 0; c < 4; c++) {
                    track.append(buildImagePlaceholder('square'));
                }

                box.on('click', function () {
                    scrollToSectionCard(section.section_key);
                });

                stage.append(box);
                return;
            }

            if (section.type === 'logo') {
                const previewLogoSize = getPreviewLogoSize(section.settings.logo_width || 180);

                const box = $(`
                    <div class="stage-sortable" data-key="${section.section_key}">
                        <div class="stage-section">
                            ${buildSectionLabel('Logo')}
                            <div class="stage-brand-row">
                                <div class="stage-logo-box"></div>
                            </div>
                        </div>
                    </div>
                `);

                box.find('.stage-logo-box').css({
                    width: previewLogoSize + 'px',
                    height: previewLogoSize + 'px'
                }).html(buildImagePlaceholder('square'));

                box.on('click', function () {
                    scrollToSectionCard(section.section_key);
                });

                stage.append(box);
                return;
            }

            if (section.type === 'company_name') {
                const box = $(`
                    <div class="stage-sortable" data-key="${section.section_key}">
                        <div class="stage-section">
                            ${buildSectionLabel('Company Name')}
                            <div class="stage-text-block stage-company-name-only">Company Name</div>
                        </div>
                    </div>
                `);

                box.find('.stage-company-name-only').css({
                    fontSize: (section.settings.font_size || 20) + 'px',
                    textAlign: section.settings.text_align || 'left',
                    color: section.text_color || theme.page_text_color,
                    fontWeight: '700'
                });

                box.on('click', function () {
                    scrollToSectionCard(section.section_key);
                });

                stage.append(box);
                return;
            }

            if (section.type === 'single_image') {
                const count = parseInt(section.image_count || 3, 10);
                const box = $(`
                    <div class="stage-sortable" data-key="${section.section_key}">
                        <div class="stage-section stage-image-row">
                            ${buildSectionLabel('Image Row')}
                            <div class="stage-image-row-grid"></div>
                        </div>
                    </div>
                `);

                for (let c = 0; c < count; c++) {
                    box.find('.stage-image-row-grid').append(buildImagePlaceholder('square'));
                }

                box.on('click', function () {
                    scrollToSectionCard(section.section_key);
                });

                stage.append(box);
                return;
            }

            if (section.type === 'gallery') {
                const box = $(`
                    <div class="stage-sortable" data-key="${section.section_key}">
                        <div class="stage-section stage-gallery">
                            ${buildSectionLabel('Gallery')}
                            <div class="stage-gallery-grid">
                                ${buildImagePlaceholder('gallery-square')}
                                ${buildImagePlaceholder('gallery-square')}
                                ${buildImagePlaceholder('gallery-square')}

                                ${buildImagePlaceholder('gallery-wide', 'gallery-span-2')}
                                ${buildImagePlaceholder('gallery-square')}

                                ${buildImagePlaceholder('gallery-square')}
                                ${buildImagePlaceholder('gallery-wide', 'gallery-span-2')}
                            </div>
                        </div>
                    </div>
                `);

                box.on('click', function () {
                    scrollToSectionCard(section.section_key);
                });

                stage.append(box);
                return;
            }

            if (section.type === 'text_block') {
                const box = $(`
                    <div class="stage-sortable" data-key="${section.section_key}">
                        <div class="stage-section">
                            ${buildSectionLabel('Text Block')}
                            <div class="stage-text-block"></div>
                        </div>
                    </div>
                `);

                box.find('.stage-text-block').text(getSectionPreviewText(section) || 'Sample text block').css({
                    fontSize: (section.settings.font_size || 20) + 'px',
                    textAlign: section.settings.text_align || 'left',
                    color: section.text_color || theme.page_text_color
                });

                box.on('click', function () {
                    scrollToSectionCard(section.section_key);
                });

                stage.append(box);
                return;
            }
        });

            initPreviewSortable();
            serializeState();
        }

        function initPreviewSortable() {
            const el = document.getElementById('template-stage-inner');

            if (!el) {
                return;
            }

            if (previewSortable) {
                previewSortable.destroy();
            }

            previewSortable = new Sortable(el, {
                animation: 150,
                draggable: '.stage-sortable',
                ghostClass: 'stage-sortable-active',
                onEnd: function () {
                    const orderedKeys = $('#template-stage-inner .stage-sortable').map(function () {
                        return $(this).data('key');
                    }).get();

                    const reordered = [];

                    orderedKeys.forEach(function (key) {
                        const section = sections.find(item => item.section_key === key);
                        if (section) {
                            reordered.push(section);
                        }
                    });

                    sections = reordered;
                    renderSections();
                }
            });
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
            delete field.font_weight;
            delete field.font_style;
        });

            delete theme.accent_color;
            delete theme.button_text_color;
            delete theme.section_background_color;

            $('#theme-json-input').val(JSON.stringify(theme));
            $('#sections-json-input').val(JSON.stringify(sections));
            $('#custom-fields-json-input').val(JSON.stringify(customFields));
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

        sanitizeLoadedState();

        sharedPicker = new iro.ColorPicker('#shared-color-picker', {
            width: 180,
            color: theme.page_background_color || '#ffffff',
            borderWidth: 1,
            borderColor: '#ffffff'
        });

        sharedPicker.on('color:change', function (color) {
            if (!activeColorTarget) return;
            applyTargetColor(activeColorTarget, color.hexString);
        });

        sharedPicker.on('input:end', function (color) {
            if (!activeColorTarget) return;
            applyTargetColor(activeColorTarget, color.hexString);
            hideColorPopover();
        });

        $(document).on('click', '.js-color-swatch', function (e) {
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

        $(document).on('click', function (e) {
            const popover = document.getElementById('shared-color-picker-popover');
            if (popover && !popover.contains(e.target) && !e.target.closest('.js-color-swatch')) {
                hideColorPopover();
            }
        });

        $('.add-section-btn').on('click', function () {
            const type = $(this).data('type');

            if (!canAddSectionType(type)) {
                return;
            }

            const newSection = createDefaultSection(type);
            sections.push(newSection);
            renderSections();

            setTimeout(function () {
                scrollToSectionCard(newSection.section_key);
            }, 60);
        });

        $('#add-custom-field-btn').on('click', function () {
            customFields.push(createDefaultField());
            renderCustomFields();
            renderSections();
        });

        $('#allow-downloads-checkbox').on('change', function () {
            renderStage();
            serializeState();
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