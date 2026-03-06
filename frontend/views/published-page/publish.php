<?php
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;

$this->title = 'Publish Folder: ' . $folder->name;
?>

<div class="publish-page-wrap">
    <div class="publish-shell">
        <div class="publish-header">
            <div>
                <h1><?= Html::encode($this->title) ?></h1>
                <p>Select a template, fill custom fields, choose section images, and publish.</p>
            </div>
        </div>

        <?php $form = ActiveForm::begin(['id' => 'publish-page-form']); ?>

        <div class="publish-grid">
            <div class="publish-main">
                <div class="publish-card">
                    <h3>Page Settings</h3>
                    <?= $form->field($page, 'template_id')->dropDownList(
                        ['' => '-- Select Template --'] + \yii\helpers\ArrayHelper::map($templates, 'template_id', 'name'),
                        ['id' => 'template-select']
                    ) ?>

                    <?= $form->field($page, 'uri')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'my-page-uri',
                    ])->hint('Public URL: https://fotuka.com/pages/<uri>') ?>

                    <?= $form->field($page, 'page_title')->textInput(['maxlength' => true, 'placeholder' => 'Optional page title']) ?>

                    <div id="password-field-wrap" style="display:none;">
                        <?= $form->field($page, 'page_password')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Password for this page',
                        ]) ?>
                    </div>
                </div>

                <div class="publish-card" id="custom-fields-wrap" style="display:none;">
                    <h3>Custom Fields</h3>
                    <div id="custom-fields-container"></div>
                </div>

                <div class="publish-card" id="section-assets-wrap" style="display:none;">
                    <h3>Section Image Selection</h3>
                    <div id="section-assets-container"></div>
                </div>
            </div>

            <div class="publish-side">
                <div class="publish-card sticky-card">
                    <h3>Folder Images</h3>
                    <p class="muted">Images in this folder will be used in sections and the gallery.</p>
                    <div class="asset-grid-preview" id="folder-assets-preview"></div>
                </div>
            </div>
        </div>

        <div class="publish-footer">
            <button type="submit" class="btn btn-primary btn-lg">Publish Page</button>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<style>
    .publish-page-wrap {
        padding: 24px;
        background: #f8fafc;
    }
    .publish-shell {
        max-width: 1480px;
        margin: 0 auto;
    }
    .publish-header {
        margin-bottom: 18px;
    }
    .publish-header h1 {
        margin: 0 0 6px;
        font-size: 30px;
        font-weight: 700;
    }
    .publish-header p {
        margin: 0;
        color: #6b7280;
    }
    .publish-grid {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 18px;
    }
    .publish-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 18px;
        margin-bottom: 18px;
        box-shadow: 0 12px 30px rgba(0,0,0,.04);
    }
    .publish-card h3 {
        margin: 0 0 12px;
        font-size: 18px;
        font-weight: 700;
    }
    .muted {
        color: #6b7280;
    }
    .sticky-card {
        position: sticky;
        top: 20px;
    }
    .asset-grid-preview,
    .asset-picker-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    .asset-thumb,
    .asset-picker-item {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid transparent;
        background: #f3f4f6;
        cursor: pointer;
    }
    .asset-thumb img,
    .asset-picker-item img {
        width: 100%;
        height: 100px;
        object-fit: cover;
        display: block;
    }
    .asset-picker-item.selected {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,.15);
    }
    .asset-picker-caption {
        padding: 6px 8px;
        font-size: 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .section-picker-block {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 14px;
        margin-bottom: 14px;
    }
    .section-picker-title {
        font-weight: 700;
        margin-bottom: 6px;
    }
    .section-picker-help {
        color: #6b7280;
        font-size: 13px;
        margin-bottom: 10px;
    }
    .publish-footer {
        display: flex;
        justify-content: flex-end;
    }
    @media (max-width: 1100px) {
        .publish-grid {
            grid-template-columns: 1fr;
        }
        .sticky-card {
            position: static;
        }
    }
</style>

<script>
const templatesPayload = <?= Json::htmlEncode($templatesPayload) ?>;
const assetsPayload = <?= Json::htmlEncode($assetsPayload) ?>;

function renderFolderPreview() {
    const wrap = $('#folder-assets-preview');
    wrap.empty();

    assetsPayload.forEach(asset => {
        wrap.append(`
        <div class="asset-thumb">
            <img src="${asset.thumbnail_url || asset.preview_url || ''}" alt="">
        </div>`);
    });
}

function renderTemplateDependentUi(templateId) {
    const template = templatesPayload[templateId];
    const customFieldsWrap = $('#custom-fields-wrap');
    const customFieldsContainer = $('#custom-fields-container');
    const sectionAssetsWrap = $('#section-assets-wrap');
    const sectionAssetsContainer = $('#section-assets-container');
    const passwordWrap = $('#password-field-wrap');

    customFieldsContainer.empty();
    sectionAssetsContainer.empty();

    if (!template) {
        customFieldsWrap.hide();
        sectionAssetsWrap.hide();
        passwordWrap.hide();
        return;
    }

    if (parseInt(template.password_enabled, 10) === 1) {
        passwordWrap.show();
    } else {
        passwordWrap.hide();
        $('#publishedpage-page_password').val('');
    }

    if (template.custom_fields.length) {
        template.custom_fields.forEach(field => {
            customFieldsContainer.append(`
            <div class="form-group">
                <label>${escapeHtml(field.name)}</label>
                <textarea class="form-control" rows="3" name="custom_field_values[${field.custom_field_id}]"
                    style="color:${field.text_color || '#111827'};font-size:${field.font_size || 16}px;text-align:${field.text_align || 'left'};font-style:${field.font_style || 'normal'};font-weight:${field.font_weight || '400'};"></textarea>
            </div>
        `);
    });
        customFieldsWrap.show();
    } else {
        customFieldsWrap.hide();
    }

    const selectableSections = template.sections.filter(section => section.requires_assets);
    if (selectableSections.length) {
        selectableSections.forEach(section => {
            const maxSelect = section.type === 'header_image' ? 1 : (section.image_count || 1);
        const helpText = section.type === 'header_image'
            ? 'Select 1 image for the page header.'
            : (section.type === 'image_carousel'
                ? `Select up to ${maxSelect} images for the carousel.`
                : `Select up to ${maxSelect} image(s) for this row.`);

        const block = $(`
            <div class="section-picker-block" data-section-id="${section.section_id}" data-max="${maxSelect}">
                <div class="section-picker-title">${escapeHtml(section.label)}</div>
                <div class="section-picker-help">${helpText}</div>
                <div class="asset-picker-grid"></div>
            </div>
        `);

        const grid = block.find('.asset-picker-grid');
        assetsPayload.forEach(asset => {
            const item = $(`
                <div class="asset-picker-item" data-asset-id="${asset.id}">
                    <input type="hidden" disabled name="section_assets[${section.section_id}][]" value="${asset.id}">
                    <img src="${asset.thumbnail_url || asset.preview_url || ''}" alt="">
                    <div class="asset-picker-caption">${escapeHtml(asset.filename || asset.title || ('Asset #' + asset.id))}</div>
                </div>
            `);

        item.on('click', function() {
            const max = parseInt(block.data('max'), 10);
            const selectedItems = grid.find('.asset-picker-item.selected');

            if (item.hasClass('selected')) {
                item.removeClass('selected');
                item.find('input').prop('disabled', true);
                return;
            }

            if (selectedItems.length >= max) {
                alert(`You can only select ${max} item(s) for this section.`);
                return;
            }

            item.addClass('selected');
            item.find('input').prop('disabled', false);
        });

        grid.append(item);
    });

        sectionAssetsContainer.append(block);
    });

        sectionAssetsWrap.show();
    } else {
        sectionAssetsWrap.hide();
    }
}

function escapeHtml(value) {
    return $('<div>').text(value || '').html();
}

$(function() {
    renderFolderPreview();

    $('#template-select').on('change', function() {
        renderTemplateDependentUi($(this).val());
    });

    if ($('#template-select').val()) {
        renderTemplateDependentUi($('#template-select').val());
    }
});
</script>