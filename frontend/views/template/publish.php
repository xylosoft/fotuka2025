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

        html, body {
            min-height: 100%;
        }

        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--page-bg);
            color: var(--ink);
            overflow-x: hidden;
        }

        a {
            color: inherit;
        }

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

        .tpl-public-gallery {
            width: 100%;
            height: auto !important;
            overflow: visible !important;
            background: transparent !important;
        }

        .tpl-gallery-grid {
            width: 100%;
            height: auto !important;
            display: block;
            background: transparent !important;
        }

        .tpl-gallery-mosaic {
            display: flex;
            flex-direction: column;
            gap: var(--gallery-gap, 14px);
            width: 100%;
        }

        .tpl-gallery-block {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            grid-auto-rows: var(--gallery-unit, 84px);
            gap: var(--gallery-gap, 14px);
        }

        .tpl-gallery-card {
            position: relative;
            min-width: 0;
            min-height: 0;
            overflow: hidden;
            border-radius: 16px;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            cursor: pointer;
        }

        .tpl-gallery-card a {
            display: block;
            width: 100%;
            height: 100%;
        }

        .tpl-gallery-card img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            border-radius: 16px;
            background: transparent !important;
        }

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

        .pub-lightbox.is-open {
            display: flex;
        }

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

        .pub-lightbox-btn.close {
            right: 18px;
        }

        .pub-lightbox-btn.prev {
            top: 50%;
            left: 18px;
            transform: translateY(-50%);
        }

        .pub-lightbox-btn.next {
            top: 50%;
            right: 18px;
            transform: translateY(-50%);
        }

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

            .pub-canvas-wrap {
                max-width: none;
            }

            .pub-floating-actions {
                top: 14px;
                right: 12px;
            }
        }

        @media (max-width: 700px) {
            .pub-lightbox {
                padding: 16px;
            }

            .pub-lightbox-dialog {
                width: 100%;
                height: min(78vh, 680px);
                border-radius: 20px;
            }

            .pub-lightbox-btn {
                width: 42px;
                height: 42px;
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
        const carouselScrollSpeed = <?= json_encode((float) $carouselAutoScrollSpeed) ?>;
        const carouselGap = <?= json_encode((int) $carouselImageGap) ?>;
        const baseCanvasHeight = <?= json_encode((int) $canvasHeight) ?>;

        const lightbox = document.getElementById('pubLightbox');
        const imageEl = document.getElementById('pubLightboxImage');
        const captionEl = document.getElementById('pubLightboxCaption');
        const closeBtn = document.getElementById('pubLightboxClose');
        const prevBtn = document.getElementById('pubLightboxPrev');
        const nextBtn = document.getElementById('pubLightboxNext');

        let activeGalleryItems = [];
        let lightboxIndex = 0;

        function shuffle(array) {
            const cloned = array.slice();
            for (let i = cloned.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [cloned[i], cloned[j]] = [cloned[j], cloned[i]];
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
            const attr = el.getAttribute(key);
            if (attr !== null && attr !== '') {
                const parsed = parseFloat(attr);
                if (Number.isFinite(parsed)) return parsed;
            }

            const value = getNumericStyle(el, prop, fallback);
            el.setAttribute(key, String(value));
            return value;
        }

        function getCardImage(card) {
            return card.querySelector('img');
        }

        function getCardRatio(card) {
            const img = getCardImage(card);
            const cardWidth = parseFloat(card.getAttribute('data-width') || '0');
            const cardHeight = parseFloat(card.getAttribute('data-height') || '0');
            if (cardWidth > 0 && cardHeight > 0) {
                return cardWidth / cardHeight;
            }

            if (img) {
                const imgWidth = parseFloat(img.getAttribute('data-width') || '0');
                const imgHeight = parseFloat(img.getAttribute('data-height') || '0');
                if (imgWidth > 0 && imgHeight > 0) {
                    return imgWidth / imgHeight;
                }

                if (img.naturalWidth > 0 && img.naturalHeight > 0) {
                    return img.naturalWidth / img.naturalHeight;
                }
            }

            return 1.18;
        }

        function sortByRatio(items, direction) {
            return items.slice().sort(function (a, b) {
                return direction === 'asc' ? a.ratio - b.ratio : b.ratio - a.ratio;
            });
        }

        function createBlock(nodes, spans) {
            return {
                nodes: nodes,
                spans: spans
            };
        }

        function buildThreeBlock(chunk) {
            const portraits = sortByRatio(chunk.filter(function (item) { return item.ratio < 0.92; }), 'asc');
            const landscapes = sortByRatio(chunk.filter(function (item) { return item.ratio > 1.28; }), 'desc');
            const random = Math.random();

            if (portraits.length && random < 0.42) {
                const hero = portraits[0];
                const others = chunk.filter(function (item) { return item !== hero; });
                if (Math.random() < 0.5) {
                    return createBlock(
                        [hero.node, others[0].node, others[1].node],
                        [{ c: 4, r: 6 }, { c: 8, r: 3 }, { c: 8, r: 3 }]
                    );
                }
                return createBlock(
                    [others[0].node, others[1].node, hero.node],
                    [{ c: 8, r: 3 }, { c: 8, r: 3 }, { c: 4, r: 6 }]
                );
            }

            if (landscapes.length && random < 0.84) {
                const hero = landscapes[0];
                const others = chunk.filter(function (item) { return item !== hero; });
                if (Math.random() < 0.5) {
                    return createBlock(
                        [hero.node, others[0].node, others[1].node],
                        [{ c: 8, r: 6 }, { c: 4, r: 3 }, { c: 4, r: 3 }]
                    );
                }
                return createBlock(
                    [others[0].node, others[1].node, hero.node],
                    [{ c: 4, r: 3 }, { c: 4, r: 3 }, { c: 8, r: 6 }]
                );
            }

            return createBlock(
                chunk.map(function (item) { return item.node; }),
                [{ c: 4, r: 3 }, { c: 4, r: 3 }, { c: 4, r: 3 }]
            );
        }

        function buildFiveBlock(chunk) {
            const sortedForHero = sortByRatio(chunk, 'asc');
            const hero = sortedForHero[Math.min(1, sortedForHero.length - 1)] || chunk[0];
            const others = chunk.filter(function (item) { return item !== hero; });

            if (Math.random() < 0.5) {
                return createBlock(
                    [others[0].node, hero.node, others[1].node, others[2].node, others[3].node],
                    [{ c: 3, r: 3 }, { c: 6, r: 6 }, { c: 3, r: 3 }, { c: 3, r: 3 }, { c: 3, r: 3 }]
                );
            }

            return createBlock(
                [others[0].node, others[1].node, hero.node, others[2].node, others[3].node],
                [{ c: 4, r: 3 }, { c: 4, r: 3 }, { c: 4, r: 6 }, { c: 4, r: 3 }, { c: 4, r: 3 }]
            );
        }

        function buildTwoBlock(chunk) {
            if (chunk[0].ratio < 0.9 || chunk[1].ratio < 0.9) {
                return createBlock(
                    [chunk[0].node, chunk[1].node],
                    [{ c: 6, r: 5 }, { c: 6, r: 5 }]
                );
            }

            return createBlock(
                [chunk[0].node, chunk[1].node],
                [{ c: 6, r: 4 }, { c: 6, r: 4 }]
            );
        }

        function buildOneBlock(chunk) {
            const ratio = chunk[0].ratio;
            if (ratio < 0.92) {
                return createBlock([chunk[0].node], [{ c: 4, r: 6 }]);
            }
            if (ratio > 1.3) {
                return createBlock([chunk[0].node], [{ c: 8, r: 4 }]);
            }
            return createBlock([chunk[0].node], [{ c: 5, r: 4 }]);
        }

        function chooseChunkSize(remaining) {
            if (remaining >= 10 && Math.random() < 0.28) return 5;
            if (remaining === 9 && Math.random() < 0.25) return 5;
            if (remaining === 8 && Math.random() < 0.5) return 5;
            if (remaining === 7) return 5;
            if (remaining === 6) return 3;
            if (remaining === 5) return 5;
            if (remaining === 4) return 2;
            if (remaining === 3) return 3;
            if (remaining === 2) return 2;
            return 1;
        }

        function buildGalleryPlan(cards) {
            const items = cards.map(function (card) {
                return {
                    node: card,
                    ratio: getCardRatio(card)
                };
            });

            const blocks = [];
            let cursor = 0;

            while (cursor < items.length) {
                const remaining = items.length - cursor;
                const chunkSize = Math.min(chooseChunkSize(remaining), remaining);
                const chunk = items.slice(cursor, cursor + chunkSize);

                if (chunk.length === 5) {
                    blocks.push(buildFiveBlock(chunk));
                } else if (chunk.length === 3) {
                    blocks.push(buildThreeBlock(chunk));
                } else if (chunk.length === 2) {
                    blocks.push(buildTwoBlock(chunk));
                } else {
                    blocks.push(buildOneBlock(chunk));
                }

                cursor += chunk.length;
            }

            return blocks;
        }

        function ensureGalleryCards(galleryRoot) {
            if (galleryRoot._galleryCards) {
                return galleryRoot._galleryCards;
            }

            const sourceRoot = galleryRoot.querySelector('.tpl-gallery-grid') || galleryRoot;
            galleryRoot._layoutRoot = sourceRoot;

            let cards = Array.from(sourceRoot.querySelectorAll('.tpl-gallery-card'));

            if (!cards.length) {
                const images = Array.from(sourceRoot.querySelectorAll('img'));
                cards = images.map(function (img) {
                    const card = document.createElement('div');
                    card.className = 'tpl-gallery-card';
                    const parent = img.parentElement;
                    if (parent && parent.tagName && parent.tagName.toLowerCase() === 'a') {
                        parent.parentElement.insertBefore(card, parent);
                        card.appendChild(parent);
                    } else if (parent) {
                        parent.insertBefore(card, img);
                        card.appendChild(img);
                    }
                    return card;
                });
            }

            galleryRoot._galleryCards = cards;
            return cards;
        }

        function bindGalleryLightbox(galleryRoot) {
            if (galleryRoot.dataset.galleryLightboxBound === '1') {
                return;
            }

            galleryRoot.dataset.galleryLightboxBound = '1';

            galleryRoot.addEventListener('click', function (event) {
                const card = event.target.closest('.tpl-gallery-card');
                if (!card || !galleryRoot.contains(card)) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                const displayCards = galleryRoot._displayCards || [];
                const index = displayCards.indexOf(card);
                if (index < 0) {
                    return;
                }

                const items = displayCards.map(function (displayCard, idx) {
                    const img = getCardImage(displayCard);
                    const anchor = displayCard.querySelector('a[href]');
                    const url =
                        displayCard.getAttribute('data-fullsrc') ||
                        (img ? (img.getAttribute('data-fullsrc') || img.getAttribute('data-lightbox-url') || '') : '') ||
                        (anchor ? anchor.getAttribute('href') : '') ||
                        (img ? (img.currentSrc || img.getAttribute('src') || '') : '');

                    const title =
                        displayCard.getAttribute('data-title') ||
                        (img ? (img.getAttribute('alt') || img.getAttribute('title') || '') : '') ||
                        ('Image ' + (idx + 1));

                    return {
                        url: url,
                        title: title
                    };
                }).filter(function (item) {
                    return !!item.url;
                });

                if (!items.length) {
                    return;
                }

                activeGalleryItems = items;
                lightboxIndex = Math.min(index, items.length - 1);
                renderLightbox();
                lightbox.classList.add('is-open');
                lightbox.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            });
        }

        function buildGalleryMosaic(galleryRoot, forceRandom) {
            const cards = ensureGalleryCards(galleryRoot);
            if (!cards.length) {
                return;
            }

            const layoutRoot = galleryRoot._layoutRoot || galleryRoot;
            const component = galleryRoot.closest('.tpl-public-component');
            const componentWidth = getNumericStyle(component, 'width', galleryRoot.clientWidth || 1200);
            const gap = Math.max(10, Math.min(18, Math.round(componentWidth / 85)));
            const unit = Math.max(58, Math.min(88, Math.round((componentWidth - (11 * gap)) / 12)));

            galleryRoot.style.setProperty('--gallery-gap', gap + 'px');
            galleryRoot.style.setProperty('--gallery-unit', unit + 'px');

            if (forceRandom || !galleryRoot._galleryPlan) {
                galleryRoot._galleryPlan = buildGalleryPlan(cards);
            }

            layoutRoot.innerHTML = '';
            layoutRoot.className = 'tpl-gallery-mosaic';

            const displayCards = [];

            galleryRoot._galleryPlan.forEach(function (blockPlan) {
                const block = document.createElement('div');
                block.className = 'tpl-gallery-block';

                blockPlan.nodes.forEach(function (node, index) {
                    const span = blockPlan.spans[index] || { c: 4, r: 3 };
                    node.style.gridColumn = 'span ' + span.c;
                    node.style.gridRow = 'span ' + span.r;
                    block.appendChild(node);
                    displayCards.push(node);
                });

                layoutRoot.appendChild(block);
            });

            galleryRoot._displayCards = displayCards;
            bindGalleryLightbox(galleryRoot);
        }

        function renderLightbox() {
            const item = activeGalleryItems[lightboxIndex];
            if (!item) return;

            imageEl.src = item.url || '';
            imageEl.alt = item.title || '';
            captionEl.textContent = item.title || '';
        }

        function closeLightbox() {
            lightbox.classList.remove('is-open');
            lightbox.setAttribute('aria-hidden', 'true');
            imageEl.src = '';
            imageEl.alt = '';
            captionEl.textContent = '';
            document.body.style.overflow = '';
        }

        function prevLightbox() {
            if (!activeGalleryItems.length) return;
            lightboxIndex = (lightboxIndex - 1 + activeGalleryItems.length) % activeGalleryItems.length;
            renderLightbox();
        }

        function nextLightbox() {
            if (!activeGalleryItems.length) return;
            lightboxIndex = (lightboxIndex + 1) % activeGalleryItems.length;
            renderLightbox();
        }

        function prepareGalleries(forceRandom) {
            const components = Array.from(canvas.querySelectorAll('.tpl-public-component')).map(function (el) {
                const originalTop = getOriginalMetric(el, 'data-original-top', 'top', el.offsetTop || 0);
                const originalHeight = getOriginalMetric(el, 'data-original-height', 'height', el.offsetHeight || 0);
                return {
                    el: el,
                    originalTop: originalTop,
                    originalHeight: originalHeight,
                    originalBottom: originalTop + originalHeight,
                    newHeight: originalHeight,
                    gallery: el.classList.contains('tpl-public-gallery') ? el : el.querySelector('.tpl-public-gallery')
                };
            });

            components.forEach(function (item) {
                if (!item.gallery) return;

                buildGalleryMosaic(item.gallery, forceRandom);

                item.gallery.style.height = 'auto';
                item.el.style.height = 'auto';

                const measuredHeight = Math.ceil(item.gallery.getBoundingClientRect().height);
                item.newHeight = measuredHeight;
                item.gallery.style.height = measuredHeight + 'px';
                item.el.style.height = measuredHeight + 'px';
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
            const images = Array.from(canvas.querySelectorAll('.tpl-public-gallery img'));
            if (!images.length) {
                callback();
                return;
            }

            let pending = 0;
            let resolved = false;

            function done() {
                pending -= 1;
                if (pending <= 0 && !resolved) {
                    resolved = true;
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
                if (!resolved) {
                    resolved = true;
                    callback();
                }
            }, 1800);
        }

        function boot(forceRandomGalleryLayout) {
            waitForGalleryImages(function () {
                prepareGalleries(forceRandomGalleryLayout);
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
                    prepareGalleries(false);
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
            prepareGalleries(false);
        });
        window.addEventListener('resize', debouncedResize);
    })();
</script>
</body>
</html>