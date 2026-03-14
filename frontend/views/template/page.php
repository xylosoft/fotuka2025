<?php

use common\models\WebsiteTemplateRenderer;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\WebsitePublication $publication */
/** @var array $definition */
/** @var array $values */
/** @var string $folderName */

$this->title = $publication->page_title ?: $folderName;

$page = $definition['page'] ?? [];
$canvasHeight = (int) ($page['canvas_min_height'] ?? 1500);
$backgroundColor = $page['background_color'] ?? '#ffffff';
$buttonColor = $page['button_color'] ?? '#2563eb';
$components = $definition['components'] ?? [];

usort($components, function ($a, $b) {
    return (int) ($a['z'] ?? 0) <=> (int) ($b['z'] ?? 0);
});

$lightboxImages = [];
$renderedComponents = [];
foreach ($components as $component) {
    $renderedComponents[] = WebsiteTemplateRenderer::renderComponent($component, $values, $lightboxImages);
}

$downloadAllUrl = Url::to(['/folder/download-all', 'id' => $publication->folder_id]);

$carouselAutoScrollSpeed = 55; // pixels per second
$carouselImageGap = 0;         // pixels between carousel images
$footerBarHeight = 34;         // pixels
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        :root {
            --page-bg: <?= Html::encode($backgroundColor) ?>;
            --button-bg: <?= Html::encode($buttonColor) ?>;
            --button-text: #ffffff;
            --footer-h: <?= (int) $footerBarHeight ?>px;
            --carousel-gap: <?= (int) $carouselImageGap ?>px;

            --ink: #10233f;
            --muted: #617997;

            --shadow: 0 18px 60px rgba(2,6,23,0.10);
            --shadow-soft: 0 10px 28px rgba(2,6,23,0.08);

            --wedding-btn-bg: rgba(255,255,255,0.90);
            --wedding-btn-border: rgba(15,23,42,0.18);
            --wedding-btn-text: #334155;
            --wedding-top-bg: rgba(243,244,246,0.65);
            --wedding-overlay-bg: rgba(2,6,23,0.84);
            --wedding-body-bg: #0B1220;
        }

        * { box-sizing: border-box; }
        html, body { min-height: 100%; }

        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--page-bg);
            color: var(--ink);
            overflow-x: hidden;
        }

        a { color: inherit; }

        .pub-shell {
            min-height: 100vh;
            background: var(--page-bg);
            padding-bottom: calc(var(--footer-h) + 12px);
        }

        .pub-floating-actions {
            position: fixed;
            top: 20px;
            right: max(
                    12px,
                    calc((100vw - (<?= (int) 1200 ?>px + 120px)) / 2 + <?= (int) 55 ?>px)
            );
            z-index: 60;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pub-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 0 18px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            border: none;
            cursor: pointer;
            white-space: nowrap;
        }

        .pub-btn-primary {
            background: var(--button-bg);
            color: var(--button-text);
            box-shadow: 0 18px 30px rgba(16,35,63,.15);
        }

        .pub-stage {
            padding: 18px 24px 10px;
        }

        .pub-canvas-wrap {
            max-width: calc(<?= (int) 1200 ?>px + 120px);
            margin: 0 auto;
        }

        .pub-canvas {
            position: relative;
            width: <?= (int) 1200 ?>px;
            min-height: <?= (int) $canvasHeight ?>px;
            height: <?= (int) $canvasHeight ?>px;
            margin: 0 auto;
            background: var(--page-bg);
        }

        .tpl-public-component {
            position: absolute;
        }

        .tpl-public-text {
            overflow: hidden;
        }

        .tpl-public-text p:first-child {
            margin-top: 0;
        }

        .tpl-public-text p:last-child {
            margin-bottom: 0;
        }

        .tpl-public-empty {
            border: 2px dashed #bfd1e5;
            border-radius: 16px;
            background: rgba(255,255,255,.35);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 16px;
        }

        .tpl-empty-inner {
            color: #67809f;
            font-weight: 700;
            line-height: 1.5;
        }

        .tpl-public-image img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            border-radius: 18px;
            cursor: default;
        }

        /* =====================================
           GALLERY — self-contained justified rows
           ===================================== */

        .tpl-public-gallery {
            width: 100%;
            height: auto !important;
            overflow: visible !important;
            background: transparent !important;
        }

        .tpl-gallery-grid {
            position: relative;
            width: 100%;
            min-height: 1px;
            background: transparent !important;
        }

        .tpl-gallery-card {
            position: absolute;
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid rgba(15,23,42,0.10);
            box-shadow: var(--shadow-soft);
            background: #E5E7EB;
            line-height: 0;
        }

        .tpl-gallery-card img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: center center;
            border-radius: 18px;
            transform: none;
            filter: saturate(1.03) contrast(1.02);
            transition: transform 0.25s ease;
            cursor: pointer;
        }

        .tpl-gallery-card:hover img {
            transform: scale(1.03);
        }

        .tpl-gallery-card::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 55%, rgba(2,6,23,0.22) 100%);
            opacity: 0;
            transition: opacity 0.2s ease;
            pointer-events: none;
        }

        .tpl-gallery-card:hover::after {
            opacity: 1;
        }

        /* =====================================
           CAROUSEL
           ===================================== */

        .tpl-public-carousel {
            width: 100%;
            height: 100%;
            display: block;
            overflow: hidden;
            background: transparent;
            border-radius: 0;
        }

        .tpl-carousel-arrow {
            display: none !important;
        }

        .tpl-carousel-viewport {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: transparent;
            border-radius: 0;
        }

        .tpl-carousel-track {
            height: 100%;
            display: flex;
            align-items: stretch;
            gap: var(--carousel-gap);
            will-change: transform;
            transform: translateX(0);
        }

        .tpl-carousel-slide {
            flex: 0 0 auto;
            width: auto !important;
            height: 100%;
            min-width: 0;
            display: flex;
            align-items: stretch;
            justify-content: center;
            overflow: hidden;
            background: transparent;
            border-radius: 0;
        }

        .tpl-carousel-slide img {
            width: auto;
            height: 100%;
            max-width: none;
            display: block;
            object-fit: cover;
            border-radius: 0;
            background: transparent;
            cursor: default;
        }

        .pub-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 70;
            height: var(--footer-h);
            display: flex;
            align-items: center;
            backdrop-filter: blur(16px);
            background: rgba(255,255,255,.78);
            border-top: 1px solid rgba(208,220,237,.95);
        }

        .pub-footer-inner {
            width: min(calc(<?= (int) 1200 ?>px + 30px), 100%);
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .pub-footer-note {
            font-size: 12px;
            font-weight: 700;
            color: var(--muted);
            white-space: nowrap;
        }

        .pub-footer-note a {
            text-decoration: none;
            font-weight: inherit;
        }

        /* =====================================
           LIGHTBOX — wedding.php style
           ===================================== */

        .lightbox {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            background: var(--wedding-overlay-bg);
            padding: 24px;
        }

        .lightbox.open {
            display: flex;
        }

        .lb-panel {
            width: min(1120px, 96vw);
            max-height: 92vh;
            border-radius: 18px;
            overflow: hidden;
            background: rgba(255,255,255,0.92);
            border: 1px solid rgba(255,255,255,0.20);
            box-shadow: var(--shadow);
            display: grid;
            grid-template-rows: auto 1fr;
        }

        .lb-top {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            padding: 12px 12px;
            border-bottom: 1px solid rgba(15,23,42,0.10);
            background: var(--wedding-top-bg);
        }

        .lb-spacer {
            flex: 1 1 auto;
        }

        .lb-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .lb-btn {
            height: 28px;
            padding: 0 10px;
            border-radius: 10px;
            border: 1px solid var(--wedding-btn-border);
            background: var(--wedding-btn-bg);
            cursor: pointer;
            font-weight: 900;
            font-size: 12px;
            color: var(--wedding-btn-text);
        }

        .lb-btn:hover {
            background: rgba(255,255,255,1);
        }

        .lb-body {
            background: var(--wedding-body-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .lb-body img {
            width: 100%;
            height: 100%;
            max-height: 78vh;
            object-fit: contain;
            display: block;
        }

        @media (max-width: 1320px) {
            .pub-stage {
                padding: 70px 12px 8px;
                overflow-x: auto;
            }

            .pub-canvas-wrap {
                max-width: none;
            }

            .pub-floating-actions {
                top: <?= (int) 20 ?>px;
                right: 12px;
            }
        }

        @media (max-width: 700px) {
            .lightbox {
                padding: 16px;
            }

            .pub-footer-inner {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<div class="pub-shell">
    <?php if ((int) $publication->allow_download_all === 1): ?>
        <div class="pub-floating-actions">
            <a class="pub-btn pub-btn-primary" href="<?= Html::encode($downloadAllUrl) ?>">Download All</a>
        </div>
    <?php endif; ?>

    <div class="pub-stage">
        <div class="pub-canvas-wrap">
            <div class="pub-canvas" id="pubCanvas">
                <?= implode("\n", $renderedComponents) ?>
            </div>
        </div>
    </div>
</div>


<div class="pub-footer">
    <div class="pub-footer-inner">
        <div class="pub-footer-note">Powered by <a href="/">Fotuka</a>.</div>
    </div>
</div>

<div class="lightbox" id="lightbox" aria-hidden="true">
    <div class="lb-panel" role="dialog" aria-modal="true" aria-label="Image preview">
        <div class="lb-top">
            <div class="lb-spacer"></div>
            <div class="lb-actions">
                <button class="lb-btn" type="button" id="lbPrev">← Prev</button>
                <button class="lb-btn" type="button" id="lbNext">Next →</button>
                <button class="lb-btn" type="button" id="lbClose">✕ Close</button>
            </div>
        </div>
        <div class="lb-body">
            <img id="lbImg" src="" alt="">
        </div>
    </div>
</div>

<script>
    (function () {
        const canvas = document.getElementById('pubCanvas');
        const lightboxItems = <?= json_encode($lightboxImages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        const carouselScrollSpeed = <?= json_encode((float) $carouselAutoScrollSpeed) ?>;
        const carouselGap = <?= json_encode((int) $carouselImageGap) ?>;
        const baseCanvasHeight = <?= json_encode((int) $canvasHeight) ?>;

        const lightbox = document.getElementById('lightbox');
        const lbImg = document.getElementById('lbImg');
        const btnPrev = document.getElementById('lbPrev');
        const btnNext = document.getElementById('lbNext');
        const btnClose = document.getElementById('lbClose');

        let activeLightboxIndexes = [];
        let lightboxCursor = 0;

        function getNumericStyle(el, prop, fallback) {
            const inline = parseFloat(el.style[prop]);
            if (Number.isFinite(inline)) {
                return inline;
            }

            const computed = parseFloat(window.getComputedStyle(el)[prop]);
            if (Number.isFinite(computed)) {
                return computed;
            }

            return fallback;
        }

        function getOriginalMetric(el, key, prop, fallback) {
            const existing = parseFloat(el.getAttribute(key));
            if (Number.isFinite(existing)) {
                return existing;
            }

            const value = getNumericStyle(el, prop, fallback);
            el.setAttribute(key, String(value));
            return value;
        }

        function getGalleryTriggerIndex(node) {
            if (!node) {
                return -1;
            }

            const img = node.matches('img') ? node : node.querySelector('img');
            if (!img) {
                return -1;
            }

            const parsed = parseInt(img.getAttribute('data-lightbox-index') || '-1', 10);
            return Number.isFinite(parsed) ? parsed : -1;
        }

        function getLightboxItem(index, fallbackSrc) {
            const item = lightboxItems[index] || null;

            if (item && item.url) {
                return item;
            }

            return {
                url: fallbackSrc || '',
                title: '',
                width: 0,
                height: 0
            };
        }

        function renderLightbox(fallbackSrc) {
            const globalIndex = activeLightboxIndexes[lightboxCursor];
            const item = getLightboxItem(globalIndex, fallbackSrc);

            if (!item || !item.url) {
                return;
            }

            lbImg.src = item.url || '';
            lbImg.alt = item.title || '';
        }

        function openLightbox(indexes, clickedIndex, fallbackSrc) {
            if (!indexes.length) {
                return;
            }

            activeLightboxIndexes = indexes.slice();
            lightboxCursor = Math.max(0, indexes.indexOf(clickedIndex));
            renderLightbox(fallbackSrc);

            lightbox.classList.add('open');
            lightbox.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.remove('open');
            lightbox.setAttribute('aria-hidden', 'true');
            lbImg.src = '';
            lbImg.alt = '';
            activeLightboxIndexes = [];
            lightboxCursor = 0;
            document.body.style.overflow = '';
        }

        function nextLightbox() {
            if (!activeLightboxIndexes.length) {
                return;
            }

            lightboxCursor = (lightboxCursor + 1) % activeLightboxIndexes.length;
            renderLightbox();
        }

        function prevLightbox() {
            if (!activeLightboxIndexes.length) {
                return;
            }

            lightboxCursor = (lightboxCursor - 1 + activeLightboxIndexes.length) % activeLightboxIndexes.length;
            renderLightbox();
        }

        function getCardRatio(card) {
            const img = card.querySelector('img');
            if (!img) {
                return 4 / 3;
            }

            let width = parseFloat(img.getAttribute('width') || '0');
            let height = parseFloat(img.getAttribute('height') || '0');

            if (!(width > 0 && height > 0)) {
                const idx = getGalleryTriggerIndex(img);
                const meta = lightboxItems[idx] || null;
                width = parseFloat(meta && meta.width ? meta.width : 0);
                height = parseFloat(meta && meta.height ? meta.height : 0);
            }

            if (!(width > 0 && height > 0) && img.naturalWidth > 0 && img.naturalHeight > 0) {
                width = img.naturalWidth;
                height = img.naturalHeight;
            }

            if (!(width > 0 && height > 0)) {
                return 4 / 3;
            }

            return width / height;
        }

        function layoutJustifiedGallery(galleryEl) {
            const component = galleryEl.classList.contains('tpl-public-component')
                ? galleryEl
                : (galleryEl.closest('.tpl-public-component') || galleryEl);

            const grid = galleryEl.querySelector('.tpl-gallery-grid') || galleryEl;
            const cards = Array.from(grid.querySelectorAll('.tpl-gallery-card'));

            if (!cards.length) {
                grid.style.height = '0px';
                return 0;
            }

            const componentWidth = Math.max(
                320,
                Math.round(component.clientWidth || getNumericStyle(component, 'width', galleryEl.clientWidth || 1200))
            );

            const gap = Math.max(10, Math.min(14, Math.round(componentWidth / 100)));
            const targetRowHeight = Math.max(240, Math.min(320, Math.round(componentWidth / 3.9))); // TODO: Adjust as needed
            const widowRowHeight = Math.max(210, Math.min(280, targetRowHeight)); // TODO: Adjust as needed
            const forceBreakAt = componentWidth > 1000 ? 4 : 3;

            grid.style.position = 'relative';
            grid.style.width = '100%';

            const items = cards.map(function (card) {
                return {
                    card: card,
                    ratio: getCardRatio(card)
                };
            });

            const rows = [];
            let currentRow = [];
            let ratioSum = 0;

            function finalizeRow(isLast) {
                if (!currentRow.length) {
                    return;
                }

                const rowGap = gap * (currentRow.length - 1);
                let rowHeight;
                let justify = !isLast;

                if (!justify && currentRow.length >= 3) {
                    justify = true;
                }

                if (justify) {
                    rowHeight = (componentWidth - rowGap) / ratioSum;
                } else {
                    rowHeight = widowRowHeight;
                }

                rowHeight = Math.max(160, Math.min(360, rowHeight));

                rows.push({
                    items: currentRow.slice(),
                    ratioSum: ratioSum,
                    height: rowHeight,
                    justify: justify
                });

                currentRow = [];
                ratioSum = 0;
            }

            items.forEach(function (item) {
                currentRow.push(item);
                ratioSum += item.ratio;

                const projectedWidth = (ratioSum * targetRowHeight) + (gap * (currentRow.length - 1));

                if (projectedWidth >= componentWidth && currentRow.length >= 2) {
                    finalizeRow(false);
                } else if (currentRow.length >= forceBreakAt) {
                    finalizeRow(false);
                }
            });

            finalizeRow(true);

            let top = 0;

            rows.forEach(function (row) {
                const availableWidth = componentWidth - (gap * (row.items.length - 1));
                let left = 0;

                row.items.forEach(function (entry, index) {
                    let width;

                    if (row.justify) {
                        if (index === row.items.length - 1) {
                            width = componentWidth - left;
                        } else {
                            width = Math.round((entry.ratio / row.ratioSum) * availableWidth);
                        }
                    } else {
                        width = Math.round(entry.ratio * row.height);
                    }

                    width = Math.max(80, width);

                    entry.card.style.left = left + 'px';
                    entry.card.style.top = top + 'px';
                    entry.card.style.width = width + 'px';
                    entry.card.style.height = Math.round(row.height) + 'px';

                    left += width + gap;
                });

                top += Math.round(row.height) + gap;
            });

            const totalHeight = Math.max(0, top - gap);
            grid.style.height = totalHeight + 'px';

            return totalHeight;
        }

        function bindGalleryLightboxes() {
            const galleries = Array.from(canvas.querySelectorAll('.tpl-public-gallery'));

            galleries.forEach(function (galleryEl) {
                if (galleryEl.dataset.lightboxBound === '1') {
                    return;
                }

                galleryEl.dataset.lightboxBound = '1';

                galleryEl.addEventListener('click', function (event) {
                    const trigger = event.target.closest('.tpl-lightbox-trigger, .tpl-gallery-card, .tpl-gallery-card img');
                    if (!trigger || !galleryEl.contains(trigger)) {
                        return;
                    }

                    const img = trigger.matches('img') ? trigger : trigger.querySelector('img');
                    if (!img) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();

                    const triggers = Array.from(galleryEl.querySelectorAll('.tpl-lightbox-trigger'));
                    const indexes = triggers
                        .map(function (node) {
                            return getGalleryTriggerIndex(node);
                        })
                        .filter(function (index) {
                            return index >= 0;
                        });

                    const clickedIndex = getGalleryTriggerIndex(img);

                    if (!indexes.length || clickedIndex < 0) {
                        return;
                    }

                    openLightbox(indexes, clickedIndex, img.currentSrc || img.getAttribute('src') || '');
                });
            });
        }

        function prepareLayout() {
            const components = Array.from(canvas.querySelectorAll('.tpl-public-component')).map(function (el) {
                const originalTop = getOriginalMetric(el, 'data-original-top', 'top', el.offsetTop || 0);
                const originalHeight = getOriginalMetric(el, 'data-original-height', 'height', el.offsetHeight || 0);
                const galleryEl = el.classList.contains('tpl-public-gallery') ? el : el.querySelector('.tpl-public-gallery');

                return {
                    el: el,
                    originalTop: originalTop,
                    originalHeight: originalHeight,
                    originalBottom: originalTop + originalHeight,
                    newHeight: originalHeight,
                    gallery: galleryEl
                };
            });

            components.forEach(function (item) {
                if (!item.gallery) {
                    return;
                }

                item.gallery.style.height = 'auto';
                item.el.style.height = 'auto';

                const measuredHeight = layoutJustifiedGallery(item.gallery);

                item.newHeight = Math.max(1, Math.ceil(measuredHeight));
                item.gallery.style.height = item.newHeight + 'px';
                item.el.style.height = item.newHeight + 'px';
            });

            const galleryShifts = components
                .filter(function (item) { return item.gallery; })
                .map(function (item) {
                    return {
                        end: item.originalBottom,
                        delta: item.newHeight - item.originalHeight
                    };
                });

            let maxBottom = 0;

            components.forEach(function (item) {
                let shift = 0;

                galleryShifts.forEach(function (galleryShift) {
                    if (item.originalTop >= galleryShift.end) {
                        shift += galleryShift.delta;
                    }
                });

                const nextTop = item.originalTop + shift;
                const nextHeight = item.gallery ? item.newHeight : item.originalHeight;

                item.el.style.top = Math.round(nextTop) + 'px';

                if (!item.gallery) {
                    item.el.style.height = Math.round(nextHeight) + 'px';
                }

                maxBottom = Math.max(maxBottom, nextTop + nextHeight);
            });

            canvas.style.height = Math.max(baseCanvasHeight, Math.ceil(maxBottom)) + 'px';
        }

        function initCarousels() {
            document.querySelectorAll('.tpl-public-carousel').forEach(function (carousel) {
                const viewport = carousel.querySelector('.tpl-carousel-viewport');
                const track = carousel.querySelector('.tpl-carousel-track');

                if (!viewport || !track || carousel.dataset.carouselReady === '1') {
                    return;
                }

                const originalSlides = Array.from(track.querySelectorAll('.tpl-carousel-slide'));
                if (!originalSlides.length) {
                    return;
                }

                carousel.dataset.carouselReady = '1';
                carousel.style.setProperty('--carousel-gap', carouselGap + 'px');

                const clones = originalSlides.map(function (slide) {
                    return slide.cloneNode(true);
                });

                clones.forEach(function (clone) {
                    track.appendChild(clone);
                });

                const originalCount = originalSlides.length;
                let offset = 0;
                let lastTimestamp = null;
                let loopWidth = 1;

                function measureLoopWidth() {
                    const children = Array.from(track.children);
                    let width = 0;

                    for (let i = 0; i < originalCount; i++) {
                        const child = children[i];
                        if (!child) {
                            continue;
                        }

                        width += child.getBoundingClientRect().width;

                        if (i < originalCount - 1) {
                            width += carouselGap;
                        }
                    }

                    loopWidth = Math.max(1, width);
                }

                const resizeObserver = new ResizeObserver(function () {
                    measureLoopWidth();
                });

                resizeObserver.observe(viewport);

                Array.from(track.children).forEach(function (child) {
                    resizeObserver.observe(child);
                });

                measureLoopWidth();

                function step(timestamp) {
                    if (!document.body.contains(carousel)) {
                        return;
                    }

                    if (lastTimestamp === null) {
                        lastTimestamp = timestamp;
                    }

                    let delta = (timestamp - lastTimestamp) / 1000;
                    if (delta > 0.05) {
                        delta = 0.05;
                    }
                    lastTimestamp = timestamp;

                    offset += carouselScrollSpeed * delta;

                    if (offset >= loopWidth) {
                        offset -= loopWidth;
                    }

                    track.style.transform = 'translateX(-' + offset + 'px)';
                    carousel._rafId = window.requestAnimationFrame(step);
                }

                carousel._rafId = window.requestAnimationFrame(step);
            });
        }

        function waitForGalleryImages(callback) {
            const images = Array.from(canvas.querySelectorAll('.tpl-public-gallery img'));

            if (!images.length) {
                callback();
                return;
            }

            let pending = 0;
            let finished = false;

            function done() {
                pending -= 1;
                if (pending <= 0 && !finished) {
                    finished = true;
                    callback();
                }
            }

            images.forEach(function (img) {
                if (img.complete && img.naturalWidth > 0) {
                    return;
                }

                pending += 1;
                img.addEventListener('load', done, { once: true });
                img.addEventListener('error', done, { once: true });
            });

            if (pending === 0) {
                callback();
                return;
            }

            window.setTimeout(function () {
                if (!finished) {
                    finished = true;
                    callback();
                }
            }, 1800);
        }

        function boot() {
            bindGalleryLightboxes();
            prepareLayout();
            initCarousels();

            waitForGalleryImages(function () {
                prepareLayout();
            });
        }

        btnNext.addEventListener('click', nextLightbox);
        btnPrev.addEventListener('click', prevLightbox);
        btnClose.addEventListener('click', closeLightbox);

        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (!lightbox.classList.contains('open')) {
                return;
            }

            if (event.key === 'Escape') {
                closeLightbox();
            }

            if (event.key === 'ArrowRight') {
                nextLightbox();
            }

            if (event.key === 'ArrowLeft') {
                prevLightbox();
            }
        });

        const debouncedResize = (function () {
            let timer = null;

            return function () {
                window.clearTimeout(timer);
                timer = window.setTimeout(function () {
                    prepareLayout();
                }, 120);
            };
        })();

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot);
        } else {
            boot();
        }

        window.addEventListener('load', function () {
            prepareLayout();
        });

        window.addEventListener('resize', debouncedResize);
    })();
</script>
</body>
</html>