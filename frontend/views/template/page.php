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

// Adjust this route if your app uses a different download-all endpoint.
$downloadAllUrl = Url::to(['/folder/download-all', 'id' => $publication->folder_id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        :root { --page-bg: <?= Html::encode($backgroundColor) ?>; --button-bg: <?= Html::encode($buttonColor) ?>; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; background:var(--page-bg); color:#10233f; }
        a { color:inherit; }
        .pub-shell { min-height:100vh; background:var(--page-bg); }
        .pub-topbar { position:sticky; top:0; z-index:40; backdrop-filter:blur(16px); background:rgba(255,255,255,.7); border-bottom:1px solid rgba(208,220,237,.95); }
        .pub-topbar-inner { max-width:1420px; margin:0 auto; padding:16px 24px; display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .pub-brand { display:flex; flex-direction:column; gap:4px; }
        .pub-brand strong { font-size:20px; font-weight:800; color:#13345b; }
        .pub-brand span { color:#617997; font-size:13px; }
        .pub-actions { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .pub-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:44px; padding:0 18px; border-radius:12px; font-weight:800; text-decoration:none; border:none; cursor:pointer; }
        .pub-btn-primary { background:var(--button-bg); color:#fff; box-shadow:0 18px 30px rgba(16,35,63,.15); }
        .pub-btn-secondary { background:#fff; color:#17375f; border:1px solid #dbe6f3; }
        .pub-stage { padding:36px 24px 60px; }
        .pub-canvas-wrap { max-width:calc(<?= (int) $canvasWidth ?>px + 120px); margin:0 auto; }
        .pub-canvas { position:relative; width:<?= (int) $canvasWidth ?>px; min-height:<?= (int) $canvasHeight ?>px; margin:0 auto; background:var(--page-bg); }
        .tpl-public-component { position:absolute; }
        .tpl-public-text { overflow:hidden; }
        .tpl-public-text p:first-child { margin-top:0; }
        .tpl-public-text p:last-child { margin-bottom:0; }
        .tpl-public-empty { border:2px dashed #bfd1e5; border-radius:16px; background:rgba(255,255,255,.35); display:flex; align-items:center; justify-content:center; text-align:center; padding:16px; }
        .tpl-empty-inner { color:#67809f; font-weight:700; line-height:1.5; }
        .tpl-public-image img,
        .tpl-gallery-card img,
        .tpl-carousel-slide img { width:100%; height:100%; display:block; object-fit:cover; border-radius:16px; cursor:pointer; }
        .tpl-public-image img { border-radius:18px; }
        .tpl-public-gallery { width:100%; height:100%; overflow:hidden; }
        .tpl-gallery-grid { width:100%; height:100%; display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
        .tpl-gallery-card { min-width:0; min-height:0; }
        .tpl-public-carousel { width:100%; height:100%; display:flex; align-items:center; gap:10px; }
        .tpl-carousel-arrow { width:40px; height:40px; border:none; border-radius:999px; background:rgba(15,28,49,.68); color:#fff; font-size:22px; font-weight:800; cursor:pointer; flex:0 0 auto; }
        .tpl-carousel-viewport { flex:1 1 auto; height:100%; overflow:hidden; }
        .tpl-carousel-track { height:100%; display:flex; transition:transform .24s ease; }
        .tpl-carousel-slide { flex:0 0 100%; height:100%; min-width:0; }
        .pub-lightbox { position:fixed; inset:0; z-index:9999; display:none; align-items:center; justify-content:center; background:rgba(6,16,29,.86); padding:40px; }
        .pub-lightbox.is-open { display:flex; }
        .pub-lightbox-dialog { position:relative; width:min(1120px,94vw); height:min(82vh,820px); border-radius:24px; background:#09121f; overflow:hidden; box-shadow:0 26px 70px rgba(0,0,0,.42); }
        .pub-lightbox-dialog img { width:100%; height:100%; object-fit:contain; display:block; background:#09121f; }
        .pub-lightbox-btn { position:absolute; top:18px; width:46px; height:46px; border:none; border-radius:999px; background:rgba(255,255,255,.14); color:#fff; font-size:22px; font-weight:800; cursor:pointer; }
        .pub-lightbox-btn.close { right:18px; }
        .pub-lightbox-btn.prev { top:50%; left:18px; transform:translateY(-50%); }
        .pub-lightbox-btn.next { top:50%; right:18px; transform:translateY(-50%); }
        .pub-lightbox-caption { position:absolute; left:20px; right:82px; bottom:18px; background:rgba(255,255,255,.12); color:#fff; border-radius:14px; padding:12px 14px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-weight:700; }
        @media (max-width:1320px) {
            .pub-stage { padding:18px 12px 36px; overflow:auto; }
            .pub-canvas-wrap { max-width:none; }
        }
    </style>
</head>
<body>
<div class="pub-shell">
    <div class="pub-topbar">
        <div class="pub-topbar-inner">
            <div class="pub-brand">
                <strong><?= Html::encode($publication->page_title ?: $folderName) ?></strong>
                <span>Published from <?= Html::encode($folderName) ?></span>
            </div>
            <div class="pub-actions">
                <?php if ((int) $publication->allow_download_all === 1): ?>
                    <a class="pub-btn pub-btn-primary" href="<?= Html::encode($downloadAllUrl) ?>">Download All</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="pub-stage">
        <div class="pub-canvas-wrap">
            <div class="pub-canvas">
                <?= implode("\n", $renderedComponents) ?>
            </div>
        </div>
    </div>
</div>

<div id="pubLightbox" class="pub-lightbox">
    <div class="pub-lightbox-dialog">
        <button type="button" class="pub-lightbox-btn close" id="pubLightboxClose">✕</button>
        <button type="button" class="pub-lightbox-btn prev" id="pubLightboxPrev">‹</button>
        <button type="button" class="pub-lightbox-btn next" id="pubLightboxNext">›</button>
        <img id="pubLightboxImage" src="" alt="">
        <div id="pubLightboxCaption" class="pub-lightbox-caption"></div>
    </div>
</div>

<script>
    (function(){
        const lightboxItems = <?= json_encode($lightboxImages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        const lightbox = document.getElementById('pubLightbox');
        const imageEl = document.getElementById('pubLightboxImage');
        const captionEl = document.getElementById('pubLightboxCaption');
        const closeBtn = document.getElementById('pubLightboxClose');
        const prevBtn = document.getElementById('pubLightboxPrev');
        const nextBtn = document.getElementById('pubLightboxNext');
        let lightboxIndex = 0;

        function renderLightbox(){
            const item = lightboxItems[lightboxIndex];
            if(!item) return;
            imageEl.src = item.url || '';
            captionEl.textContent = item.title || '';
        }
        function openLightbox(index){
            if(!lightboxItems.length) return;
            lightboxIndex = index;
            renderLightbox();
            lightbox.classList.add('is-open');
        }
        function closeLightbox(){ lightbox.classList.remove('is-open'); }
        function prevLightbox(){ if(!lightboxItems.length) return; lightboxIndex = (lightboxIndex - 1 + lightboxItems.length) % lightboxItems.length; renderLightbox(); }
        function nextLightbox(){ if(!lightboxItems.length) return; lightboxIndex = (lightboxIndex + 1) % lightboxItems.length; renderLightbox(); }

        document.querySelectorAll('.tpl-lightbox-trigger').forEach(trigger => {
            trigger.addEventListener('click', () => openLightbox(parseInt(trigger.getAttribute('data-lightbox-index') || '0', 10)));
    });
        closeBtn.addEventListener('click', closeLightbox);
        prevBtn.addEventListener('click', prevLightbox);
        nextBtn.addEventListener('click', nextLightbox);
        lightbox.addEventListener('click', e => { if(e.target === lightbox) closeLightbox(); });
        document.addEventListener('keydown', e => {
            if(!lightbox.classList.contains('is-open')) return;
        if(e.key === 'Escape') closeLightbox();
        if(e.key === 'ArrowLeft') prevLightbox();
        if(e.key === 'ArrowRight') nextLightbox();
    });

        document.querySelectorAll('.tpl-public-carousel').forEach(carousel => {
            const track = carousel.querySelector('.tpl-carousel-track');
        const slides = carousel.querySelectorAll('.tpl-carousel-slide');
        const prev = carousel.querySelector('.tpl-carousel-arrow.is-prev');
        const next = carousel.querySelector('.tpl-carousel-arrow.is-next');
        let index = 0;
        function render(){
            if(!track || !slides.length) return;
            track.style.transform = `translateX(-${index * 100}%)`;
        }
        if(prev) prev.addEventListener('click', () => { index = (index - 1 + slides.length) % slides.length; render(); });
        if(next) next.addEventListener('click', () => { index = (index + 1) % slides.length; render(); });
        render();
    });
    })();
</script>
</body>
</html>