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
$canvasWidth = (int) ($page['canvas_width'] ?? 1200);
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
            top: 16px;
            right: 16px;
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
            max-width: calc(<?= (int) $canvasWidth ?>px + 120px);
            margin: 0 auto;
        }

        .pub-canvas {
            position: relative;
            width: <?= (int) $canvasWidth ?>px;
            min-height: <?= (int) $canvasHeight ?>px;
            height: <?= (int) $canvasHeight ?>px;
            margin: 0 auto;
            background: var(--page-bg);
        }

        .tpl-public-component { position: absolute; }

        .tpl-public-text { overflow: hidden; }
        .tpl-public-text p:first-child { margin-top: 0; }
        .tpl-public-text p:last-child { margin-bottom: 0; }

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

        .tpl-public-gallery {
            width: 100%;
            height: auto !important;
            overflow: visible !important;
            background: transparent !important;
        }

        .tpl-gallery-grid {
            width: 100%;
            height: auto !important;
            background: transparent !important;
        }

        .tpl-gallery-mosaic {
            display: flex;
            flex-direction: column;
            gap: var(--gallery-gap, 14px);
            width: 100%;
        }

        .tpl-gallery-row {
            width: 100%;
            display: grid;
            gap: var(--gallery-gap, 14px);
            align-items: stretch;
        }

        .tpl-gallery-row.row-three {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            grid-auto-rows: var(--gallery-row-md, 300px);
        }

        .tpl-gallery-row.row-two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-auto-rows: var(--gallery-row-lg, 340px);
        }

        .tpl-gallery-row.row-hero-left {
            grid-template-columns: minmax(0, 1.65fr) minmax(0, 1fr);
            grid-template-rows: repeat(2, var(--gallery-row-sm, 190px));
        }
        .tpl-gallery-row.row-hero-left > .tpl-gallery-card:nth-child(1) { grid-column: 1; grid-row: 1 / span 2; }
        .tpl-gallery-row.row-hero-left > .tpl-gallery-card:nth-child(2) { grid-column: 2; grid-row: 1; }
        .tpl-gallery-row.row-hero-left > .tpl-gallery-card:nth-child(3) { grid-column: 2; grid-row: 2; }

        .tpl-gallery-row.row-hero-right {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1.65fr);
            grid-template-rows: repeat(2, var(--gallery-row-sm, 190px));
        }
        .tpl-gallery-row.row-hero-right > .tpl-gallery-card:nth-child(1) { grid-column: 1; grid-row: 1; }
        .tpl-gallery-row.row-hero-right > .tpl-gallery-card:nth-child(2) { grid-column: 1; grid-row: 2; }
        .tpl-gallery-row.row-hero-right > .tpl-gallery-card:nth-child(3) { grid-column: 2; grid-row: 1 / span 2; }

        .tpl-gallery-row.row-portrait-left {
            grid-template-columns: minmax(0, .92fr) minmax(0, 1.08fr);
            grid-template-rows: repeat(2, var(--gallery-row-sm, 190px));
        }
        .tpl-gallery-row.row-portrait-left > .tpl-gallery-card:nth-child(1) { grid-column: 1; grid-row: 1 / span 2; }
        .tpl-gallery-row.row-portrait-left > .tpl-gallery-card:nth-child(2) { grid-column: 2; grid-row: 1; }
        .tpl-gallery-row.row-portrait-left > .tpl-gallery-card:nth-child(3) { grid-column: 2; grid-row: 2; }

        .tpl-gallery-row.row-portrait-right {
            grid-template-columns: minmax(0, 1.08fr) minmax(0, .92fr);
            grid-template-rows: repeat(2, var(--gallery-row-sm, 190px));
        }
        .tpl-gallery-row.row-portrait-right > .tpl-gallery-card:nth-child(1) { grid-column: 1; grid-row: 1; }
        .tpl-gallery-row.row-portrait-right > .tpl-gallery-card:nth-child(2) { grid-column: 1; grid-row: 2; }
        .tpl-gallery-row.row-portrait-right > .tpl-gallery-card:nth-child(3) { grid-column: 2; grid-row: 1 / span 2; }

        .tpl-gallery-row.row-center-hero {
            grid-template-columns: minmax(0, .95fr) minmax(0, 1.15fr) minmax(0, .95fr);
            grid-template-rows: repeat(2, var(--gallery-row-sm, 190px));
        }
        .tpl-gallery-row.row-center-hero > .tpl-gallery-card:nth-child(1) { grid-column: 1; grid-row: 1; }
        .tpl-gallery-row.row-center-hero > .tpl-gallery-card:nth-child(2) { grid-column: 1; grid-row: 2; }
        .tpl-gallery-row.row-center-hero > .tpl-gallery-card:nth-child(3) { grid-column: 2; grid-row: 1 / span 2; }
        .tpl-gallery-row.row-center-hero > .tpl-gallery-card:nth-child(4) { grid-column: 3; grid-row: 1; }
        .tpl-gallery-row.row-center-hero > .tpl-gallery-card:nth-child(5) { grid-column: 3; grid-row: 2; }

        .tpl-gallery-row.row-four-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-template-rows: repeat(2, var(--gallery-row-sm, 190px));
        }

        .tpl-gallery-row.row-single-wide {
            grid-template-columns: minmax(0, 1fr);
            grid-auto-rows: var(--gallery-row-xl, 360px);
        }

        .tpl-gallery-row.row-single-portrait {
            grid-template-columns: minmax(0, 40%);
            grid-auto-rows: var(--gallery-row-xl, 360px);
        }

        .tpl-gallery-row.row-single-square {
            grid-template-columns: minmax(260px, 34%);
            grid-auto-rows: var(--gallery-row-lg, 340px);
        }

        .tpl-gallery-card {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
        }

        .tpl-gallery-card img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: center center;
            border-radius: 18px;
            background: transparent !important;
            cursor: pointer;
        }

        .tpl-public-carousel {
            width: 100%;
            height: 100%;
            display: block;
            overflow: hidden;
            background: transparent;
            border-radius: 0;
        }

        .tpl-carousel-arrow { display: none !important; }

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
            width: min(calc(<?= (int) $canvasWidth ?>px + 120px), 100%);
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

        .pub-lightbox {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(6,16,29,.86);
            padding: 40px;
        }

        .pub-lightbox.is-open { display: flex; }

        .pub-lightbox-dialog {
            position: relative;
            width: min(1120px, 94vw);
            height: min(82vh, 820px);
            border-radius: 24px;
            overflow: hidden;
            background: #09121f;
            border: 1px solid rgba(255,255,255,.08);
            box-shadow: 0 26px 70px rgba(0,0,0,.42);
        }

        .pub-lightbox-dialog img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            background: #09121f;
        }

        .pub-lightbox-btn {
            position: absolute;
            top: 18px;
            width: 46px;
            height: 46px;
            border: none;
            border-radius: 999px;
            background: rgba(255,255,255,.14);
            color: #fff;
            font-size: 22px;
            font-weight: 800;
            cursor: pointer;
        }

        .pub-lightbox-btn.close { right: 18px; }
        .pub-lightbox-btn.prev { top: 50%; left: 18px; transform: translateY(-50%); }
        .pub-lightbox-btn.next { top: 50%; right: 18px; transform: translateY(-50%); }

        .pub-lightbox-caption {
            position: absolute;
            left: 20px;
            right: 82px;
            bottom: 18px;
            background: rgba(255,255,255,.12);
            color: #fff;
            border-radius: 14px;
            padding: 12px 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 700;
        }

        @media (max-width: 1320px) {
            .pub-stage {
                padding: 70px 12px 8px;
                overflow-x: auto;
            }
            .pub-canvas-wrap { max-width: none; }
            .pub-floating-actions { top: 14px; right: 12px; }
        }

        @media (max-width: 700px) {
            .pub-lightbox { padding: 16px; }
            .pub-lightbox-dialog {
                width: 100%;
                height: min(78vh, 680px);
                border-radius: 20px;
            }
            .pub-lightbox-btn { width: 42px; height: 42px; }
            .pub-footer-inner { justify-content: center; }
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

<div id="pubLightbox" class="pub-lightbox" aria-hidden="true">
    <div class="pub-lightbox-dialog" role="dialog" aria-modal="true" aria-label="Gallery preview">
        <button type="button" class="pub-lightbox-btn close" id="pubLightboxClose" aria-label="Close">✕</button>
        <button type="button" class="pub-lightbox-btn prev" id="pubLightboxPrev" aria-label="Previous">‹</button>
        <button type="button" class="pub-lightbox-btn next" id="pubLightboxNext" aria-label="Next">›</button>
        <img id="pubLightboxImage" src="" alt="">
        <div id="pubLightboxCaption" class="pub-lightbox-caption"></div>
    </div>
</div>

<script>
    (function () {
        const canvas = document.getElementById('pubCanvas');
        const lightboxItems = <?= json_encode($lightboxImages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        const carouselScrollSpeed = <?= json_encode((float) $carouselAutoScrollSpeed) ?>;
        const carouselGap = <?= json_encode((int) $carouselImageGap) ?>;
        const baseCanvasHeight = <?= json_encode((int) $canvasHeight) ?>;

        const lightbox = document.getElementById('pubLightbox');
        const imageEl = document.getElementById('pubLightboxImage');
        const captionEl = document.getElementById('pubLightboxCaption');
        const closeBtn = document.getElementById('pubLightboxClose');
        const prevBtn = document.getElementById('pubLightboxPrev');
        const nextBtn = document.getElementById('pubLightboxNext');

        let activeLightboxIndexes = [];
        let lightboxCursor = 0;

        function shuffle(list) {
            const cloned = list.slice();
            for (let i = cloned.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                const tmp = cloned[i];
                cloned[i] = cloned[j];
                cloned[j] = tmp;
            }
            return cloned;
        }

        function getNumericStyle(el, prop, fallback) {
            const inline = parseFloat(el.style[prop]);
            if (Number.isFinite(inline)) return inline;

            const computed = parseFloat(window.getComputedStyle(el)[prop]);
            if (Number.isFinite(computed)) return computed;

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

        function getLightboxMeta(index) {
            const item = lightboxItems[index] || null;
            return item || { url: '', title: '', width: 0, height: 0 };
        }

        function renderLightbox() {
            const globalIndex = activeLightboxIndexes[lightboxCursor];
            const item = getLightboxMeta(globalIndex);
            if (!item || !item.url) return;

            imageEl.src = item.url || '';
            imageEl.alt = item.title || '';
            captionEl.textContent = item.title || '';
        }

        function openLightbox(indexes, clickedIndex) {
            if (!indexes.length) return;

            activeLightboxIndexes = indexes.slice();
            lightboxCursor = Math.max(0, indexes.indexOf(clickedIndex));
            renderLightbox();
            lightbox.classList.add('is-open');
            lightbox.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.remove('is-open');
            lightbox.setAttribute('aria-hidden', 'true');
            imageEl.src = '';
            imageEl.alt = '';
            captionEl.textContent = '';
            activeLightboxIndexes = [];
            lightboxCursor = 0;
            document.body.style.overflow = '';
        }

        function prevLightbox() {
            if (!activeLightboxIndexes.length) return;
            lightboxCursor = (lightboxCursor - 1 + activeLightboxIndexes.length) % activeLightboxIndexes.length;
            renderLightbox();
        }

        function nextLightbox() {
            if (!activeLightboxIndexes.length) return;
            lightboxCursor = (lightboxCursor + 1) % activeLightboxIndexes.length;
            renderLightbox();
        }

        function getGalleryTriggerIndex(img) {
            const parsed = parseInt(img.getAttribute('data-lightbox-index') || '-1', 10);
            return Number.isFinite(parsed) ? parsed : -1;
        }

        function getImageRatio(img) {
            const lightboxIndex = getGalleryTriggerIndex(img);
            const meta = getLightboxMeta(lightboxIndex);
            const width = parseFloat(meta.width || 0);
            const height = parseFloat(meta.height || 0);

            if (width > 0 && height > 0) {
                return width / height;
            }

            if (img.naturalWidth > 0 && img.naturalHeight > 0) {
                return img.naturalWidth / img.naturalHeight;
            }

            return 1.15;
        }

        function ensureGalleryState(galleryEl) {
            if (galleryEl._galleryState) {
                return galleryEl._galleryState;
            }

            const grid = galleryEl.querySelector('.tpl-gallery-grid') || galleryEl;
            const cards = Array.from(grid.querySelectorAll('.tpl-gallery-card'));
            const state = {
                grid: grid,
                cards: cards,
                plan: null
            };

            galleryEl._galleryState = state;
            return state;
        }

        function chooseChunkCounts(total) {
            const counts = [5, 4, 3, 2, 1];
            const memo = {};

            function canSolve(remaining) {
                if (remaining === 0) return true;
                if (remaining < 0) return false;
                if (memo.hasOwnProperty(remaining)) return memo[remaining];

                for (let i = 0; i < counts.length; i++) {
                    const count = counts[i];
                    if (count > remaining) continue;
                    if (remaining - count === 1 && remaining !== 1) continue;
                    if (canSolve(remaining - count)) {
                        memo[remaining] = true;
                        return true;
                    }
                }

                memo[remaining] = false;
                return false;
            }

            function solve(remaining) {
                if (remaining === 0) return [];

                const valid = shuffle(counts.filter(function (count) {
                    if (count > remaining) return false;
                    if (remaining - count === 1 && remaining !== 1) return false;
                    return canSolve(remaining - count);
                }));

                for (let i = 0; i < valid.length; i++) {
                    const count = valid[i];
                    const tail = solve(remaining - count);
                    if (tail) {
                        return [count].concat(tail);
                    }
                }

                return null;
            }

            return solve(total) || Array.from({ length: total }, function () { return 1; });
        }

        function sortByRatio(items, direction) {
            const factor = direction === 'asc' ? 1 : -1;
            return items.slice().sort(function (a, b) {
                return factor * (a.ratio - b.ratio);
            });
        }

        function chooseRow(chunk) {
            const count = chunk.length;
            const portraits = sortByRatio(chunk.filter(function (item) { return item.ratio < 0.92; }), 'asc');
            const landscapes = sortByRatio(chunk.filter(function (item) { return item.ratio > 1.28; }), 'desc');
            const balanced = chunk.slice().sort(function (a, b) {
                return Math.abs(a.ratio - 1) - Math.abs(b.ratio - 1);
            });

            if (count === 5) {
                const hero = balanced[0] || chunk[0];
                const others = chunk.filter(function (item) { return item !== hero; });
                return {
                    cls: 'row-center-hero',
                    items: [others[0], others[1], hero, others[2], others[3]]
                };
            }

            if (count === 4) {
                return {
                    cls: 'row-four-grid',
                    items: chunk.slice()
                };
            }

            if (count === 3) {
                if (portraits.length && Math.random() < 0.5) {
                    const hero = portraits[0];
                    const others = chunk.filter(function (item) { return item !== hero; });
                    if (Math.random() < 0.5) {
                        return { cls: 'row-portrait-left', items: [hero, others[0], others[1]] };
                    }
                    return { cls: 'row-portrait-right', items: [others[0], others[1], hero] };
                }

                if (landscapes.length && Math.random() < 0.78) {
                    const hero = landscapes[0];
                    const others = chunk.filter(function (item) { return item !== hero; });
                    if (Math.random() < 0.5) {
                        return { cls: 'row-hero-left', items: [hero, others[0], others[1]] };
                    }
                    return { cls: 'row-hero-right', items: [others[0], others[1], hero] };
                }

                return {
                    cls: 'row-three',
                    items: chunk.slice()
                };
            }

            if (count === 2) {
                return {
                    cls: 'row-two',
                    items: chunk.slice()
                };
            }

            const single = chunk[0];
            if (single.ratio < 0.9) {
                return { cls: 'row-single-portrait', items: [single] };
            }
            if (single.ratio > 1.25) {
                return { cls: 'row-single-wide', items: [single] };
            }
            return { cls: 'row-single-square', items: [single] };
        }

        function buildGalleryPlan(cards) {
            const items = cards.map(function (card) {
                const img = card.querySelector('img');
                return {
                    card: card,
                    img: img,
                    ratio: img ? getImageRatio(img) : 1.15
                };
            });

            const counts = chooseChunkCounts(items.length);
            const rows = [];
            let cursor = 0;

            counts.forEach(function (count) {
                const chunk = items.slice(cursor, cursor + count);
                if (chunk.length) {
                    rows.push(chooseRow(chunk));
                }
                cursor += count;
            });

            return rows;
        }

        function buildGalleryMosaic(galleryEl, forceRandom) {
            const state = ensureGalleryState(galleryEl);
            if (!state.cards.length) return;

            const component = galleryEl.classList.contains('tpl-public-component')
                ? galleryEl
                : galleryEl.closest('.tpl-public-component');
            const componentWidth = getNumericStyle(component, 'width', galleryEl.clientWidth || 1200);

            const gap = Math.max(10, Math.min(16, Math.round(componentWidth / 95)));
            const threeColWidth = Math.max(220, Math.round((componentWidth - (gap * 2)) / 3));
            const twoColWidth = Math.max(280, Math.round((componentWidth - gap) / 2));
            const rowMd = Math.max(240, Math.min(330, threeColWidth));
            const rowSm = Math.max(170, Math.min(220, Math.round((rowMd - gap) / 2)));
            const rowLg = Math.max(280, Math.min(380, Math.round(twoColWidth * 0.82)));
            const rowXl = Math.max(300, Math.min(420, Math.round(componentWidth * 0.34)));

            galleryEl.style.setProperty('--gallery-gap', gap + 'px');
            galleryEl.style.setProperty('--gallery-row-sm', rowSm + 'px');
            galleryEl.style.setProperty('--gallery-row-md', rowMd + 'px');
            galleryEl.style.setProperty('--gallery-row-lg', rowLg + 'px');
            galleryEl.style.setProperty('--gallery-row-xl', rowXl + 'px');

            if (forceRandom || !state.plan) {
                state.plan = buildGalleryPlan(state.cards);
            }

            state.grid.innerHTML = '';
            state.grid.className = 'tpl-gallery-grid tpl-gallery-mosaic';

            state.plan.forEach(function (rowPlan) {
                const row = document.createElement('div');
                row.className = 'tpl-gallery-row ' + rowPlan.cls;

                rowPlan.items.forEach(function (item) {
                    row.appendChild(item.card);
                });

                state.grid.appendChild(row);
            });
        }

        function bindGalleryLightboxes() {
            const galleries = Array.from(canvas.querySelectorAll('.tpl-public-gallery'));

            galleries.forEach(function (galleryEl) {
                if (galleryEl.dataset.lightboxBound === '1') return;
                galleryEl.dataset.lightboxBound = '1';

                galleryEl.addEventListener('click', function (event) {
                    const trigger = event.target.closest('.tpl-lightbox-trigger');
                    if (!trigger || !galleryEl.contains(trigger)) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();

                    const triggers = Array.from(galleryEl.querySelectorAll('.tpl-lightbox-trigger'));
                    const indexes = triggers
                        .map(function (img) { return getGalleryTriggerIndex(img); })
                        .filter(function (index) { return index >= 0 && lightboxItems[index]; });
                    const clickedIndex = getGalleryTriggerIndex(trigger);

                    if (!indexes.length || clickedIndex < 0) {
                        return;
                    }

                    openLightbox(indexes, clickedIndex);
                });
            });
        }

        function prepareLayout(forceRandomGalleryLayout) {
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
                if (!item.gallery) return;

                buildGalleryMosaic(item.gallery, forceRandomGalleryLayout);
                item.gallery.style.height = 'auto';
                item.el.style.height = 'auto';

                const measuredHeight = Math.ceil(item.gallery.getBoundingClientRect().height);
                item.newHeight = measuredHeight;
                item.gallery.style.height = measuredHeight + 'px';
                item.el.style.height = measuredHeight + 'px';
            });

            const shifts = components
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
                shifts.forEach(function (galleryShift) {
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
                if (!viewport || !track || carousel.dataset.carouselReady === '1') return;

                const originalSlides = Array.from(track.querySelectorAll('.tpl-carousel-slide'));
                if (!originalSlides.length) return;

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
                        if (!child) continue;
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
                    if (!document.body.contains(carousel)) return;

                    if (lastTimestamp === null) {
                        lastTimestamp = timestamp;
                    }

                    let delta = (timestamp - lastTimestamp) / 1000;
                    if (delta > 0.05) delta = 0.05;
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
            const galleryImages = Array.from(canvas.querySelectorAll('.tpl-public-gallery img'));
            if (!galleryImages.length) {
                callback();
                return;
            }

            let pending = 0;
            let done = false;

            function finishOne() {
                pending -= 1;
                if (pending <= 0 && !done) {
                    done = true;
                    callback();
                }
            }

            galleryImages.forEach(function (img) {
                if (img.complete && img.naturalWidth > 0) {
                    return;
                }

                pending += 1;
                img.addEventListener('load', finishOne, { once: true });
                img.addEventListener('error', finishOne, { once: true });
            });

            if (pending === 0) {
                callback();
                return;
            }

            window.setTimeout(function () {
                if (!done) {
                    done = true;
                    callback();
                }
            }, 1800);
        }

        function boot(forceRandom) {
            bindGalleryLightboxes();
            waitForGalleryImages(function () {
                prepareLayout(forceRandom);
                initCarousels();
            });
        }

        closeBtn.addEventListener('click', closeLightbox);
        prevBtn.addEventListener('click', prevLightbox);
        nextBtn.addEventListener('click', nextLightbox);

        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (!lightbox.classList.contains('is-open')) return;

            if (event.key === 'Escape') closeLightbox();
            if (event.key === 'ArrowLeft') prevLightbox();
            if (event.key === 'ArrowRight') nextLightbox();
        });

        const debouncedResize = (function () {
            let timer = null;
            return function () {
                window.clearTimeout(timer);
                timer = window.setTimeout(function () {
                    prepareLayout(false);
                }, 120);
            };
        })();

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                boot(true);
            });
        } else {
            boot(true);
        }

        window.addEventListener('load', function () {
            prepareLayout(false);
        });
        window.addEventListener('resize', debouncedResize);
    })();
</script>
</body>
</html>