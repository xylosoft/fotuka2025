<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* =========================
   CONFIG (DB-friendly)
   ========================= */
$brandName         = 'Fotuka';
$photographerName  = 'Romero Weddings';
$photographerTag   = 'Wedding Photography';
$logoText          = 'RW'; // replace with image later if you want

$coupleNames       = 'Paty & ROM';
$weddingDate       = 'November 1, 2026';
$weddingLocation   = 'Dallas, Texas';
$venueName         = 'The Olive Grove Estate';

$galleryHeadline   = 'A Celebration of Love';
$gallerySubhead    = 'A curated collection to relive the day—share with friends & family, and download anytime.';

$downloadAllUrl    = Url::to(['/share/download-all', 'token' => 'REPLACE_ME']); // wire later

// Visual theme parameters (store in DB later)
$theme = [
    'bg'            => '#FFFFFF',
    'ink'           => '#0F172A',
    'muted'         => '#5B6475',
    'border'        => 'rgba(15, 23, 42, 0.12)',

    // Wedding accent (rose/dusty)
    'accent'        => '#B76E79',
    'accentSoft'    => 'rgba(183, 110, 121, 0.14)',

    // Button style (thin grey like your multi-select buttons)
    'btnBg'         => '#E5E7EB',
    'btnBorder'     => '#6B7280',
    'btnText'       => '#374151',
    'btnHoverBg'    => '#D1D5DB',

    // Soft top+bottom glow (no repeating)
    'topGlow'       => 'rgba(183, 110, 121, 0.22)',
    'bottomGlow'    => 'rgba(37, 99, 235, 0.10)',

    // Typography
    'fontPrimary'   => 'ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial',
    'fontSerif'     => 'ui-serif, Georgia, "Times New Roman", Times, serif',
];

// Placeholder wedding images (Unsplash)
$images = [
    ['src' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=2000&q=80', 'alt' => 'Ceremony'],
    ['src' => 'https://images.unsplash.com/photo-1523438097201-512ae7d59c18?auto=format&fit=crop&w=2000&q=80', 'alt' => 'Portraits'],
    ['src' => 'https://images.unsplash.com/photo-1520857014576-2c4f4c972b57?auto=format&fit=crop&w=2000&q=80', 'alt' => 'Bouquet'],
    ['src' => 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=2000&q=80', 'alt' => 'Reception'],
    ['src' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=2000&q=80', 'alt' => 'Kiss'],
    ['src' => 'https://images.unsplash.com/photo-1529634897861-1f1f9a2f1a9b?auto=format&fit=crop&w=2000&q=80', 'alt' => 'Rings'],
    ['src' => 'https://images.unsplash.com/photo-1526481280695-3c687fd5432c?auto=format&fit=crop&w=2000&q=80', 'alt' => 'Details'],
    ['src' => 'https://images.unsplash.com/photo-1523438097201-512ae7d59c18?auto=format&fit=crop&w=2000&q=80', 'alt' => 'Golden Hour'],
];
$images = [
    [
        'src' => 'https://images.unsplash.com/flagged/photo-1569325527432-2ebe2c958a4d?auto=format&fit=crop&fm=jpg&ixlib=rb-4.1.0&q=60&w=3000',
        'alt' => 'Wedding couple portrait'
    ],
    [
        'src' => 'https://images.unsplash.com/photo-1733782072957-1dbf4619b7c7?auto=format&fit=crop&fm=jpg&ixlib=rb-4.1.0&q=60&w=3000',
        'alt' => 'Wedding rings on book'
    ],
    [
        'src' => 'https://images.unsplash.com/photo-1704455308461-1e18a7e11d28?auto=format&fit=crop&fm=jpg&ixlib=rb-4.1.0&q=60&w=3000',
        'alt' => 'First dance moment'
    ],
    [
        'src' => 'https://images.unsplash.com/photo-1713211460724-7d7f608528a8?auto=format&fit=crop&fm=jpg&ixlib=rb-4.1.0&q=60&w=3000',
        'alt' => 'Closeup of wedding rings'
    ],
    [
        'src' => 'https://images.unsplash.com/photo-1772133924806-d4d7a4c20b41?auto=format&fit=crop&fm=jpg&ixlib=rb-4.1.0&q=60&w=3000',
        'alt' => 'Bridal party celebration'
    ],
    [
        'src' => 'https://images.unsplash.com/photo-1726068438246-ca37df89903d?auto=format&fit=crop&fm=jpg&ixlib=rb-4.1.0&q=60&w=3000',
        'alt' => 'Romantic wedding portrait'
    ],
    [
        'src' => 'https://images.unsplash.com/photo-1680695779444-24fc71296e66?auto=format&fit=crop&fm=jpg&ixlib=rb-4.1.0&q=60&w=3000',
        'alt' => 'Wedding reception table details'
    ],
    [
        'src' => 'https://images.unsplash.com/photo-1758810411905-04fb6f9396e1?auto=format&fit=crop&fm=jpg&ixlib=rb-4.1.0&q=60&w=3000',
        'alt' => 'Outdoor wedding ceremony'
    ],
    [
        'src' => 'https://images.unsplash.com/photo-1521543832500-49e69fb2bea2?auto=format&fit=crop&fm=jpg&ixlib=rb-4.1.0&q=60&w=3000',
        'alt' => 'Bridal bouquet closeup'
    ],
    [
        'src' => 'https://images.unsplash.com/photo-1744497786604-0ceaf47fbaa1?auto=format&fit=crop&fm=jpg&ixlib=rb-4.1.0&q=60&w=3000',
        'alt' => 'Soft bouquet detail'
    ],
    [
        'src' => 'https://images.unsplash.com/photo-1762319981432-609103ab4a75?auto=format&fit=crop&fm=jpg&ixlib=rb-4.1.0&q=60&w=3000',
        'alt' => 'Wedding celebration party'
    ],
    [
        'src' => 'https://images.unsplash.com/photo-1522143296900-b2c450f80fa7?auto=format&fit=crop&fm=jpg&ixlib=rb-4.1.0&q=60&w=3000',
        'alt' => 'Ring exchange moment'
    ],
];

$this->title = Html::encode($coupleNames . ' — Wedding Gallery');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?= Html::encode($this->title) ?></title>

    <style>
        :root{
            --bg: <?= Html::encode($theme['bg']) ?>;
            --ink: <?= Html::encode($theme['ink']) ?>;
            --muted: <?= Html::encode($theme['muted']) ?>;
            --border: <?= Html::encode($theme['border']) ?>;

            --accent: <?= Html::encode($theme['accent']) ?>;
            --accentSoft: <?= Html::encode($theme['accentSoft']) ?>;

            --btnBg: <?= Html::encode($theme['btnBg']) ?>;
            --btnBorder: <?= Html::encode($theme['btnBorder']) ?>;
            --btnText: <?= Html::encode($theme['btnText']) ?>;
            --btnHoverBg: <?= Html::encode($theme['btnHoverBg']) ?>;

            --topGlow: <?= Html::encode($theme['topGlow']) ?>;
            --bottomGlow: <?= Html::encode($theme['bottomGlow']) ?>;

            --fontPrimary: <?= Html::encode($theme['fontPrimary']) ?>;
            --fontSerif: <?= Html::encode($theme['fontSerif']) ?>;

            --max: 1200px;
            --radius: 18px;
            --shadow: 0 18px 60px rgba(2,6,23,0.10);
            --shadow2: 0 10px 28px rgba(2,6,23,0.08);
        }

        *{ box-sizing:border-box; }
        html, body { height: 100%; }
        body{
            margin:0;
            background: var(--bg);
            color: var(--ink);
            font-family: var(--fontPrimary);
            overflow-x:hidden;
        }

        /* Elegant top & bottom glow (no repeating) */
        .top-shade{
            background:
                    radial-gradient(1100px 620px at 20% -10%, var(--topGlow), transparent 60%),
                    radial-gradient(900px 540px at 85% 0%, rgba(15,23,42,0.06), transparent 55%),
                    var(--bg);
        }
        .bottom-shade{
            background:
                    radial-gradient(1100px 620px at 85% 120%, var(--topGlow), transparent 58%),
                    radial-gradient(900px 560px at 12% 115%, var(--bottomGlow), transparent 55%),
                    var(--bg);
        }

        .container{
            width:100%;
            max-width: var(--max);
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .topbar{
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.80);
            border-bottom: 1px solid rgba(15,23,42,0.08);
        }
        .topbar-inner{
            height: 72px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 14px;
        }
        .brand{
            display:flex;
            align-items:center;
            gap: 12px;
        }
        .mark{
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display:flex;
            align-items:center;
            justify-content:center;
            background: linear-gradient(135deg, var(--accent) 0%, rgba(183,110,121,0.55) 55%, rgba(255,255,255,0.55) 100%);
            border: 1px solid rgba(183,110,121,0.35);
            box-shadow: 0 10px 24px rgba(183,110,121,0.25);
            font-weight: 900;
            letter-spacing: -0.02em;
            color: #fff;
            flex: 0 0 auto;
        }
        .brandtext{
            display:flex;
            flex-direction:column;
            line-height:1.05;
        }
        .brandtext strong{
            font-weight: 1000;
            letter-spacing:-0.03em;
        }
        .brandtext span{
            font-size: 12px;
            color: var(--muted);
            font-weight: 700;
            margin-top: 3px;
        }

        /* Download button (thin, Fotuka-like) */
        .action-btn{
            width: 132px;
            height: 28px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 10px;
            border: 2px solid var(--btnBorder);
            background: var(--btnBg);
            color: var(--btnText);
            cursor: pointer;
            transition: all 0.15s ease;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap: 8px;
            white-space: nowrap;
            text-decoration:none;
        }
        .action-btn:hover{ background: var(--btnHoverBg); }

        /* Hero (gallery vibe) */
        header.hero{
            padding: 34px 0 18px;
        }
        .hero-grid{
            display:grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }
        .kicker{
            display:inline-flex;
            align-items:center;
            gap: 10px;
            width: fit-content;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.72);
            border: 1px solid rgba(15,23,42,0.10);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: #334155;
        }
        .dot{
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--accent);
            box-shadow: 0 8px 16px rgba(183,110,121,0.25);
        }
        .title{
            font-family: var(--fontSerif);
            font-size: clamp(38px, 4.4vw, 66px);
            letter-spacing:-0.03em;
            margin: 10px 0 8px;
            line-height: 1.02;
        }
        .sub{
            margin: 0;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.65;
            max-width: 78ch;
        }
        .meta{
            margin-top: 14px;
            display:flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .pill{
            display:inline-flex;
            align-items:center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.72);
            border: 1px solid rgba(15,23,42,0.10);
            font-size: 12px;
            font-weight: 800;
            color: #334155;
        }

        /* Featured image + grid */
        .featured{
            margin-top: 16px;
            border-radius: 22px;
            overflow:hidden;
            border: 1px solid rgba(15,23,42,0.10);
            box-shadow: var(--shadow);
            background: #E5E7EB;
            position:relative;
            min-height: 420px;
        }
        .featured img{
            width:100%;
            height:100%;
            object-fit: cover;
            display:block;
            transform: scale(1.02);
            filter: saturate(1.03) contrast(1.02);
        }
        .featured:after{
            content:"";
            position:absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(2,6,23,0.05) 0%, rgba(2,6,23,0.42) 100%);
            pointer-events:none;
        }
        .featured-caption{
            position:absolute;
            left: 18px;
            bottom: 16px;
            right: 18px;
            color: #fff;
            display:flex;
            align-items:flex-end;
            justify-content:space-between;
            gap: 12px;
            text-shadow: 0 12px 26px rgba(0,0,0,0.35);
            pointer-events:none;
        }
        .featured-caption strong{
            font-family: var(--fontSerif);
            font-size: 22px;
            letter-spacing:-0.02em;
        }
        .featured-caption span{
            font-weight: 800;
            opacity: 0.92;
            font-size: 13px;
            white-space: nowrap;
        }

        section.gallery{
            padding: 18px 0 40px;
        }

        .grid{
            display:grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 12px;
            margin-top: 12px;
        }
        .tile{
            position:relative;
            border-radius: 18px;
            overflow:hidden;
            border: 1px solid rgba(15,23,42,0.10);
            box-shadow: var(--shadow2);
            background: #E5E7EB;
            cursor:pointer;
            min-height: 220px;
        }
        .tile img{
            width:100%;
            height:100%;
            object-fit: cover;
            transform: scale(1.02);
            filter: saturate(1.03) contrast(1.02);
            transition: transform 0.25s ease;
            display:block;
        }
        .tile:hover img{ transform: scale(1.06); }

        .veil{
            position:absolute;
            inset:0;
            background: linear-gradient(180deg, transparent 55%, rgba(2,6,23,0.38) 100%);
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .tile:hover .veil{ opacity: 1; }

        .label{
            position:absolute;
            left: 14px;
            bottom: 12px;
            right: 14px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            color:#fff;
            font-weight: 900;
            letter-spacing:-0.01em;
            opacity: 0;
            transform: translateY(6px);
            transition: all 0.2s ease;
            text-shadow: 0 10px 22px rgba(0,0,0,0.30);
        }
        .tile:hover .label{
            opacity: 1;
            transform: translateY(0);
        }
        .label small{
            font-weight: 800;
            opacity: 0.92;
            font-size: 12px;
        }

        .col-4{ grid-column: span 4; }
        .col-6{ grid-column: span 6; }
        .col-8{ grid-column: span 8; }
        .col-12{ grid-column: span 12; }

        /* Lightbox */
        .lightbox{
            position: fixed;
            inset: 0;
            z-index: 100;
            display:none;
            align-items:center;
            justify-content:center;
            background: rgba(2,6,23,0.84);
            padding: 24px;
        }
        .lightbox.open{ display:flex; }
        .lb-panel{
            width: min(1120px, 96vw);
            max-height: 92vh;
            border-radius: 18px;
            overflow:hidden;
            background: rgba(255,255,255,0.92);
            border: 1px solid rgba(255,255,255,0.20);
            box-shadow: var(--shadow);
            display:grid;
            grid-template-rows: auto 1fr;
        }
        .lb-top{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 10px;
            padding: 12px 12px;
            border-bottom: 1px solid rgba(15,23,42,0.10);
            background: rgba(243,244,246,0.65);
        }
        .lb-title{
            font-weight: 1000;
            letter-spacing:-0.02em;
            font-size: 13px;
            color:#111827;
            display:flex;
            align-items:center;
            gap: 10px;
        }
        .lb-dot{
            width: 10px; height: 10px; border-radius: 999px;
            background: var(--accent);
            box-shadow: 0 8px 16px rgba(183,110,121,0.25);
        }
        .lb-actions{
            display:flex;
            gap: 10px;
            align-items:center;
        }
        .lb-btn{
            height: 28px;
            padding: 0 10px;
            border-radius: 10px;
            border: 1px solid rgba(15,23,42,0.18);
            background: rgba(255,255,255,0.90);
            cursor:pointer;
            font-weight: 900;
            font-size: 12px;
            color:#334155;
        }
        .lb-btn:hover{ background: rgba(255,255,255,1); }
        .lb-body{
            background: #0B1220;
            display:flex;
            align-items:center;
            justify-content:center;
            padding: 10px;
        }
        .lb-body img{
            width: 100%;
            height: 100%;
            max-height: 78vh;
            object-fit: contain;
            display:block;
        }

        /* Footer tiny powered-by */
        .powered{
            padding: 18px 0 28px;
            display:flex;
            justify-content:flex-end;
        }
        .powered span{
            font-size: 12px;
            color: rgba(91,100,117,0.90);
            font-weight: 800;
        }

        /* Responsive */
        @media (max-width: 980px){
            .featured{ min-height: 340px; }
        }
        @media (max-width: 720px){
            .col-4, .col-6, .col-8{ grid-column: span 12; }
            .tile{ min-height: 200px; }
            .topbar-inner{ height:auto; padding: 12px 0; align-items:flex-start; }
            .action-btn{ width: 100%; }
            .featured-caption{ flex-direction: column; align-items:flex-start; }
            .featured-caption span{ white-space: normal; }
        }
    </style>
</head>

<body>

<div class="top-shade">

    <!-- HEADER -->
    <div class="topbar">
        <div class="container">
            <div class="topbar-inner">
                <div class="brand">
                    <div class="mark"><?= Html::encode($logoText) ?></div>
                    <div class="brandtext">
                        <strong><?= Html::encode($photographerName) ?></strong>
                        <span><?= Html::encode($photographerTag) ?></span>
                    </div>
                </div>

                <a class="action-btn" href="<?= Html::encode($downloadAllUrl) ?>">⬇️ Download All</a>
            </div>
        </div>
    </div>

    <!-- HERO -->
    <header class="hero">
        <div class="container">
            <div class="hero-grid">

                <div class="title"><?= Html::encode($coupleNames) ?></div>
                <p class="sub"><?= Html::encode($gallerySubhead) ?></p>

                <div class="meta">
                    <div class="pill"><span class="dot"></span><?= Html::encode($weddingDate) ?></div>
                    <div class="pill"><span class="dot"></span><?= Html::encode($venueName) ?></div>
                    <div class="pill"><span class="dot"></span><?= Html::encode($weddingLocation) ?></div>
                </div>

                <!-- Featured hero photo -->
                <div class="featured" id="featured" data-index="0" data-src="<?= Html::encode($images[0]['src']) ?>" data-alt="<?= Html::encode($images[0]['alt']) ?>">
                    <img src="<?= Html::encode($images[0]['src']) ?>" alt="<?= Html::encode($images[0]['alt']) ?>">
                    <div class="featured-caption">
                        <div>
                            <strong><?= Html::encode($coupleNames) ?></strong>
                        </div>
                        <span>Click to view · Swipe/arrow keys supported</span>
                    </div>
                </div>

            </div>
        </div>
    </header>

</div><!-- /top-shade -->


<!-- GALLERY GRID -->
<section class="gallery">
    <div class="container">
        <div class="grid" id="galleryGrid">
            <?php foreach ($images as $i => $img): ?>
                <?php
                // Skip first image here since it is featured (still include in lightbox via dataset)
                if ($i === 0) continue;

                // Curated pattern for a premium “editorial” look
                $span = 'col-4';
                if ($i === 1) $span = 'col-8';
                if ($i === 2) $span = 'col-4';
                if ($i === 3) $span = 'col-6';
                if ($i === 4) $span = 'col-6';
                if ($i === count($images) - 1) $span = 'col-8';
                ?>
                <div class="tile <?= Html::encode($span) ?>"
                     data-index="<?= (int)$i ?>"
                     data-src="<?= Html::encode($img['src']) ?>"
                     data-alt="<?= Html::encode($img['alt']) ?>">
                    <img src="<?= Html::encode($img['src']) ?>" alt="<?= Html::encode($img['alt']) ?>" loading="lazy">
                    <div class="veil"></div>
                    <div class="label">
                        <div><?= Html::encode($img['alt']) ?></div>
                        <small><?= Html::encode($weddingDate) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- BOTTOM SHADE -->
<div class="bottom-shade">
    <div class="container">
        <div class="powered">
            <span>Powered by <?= Html::encode($brandName) ?></span>
        </div>
    </div>
</div>


<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" aria-hidden="true">
    <div class="lb-panel" role="dialog" aria-modal="true" aria-label="Image preview">
        <div class="lb-top">
            <div class="lb-title"><span class="lb-dot"></span><span id="lbTitle"><?= Html::encode($coupleNames) ?></span></div>
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
        // Build a consistent ordered list that includes the featured image at index 0.
        const all = <?= json_encode($images, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

        const tiles = Array.from(document.querySelectorAll('#galleryGrid .tile'));
        const featured = document.getElementById('featured');

        const lightbox = document.getElementById('lightbox');
        const lbImg = document.getElementById('lbImg');
        const lbTitle = document.getElementById('lbTitle');

        const btnPrev = document.getElementById('lbPrev');
        const btnNext = document.getElementById('lbNext');
        const btnClose = document.getElementById('lbClose');

        let currentIndex = -1;

        function openAt(index) {
            if (index < 0 || index >= all.length) return;
            currentIndex = index;

            const item = all[currentIndex];
            lbImg.src = item.src;
            lbImg.alt = item.alt || '';
            lbTitle.textContent = item.alt ? item.alt : 'Photo';

            lightbox.classList.add('open');
            lightbox.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function close() {
            lightbox.classList.remove('open');
            lightbox.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            lbImg.src = '';
            lbImg.alt = '';
            currentIndex = -1;
        }

        function next() {
            if (currentIndex === -1) return;
            openAt((currentIndex + 1) % all.length);
        }

        function prev() {
            if (currentIndex === -1) return;
            openAt((currentIndex - 1 + all.length) % all.length);
        }

        // Featured click opens index 0
        if (featured) {
            featured.addEventListener('click', () => openAt(0));
            featured.style.cursor = 'pointer';
        }

        // Tiles open by their data-index (which matches $images index)
        tiles.forEach((tile) => {
            tile.addEventListener('click', () => {
                const idx = parseInt(tile.getAttribute('data-index'), 10);
        if (!Number.isNaN(idx)) openAt(idx);
    });
    });

        btnNext.addEventListener('click', next);
        btnPrev.addEventListener('click', prev);
        btnClose.addEventListener('click', close);

        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) close();
    });

        document.addEventListener('keydown', (e) => {
            if (!lightbox.classList.contains('open')) return;

        if (e.key === 'Escape') close();
        if (e.key === 'ArrowRight') next();
        if (e.key === 'ArrowLeft') prev();
    });
    })();
</script>

</body>
</html>A Celebration of Love