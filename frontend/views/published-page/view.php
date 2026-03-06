<?php
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\TemplateSection;

$this->title = $page->page_title ?: ($template->name ?: 'Published Page');

function inlineStyle(array $styles) {
    $pairs = [];
    foreach ($styles as $key => $value) {
        if ($value === null || $value === '') {
            continue;
        }
        $pairs[] = $key . ':' . $value;
    }
    return implode(';', $pairs);
}

function assetImageUrl($asset) {
    return $asset->preview_url ?: $asset->thumbnail_url;
}

function galleryTileStyle($asset, $index) {
    $width = (int)($asset->file->width ?? 0);
    $height = (int)($asset->file->height ?? 0);
    $ratio = ($width > 0 && $height > 0) ? ($width / max($height, 1)) : 1.0;

    if ($ratio >= 1.75) {
        return 'grid-column: span 7; grid-row: span 2;';
    }

    if ($ratio >= 1.25) {
        return 'grid-column: span 6; grid-row: span 2;';
    }

    if ($ratio <= 0.78) {
        return 'grid-column: span 5; grid-row: span 3;';
    }

    if ($index % 7 === 0) {
        return 'grid-column: span 7; grid-row: span 2;';
    }

    return 'grid-column: span 4; grid-row: span 2;';
}

function fieldValueStyle($field) {
    if (!$field) {
        return '';
    }

    return inlineStyle([
        'color' => $field->text_color ?: '#111827',
        'font-size' => $field->font_size ? $field->font_size . 'px' : '16px',
        'font-weight' => $field->font_weight ?: '400',
        'font-style' => $field->font_style ?: 'normal',
        'text-align' => $field->text_align ?: 'left',
        'white-space' => 'pre-line',
    ]);
}
?>

<div class="public-template-page"
     style="--page-bg: <?= Html::encode($theme['page_background_color']) ?>;
             --page-text: <?= Html::encode($theme['page_text_color']) ?>;
             --accent: <?= Html::encode($theme['accent_color']) ?>;
             --button-bg: <?= Html::encode($theme['button_color']) ?>;
             --button-text: <?= Html::encode($theme['button_text_color']) ?>;
             --section-bg: <?= Html::encode($theme['section_background_color']) ?>;">

    <?php if ($page->allow_downloads): ?>
        <div class="public-actions-bar">
            <a href="<?= Url::to(['/pages/' . $page->uri . '/download-all']) ?>" class="download-all-btn">Download All</a>
        </div>
    <?php endif; ?>

    <div class="public-template-shell">
        <?php
        $sections = $template->sections;
        $count = count($sections);

        for ($i = 0; $i < $count; $i++):
            $section = $sections[$i];
            $settings = $section->getSettings();
            $selectedAssets = $selectedAssetsBySection[$section->section_id] ?? [];
            $customValue = $section->custom_field_id ? ($valuesByCustomField[$section->custom_field_id] ?? '') : '';
            $field = $section->customField ?? null;

            $sectionText = '';
            if (($settings['text_mode'] ?? 'static') === 'custom_field') {
                $sectionText = (string)$customValue;
            } else {
                $sectionText = (string)$section->text;
            }

            $sectionStyle = inlineStyle([
                'background' => 'var(--section-bg)',
                'color' => $section->text_color ?: 'var(--page-text)',
            ]);

            $nextSection = $sections[$i + 1] ?? null;

            if (
                $section->type === TemplateSection::TYPE_LOGO
                && $nextSection
                && $nextSection->type === TemplateSection::TYPE_COMPANY_NAME
            ):
                $logoSettings = $section->getSettings();
                $companySettings = $nextSection->getSettings();
                $logoWidth = (int)($logoSettings['logo_width'] ?? 180);
                ?>
                <section class="section-block brand-row-block">
                    <div class="brand-row-inner">
                        <?php if ($customer && !empty($customer->logo_url)): ?>
                            <div class="brand-logo-wrap" style="width:<?= $logoWidth ?>px;">
                                <img src="<?= Html::encode($customer->logo_url) ?>" alt="Logo" class="brand-logo">
                            </div>
                        <?php endif; ?>

                        <div class="brand-company-name"
                             style="font-size:<?= (int)($companySettings['font_size'] ?? 32) ?>px;
                                     text-align:<?= Html::encode($companySettings['text_align'] ?? 'left') ?>;
                                     color:<?= Html::encode($nextSection->text_color ?: $theme['page_text_color']) ?>;">
                            <?= Html::encode($customer ? $customer->display_name : '') ?>
                        </div>
                    </div>
                </section>
                <?php
                $i++;
                continue;
            endif;

            if (
                $section->type === TemplateSection::TYPE_COMPANY_NAME
                && $nextSection
                && $nextSection->type === TemplateSection::TYPE_LOGO
            ):
                $logoSettings = $nextSection->getSettings();
                $companySettings = $section->getSettings();
                $logoWidth = (int)($logoSettings['logo_width'] ?? 180);
                ?>
                <section class="section-block brand-row-block">
                    <div class="brand-row-inner">
                        <div class="brand-company-name"
                             style="font-size:<?= (int)($companySettings['font_size'] ?? 32) ?>px;
                                     text-align:<?= Html::encode($companySettings['text_align'] ?? 'left') ?>;
                                     color:<?= Html::encode($section->text_color ?: $theme['page_text_color']) ?>;">
                            <?= Html::encode($customer ? $customer->display_name : '') ?>
                        </div>

                        <?php if ($customer && !empty($customer->logo_url)): ?>
                            <div class="brand-logo-wrap" style="width:<?= $logoWidth ?>px;">
                                <img src="<?= Html::encode($customer->logo_url) ?>" alt="Logo" class="brand-logo">
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
                <?php
                $i++;
                continue;
            endif;
            ?>

            <?php if ($section->type === TemplateSection::TYPE_HEADER_IMAGE): ?>
            <?php $heroAsset = $selectedAssets[0] ?? null; ?>
            <section class="section-block highlighted-image-block" style="<?= Html::encode($sectionStyle) ?>;<?= $section->height ? 'min-height:' . (int)$section->height . 'px;' : 'min-height:420px;' ?>">
                <?php if ($heroAsset && assetImageUrl($heroAsset)): ?>
                    <img src="<?= Html::encode(assetImageUrl($heroAsset)) ?>" class="highlighted-image-bg" alt="">
                <?php endif; ?>

                <div class="highlighted-image-overlay"></div>

                <?php if ($sectionText !== ''): ?>
                    <div class="highlighted-image-text"
                         style="font-size:<?= (int)($settings['font_size'] ?? 36) ?>px;
                                 text-align:<?= Html::encode($settings['text_align'] ?? 'center') ?>;
                         <?= $field ? fieldValueStyle($field) : '' ?>">
                        <?= nl2br(Html::encode($sectionText)) ?>
                    </div>
                <?php endif; ?>
            </section>

        <?php elseif ($section->type === TemplateSection::TYPE_LOGO): ?>
            <?php $logoWidth = (int)($settings['logo_width'] ?? 180); ?>
            <?php if ($customer && !empty($customer->logo_url)): ?>
                <section class="section-block logo-only-block">
                    <div class="logo-only-inner" style="width:<?= $logoWidth ?>px;">
                        <img src="<?= Html::encode($customer->logo_url) ?>" alt="Logo" class="brand-logo">
                    </div>
                </section>
            <?php endif; ?>

        <?php elseif ($section->type === TemplateSection::TYPE_COMPANY_NAME): ?>
            <section class="section-block company-name-only-block"
                     style="font-size:<?= (int)($settings['font_size'] ?? 32) ?>px;
                             text-align:<?= Html::encode($settings['text_align'] ?? 'left') ?>;
                             color:<?= Html::encode($section->text_color ?: $theme['page_text_color']) ?>;">
                <?= Html::encode($customer ? $customer->display_name : '') ?>
            </section>

        <?php elseif ($section->type === TemplateSection::TYPE_TEXT_BLOCK): ?>
            <section class="section-block text-block-section"
                     style="font-size:<?= (int)($settings['font_size'] ?? 20) ?>px;
                             text-align:<?= Html::encode($settings['text_align'] ?? 'left') ?>;
                             color:<?= Html::encode($section->text_color ?: $theme['page_text_color']) ?>;
                     <?= $field ? fieldValueStyle($field) : '' ?>">
                <?= nl2br(Html::encode($sectionText)) ?>
            </section>

        <?php elseif ($section->type === TemplateSection::TYPE_SINGLE_IMAGE): ?>
            <section class="section-block image-row-section">
                <div class="image-row-grid count-<?= count($selectedAssets) ?>">
                    <?php foreach ($selectedAssets as $asset): ?>
                        <?php if (!assetImageUrl($asset)) continue; ?>
                        <div class="image-row-item">
                            <img src="<?= Html::encode(assetImageUrl($asset)) ?>" alt="">
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        <?php elseif ($section->type === TemplateSection::TYPE_IMAGE_CAROUSEL): ?>
            <section class="section-block carousel-section"
                     style="<?= $section->height ? 'height:' . (int)$section->height . 'px;' : 'height:280px;' ?>">
                <div class="carousel-track">
                    <?php $carouselAssets = array_merge($selectedAssets, $selectedAssets); ?>
                    <?php foreach ($carouselAssets as $asset): ?>
                        <?php if (!assetImageUrl($asset)) continue; ?>
                        <div class="carousel-slide">
                            <img src="<?= Html::encode(assetImageUrl($asset)) ?>" alt="">
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        <?php elseif ($section->type === TemplateSection::TYPE_GALLERY): ?>
            <section class="section-block gallery-section">
                <div class="gallery-mosaic-grid" id="gallery-grid">
                    <?php foreach ($allImageAssets as $index => $asset): ?>
                        <?php if (!assetImageUrl($asset)) continue; ?>
                        <button type="button"
                                class="gallery-mosaic-item"
                                style="<?= galleryTileStyle($asset, $index) ?>"
                                data-index="<?= (int)$index ?>"
                                data-url="<?= Html::encode(assetImageUrl($asset)) ?>">
                            <img src="<?= Html::encode(assetImageUrl($asset)) ?>" alt="">
                        </button>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php endfor; ?>
    </div>
</div>

<div class="gallery-lightbox" id="gallery-lightbox" style="display:none;">
    <button type="button" class="lightbox-close" id="lightbox-close">×</button>
    <button type="button" class="lightbox-nav prev" id="lightbox-prev">‹</button>
    <img src="" alt="" id="lightbox-image">
    <button type="button" class="lightbox-nav next" id="lightbox-next">›</button>
</div>

<style>
    body {
        background: var(--page-bg);
        color: var(--page-text);
    }
    .public-template-page {
        background: var(--page-bg);
        color: var(--page-text);
        min-height: 100vh;
    }
    .public-actions-bar {
        display: flex;
        justify-content: flex-end;
        padding: 18px 24px 0;
    }
    .download-all-btn {
        background: var(--button-bg);
        color: var(--button-text);
        text-decoration: none;
        padding: 12px 18px;
        border-radius: 12px;
        font-weight: 700;
    }
    .public-template-shell {
        max-width: 1320px;
        margin: 0 auto;
        padding: 0 20px 40px;
    }
    .section-block {
        position: relative;
        border-radius: 18px;
        overflow: hidden;
        margin-top: 20px;
        box-shadow: 0 14px 34px rgba(0,0,0,.06);
        background: var(--section-bg);
    }
    .highlighted-image-block {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .highlighted-image-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .highlighted-image-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,.18), rgba(0,0,0,.4));
    }
    .highlighted-image-text {
        position: relative;
        z-index: 2;
        color: #fff;
        font-weight: 700;
        max-width: 960px;
        padding: 24px;
        line-height: 1.2;
        white-space: pre-line;
    }
    .brand-row-block,
    .logo-only-block,
    .company-name-only-block,
    .text-block-section {
        padding: 28px;
    }
    .brand-row-inner {
        display: flex;
        align-items: center;
        gap: 22px;
        flex-wrap: wrap;
    }
    .brand-logo-wrap,
    .logo-only-inner {
        flex: 0 0 auto;
    }
    .brand-logo {
        width: 100%;
        height: auto;
        display: block;
    }
    .brand-company-name {
        font-weight: 700;
        line-height: 1.2;
    }
    .company-name-only-block {
        font-weight: 700;
    }
    .text-block-section {
        white-space: pre-line;
    }
    .image-row-section {
        padding: 22px;
    }
    .image-row-grid {
        display: grid;
        gap: 18px;
        align-items: start;
    }
    .image-row-grid.count-1 { grid-template-columns: minmax(0, 1fr); }
    .image-row-grid.count-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .image-row-grid.count-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .image-row-grid.count-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .image-row-grid.count-5 { grid-template-columns: repeat(5, minmax(0, 1fr)); }

    .image-row-item img {
        width: 100%;
        height: auto;
        display: block;
        border-radius: 14px;
    }
    .carousel-section {
        overflow: hidden;
    }
    .carousel-track {
        display: flex;
        gap: 18px;
        width: max-content;
        animation: scrollCarousel 40s linear infinite;
        padding: 18px;
        height: 100%;
        align-items: center;
    }
    .carousel-slide {
        height: 100%;
        width: 420px;
        flex: 0 0 auto;
    }
    .carousel-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 16px;
    }
    @keyframes scrollCarousel {
        from { transform: translateX(0); }
        to { transform: translateX(-50%); }
    }
    .gallery-section {
        padding: 20px;
    }
    .gallery-mosaic-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        grid-auto-rows: 150px;
        grid-auto-flow: dense;
        gap: 14px;
    }
    .gallery-mosaic-item {
        border: 0;
        padding: 0;
        margin: 0;
        background: transparent;
        cursor: pointer;
        overflow: hidden;
        border-radius: 16px;
    }
    .gallery-mosaic-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform .18s ease;
    }
    .gallery-mosaic-item:hover img {
        transform: scale(1.02);
    }
    .gallery-lightbox {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.9);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .gallery-lightbox img {
        max-width: 90vw;
        max-height: 84vh;
        border-radius: 12px;
    }
    .lightbox-close,
    .lightbox-nav {
        position: absolute;
        border: 0;
        background: rgba(255,255,255,.12);
        color: #fff;
        width: 48px;
        height: 48px;
        border-radius: 999px;
        font-size: 28px;
        cursor: pointer;
    }
    .lightbox-close {
        top: 22px;
        right: 22px;
    }
    .lightbox-nav.prev {
        left: 24px;
    }
    .lightbox-nav.next {
        right: 24px;
    }
    @media (max-width: 1100px) {
        .gallery-mosaic-grid {
            grid-template-columns: repeat(8, 1fr);
            grid-auto-rows: 130px;
        }
        .image-row-grid.count-4,
        .image-row-grid.count-5 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 768px) {
        .public-template-shell {
            padding: 0 14px 28px;
        }
        .highlighted-image-block {
            min-height: 320px !important;
        }
        .highlighted-image-text {
            font-size: 28px !important;
        }
        .carousel-slide {
            width: 280px;
        }
        .gallery-mosaic-grid {
            grid-template-columns: repeat(4, 1fr);
            grid-auto-rows: 120px;
        }
        .image-row-grid.count-3,
        .image-row-grid.count-4,
        .image-row-grid.count-5 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 520px) {
        .gallery-mosaic-grid {
            grid-template-columns: repeat(2, 1fr);
            grid-auto-rows: 110px;
        }
        .image-row-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<script>
    const galleryUrls = $('#gallery-grid .gallery-mosaic-item').map(function() {
        return $(this).data('url');
    }).get();

    let currentGalleryIndex = 0;

    function openLightbox(index) {
        currentGalleryIndex = index;
        $('#lightbox-image').attr('src', galleryUrls[currentGalleryIndex] || '');
        $('#gallery-lightbox').fadeIn(150);
    }

    function closeLightbox() {
        $('#gallery-lightbox').fadeOut(150);
    }

    function nextLightbox() {
        if (!galleryUrls.length) return;
        currentGalleryIndex = (currentGalleryIndex + 1) % galleryUrls.length;
        $('#lightbox-image').attr('src', galleryUrls[currentGalleryIndex]);
    }

    function prevLightbox() {
        if (!galleryUrls.length) return;
        currentGalleryIndex = (currentGalleryIndex - 1 + galleryUrls.length) % galleryUrls.length;
        $('#lightbox-image').attr('src', galleryUrls[currentGalleryIndex]);
    }

    $(function() {
        $('#gallery-grid').on('click', '.gallery-mosaic-item', function() {
            openLightbox(parseInt($(this).data('index'), 10));
        });

        $('#lightbox-close').on('click', closeLightbox);
        $('#lightbox-next').on('click', nextLightbox);
        $('#lightbox-prev').on('click', prevLightbox);

        $(document).on('keydown', function(e) {
            if ($('#gallery-lightbox').is(':visible')) {
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowRight') nextLightbox();
                if (e.key === 'ArrowLeft') prevLightbox();
            }
        });
    });
</script>