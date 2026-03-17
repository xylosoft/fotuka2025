<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\WebsiteTemplate $model */
/** @var array $definition */

$this->title = $model->isNewRecord ? 'New Template' : 'Edit Template';

$page = $definition['page'] ?? [];
$canvasWidth = 1200;
$canvasMinHeight = max(1500, (int) ($page['canvas_min_height'] ?? 1500));
?>
<style>
    html, body {
        min-height: 100%;
        margin: 0;
        background: linear-gradient(180deg, #f4f8fc 0%, #edf4fb 100%);
    }

    .template-editor-page {
        margin-top:18px;
        box-sizing: border-box;
        position: relative;
        left: 50%;
        right: 50%;
        margin-left: -50vw;
        margin-right: -50vw;
        width: 100vw;
        background: linear-gradient(180deg, #f4f8fc 0%, #edf4fb 100%);

        padding: 0 20px 24px;
        color: #10233f;
    }

    .template-editor-page,
    .template-editor-page * {
        box-sizing: border-box;
    }

    .breadcrum-div{
        padding-top:6px;
        max-width: 1200px;
        margin: 0 auto;
        display: block;
    }
    .tpl-editor-shell {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 20px;
        align-items: start;
    }

    .tpl-editor-main,
    .tpl-canvas-wrap,
    .tpl-preview-topbar,
    .tpl-canvas-stage {
        min-width: 0;
    }

    .tpl-editor-panel,
    .tpl-editor-main {
        background: #fff;
        border: 1px solid #dbe6f3;
        border-radius: 22px;
        box-shadow: 0 20px 48px rgba(17, 40, 74, 0.08);
    }

    .tpl-editor-panel {
        position: sticky;
        top: 20px;
        padding: 18px;
        max-height: calc(100vh - 40px);
        overflow: auto;
    }

    .tpl-editor-main {
        padding: 18px;
    }

    .tpl-form-row {
        margin-bottom: 16px;
    }

    .tpl-form-row label {
        display: block;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #627894;
        margin-bottom: 8px;
    }

    .tpl-input {
        width: 100%;
        border: 1px solid #d6e1ef;
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 14px;
        outline: none;
        background: #fff;
    }

    .tpl-button-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .tpl-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        border: none;
        border-radius: 12px;
        padding: 0 14px;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
        transition: .2s ease;
    }

    .tpl-btn-primary {
        background: #2563eb;
        color: #fff;
        box-shadow: 0 14px 28px rgba(37, 99, 235, 0.22);
    }

    .tpl-btn-primary:hover {
        background: #1d4ed8;
        color: #fff;
        text-decoration: none;
    }

    .tpl-btn-secondary {
        background: #fff;
        color: #13355f;
        border: 1px solid #d6e1ef;
    }

    .tpl-btn-secondary:hover {
        background: #f8fbff;
    }

    .tpl-preview-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }

    .tpl-preview-title h1 {
        margin: 0;
        font-size: 24px;
        font-weight: 800;
    }

    .tpl-url-placeholder {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f7fbff;
        border: 1px solid #dce7f3;
        padding: 5px;
        border-radius: 14px;
        min-width: 330px;
        justify-content: center;
        color: #5a6f8d;
    }

    .tpl-canvas-wrap {
        width: 100%;
        overflow: hidden;
        background: transparent;
        border: none;
        border-radius: 0;
        padding: 0;
        display: block;
    }

    .tpl-canvas-stage {
        position: relative;
        width: 100%;
        margin: 0;
    }

    .tpl-canvas {
        position: relative;
        width: <?= $canvasWidth ?>px;
        min-height: <?= $canvasMinHeight ?>px;
        margin: 0;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 30px 60px rgba(17, 40, 74, .14);
        overflow: hidden;
        transform-origin: top left;
    }

    .tpl-empty-canvas {
        position: absolute;
        inset: 24px;
        border: 2px dashed #c8d6e8;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6a809c;
        font-size: 18px;
        font-weight: 700;
        pointer-events: none;
    }

    .tpl-component {
        position: absolute;
        border: 1px dashed transparent;
        transition: box-shadow .16s ease, border-color .16s ease;
        overflow: hidden;
        border-radius: 8px;
        background: transparent;
        cursor: move;
    }

    .tpl-component:hover {
        border-color: #98b7e0;
    }

    .tpl-component.is-selected {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .14);
    }

    .tpl-component-content {
        width: 100%;
        height: 100%;
        overflow: hidden;
        pointer-events: none;
    }

    .tpl-component-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        padding: 4px 8px;
        border-radius: 999px;
        background: rgba(16, 35, 63, .82);
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .04em;
        z-index: 6;
        pointer-events: none;
    }

    .tpl-component-controls {
        position: absolute;
        top: 8px;
        right: 8px;
        display: flex;
        gap: 6px;
        z-index: 7;
        opacity: 0;
        transition: opacity .15s ease;
    }

    .tpl-component:hover .tpl-component-controls,
    .tpl-component.is-selected .tpl-component-controls {
        opacity: 1;
    }

    .tpl-component-control {
        width: 28px;
        height: 28px;
        border: none;
        border-radius: 8px;
        background: rgba(16, 35, 63, .82);
        color: #fff;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .tpl-component-control:hover {
        background: #2563eb;
    }

    .tpl-placeholder-box,
    .tpl-placeholder-gallery,
    .tpl-placeholder-carousel {
        width: 100%;
        height: 100%;
        border: 2px dashed #8fb2da;
        border-radius: 12px;
        background: linear-gradient(180deg, rgba(248, 251, 255, .98) 0%, rgba(240, 247, 255, .98) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #557193;
        text-align: center;
        padding: 16px;
    }

    .tpl-placeholder-carousel .inner,
    .tpl-placeholder-gallery .inner,
    .tpl-placeholder-box .inner {
        width: 100%;
    }

    .tpl-carousel-sample {
        display: grid;
        gap: var(--tpl-carousel-gap, 12px);
        margin: 14px auto 0;
        grid-template-columns: repeat(4, var(--tpl-carousel-cell-size, 68px));
        justify-content: center;
    }

    .tpl-gallery-sample {
        display: grid;
        gap: 10px;
        margin: 14px auto 0;
        width: 400px;
        max-width: 100%;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .tpl-sample-cell {
        width: 100%;
        aspect-ratio: 1 / 1;
        height: auto;
        border: 2px dashed #b6cbe5;
        border-radius: 10px;
        background: rgba(255, 255, 255, .8);
    }

    .tpl-static-text-preview,
    .tpl-dynamic-text-preview {
        width: 100%;
        height: 100%;
        overflow: auto;
        box-sizing: border-box;
        padding: 38px 12px 12px 12px;
        color: #10233f;
    }

    .tpl-static-text-preview p,
    .tpl-dynamic-text-preview p {
        margin: 0 0 10px;
    }

    .tpl-static-text-preview p:last-child,
    .tpl-dynamic-text-preview p:last-child {
        margin-bottom: 0;
    }

    .tpl-dynamic-text-preview {
        background: rgba(247, 250, 255, .9);
    }

    .tpl-dynamic-chip {
        display: inline-flex;
        padding: 6px 10px;
        border-radius: 999px;
        background: #e0edff;
        color: #174a95;
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .tpl-section-title {
        margin: 18px 0 12px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #657b96;
        font-weight: 800;
    }

    .tpl-color-preview {
        width: 100%;
        border: 1px solid #d6e1ef;
        border-radius: 12px;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        background: #fff;
    }

    .tpl-color-swatch {
        width: 26px;
        height: 26px;
        border-radius: 8px;
        border: 1px solid rgba(16, 35, 63, .1);
    }

    .tpl-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(14, 25, 42, .64);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }

    .tpl-modal-backdrop.is-open {
        display: flex;
    }

    .tpl-modal-card {
        width: min(920px, calc(100vw - 40px));
        background: #fff;
        border-radius: 22px;
        box-shadow: 0 24px 50px rgba(0, 0, 0, .24);
        overflow: visible;
        position: relative;
        z-index: 10000;
    }

    .tox-tinymce-aux,
    .tox-menu,
    .tox-collection,
    .tox-collection__group,
    .tox-dialog,
    .tox-dialog-wrap,
    .tox-silver-sink,
    .tox-pop {
        z-index: 10050 !important;
    }

    .tpl-modal-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid #e6edf8;
    }

    .tpl-modal-head h3 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
    }

    .tpl-modal-body {
        padding: 20px;
    }

    .tpl-modal-close {
        border: none;
        background: #f3f7fc;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        cursor: pointer;
        font-size: 20px;
    }

    .tpl-note {
        font-size: 13px;
        color: #637791;
        line-height: 1.5;
    }

    .pcr-app .pcr-interaction {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
    }

    .pcr-app .pcr-interaction .pcr-save,
    .pcr-app .pcr-interaction .pcr-cancel {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 14px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 14px;
        cursor: pointer;
        transition: .2s ease;
        box-shadow: none;
        background-image: none;
    }

    .pcr-app .pcr-interaction .pcr-save {
        background: #2563eb;
        color: #fff;
        border: none;
        box-shadow: 0 14px 28px rgba(37, 99, 235, 0.22);
    }

    .pcr-app .pcr-interaction .pcr-save:hover {
        background: #1d4ed8;
    }

    .pcr-app .pcr-interaction .pcr-cancel {
        background: #fff;
        color: #13355f;
        border: 1px solid #d6e1ef;
    }

    .pcr-app .pcr-interaction .pcr-cancel:hover {
        background: #f8fbff;
    }
    /* Pickr popup styled closer to your site modal */
    .pcr-app.tpl-pickr-popup {
        border: 1px solid #dbe6f3;
        border-radius: 22px;
        box-shadow: 0 24px 50px rgba(0, 0, 0, .18);
        overflow: hidden;
        z-index: 10050 !important;
        background: #fff;
    }

    /* Title bar */
    .tpl-pickr-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 18px;
        border-bottom: 1px solid #e6edf8;
        background: #fff;
    }

    .tpl-pickr-title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: #10233f;
    }

    /* Slightly calmer inner spacing */
    .pcr-app.tpl-pickr-popup .pcr-selection,
    .pcr-app.tpl-pickr-popup .pcr-swatches,
    .pcr-app.tpl-pickr-popup .pcr-interaction {
        padding-left: 16px;
        padding-right: 16px;
    }

    /* Buttons aligned right */
    .pcr-app.tpl-pickr-popup .pcr-interaction {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 8px;
        padding-bottom: 14px;
    }

    /* Smaller, less prominent buttons */
    .pcr-app.tpl-pickr-popup .pcr-interaction .pcr-save,
    .pcr-app.tpl-pickr-popup .pcr-interaction .pcr-cancel {
        width: auto;
        flex: 0 0 auto;
        min-height: 32px;
        padding: 0 10px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        box-shadow: none;
        background-image: none;
        cursor: pointer;
    }

    /* Save = blue */
    .pcr-app.tpl-pickr-popup .pcr-interaction .pcr-save {
        background: #2563eb;
        color: #fff;
        border: none;
    }

    .pcr-app.tpl-pickr-popup .pcr-interaction .pcr-save:hover {
        background: #1d4ed8;
    }

    /* Cancel = white, not red */
    .pcr-app.tpl-pickr-popup .pcr-interaction .pcr-cancel {
        background: #fff !important;
        color: #13355f !important;
        border: 1px solid #d6e1ef !important;
    }

    .pcr-app.tpl-pickr-popup .pcr-interaction .pcr-cancel:hover {
        background: #f8fbff !important;
    }

    .tpl-insert-elements-row {
        gap: 8px;
    }

    .tpl-btn-insert {
        min-height: 32px;
        padding: 0 10px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        gap: 6px;
    }

    .tpl-btn-insert:hover {
        background: #f4f8fd;
    }
    .tpl-field-error {
        display: none;
        margin-top: 8px;
        color: #dc2626;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.35;
    }

    .tpl-input.has-error {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.10);
    }
    .tpl-color-setting-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
        min-height: 40px;
    }

    .tpl-color-setting-label {
        font-size: 15px;
        font-weight: 500;
        color: #13355f;
        text-transform: none;
        letter-spacing: normal;
    }

    .tpl-color-swatch-btn {
        width: 44px;
        height: 44px;
        padding: 0;
        border: none;
        background: transparent;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .tpl-color-swatch-btn:focus {
        outline: none;
    }

    .tpl-color-swatch-btn .tpl-color-swatch {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        border: 1px solid rgba(16, 35, 63, .12);
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.18);
    }
    .tpl-url-placeholder-block {
        display: flex;
        flex-direction: column;
        align-items: left;
    }

    .tpl-url-placeholder-note {
        margin-top: 6px;
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
        text-align: center;
    }
    .tpl-field-error {
        color: #dc3545;
        font-size: 12px;
        margin-top: 6px;
    }

    .tpl-input.tpl-input-error {
        border: 1px solid #dc3545;
        box-shadow: 0 0 0 1px rgba(220, 53, 69, 0.08);
    }

</style>
<div class="template-editor-page">
    <form id="templateEditorForm" method="post">
    <?php
        $form = ActiveForm::begin(['id' => 'templateEditorForm','method' => 'post',]);
    ?>
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
        <input type="hidden" name="WebsiteTemplate[definition_json]" id="definitionJsonField" value="<?= Html::encode($model->definition_json) ?>">

        <div class="breadcrum-div">
            <a class="breadcrum-link" href="/folders">Folders</a>
            &nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;
            <a class="breadcrum-link" href="/templates">Website Templates</a>
            &nbsp;&nbsp;/&nbsp;&nbsp;
            <span class="breadcrum-static">Website Template Builder<span>
        </div>
        <div class="tpl-editor-shell">
            <div class="tpl-editor-panel">
                <div class="tpl-form-row<?= $model->hasErrors('name') ? ' has-error' : '' ?>">
                    <label for="templateNameInput">Template name</label>

                    <?= Html::activeTextInput($model, 'name', [
                        'id' => 'templateNameInput',
                        'class' => 'tpl-input' . ($model->hasErrors('name') ? ' tpl-input-error' : ''),
                        'placeholder' => 'Untitled Template',
                    ]) ?>

                    <div
                            id="templateNameError"
                            class="tpl-field-error"
                            style="<?= $model->hasErrors('name') ? 'display:block;' : 'display:none;' ?>"
                    >
                        <?= Html::encode($model->getFirstError('name')) ?>
                    </div>
                </div>

                <div class="tpl-section-title">Page Elements</div>
                <div class="tpl-button-row tpl-insert-elements-row">
                    <button class="tpl-btn tpl-btn-secondary tpl-btn-insert" type="button" data-add="static_text">Static Text</button>
                    <button class="tpl-btn tpl-btn-secondary tpl-btn-insert" type="button" data-add="dynamic_text">Dynamic Text</button>
                    <button class="tpl-btn tpl-btn-secondary tpl-btn-insert" type="button" data-add="image">Image</button>
                    <button class="tpl-btn tpl-btn-secondary tpl-btn-insert" type="button" data-add="carousel">Image Carousel</button>
                    <button class="tpl-btn tpl-btn-secondary tpl-btn-insert" type="button" data-add="gallery">Image Gallery</button>
                </div>

                <div class="tpl-section-title">Page settings</div>

                <div class="tpl-color-setting-row">
                    <div class="tpl-color-setting-label">Background color</div>
                    <button type="button" id="backgroundColorBtn" class="tpl-color-swatch-btn" aria-label="Choose background color">
                        <span id="backgroundColorSwatch" class="tpl-color-swatch" style="background: <?= Html::encode($page['background_color'] ?? '#ffffff') ?>"></span>
                    </button>
                </div>

                <div class="tpl-color-setting-row">
                    <div class="tpl-color-setting-label">Button color</div>
                    <button type="button" id="buttonColorBtn" class="tpl-color-swatch-btn" aria-label="Choose background color">
                        <span id="buttonColorSwatch" class="tpl-color-swatch" style="background: <?= Html::encode($page['button_color'] ?? '#2563eb') ?>"></span>
                    </button>
                </div>

                <div class="tpl-section-title">Save</div>
                <div class="tpl-button-row">
                    <button class="tpl-btn tpl-btn-primary" type="submit">Save Template</button>
                    <a class="tpl-btn tpl-btn-secondary" href="javascript:history.back()">Back</a>
                </div>

                <div class="tpl-note" style="margin-top:10px;">
                    Double-click a text component to edit it. Drag components directly on the preview to move them, resize from any edge, use Delete/Backspace to remove the selected component, and use the small top-right controls on each box to move it backward or forward.
                </div>
            </div>

            <div class="tpl-editor-main">
                <div class="tpl-preview-topbar">
                    <div class="tpl-preview-title">
                        <a class="tpl-back-link" href="/templates">← Back to Templates</a>
                        <div>
                            <h1>Website Template Builder</h1>
                        </div>

                    </div>

                    <div class="tpl-url-placeholder-block">
                        <div class="tpl-url-placeholder">
                            <span>https://fotuka.com/page/&lt;Page Name&gt;</span>
                        </div>
                        <div class="tpl-url-placeholder-note">
                            You will customize at publishing time
                        </div>
                    </div>
                </div>

                <div class="tpl-canvas-wrap" id="canvasWrap">
                    <div class="tpl-canvas-stage" id="canvasStage">
                        <div class="tpl-canvas" id="templateCanvas"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php ActiveForm::end(); ?>


        <div class="tpl-modal-backdrop" id="textEditorModal">
        <div class="tpl-modal-card">
            <div class="tpl-modal-head">
                <h3>Edit Content</h3>
                <button type="button" class="tpl-modal-close" id="closeTextEditor">&times;</button>
            </div>
            <div class="tpl-modal-body">
                <textarea id="componentTextEditor"></textarea>
                <div class="tpl-button-row" style="margin-top:16px;">
                    <button type="button" class="tpl-btn tpl-btn-primary" id="saveTextEditorBtn">Apply</button>
                    <button type="button" class="tpl-btn tpl-btn-secondary" id="cancelTextEditorBtn">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/classic.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/<?=Yii::$app->params['TINYMCE_API']?>/tinymce/6/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>

    <script>
        (function () {
            const initialDefinition = <?= Json::htmlEncode($definition) ?>;

            const state = {
                definition: JSON.parse(JSON.stringify(initialDefinition)),
                selectedId: null,
                zoom: 1,
                activeTextEditorId: null,
            };

            const canvas = document.getElementById('templateCanvas');
            const canvasWrap = document.getElementById('canvasWrap');
            const canvasStage = document.getElementById('canvasStage');
            const hiddenJsonField = document.getElementById('definitionJsonField');
            const templateNameInput = document.getElementById('templateNameInput');
            const templateNameError = document.getElementById('templateNameError');
            const textEditorModal = document.getElementById('textEditorModal');
            const closeTextEditor = document.getElementById('closeTextEditor');
            const cancelTextEditorBtn = document.getElementById('cancelTextEditorBtn');
            const saveTextEditorBtn = document.getElementById('saveTextEditorBtn');
            const backgroundColorSwatch = document.getElementById('backgroundColorSwatch');
            const buttonColorSwatch = document.getElementById('buttonColorSwatch');
            let backgroundOriginalColor = state.definition.page.background_color;
            let buttonOriginalColor = state.definition.page.button_color;

            function normalizedTemplateName() {
                return (templateNameInput.value || '').trim();
            }

            function isInvalidTemplateName() {
                const value = normalizedTemplateName().toLowerCase();
                return value === '' || value === 'untitled template';
            }

            function showTemplateNameError() {
                templateNameInput.classList.add('has-error');
                templateNameError.style.display = 'block';
            }

            function clearTemplateNameError() {
                templateNameInput.classList.remove('has-error');
                templateNameError.style.display = 'none';
            }

            function enhancePickrPopup(pickr, titleText) {
                const root = pickr.getRoot();

                if (!root || !root.app) {
                    return;
                }

                const app = root.app;
                app.classList.add('tpl-pickr-popup');

                if (!app.querySelector('.tpl-pickr-header')) {
                    const header = document.createElement('div');
                    header.className = 'tpl-pickr-header';
                    header.innerHTML = '<div class="tpl-pickr-title">' + titleText + '</div>';
                    app.prepend(header);
                }
            }

            function ensurePageDefaults() {
                if (!state.definition.page) {
                    state.definition.page = {};
                }

                if (!Array.isArray(state.definition.components)) {
                    state.definition.components = [];
                }

                state.definition.page.canvas_width = 1200;
                state.definition.page.canvas_min_height = parseInt(state.definition.page.canvas_min_height || 1500, 10);
                state.definition.page.background_color = state.definition.page.background_color || '#ffffff';
                state.definition.page.button_color = state.definition.page.button_color || '#2563eb';
            }

            function uid(prefix = 'cmp') {
                return prefix + '_' + Math.random().toString(36).slice(2, 10);
            }

            function slugify(text) {
                return (text || '')
                    .toString()
                    .trim()
                    .toLowerCase()
                    .replace(/[^a-z0-9\-_]+/g, '_')
                    .replace(/^_+|_+$/g, '') || ('field_' + Math.random().toString(36).slice(2, 6));
            }

            function componentTypeLabel(type) {
                const labels = {
                    static_text: 'Static Text',
                    dynamic_text: 'Dynamic Text',
                    image: 'Image',
                    carousel: 'Carousel',
                    gallery: 'Gallery'
                };

                return labels[type] || type;
            }

            function getComponent(id) {
                return state.definition.components.find(function (component) {
                    return component.id === id;
                }) || null;
            }

            function getComponentIndex(id) {
                return state.definition.components.findIndex(function (component) {
                    return component.id === id;
                });
            }

            function normalizeZ() {
                state.definition.components
                    .sort(function (a, b) {
                        return (a.z || 0) - (b.z || 0);
                    })
                    .forEach(function (component, index) {
                        component.z = index + 1;
                    });
            }

            function getNewComponentPosition() {
                const index = state.definition.components.length;
                const stagger = index % 6;

                return {
                    x: 50 + (stagger * 36),
                    y: 60 + (stagger * 28)
                };
            }

            function createComponent(type) {
                const countOfType = state.definition.components.filter(function (component) {
                    return component.type === type;
                }).length + 1;

                const position = getNewComponentPosition();
                const canvasWidth = parseInt(state.definition.page.canvas_width || 1200, 10);

                const base = {
                    id: uid(),
                    type: type,
                    label: componentTypeLabel(type) + ' ' + countOfType,
                    x: position.x,
                    y: position.y,
                    w: 360,
                    h: 180,
                    z: state.definition.components.length + 1,
                    style: {}
                };

                if (type === 'static_text') {
                    base.h = 160;
                    base.html = '<p><strong>Static text</strong> — double-click to edit this content directly in the template.</p>';
                }

                if (type === 'dynamic_text') {
                    base.h = 180;
                    base.field_name = slugify('text_' + countOfType);
                    base.default_html = '<p>Enter content at publishing time.</p>';
                }

                if (type === 'image') {
                    base.field_name = slugify('image_' + countOfType);
                    base.h = 260;
                }

                if (type === 'carousel') {
                    base.field_name = slugify('carousel_' + countOfType);
                    base.x = 0;
                    base.w = canvasWidth;
                    base.h = 280;
                }

                if (type === 'gallery') {
                    base.field_name = slugify('gallery_' + countOfType);
                    base.x = 0;
                    base.w = canvasWidth;
                    base.h = 360;
                }

                state.definition.components.push(base);
                normalizeZ();
                state.selectedId = base.id;
                render();
            }

            function removeComponent(id) {
                state.definition.components = state.definition.components.filter(function (component) {
                    return component.id !== id;
                });

                if (state.selectedId === id) {
                    if (state.definition.components.length) {
                        state.selectedId = state.definition.components[state.definition.components.length - 1].id;
                    } else {
                        state.selectedId = null;
                    }
                }

                normalizeZ();
                render();
            }

            function moveComponentForward(id) {
                const component = getComponent(id);

                if (!component) {
                    return;
                }

                component.z = (component.z || 1) + 1.5;
                normalizeZ();
                render();
            }

            function moveComponentBackward(id) {
                const component = getComponent(id);

                if (!component) {
                    return;
                }

                component.z = Math.max(0, (component.z || 1) - 1.5);
                normalizeZ();
                render();
            }

            function escapeHtml(text) {
                return (text || '').replace(/[&<>"']/g, function (match) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    }[match];
                });
            }

            function renderComponentControls(component) {
                return `
                    <div class="tpl-component-controls">
                        <button type="button" class="tpl-component-control" data-action="backward" data-component-id="${component.id}" title="Send backward">↓</button>
                        <button type="button" class="tpl-component-control" data-action="forward" data-component-id="${component.id}" title="Bring forward">↑</button>
                    </div>
                `;
            }

            function componentMarkup(component) {
                let content = '';

                if (component.type === 'static_text') {
                    content = `
                        <div class="tpl-component-badge">Static Text</div>
                        ${renderComponentControls(component)}
                        <div class="tpl-component-content tpl-static-text-preview">
                            ${component.html || '<p>Double-click to edit text.</p>'}
                        </div>
                    `;
                } else if (component.type === 'dynamic_text') {
                    content = `
                        <div class="tpl-component-badge">Dynamic Text</div>
                        ${renderComponentControls(component)}
                        <div class="tpl-component-content tpl-dynamic-text-preview">
                            <div class="tpl-dynamic-chip">${escapeHtml(component.field_name || 'field_name')}</div>
                            ${component.default_html || '<p>Enter content at publish time.</p>'}
                        </div>
                    `;
                } else if (component.type === 'image') {
                    content = `
                        <div class="tpl-component-badge">Image</div>
                        ${renderComponentControls(component)}
                        <div class="tpl-component-content tpl-placeholder-box">
                            <div class="inner">
                                <div style="font-size:22px; font-weight:800; margin-bottom:8px;">Image Placeholder</div>
                                <div>${escapeHtml(component.field_name || component.label || 'image')}</div>
                            </div>
                        </div>
                    `;
                } else if (component.type === 'carousel') {
                    const metrics = getCarouselSampleMetrics(component.w || 360, component.h || 280);
                    content = `
                            <div class="tpl-component-badge">Carousel</div>
                            ${renderComponentControls(component)}
                            <div class="tpl-component-content tpl-placeholder-carousel">
                                <div class="inner">
                                    <div style="font-size:22px; font-weight:800;">Image Carousel</div>
                                    <div style="margin-top:6px;">${escapeHtml(component.field_name || component.label || 'carousel')}</div>
                                    <div
                                        class="tpl-carousel-sample"
                                        style="--tpl-carousel-cell-size:${metrics.cellSize}px; --tpl-carousel-gap:${metrics.gap}px;"
                                    >
                                        <div class="tpl-sample-cell"></div>
                                        <div class="tpl-sample-cell"></div>
                                        <div class="tpl-sample-cell"></div>
                                        <div class="tpl-sample-cell"></div>
                                    </div>
                                </div>
                            </div>`;
                } else if (component.type === 'gallery') {
                    content = `
                        <div class="tpl-component-badge">Gallery</div>
                        ${renderComponentControls(component)}
                        <div class="tpl-component-content tpl-placeholder-gallery">
                            <div class="inner">
                                <div style="font-size:22px; font-weight:800;">Gallery Grid</div>
                                <div style="margin-top:6px;">${escapeHtml(component.field_name || component.label || 'gallery')}</div>
                                <div class="tpl-gallery-sample">
                                    <div class="tpl-sample-cell"></div>
                                    <div class="tpl-sample-cell"></div>
                                    <div class="tpl-sample-cell"></div>
                                    <div class="tpl-sample-cell"></div>
                                    <div class="tpl-sample-cell"></div>
                                    <div class="tpl-sample-cell"></div>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    content = `
                        ${renderComponentControls(component)}
                        <div class="tpl-component-content">Unsupported component</div>
                    `;
                }

                return content;
            }

            function syncHiddenField() {
                hiddenJsonField.value = JSON.stringify(state.definition);
            }

            function getCanvasRenderHeight() {
                const baseMinHeight = Math.max(900, parseInt(state.definition.page.canvas_min_height || 1500, 10));

                if (!Array.isArray(state.definition.components) || !state.definition.components.length) {
                    return baseMinHeight;
                }

                const lowestBottom = state.definition.components.reduce(function (max, component) {
                    const y = parseInt(component.y || 0, 10);
                    const h = parseInt(component.h || 0, 10);
                    return Math.max(max, y + h);
                }, 0);

                return Math.max(baseMinHeight, lowestBottom + 60);
            }

            function queueFitZoom() {
                requestAnimationFrame(function () {
                    applyFitZoom();

                    requestAnimationFrame(function () {
                        applyFitZoom();
                    });
                });
            }

            function applyFitZoom() {
                const canvasWidth = state.definition.page.canvas_width || 1200;
                const canvasHeight = getCanvasRenderHeight();

                const wrapStyles = window.getComputedStyle(canvasWrap);
                const horizontalPadding =
                    (parseFloat(wrapStyles.paddingLeft) || 0) +
                    (parseFloat(wrapStyles.paddingRight) || 0);

                const availableWidth = Math.max(300, canvasWrap.clientWidth - horizontalPadding);

                const zoom = Math.max(0.45, availableWidth / canvasWidth);
                state.zoom = zoom;

                canvas.style.transform = 'scale(' + zoom + ')';
                canvasStage.style.width = availableWidth + 'px';
                canvasStage.style.height = Math.round(canvasHeight * zoom) + 'px';
            }
            function syncSelectionClasses() {
                const elements = canvas.querySelectorAll('.tpl-component');

                elements.forEach(function (element) {
                    const id = element.getAttribute('data-component-id');
                    element.classList.toggle('is-selected', id === state.selectedId);
                });
            }

            function selectComponent(id) {
                state.selectedId = id;
                syncSelectionClasses();
            }

            function bindCanvasDomEvents() {
                const elements = canvas.querySelectorAll('.tpl-component');

                elements.forEach(function (element) {
                    element.addEventListener('click', function () {
                        const id = element.getAttribute('data-component-id');
                        selectComponent(id);
                    });

                    element.addEventListener('dblclick', function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        const id = element.getAttribute('data-component-id');
                        const component = getComponent(id);

                        if (component && component.type === 'static_text') {
                            selectComponent(id);
                            openTextEditor(id);
                        }
                    });
                });

                const controlButtons = canvas.querySelectorAll('.tpl-component-control');

                controlButtons.forEach(function (button) {
                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        const id = button.getAttribute('data-component-id');
                        const action = button.getAttribute('data-action');

                        selectComponent(id);

                        if (action === 'forward') {
                            moveComponentForward(id);
                        } else if (action === 'backward') {
                            moveComponentBackward(id);
                        }
                    });
                });

                canvas.addEventListener('click', function (event) {
                    if (event.target === canvas) {
                        state.selectedId = null;
                        syncSelectionClasses();
                    }
                });
            }

            function initInteract() {
                const elements = canvas.querySelectorAll('.tpl-component');

                elements.forEach(function (element) {
                    interact(element).unset();

                    const interactable = interact(element);

                    interactable.draggable({
                        ignoreFrom: '.tpl-component-control',
                        listeners: {
                            start(event) {
                                const id = event.target.getAttribute('data-component-id');
                                selectComponent(id);
                            },
                            move(event) {
                                const id = event.target.getAttribute('data-component-id');
                                const component = getComponent(id);

                                if (!component) {
                                    return;
                                }

                                const nextY = Math.max(0, Math.round((component.y || 0) + (event.dy / state.zoom)));

                                if (component.type === 'gallery') {
                                    component.x = 0;
                                    component.y = nextY;
                                } else {
                                    component.x = Math.max(0, Math.round((component.x || 0) + (event.dx / state.zoom)));
                                    component.y = nextY;
                                }

                                event.target.style.left = component.x + 'px';
                                event.target.style.top = component.y + 'px';

                                syncHiddenField();
                            }                        }
                    });

                    if (element.classList.contains('tpl-can-resize')) {
                        interactable.resizable({
                            ignoreFrom: '.tpl-component-control',
                            edges: {
                                left: true,
                                right: true,
                                top: true,
                                bottom: true
                            },
                            listeners: {
                                start(event) {
                                    const id = event.target.getAttribute('data-component-id');
                                    selectComponent(id);
                                },
                                move(event) {
                                    const id = event.target.getAttribute('data-component-id');
                                    const component = getComponent(id);

                                    if (!component || !event.rect) {
                                        return;
                                    }

                                    const deltaRect = event.deltaRect || { left: 0, top: 0 };

                                    component.x = Math.max(0, Math.round((component.x || 0) + ((deltaRect.left || 0) / state.zoom)));
                                    component.y = Math.max(0, Math.round((component.y || 0) + ((deltaRect.top || 0) / state.zoom)));
                                    component.w = Math.max(120, Math.round(event.rect.width / state.zoom));
                                    component.h = Math.max(80, Math.round(event.rect.height / state.zoom));

                                    event.target.style.left = component.x + 'px';
                                    event.target.style.top = component.y + 'px';
                                    event.target.style.width = component.w + 'px';
                                    event.target.style.height = component.h + 'px';

                                    if (component.type === 'carousel') {
                                        const metrics = getCarouselSampleMetrics(component.w, component.h);
                                        const sample = event.target.querySelector('.tpl-carousel-sample');

                                        if (sample) {
                                            sample.style.setProperty('--tpl-carousel-cell-size', metrics.cellSize + 'px');
                                            sample.style.setProperty('--tpl-carousel-gap', metrics.gap + 'px');
                                        }
                                    }
                                    syncHiddenField();
                                }
                            },
                            modifiers: [
                                interact.modifiers.restrictEdges({
                                    outer: 'parent'
                                }),
                                interact.modifiers.restrictSize({
                                    min: { width: 120, height: 80 }
                                })
                            ]
                        });
                    }
                });
            }

            function getCarouselSampleMetrics(width, height) {
                const safeWidth = Math.max(240, parseInt(width || 360, 10));
                const safeHeight = Math.max(180, parseInt(height || 280, 10));

                const usableWidth = Math.max(120, safeWidth - 120);
                const usableHeight = Math.max(80, safeHeight - 120);

                const cellSize = Math.max(40, Math.round(Math.min(usableWidth / 3.6, usableHeight * 0.55)));
                const gap = Math.max(8, Math.round(cellSize * 0.12));

                return {
                    cellSize: cellSize,
                    gap: gap
                };
            }

            function renderCanvas() {
                ensurePageDefaults();

                const renderHeight = getCanvasRenderHeight();

                canvas.style.width = state.definition.page.canvas_width + 'px';
                canvas.style.minHeight = renderHeight + 'px';
                canvas.style.background = state.definition.page.background_color;

                if (!state.definition.components.length) {
                    canvas.innerHTML = '<div class="tpl-empty-canvas">Use the toolbar to add your first component</div>';
                    queueFitZoom();
                    return;
                }

                normalizeZ();

                const orderedComponents = state.definition.components.slice().sort(function (a, b) {
                    return (a.z || 0) - (b.z || 0);
                });

                canvas.innerHTML = orderedComponents.map(function (component) {
                    return `
                        <div
                            class="tpl-component ${component.type !== 'gallery' ? 'tpl-can-resize' : ''} ${state.selectedId === component.id ? 'is-selected' : ''}"
                            data-component-id="${component.id}"
                            style="
                                left:${component.x}px;
                                top:${component.y}px;
                                width:${component.w}px;
                                height:${component.h}px;
                                z-index:${component.z};
                            "
                        >
                            ${componentMarkup(component)}
                        </div>
                    `;
                }).join('');

                bindCanvasDomEvents();
                initInteract();
                syncSelectionClasses();
                queueFitZoom();
            }

            function render() {
                ensurePageDefaults();
                renderCanvas();
                syncHiddenField();

                backgroundColorSwatch.style.background = state.definition.page.background_color;
                buttonColorSwatch.style.background = state.definition.page.button_color;
            }

            function openTextEditor(componentId) {
                const component = getComponent(componentId);

                if (!component) {
                    return;
                }

                state.activeTextEditorId = componentId;
                textEditorModal.classList.add('is-open');

                const content = component.type === 'static_text'
                    ? (component.html || '')
                    : (component.default_html || '');

                const existingEditor = tinymce.get('componentTextEditor');
                if (existingEditor) {
                    existingEditor.remove();
                }

                const textarea = document.getElementById('componentTextEditor');
                textarea.value = content;

                setTimeout(function () {
                    tinymce.init({
                        target: textarea,
                        height: 420,
                        menubar: false,
                        inline: false,
                        plugins: 'link lists code table autoresize',
                        toolbar: 'undo redo | blocks fontsize bold italic forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link table | code'
                    });
                }, 0);
            }

            function closeEditorModal() {
                state.activeTextEditorId = null;
                textEditorModal.classList.remove('is-open');

                const editorInstance = tinymce.get('componentTextEditor');
                if (editorInstance) {
                    editorInstance.remove();
                }
            }

            templateNameInput.addEventListener('input', function () {
                if (isInvalidTemplateName()) {
                    return;
                }

                clearTemplateNameError();
            });

            document.querySelectorAll('[data-add]').forEach(function (button) {
                button.addEventListener('click', function () {
                    createComponent(button.getAttribute('data-add'));
                });
            });

            [closeTextEditor, cancelTextEditorBtn].forEach(function (button) {
                button.addEventListener('click', function () {
                    closeEditorModal();
                });
            });

            saveTextEditorBtn.addEventListener('click', function () {
                const component = getComponent(state.activeTextEditorId);
                const editor = tinymce.get('componentTextEditor');

                if (!component || !editor) {
                    return;
                }

                editor.save();

                const html = editor.getContent({ format: 'html' }) || '<p></p>';

                if (component.type === 'static_text') {
                    component.html = html;
                } else {
                    component.default_html = html;
                }

                syncHiddenField();
                closeEditorModal();

                setTimeout(function () {
                    render();
                }, 0);
            });

            textEditorModal.addEventListener('click', function (event) {
                if (event.target === textEditorModal) {
                    closeEditorModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                const modalOpen = textEditorModal.classList.contains('is-open');
                const activeTag = document.activeElement ? document.activeElement.tagName : '';
                const isTypingTarget = ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeTag);

                if (event.key === 'Escape' && modalOpen) {
                    closeEditorModal();
                    return;
                }

                if (modalOpen || isTypingTarget) {
                    return;
                }

                if ((event.key === 'Delete' || event.key === 'Backspace') && state.selectedId) {
                    event.preventDefault();
                    removeComponent(state.selectedId);
                }
            });

            window.addEventListener('resize', function () {
                applyFitZoom();
            });

            const backgroundPickr = Pickr.create({
                el: '#backgroundColorBtn',
                theme: 'classic',
                default: state.definition.page.background_color,
                useAsButton: true,
                components: {
                    preview: true,
                    opacity: false,
                    hue: true,
                    interaction: {
                        input: false,
                        hex: false,
                        save: true,
                        cancel: true
                    }
                }
            });
            enhancePickrPopup(backgroundPickr, 'Color picker');

            backgroundPickr.on('show', function () {
                backgroundOriginalColor = state.definition.page.background_color;
            });

            backgroundPickr.on('change', function (color) {
                if (!color) {
                    return;
                }

                const newColor = color.toHEXA().toString();

                state.definition.page.background_color = newColor;
                backgroundColorSwatch.style.background = newColor;
                canvas.style.background = newColor;
                syncHiddenField();
            });

            backgroundPickr.on('save', function (color) {
                if (!color) {
                    return;
                }

                const newColor = color.toHEXA().toString();

                state.definition.page.background_color = newColor;
                backgroundColorSwatch.style.background = newColor;
                canvas.style.background = newColor;

                syncHiddenField();
                backgroundPickr.hide();
            });

            backgroundPickr.on('cancel', function () {
                state.definition.page.background_color = backgroundOriginalColor;
                backgroundColorSwatch.style.background = backgroundOriginalColor;
                canvas.style.background = backgroundOriginalColor;

                backgroundPickr.setColor(backgroundOriginalColor);
                syncHiddenField();
                backgroundPickr.hide();
            });

            const buttonPickr = Pickr.create({
                el: '#buttonColorBtn',
                theme: 'classic',
                default: state.definition.page.button_color,
                useAsButton: true,
                components: {
                    preview: true,
                    opacity: false,
                    hue: true,
                    interaction: {
                        input: false,
                        hex: false,
                        save: true,
                        cancel: true
                    }
                }
            });

            enhancePickrPopup(buttonPickr, 'Color picker');

            buttonPickr.on('show', function () {
                buttonOriginalColor = state.definition.page.button_color;
            });

            buttonPickr.on('change', function (color) {
                if (!color) {
                    return;
                }

                const newColor = color.toHEXA().toString();

                state.definition.page.button_color = newColor;
                buttonColorSwatch.style.background = newColor;
                syncHiddenField();
            });

            buttonPickr.on('save', function (color) {
                if (!color) {
                    return;
                }

                const newColor = color.toHEXA().toString();

                state.definition.page.button_color = newColor;
                buttonColorSwatch.style.background = newColor;

                syncHiddenField();
                buttonPickr.hide();
            });

            buttonPickr.on('cancel', function () {
                state.definition.page.button_color = buttonOriginalColor;
                buttonColorSwatch.style.background = buttonOriginalColor;

                buttonPickr.setColor(buttonOriginalColor);
                syncHiddenField();
                buttonPickr.hide();
            });

            document.getElementById('templateEditorForm').addEventListener('submit', function (event) {
                const value = normalizedTemplateName();

                if (value === '' || value.toLowerCase() === 'untitled template') {
                    event.preventDefault();
                    showTemplateNameError();
                    templateNameInput.focus();
                    return;
                }

                clearTemplateNameError();
                templateNameInput.value = value;
                syncHiddenField();
            });

            ensurePageDefaults();

            if (state.definition.components.length) {
                normalizeZ();
                state.selectedId = state.definition.components[state.definition.components.length - 1].id;
            }

            render();
            queueFitZoom();

            window.addEventListener('load', function () {
                queueFitZoom();
            });
        })();
    </script>
</div>