<?php
use yii\helpers\Url;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Fotuka — Simple Digital Asset Management</title>
    <meta name="description" content="Fotuka is the easiest way to organize, preview, and share your digital assets — with secure links and fast delivery." />

    <link rel="preconnect" href="https://images.unsplash.com" />

    <style>
        :root{
            --bg: #ffffff;
            --page: #ffffff;

            /* Your app-ish colors */
            --panel: #F3F4F6;
            --brand: #2563EB;
            --brand2:#DBEAFE;

            --text: #0F172A;
            --muted:#475569;
            --border:#CBD5E1;

            --shadow: 0 16px 50px rgba(2, 6, 23, 0.10);
            --shadow2: 0 10px 30px rgba(2, 6, 23, 0.08);

            --radius: 18px;
            --max: 1180px;
        }

        *{ box-sizing:border-box; }
        html, body { height:100%; }
        body{
            margin:0;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji";
            color: var(--text);
            background: var(--page);
            overflow-x:hidden;
        }

        /* Top-only + bottom-only shading (no repeating background) */
        .top-shade{
            background: radial-gradient(1200px 700px at 20% -10%, var(--brand2), transparent 60%),
            radial-gradient(900px 600px at 90% 10%, rgba(37,99,235,0.16), transparent 55%),
            var(--page);
        }
        .bottom-shade{
            background: radial-gradient(1200px 700px at 80% 120%, var(--brand2), transparent 58%),
            radial-gradient(900px 600px at 10% 110%, rgba(37,99,235,0.14), transparent 55%),
            var(--page);
        }

        a{ color:inherit; text-decoration:none; }
        .container{ width:100%; max-width: var(--max); margin:0 auto; padding: 0 20px; }

        /* Top Nav */
        .nav{
            position: sticky;
            top:0;
            z-index:50;
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.78);
            border-bottom: 1px solid rgba(203,213,225,0.7);
        }
        .nav-inner{
            height:70px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
        }
        .brand{
            display:flex;
            align-items:center;
            gap:10px;
            font-weight:800;
            letter-spacing:-0.02em;
        }
        .logo{
            width:36px; height:36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--brand) 0%, #60A5FA 50%, #93C5FD 100%);
            box-shadow: 0 10px 22px rgba(37,99,235,0.25);
            position:relative;
            overflow:hidden;
        }
        .logo:before{
            content:"";
            position:absolute; inset:-30%;
            background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.85), transparent 45%);
            transform: rotate(20deg);
        }

        .nav-links{
            display:flex;
            align-items:center;
            gap:18px;
            color: var(--muted);
            font-weight:600;
            font-size:14px;
        }
        .nav-links a{
            padding:8px 10px;
            border-radius: 10px;
        }
        .nav-links a:hover{
            background: rgba(219,234,254,0.65);
            color: #1E3A8A;
        }

        .nav-cta{ display:flex; align-items:center; gap:10px; }
        .btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            border-radius: 12px;
            border: 1px solid rgba(203,213,225,0.9);
            background: rgba(255,255,255,0.92);
            padding: 10px 14px;
            font-weight:700;
            font-size:14px;
            cursor:pointer;
            transition: transform 0.12s ease, box-shadow 0.12s ease, background 0.12s ease, border-color 0.12s ease;
            white-space:nowrap;
        }
        .btn:hover{ transform: translateY(-1px); box-shadow: var(--shadow2); border-color: rgba(37,99,235,0.35); }
        .btn-primary{
            border-color: rgba(37,99,235,0.25);
            background: linear-gradient(180deg, #3B82F6, #2563EB);
            color:#fff;
            box-shadow: 0 12px 24px rgba(37,99,235,0.25);
        }
        .btn-primary:hover{ border-color: rgba(37,99,235,0.35); }

        .hamburger{
            display:none;
            border:1px solid rgba(203,213,225,0.9);
            background: rgba(255,255,255,0.9);
            border-radius: 12px;
            height:42px;
            width:44px;
            cursor:pointer;
        }
        .hamburger span{
            display:block;
            width:18px; height:2px;
            background:#0F172A;
            margin:4px auto;
            border-radius:2px;
            opacity:0.85;
        }

        /* Hero */
        .hero{ padding: 54px 0 26px; }
        .hero-grid{
            display:grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 28px;
            align-items: stretch;
        }
        .pill{
            display:inline-flex;
            align-items:center;
            gap:10px;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid rgba(203,213,225,0.9);
            background: rgba(255,255,255,0.80);
            color: #1E3A8A;
            font-weight:800;
            font-size:12px;
            letter-spacing:0.03em;
            text-transform: uppercase;
            width: fit-content;
        }
        .pill .dot{
            width:8px;height:8px;border-radius:99px;
            background: linear-gradient(180deg, #22C55E, #16A34A);
            box-shadow: 0 6px 12px rgba(34,197,94,0.2);
        }
        h1{
            margin: 16px 0 10px;
            font-size: clamp(36px, 4vw, 56px);
            line-height: 1.02;
            letter-spacing:-0.04em;
        }
        .sub{
            color: var(--muted);
            font-size: 18px;
            line-height: 1.5;
            max-width: 60ch;
        }
        .hero-actions{
            margin-top: 18px;
            display:flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items:center;
        }
        .trust{
            margin-top: 18px;
            display:flex;
            gap: 18px;
            flex-wrap: wrap;
            color: #334155;
            font-weight:700;
            font-size: 13px;
            opacity: 0.95;
        }
        .trust span{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding: 8px 10px;
            border-radius: 12px;
            background: rgba(243,244,246,0.65);
            border: 1px solid rgba(203,213,225,0.7);
        }
        .check{
            width:18px;height:18px;border-radius:6px;
            background: rgba(37,99,235,0.12);
            border: 1px solid rgba(37,99,235,0.22);
            display:inline-flex;align-items:center;justify-content:center;
            font-size:12px;
            color:#1D4ED8;
            font-weight:900;
        }

        /* Hero right “value card” (no workspace mock) */
        .value-card{
            border-radius: var(--radius);
            background: linear-gradient(180deg, rgba(255,255,255,0.86), rgba(249,250,251,0.86));
            border: 1px solid rgba(203,213,225,0.75);
            box-shadow: var(--shadow);
            overflow:hidden;
            position:relative;
            padding: 16px;
            min-height: 340px;
        }
        .value-title{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 10px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(203,213,225,0.7);
        }
        .value-title strong{
            font-weight:1000;
            letter-spacing:-0.02em;
            font-size: 14px;
            color:#0F172A;
            opacity:0.9;
        }
        .mini-badge{
            font-size: 11px;
            font-weight:900;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid rgba(37,99,235,0.25);
            background: rgba(219,234,254,0.6);
            color: #1E3A8A;
            white-space:nowrap;
        }
        .value-list{
            margin-top: 14px;
            display:grid;
            gap: 10px;
        }
        .value-row{
            display:flex;
            gap: 12px;
            align-items:flex-start;
            padding: 12px;
            border-radius: 14px;
            background: rgba(243,244,246,0.65);
            border: 1px solid rgba(203,213,225,0.6);
        }
        .vicon{
            width: 28px; height:28px;
            border-radius: 10px;
            background: rgba(37,99,235,0.12);
            border: 1px solid rgba(37,99,235,0.20);
            display:flex; align-items:center; justify-content:center;
            font-weight:900;
            color:#1D4ED8;
            flex: 0 0 auto;
            margin-top: 1px;
        }
        .value-row strong{
            display:block;
            font-weight:1000;
            margin-bottom: 3px;
            letter-spacing:-0.01em;
            font-size: 14px;
        }
        .value-row span{
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }

        /* Logos row */
        .logos{
            padding: 12px 0 28px;
            opacity:0.9;
        }
        .logos-row{
            display:flex;
            gap: 14px;
            flex-wrap: wrap;
            align-items:center;
            justify-content:space-between;
            border-radius: var(--radius);
            border: 1px solid rgba(203,213,225,0.7);
            background: rgba(255,255,255,0.55);
            padding: 14px 16px;
        }
        .logo-chip{
            display:flex;
            align-items:center;
            gap:10px;
            padding: 10px 12px;
            border-radius: 14px;
            background: rgba(243,244,246,0.65);
            border: 1px solid rgba(203,213,225,0.6);
            font-weight:900;
            font-size: 13px;
            color:#334155;
            min-width: 160px;
            justify-content:center;
        }
        .spark{
            width:18px; height:18px; border-radius:6px;
            background: rgba(37,99,235,0.13);
            border: 1px solid rgba(37,99,235,0.22);
            display:inline-flex; align-items:center; justify-content:center;
            font-weight:900;
            color:#1D4ED8;
            font-size: 12px;
        }

        /* Sections */
        section{ padding: 46px 0; }
        .section-head{
            display:flex;
            align-items:flex-end;
            justify-content:space-between;
            gap: 16px;
            margin-bottom: 18px;
        }
        .section-head h2{
            margin:0;
            font-size: clamp(22px, 2.3vw, 32px);
            letter-spacing:-0.03em;
        }
        .section-head p{
            margin:0;
            color: var(--muted);
            max-width: 62ch;
            line-height: 1.5;
        }

        .cards{
            display:grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }
        .card{
            border-radius: var(--radius);
            border: 1px solid rgba(203,213,225,0.75);
            background: rgba(255,255,255,0.70);
            box-shadow: var(--shadow2);
            padding: 18px;
            min-height: 168px;
            position:relative;
            overflow:hidden;
        }
        .card:before{
            content:"";
            position:absolute;
            inset:-50% -50% auto auto;
            width: 240px;
            height: 240px;
            background: radial-gradient(circle at 30% 30%, rgba(37,99,235,0.18), transparent 55%);
            transform: rotate(15deg);
        }
        .card .icon{
            width: 42px; height:42px;
            border-radius: 14px;
            display:flex; align-items:center; justify-content:center;
            background: rgba(219,234,254,0.75);
            border: 1px solid rgba(37,99,235,0.22);
            font-weight: 1000;
            color: #1E3A8A;
            margin-bottom: 10px;
            position:relative;
        }
        .card h3{
            margin: 0 0 8px;
            font-size: 16px;
            letter-spacing:-0.02em;
            position:relative;
        }
        .card p{
            margin:0;
            color: var(--muted);
            line-height: 1.5;
            font-size: 14px;
            position:relative;
        }

        /* Two-column feature block */
        .split{
            display:grid;
            grid-template-columns: 0.95fr 1.05fr;
            gap: 18px;
            align-items: stretch;
        }
        .panel{
            border-radius: var(--radius);
            border: 1px solid rgba(203,213,225,0.75);
            background: rgba(255,255,255,0.72);
            box-shadow: var(--shadow2);
            padding: 18px;
        }
        .bullets{
            display:grid;
            gap: 10px;
            margin-top: 14px;
        }
        .bullet{
            display:flex;
            gap: 12px;
            align-items:flex-start;
            padding: 12px;
            border-radius: 14px;
            background: rgba(243,244,246,0.65);
            border: 1px solid rgba(203,213,225,0.6);
        }
        .bullet .bicon{
            width: 28px; height:28px;
            border-radius: 10px;
            background: rgba(37,99,235,0.12);
            border: 1px solid rgba(37,99,235,0.20);
            display:flex; align-items:center; justify-content:center;
            font-weight:900;
            color:#1D4ED8;
            flex: 0 0 auto;
            margin-top: 1px;
        }
        .bullet strong{
            display:block;
            font-weight:1000;
            margin-bottom: 4px;
            letter-spacing:-0.01em;
        }
        .bullet span{
            color: var(--muted);
            font-size: 14px;
            line-height: 1.45;
        }

        /* Creative visual (no chips) */
        .visual{
            border-radius: var(--radius);
            border: 1px solid rgba(203,213,225,0.75);
            overflow:hidden;
            box-shadow: var(--shadow);
            background: #E5E7EB;
            position:relative;
            min-height: 360px;
        }
        .visual img{
            width:100%;
            height:100%;
            object-fit: cover;
            filter: saturate(1.05) contrast(1.02);
            transform: scale(1.02);
        }
        .visual .caption{
            position:absolute;
            left: 14px;
            right: 14px;
            bottom: 14px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 12px;
            padding: 12px 12px;
            border-radius: 16px;
            background: rgba(255,255,255,0.80);
            border: 1px solid rgba(203,213,225,0.7);
            backdrop-filter: blur(8px);
        }
        .cap-left strong{
            display:block;
            font-weight:1000;
            letter-spacing:-0.02em;
            margin-bottom: 2px;
        }
        .cap-left span{
            color: var(--muted);
            font-size: 13px;
        }
        .cap-right{
            display:flex;
            gap: 10px;
            align-items:center;
            flex-wrap: wrap;
            justify-content:flex-end;
        }
        .cap-metric{
            padding: 8px 10px;
            border-radius: 999px;
            background: rgba(243,244,246,0.7);
            border: 1px solid rgba(203,213,225,0.65);
            font-weight:900;
            font-size: 12px;
            color:#334155;
            white-space:nowrap;
        }

        /* Testimonials */
        .testimonials{
            display:grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 14px;
        }
        .quote{
            border-radius: var(--radius);
            border: 1px solid rgba(203,213,225,0.75);
            background: rgba(255,255,255,0.72);
            box-shadow: var(--shadow2);
            padding: 18px;
        }
        .quote p{
            margin: 0 0 14px;
            color: #334155;
            line-height:1.55;
            font-size: 14px;
        }
        .person{
            display:flex;
            align-items:center;
            gap: 10px;
            color: #0F172A;
            font-weight:1000;
            font-size: 13px;
        }
        .avatar{
            width: 34px; height:34px;
            border-radius: 12px;
            background: rgba(37,99,235,0.12);
            border: 1px solid rgba(37,99,235,0.2);
            display:flex; align-items:center; justify-content:center;
            font-weight:1000;
            color:#1D4ED8;
        }
        .role{
            display:block;
            font-weight:700;
            color: var(--muted);
            font-size: 12px;
            margin-top: 2px;
        }

        /* Footer (gray background moved to footer grid area) */
        footer{
            padding: 0 0 44px;
            color: #475569;
        }
        .footer-grid-wrap{
            background: rgba(243,244,246,0.75);
            border-top: 1px solid rgba(203,213,225,0.75);
            padding: 26px 0 30px;
        }
        .footer-grid{
            display:grid;
            grid-template-columns: 1.2fr 1fr 1fr 1fr;
            gap: 14px;
            align-items:flex-start;
        }
        .foot-title{
            font-weight:1000;
            color: #0F172A;
            margin-bottom: 10px;
            letter-spacing:-0.02em;
        }
        .foot a{
            display:block;
            padding: 6px 0;
            color: #475569;
            font-weight:800;
            font-size: 13px;
        }
        .foot a:hover{ color:#1D4ED8; }
        .small{
            font-size: 12px;
            color:#64748B;
            line-height: 1.55;
            margin-top: 10px;
            max-width: 60ch;
        }
        .copyright{
            margin-top: 14px;
            font-size: 12px;
            color:#64748B;
            font-weight:700;
        }

        /* Responsive */
        @media (max-width: 980px){
            .hero-grid{ grid-template-columns: 1fr; }
            .cards{ grid-template-columns: 1fr; }
            .split{ grid-template-columns: 1fr; }
            .testimonials{ grid-template-columns: 1fr; }
            .logos-row{ justify-content:center; }
            .logo-chip{ min-width: 140px; }
            .footer-grid{ grid-template-columns: 1fr 1fr; }
            .nav-links{ display:none; }
            .hamburger{ display:inline-flex; align-items:center; justify-content:center; }
            .value-card{ min-height: unset; }
        }
        @media (max-width: 520px){
            .footer-grid{ grid-template-columns: 1fr; }
            .logo-chip{ width: 100%; }
            .btn{ width: 100%; }
            .hero-actions{ width: 100%; }
            .visual .caption{ flex-direction: column; align-items:flex-start; }
            .cap-right{ justify-content:flex-start; }
        }

        /* Mobile drawer */
        .drawer{
            display:none;
            padding: 12px 0 18px;
            border-top: 1px solid rgba(203,213,225,0.7);
        }
        .drawer a{
            display:block;
            padding: 10px 0;
            font-weight:900;
            color:#334155;
        }
        .drawer a:hover{ color:#1D4ED8; }
        .drawer.open{ display:block; }
    </style>
</head>

<body>

<!-- TOP SHADE WRAPPER -->
<div class="top-shade">

    <!-- NAV -->
    <div class="nav">
        <div class="container">
            <div class="nav-inner">
                <a class="brand" href="#">
                    <span class="logo" aria-hidden="true"></span>
                    <span>Fotuka</span>
                </a>

                <div class="nav-links" aria-label="Primary navigation">
                    <a href="#features">Features</a>
                    <a href="#sharing">Sharing</a>
                    <a href="#security">Security</a>
                    <a href="#pricing">Pricing</a>
                </div>

                <div class="nav-cta">
                    <a class="btn btn-primary" href="<?= Url::to(['/signup']) ?>">Try it for Free</a>
                    <button class="hamburger" id="hamburger" aria-label="Open menu" type="button">
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </div>

            <div class="drawer" id="drawer" aria-label="Mobile navigation">
                <a href="#features">Features</a>
                <a href="#sharing">Sharing</a>
                <a href="#security">Security</a>
                <a href="#pricing">Pricing</a>
            </div>
        </div>
    </div>

    <!-- HERO -->
    <header class="hero">
        <div class="container">
            <div class="hero-grid">
                <div>
                    <div class="pill"><span class="dot"></span> Built for speed, designed for simplicity</div>
                    <h1>Digital Asset Management that anyone can use in minutes.</h1>
                    <p class="sub">
                        Fotuka helps teams organize, preview, and share assets with <strong>no training</strong>.
                        Secure links and fast delivery — all in one place.
                    </p>

                    <div class="hero-actions">
                        <a class="btn btn-primary" href="<?= Url::to(['/signup']) ?>">Start Free</a>
                    </div>

                    <div class="trust">
                        <span><span class="check">✓</span> Secure share links</span>
                        <span><span class="check">✓</span> Fast previews & thumbnails</span>
                        <span><span class="check">✓</span> One-click downloads</span>
                        <span><span class="check">✓</span> Built for ease of use</span>
                    </div>
                </div>

                <div class="value-card" aria-label="Fotuka value highlights">
                    <div class="value-title">
                        <strong>Why teams switch to Fotuka</strong>
                        <span class="mini-badge">No training required</span>
                    </div>

                    <div class="value-list">
                        <div class="value-row">
                            <div class="vicon">⚡</div>
                            <div>
                                <strong>Get to “share” fast</strong>
                                <span>Upload → select → share. Clean, simple workflows that users already understand.</span>
                            </div>
                        </div>
                        <div class="value-row">
                            <div class="vicon">🔒</div>
                            <div>
                                <strong>Secure by default</strong>
                                <span>Password, expiration, and link controls without complicated setup screens.</span>
                            </div>
                        </div>
                        <div class="value-row">
                            <div class="vicon">🧩</div>
                            <div>
                                <strong>Built to scale later</strong>
                                <span>Start simple today, add powerful distribution features as your library grows.</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Logos -->
            <div class="logos">
                <div class="logos-row">
                    <div class="logo-chip"><span class="spark">✦</span> Creative Teams</div>
                    <div class="logo-chip"><span class="spark">✦</span> Agencies</div>
                    <div class="logo-chip"><span class="spark">✦</span> Real Estate</div>
                    <div class="logo-chip"><span class="spark">✦</span> Events</div>
                    <div class="logo-chip"><span class="spark">✦</span> E-commerce</div>
                </div>
            </div>

        </div>
    </header>

</div><!-- /top-shade -->


<!-- FEATURES -->
<section id="features">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Everything you need, nothing you don’t.</h2>
                <p>Fotuka focuses on speed and clarity: no hidden functionality, no confusing workflows — just the actions you need, exactly where you expect them.</p>
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <div class="icon">⚡</div>
                <h3>Fast previews</h3>
                <p>Thumbnails and previews that load quickly so users stay in flow — even with large libraries.</p>
            </div>
            <div class="card">
                <div class="icon">🧠</div>
                <h3>Simple organization</h3>
                <p>Folders that make sense. Search when you need it. No taxonomy overload for everyday users.</p>
            </div>
            <div class="card">
                <div class="icon">🔒</div>
                <h3>Secure sharing</h3>
                <p>Share by link or download with passwords and expiration — without complicated setup.</p>
            </div>
        </div>
    </div>
</section>


<!-- SHARING -->
<section id="sharing">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Share assets the way teams actually work.</h2>
                <p>Send a single file, a selection, or an entire folder — in one or two clicks.</p>
            </div>
        </div>

        <div class="split">
            <div class="panel">
                <div class="bullets">
                    <div class="bullet">
                        <div class="bicon">🔗</div>
                        <div>
                            <strong>Share Link</strong>
                            <span>Create secure links with password + expiration. Preview first, download when ready.</span>
                        </div>
                    </div>

                    <?php
                    /*
                    <div class="bullet">
                      <div class="bicon">🗂️</div>
                      <div>
                        <strong>Branded Galleries</strong>
                        <span>Make delivery look premium: clients get a clean page where they can preview and download.</span>
                      </div>
                    </div>
                    */
                    ?>

                    <div class="bullet">
                        <div class="bicon">📦</div>
                        <div>
                            <strong>One-Click ZIP</strong>
                            <span>Download selected assets as a ZIP with zero friction. Great for “send me everything” requests.</span>
                        </div>
                    </div>

                    <div class="bullet">
                        <div class="bicon">🧩</div>
                        <div>
                            <strong>Clean permissions</strong>
                            <span>Keep it simple: share what you want, revoke when needed, and avoid confusing access rules.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="visual" aria-label="Sharing visual">
                <img alt="Sharing visual" src="https://images.unsplash.com/photo-1553877522-43269d4ea984?auto=format&fit=crop&w=1600&q=80" />
                <div class="caption">
                    <div class="cap-left">
                        <strong>From library to client in seconds</strong>
                        <span>Select assets, share a secure link, and keep everything organized in one place.</span>
                    </div>
                    <div class="cap-right">
                        <span class="cap-metric">Password</span>
                        <span class="cap-metric">Expiration</span>
                        <span class="cap-metric">Revoke Anytime</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>


<!-- SECURITY -->
<section id="security">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Control without complexity.</h2>
                <p>Keep it simple for users — and still have the controls you need for the business.</p>
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <div class="icon">🛡️</div>
                <h3>Share controls</h3>
                <p>Password, expiration, download limits, and link revocation — all from one simple dialog.</p>
            </div>
            <div class="card">
                <div class="icon">🧾</div>
                <h3>Audit-ready</h3>
                <p>Track what was shared and when. Keep a clean record of asset distribution.</p>
            </div>
            <div class="card">
                <div class="icon">🏁</div>
                <h3>Simple onboarding</h3>
                <p>New users can upload, organize, and share confidently in their first session — no training required.</p>
            </div>
        </div>
    </div>
</section>


<!-- TESTIMONIALS -->
<section>
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Made for teams who want results, not menus.</h2>
                <p>Placeholder testimonials for now — but this is the tone and polish that will make Fotuka feel premium.</p>
            </div>
        </div>

        <div class="testimonials">
            <div class="quote">
                <p>“We stopped sending giant ZIP files over email. Fotuka made client delivery clean and professional — in seconds.”</p>
                <div class="person">
                    <div class="avatar">A</div>
                    <div>
                        Alex Rivera <span class="role">Agency Producer</span>
                    </div>
                </div>
            </div>

            <div class="quote">
                <p>“The UI is straightforward. I didn’t need training. Upload → select → share. That’s exactly what I wanted.”</p>
                <div class="person">
                    <div class="avatar">M</div>
                    <div>
                        Maria Chen <span class="role">Real Estate Marketing</span>
                    </div>
                </div>
            </div>

            <div class="quote">
                <p>“The share links are perfect: secure, easy, and clients love how simple it feels.”</p>
                <div class="person">
                    <div class="avatar">J</div>
                    <div>
                        Jordan Blake <span class="role">Photographer</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- PRICING TEASER -->
<section id="pricing">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Simple pricing, built to scale.</h2>
                <p>Start with one plan, keep it straightforward, and expand later as you add premium sharing and automation.</p>
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <div class="icon">🚀</div>
                <h3>Starter</h3>
                <p>Great for individuals and small teams. Upload, organize, preview, and share.</p>
                <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
                    <a class="btn btn-primary" href="<?= Url::to(['/signup']) ?>">Start Free</a>
                </div>
            </div>

            <div class="card">
                <div class="icon">🏢</div>
                <h3>Team</h3>
                <p>More collaboration tools, stronger controls, and delivery features for client work.</p>
                <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
                    <a class="btn btn-primary" href="<?= Url::to(['/signup']) ?>">Try it for Free</a>
                </div>
            </div>

            <div class="card">
                <div class="icon">✨</div>
                <h3>Business</h3>
                <p>Advanced sharing, audit trails, and integrations — when you’re ready to scale.</p>
                <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
                    <a class="btn btn-primary" href="<?= Url::to(['/signup']) ?>">Join Free</a>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- BOTTOM SHADE WRAPPER -->
<div class="bottom-shade">
    <!-- FOOTER -->
    <footer>
        <div class="footer-grid-wrap">
            <div class="container">
                <div class="footer-grid">
                    <div class="foot">
                        <div class="brand" style="margin-bottom:8px;">
                            <span class="logo" aria-hidden="true"></span>
                            <span class="foot-title">Fotuka</span>
                        </div>
                        <div class="small">
                            A modern Digital Asset Management platform designed for speed, clarity, and effortless sharing.
                        </div>
                        <div class="copyright">© <span id="year"></span> Fotuka. All rights reserved.</div>
                    </div>

                    <div class="foot">
                        <div class="foot-title">Product</div>
                        <a href="#features">Features</a>
                        <a href="#sharing">Sharing</a>
                        <a href="#security">Security</a>
                        <a href="#pricing">Pricing</a>
                    </div>

                    <div class="foot">
                        <div class="foot-title">Company</div>
                        <a href="#">About</a>
                        <a href="#">Careers</a>
                        <a href="#">Press</a>
                        <a href="#">Contact</a>
                    </div>

                    <div class="foot">
                        <div class="foot-title">Legal</div>
                        <a href="#">Privacy</a>
                        <a href="#">Terms</a>
                        <a href="#">Security</a>
                        <a href="#">Status</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>

<script>
    const drawer = document.getElementById('drawer');
    const hamburger = document.getElementById('hamburger');

    if (hamburger && drawer) {
        hamburger.addEventListener('click', () => {
            drawer.classList.toggle('open');
    });
    }
    document.getElementById('year').textContent = new Date().getFullYear();
</script>
</body>
</html>