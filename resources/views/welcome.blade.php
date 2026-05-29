<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <!-- SEO Básico -->
    <title>TMS LOG | Sistema de Gestão de Transportes e Logística Inteligente</title>
    <meta name="description" content="O TMS LOG é o software SaaS definitivo para gestão de transportes e logística. Otimize rotas com Mapbox, automatize cotações de fretes, acompanhe motoristas em tempo real e emita CT-e de forma simples e rápida. Reduza seus custos operacionais hoje!">
    <meta name="keywords" content="tms, gestão de transportes, sistema logistico, controle de cargas, cotação de fretes online, roteirizador inteligente, monitoramento de motoristas, emissores de cte, mdfe, tms log, saas logistica">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="TMS LOG | Sistema de Gestão de Transportes e Logística Inteligente">
    <meta property="og:description" content="O TMS LOG é o software SaaS definitivo para gestão de transportes e logística. Otimize rotas, automatize cotações de fretes, acompanhe motoristas em tempo real e emita CT-e de forma simples e rápida.">
    <meta property="og:image" content="{{ asset('LOGO.svg') }}">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:site_name" content="TMS LOG">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="TMS LOG | Sistema de Gestão de Transportes e Logística Inteligente">
    <meta property="twitter:description" content="O TMS LOG é o software SaaS definitivo para gestão de transportes e logística. Otimize rotas, automatize cotações de fretes, acompanhe motoristas em tempo real e emita CT-e de forma simples e rápida.">
    <meta property="twitter:image" content="{{ asset('LOGO.svg') }}">

    <!-- Schema Markup JSON-LD (E-E-A-T & Google SERPs Rich Snippets) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "Organization",
                "@id": "{{ url('/') }}/#organization",
                "name": "TMS LOG",
                "url": "{{ url('/') }}",
                "logo": {
                    "@type": "ImageObject",
                    "url": "{{ asset('LOGO.svg') }}",
                    "caption": "TMS LOG Logo"
                },
                "sameAs": [
                    "https://www.linkedin.com/company/tms-log"
                ]
            },
            {
                "@type": "WebSite",
                "@id": "{{ url('/') }}/#website",
                "url": "{{ url('/') }}",
                "name": "TMS LOG",
                "description": "Sistema de Gestão de Transportes e Roteirização Inteligente",
                "publisher": {
                    "@id": "{{ url('/') }}/#organization"
                }
            },
            {
                "@type": "SoftwareApplication",
                "@id": "{{ url('/') }}/#software",
                "name": "TMS LOG",
                "applicationCategory": "BusinessApplication",
                "operatingSystem": "All",
                "offers": {
                    "@type": "AggregateOffer",
                    "priceCurrency": "BRL",
                    "lowPrice": "99.00",
                    "highPrice": "399.00",
                    "offerCount": "3"
                },
                "featureList": [
                    "Roteirização e cálculo de rotas operacionais via Mapbox GL",
                    "Cálculo automatizado e cotação de fretes online por SMS/WhatsApp",
                    "Gestão de cargas, veículos, motoristas e ocorrências de trânsito",
                    "Faturamento integrado e emissão digital simplificada de Notas, CT-e e MDF-e",
                    "Financeiro completo com contas a pagar, a receber, fluxo de caixa e conciliação bancária",
                    "Painel CRM de vendas e pipeline comercial para captação de fretes"
                ]
            },
            {
                "@type": "FAQPage",
                "@id": "{{ url('/') }}/#faq",
                "mainEntity": [
                    {
                        "@type": "Question",
                        "name": "Como funciona o cálculo de frete no TMS LOG?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "O TMS LOG calcula o valor do frete combinando a distância exata da rota via Mapbox, as tabelas de fretes do transportador por cubagem/peso, e os impostos incidentes de forma automática, enviando uma proposta digital diretamente para o cliente."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "O TMS LOG emite CT-e e MDF-e?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Sim! O sistema possui um módulo fiscal completo e simplificado para importação do XML da nota fiscal e emissão ágil do CT-e e MDF-e em poucos segundos, agilizando o despacho de cargas."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "É possível acompanhar o motorista em tempo real?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Sim! O TMS LOG possui rastreamento por geolocalização em tempo real integrado ao painel logístico, permitindo acompanhar o deslocamento exato do motorista e emitir alertas automáticos de status."
                        }
                    }
                ]
            }
        ]
    }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #0a1628;
            --bg2: #0f1f35;
            --bg3: #162840;
            --accent: #FF6B35;
            --accent2: #FFB347;
            --text: #e2e8f0;
            --muted: #8fa4bd;
            --border: rgba(255,255,255,0.08);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--text);
            overflow-x: hidden;
        }

        /* ─── NAVBAR ─── */
        nav {
            position: fixed; top:0; left:0; right:0; z-index:999;
            padding: 18px 64px;
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(10,22,40,0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
        }
        .nav-logo { display:flex; align-items:center; gap:10px; text-decoration:none; }
        .nav-logo-icon {
            width:36px; height:36px; background: var(--accent);
            border-radius:8px; display:flex; align-items:center; justify-content:center;
            font-size:18px;
        }
        .nav-logo-text { font-size:20px; font-weight:700; color:var(--text); }
        .nav-links { display:flex; gap:36px; list-style:none; }
        .nav-links a { color:var(--muted); text-decoration:none; font-size:14px; font-weight:500; transition:color 200ms; }
        .nav-links a:hover { color:var(--text); }
        .nav-actions { display:flex; gap:12px; align-items:center; }
        .btn-text { background:none; border:none; color:var(--muted); font-size:14px; font-weight:600; cursor:pointer; transition:color 200ms; font-family:inherit; }
        .btn-text:hover { color:var(--text); }
        .btn-cta {
            padding:10px 22px;
            background: var(--accent);
            color:#fff; border:none; border-radius:8px;
            font-size:14px; font-weight:600; cursor:pointer;
            transition:all 200ms; font-family:inherit;
            box-shadow: 0 4px 20px rgba(255,107,53,0.35);
        }
        .btn-cta:hover { background:var(--accent2); transform:translateY(-1px); box-shadow:0 6px 28px rgba(255,107,53,0.45); }

        /* ─── HERO ─── */
        .hero {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            padding: 120px 64px 80px;
            gap: 60px;
            position: relative;
            overflow: hidden;
        }
        /* grid lines background */
        .hero::before {
            content:'';
            position:absolute; inset:0;
            background-image:
                linear-gradient(rgba(255,107,53,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,107,53,0.04) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events:none;
        }
        /* radial glow */
        .hero::after {
            content:'';
            position:absolute;
            top:-20%; right:-10%;
            width:700px; height:700px;
            background: radial-gradient(circle, rgba(255,107,53,0.12) 0%, transparent 65%);
            border-radius:50%;
            pointer-events:none;
            animation: breathe 8s ease-in-out infinite;
        }
        @keyframes breathe {
            0%,100% { transform: scale(1); opacity:1; }
            50% { transform: scale(1.15); opacity:0.7; }
        }

        .hero-left { position:relative; z-index:2; }
        .hero-badge {
            display:inline-flex; align-items:center; gap:8px;
            background:rgba(255,107,53,0.12); border:1px solid rgba(255,107,53,0.3);
            padding:6px 14px; border-radius:100px;
            font-size:12px; color:var(--accent); font-weight:600;
            margin-bottom:28px;
            animation: fadeUp 600ms ease both;
        }
        .hero-badge .dot { width:7px; height:7px; background:var(--accent); border-radius:50%; animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.3;} }

        .hero h1 {
            font-size: 58px;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 24px;
            animation: fadeUp 600ms 100ms ease both;
        }
        .hero h1 .line3 {
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero p {
            font-size:17px; color:var(--muted); line-height:1.7;
            max-width:480px; margin-bottom:40px;
            animation: fadeUp 600ms 200ms ease both;
        }
        .hero-btns {
            display:flex; gap:16px; align-items:center;
            animation: fadeUp 600ms 300ms ease both;
            margin-bottom:50px;
        }
        .btn-hero-primary {
            padding:14px 30px;
            background: var(--accent);
            color:#fff; border:none; border-radius:10px;
            font-size:15px; font-weight:600; cursor:pointer;
            transition:all 200ms; font-family:inherit;
            box-shadow:0 6px 24px rgba(255,107,53,0.4);
            display:flex; align-items:center; gap:10px;
            text-decoration:none;
        }
        .btn-hero-primary:hover { background:var(--accent2); transform:translateY(-2px); box-shadow:0 10px 32px rgba(255,107,53,0.5); }
        .btn-hero-secondary {
            padding:14px 28px;
            background:transparent; color:var(--text);
            border:1px solid var(--border);
            border-radius:10px; font-size:15px; font-weight:600;
            cursor:pointer; transition:all 200ms; font-family:inherit;
            display:flex; align-items:center; gap:10px;
            text-decoration:none;
        }
        .play-icon {
            width:32px; height:32px;
            background:rgba(255,255,255,0.1);
            border-radius:50%; display:flex; align-items:center; justify-content:center;
            font-size:11px;
        }
        .btn-hero-secondary:hover { border-color:var(--accent); color:var(--accent); }

        .hero-proof {
            display:flex; gap:32px; align-items:center;
            animation: fadeUp 600ms 400ms ease both;
        }
        .proof-item { display:flex; align-items:center; gap:10px; }
        .proof-icon { width:36px; height:36px; border-radius:50%; background:rgba(255,107,53,0.12); display:flex; align-items:center; justify-content:center; color:var(--accent); font-size:14px; }
        .proof-text { font-size:12px; color:var(--muted); line-height:1.4; }
        .proof-text strong { color:var(--text); display:block; font-size:13px; }

        /* ─── HERO RIGHT - visual ─── */
        .hero-right {
            position:relative; z-index:2;
            height:560px;
            animation: fadeUp 600ms 200ms ease both;
        }

        /* Truck illustration with CSS */
        .truck-wrap {
            position:absolute; top:0; left:0; right:0; bottom:100px;
            background: linear-gradient(135deg, #0f1f35 0%, #162840 100%);
            border-radius:20px;
            border:1px solid var(--border);
            overflow:hidden;
            display:flex; align-items:center; justify-content:center;
        }
        .truck-scene {
            position:relative; width:100%; height:100%;
            display:flex; align-items:center; justify-content:center;
        }
        /* road */
        .road {
            position:absolute; bottom:0; left:0; right:0; height:120px;
            background: linear-gradient(180deg, transparent 0%, rgba(255,107,53,0.04) 100%);
            border-top:1px solid rgba(255,107,53,0.15);
        }
        .road-line {
            position:absolute; bottom:55px; left:0; right:0; height:3px;
            background: repeating-linear-gradient(90deg, transparent, transparent 30px, rgba(255,107,53,0.3) 30px, rgba(255,107,53,0.3) 60px);
        }
        /* truck svg */
        .truck-svg { font-size:120px; filter:drop-shadow(0 20px 60px rgba(255,107,53,0.25)); animation: truckMove 3s ease-in-out infinite; }
        @keyframes truckMove { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
        /* speed lines */
        .speed-line { position:absolute; background:linear-gradient(90deg, transparent, rgba(255,107,53,0.3)); height:2px; border-radius:2px; }
        .sl1 { width:120px; bottom:80px; left:30px; animation: speed 2s linear infinite; }
        .sl2 { width:80px; bottom:70px; left:50px; animation: speed 2s linear infinite 0.3s; }
        .sl3 { width:60px; bottom:90px; left:20px; animation: speed 2s linear infinite 0.6s; }
        @keyframes speed { 0%{transform:translateX(0);opacity:0.8;} 100%{transform:translateX(-200px);opacity:0;} }

        /* floating metric cards */
        .metric-card {
            position:absolute;
            background:rgba(15,31,53,0.95);
            border:1px solid var(--border);
            border-radius:14px;
            padding:16px 20px;
            backdrop-filter:blur(20px);
            box-shadow:0 20px 60px rgba(0,0,0,0.5);
            min-width:170px;
        }
        .mc-label { font-size:11px; color:var(--muted); margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em; }
        .mc-value { font-size:28px; font-weight:800; color:var(--text); margin-bottom:4px; }
        .mc-change { font-size:11px; font-weight:600; }
        .mc-change.up { color:#10b981; }
        .mc-change.down { color:var(--accent); }
        .mc-sparkline { height:30px; margin-top:8px; }
        .mc-sparkline svg { width:100%; height:100%; }

        .metric-card.mc1 { top:20px; right:20px; animation: float1 4s ease-in-out infinite; }
        .metric-card.mc2 { bottom:110px; right:20px; animation: float2 5s ease-in-out infinite; }
        @keyframes float1 { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
        @keyframes float2 { 0%,100%{transform:translateY(0)} 50%{transform:translateY(8px)} }

        /* ─── SOCIAL PROOF STRIP ─── */
        .strip {
            padding:30px 64px;
            background:var(--bg2);
            border-top:1px solid var(--border);
            border-bottom:1px solid var(--border);
            display:flex; align-items:center; gap:48px;
            overflow:hidden;
        }
        .strip-label { font-size:13px; color:var(--muted); white-space:nowrap; font-weight:600; min-width:200px; }
        .strip-logos { display:flex; gap:48px; animation: marquee 25s linear infinite; }
        .strip-logo { font-size:14px; font-weight:700; color:rgba(255,255,255,0.25); white-space:nowrap; letter-spacing:1px; transition:color 200ms; }
        .strip-logo:hover { color:rgba(255,255,255,0.5); }
        @keyframes marquee { from{transform:translateX(0)} to{transform:translateX(-50%)} }

        /* ─── FEATURES ─── */
        .features {
            padding: 100px 64px;
            background:var(--bg);
        }
        .section-tag {
            display:inline-block;
            color:var(--accent); font-size:12px; font-weight:700;
            text-transform:uppercase; letter-spacing:0.1em;
            margin-bottom:16px;
        }
        .section-title {
            font-size:44px; font-weight:800;
            line-height:1.2; margin-bottom:16px;
        }
        .section-sub {
            font-size:16px; color:var(--muted);
            max-width:550px; margin-bottom:64px;
        }
        .features-grid {
            display:grid;
            grid-template-columns: repeat(5, 1fr);
            gap:20px;
        }
        .f-card {
            background:var(--bg2);
            border:1px solid var(--border);
            border-radius:16px; padding:32px 24px;
            transition:all 300ms;
            cursor:default;
        }
        .f-card:hover { transform:translateY(-8px); border-color:rgba(255,107,53,0.4); box-shadow:0 20px 50px rgba(255,107,53,0.12); }
        .f-icon { font-size:32px; color:var(--accent); margin-bottom:20px; }
        .f-card h4 { font-size:16px; font-weight:700; margin-bottom:10px; }
        .f-card p { font-size:13px; color:var(--muted); line-height:1.6; }

        /* ─── PLATFORM ─── */
        .platform {
            padding:100px 64px;
            background:var(--bg2);
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:80px; align-items:center;
        }
        .platform-left h2 { font-size:46px; font-weight:800; line-height:1.2; margin-bottom:20px; }
        .platform-left p { font-size:16px; color:var(--muted); margin-bottom:30px; line-height:1.7; }
        .check-list { list-style:none; margin-bottom:40px; }
        .check-list li {
            display:flex; align-items:center; gap:14px;
            padding:12px 0; border-bottom:1px solid var(--border);
            font-size:14px; color:var(--muted);
        }
        .check-list li .chk {
            width:22px; height:22px; min-width:22px;
            background:rgba(255,107,53,0.15); border-radius:6px;
            display:flex; align-items:center; justify-content:center;
            color:var(--accent); font-size:12px;
        }

        /* dashboard mockup */
        .dash-mock {
            background:var(--bg3);
            border:1px solid var(--border);
            border-radius:16px; overflow:hidden;
            box-shadow:0 30px 80px rgba(0,0,0,0.5);
        }
        .dash-topbar {
            background:rgba(255,255,255,0.04);
            padding:12px 20px;
            display:flex; justify-content:space-between; align-items:center;
            border-bottom:1px solid var(--border);
        }
        .dash-logo { display:flex; align-items:center; gap:8px; font-size:14px; font-weight:700; color:var(--accent); }
        .dash-user { display:flex; align-items:center; gap:8px; font-size:12px; color:var(--muted); }
        .dash-avatar { width:28px; height:28px; background:var(--accent); border-radius:50%; font-size:11px; font-weight:700; color:#fff; display:flex; align-items:center; justify-content:center; }
        .dash-body { display:grid; grid-template-columns:160px 1fr; }
        .dash-sidebar { padding:16px 12px; border-right:1px solid var(--border); }
        .dash-menu-item {
            display:flex; align-items:center; gap:10px;
            padding:9px 12px; border-radius:8px;
            font-size:12px; color:var(--muted); cursor:pointer;
            margin-bottom:4px;
        }
        .dash-menu-item.active { background:rgba(255,107,53,0.12); color:var(--accent); }
        .dash-main { padding:20px; }
        .dash-title { font-size:18px; font-weight:700; margin-bottom:16px; }
        .dash-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px; }
        .dash-stat { background:var(--bg2); border:1px solid var(--border); border-radius:10px; padding:12px; }
        .ds-label { font-size:10px; color:var(--muted); margin-bottom:4px; }
        .ds-val { font-size:16px; font-weight:800; color:var(--text); }
        .ds-change { font-size:10px; color:#10b981; }
        /* map mock */
        .map-mock {
            background:var(--bg2);
            border:1px solid var(--border);
            border-radius:10px; height:140px;
            position:relative; overflow:hidden;
        }
        .map-grid {
            position:absolute; inset:0;
            background-image:
                linear-gradient(rgba(255,107,53,0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,107,53,0.06) 1px, transparent 1px);
            background-size:30px 30px;
        }
        .map-dot { position:absolute; width:8px; height:8px; background:var(--accent); border-radius:50%; box-shadow:0 0 12px var(--accent); }
        .map-dot::before { content:''; position:absolute; inset:-4px; background:rgba(255,107,53,0.2); border-radius:50%; animation:ripple 2s infinite; }
        @keyframes ripple { 0%{transform:scale(1);opacity:0.8;} 100%{transform:scale(2.5);opacity:0;} }
        .map-line { position:absolute; height:2px; background:linear-gradient(90deg,rgba(255,107,53,0.6),transparent); transform-origin:left; }
        .md1{top:30%;left:20%;}
        .md2{top:50%;left:45%;}
        .md3{top:25%;left:60%;}
        .md4{top:60%;left:75%;}
        .ml1{top:calc(30% + 3px);left:22%;width:24%;transform:rotate(15deg);}
        .ml2{top:calc(50% + 3px);left:47%;width:16%;transform:rotate(-20deg);}
        .ml3{top:calc(25% + 3px);left:62%;width:15%;transform:rotate(30deg);}

        /* ─── RESULTS ─── */
        .results {
            padding:100px 64px;
            background:var(--bg);
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:80px; align-items:center;
        }
        .results-left h2 { font-size:44px; font-weight:800; line-height:1.2; margin-bottom:16px; }
        .results-left p { font-size:16px; color:var(--muted); }
        .results-grid {
            display:grid; grid-template-columns:1fr 1fr; gap:24px;
        }
        .r-card {
            background:var(--bg2);
            border:1px solid var(--border);
            border-radius:16px; padding:32px;
            transition:all 300ms;
        }
        .r-card:hover { border-color:rgba(255,107,53,0.4); transform:translateY(-4px); }
        .r-icon { font-size:24px; color:var(--accent); margin-bottom:16px; }
        .r-number { font-size:48px; font-weight:800; background:linear-gradient(135deg,var(--accent),var(--accent2)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; margin-bottom:8px; }
        .r-label { font-size:14px; color:var(--muted); }

        /* ─── CLIENTS ─── */
        .clients {
            padding:100px 64px;
            background:var(--bg2);
            text-align:center;
        }
        .clients-intro { max-width:600px; margin:0 auto 60px; }
        .clients-intro h2 { font-size:40px; font-weight:800; margin-bottom:16px; }
        .clients-intro p { font-size:16px; color:var(--muted); }
        .clients-rating { display:flex; align-items:center; justify-content:center; gap:10px; margin-bottom:48px; }
        .stars { color:var(--accent); font-size:18px; letter-spacing:2px; }
        .rating-val { font-size:14px; color:var(--muted); }
        .logos-grid {
            display:grid; grid-template-columns:repeat(4,1fr); gap:20px;
            max-width:1000px; margin:0 auto;
        }
        .logo-card {
            background:rgba(255,255,255,0.03);
            border:1px solid var(--border);
            border-radius:12px; padding:20px;
            font-size:13px; font-weight:700; color:rgba(255,255,255,0.25);
            letter-spacing:1px; transition:all 200ms; cursor:default;
            display:flex; align-items:center; justify-content:center;
            height:60px;
        }
        .logo-card:hover { color:var(--accent); border-color:rgba(255,107,53,0.3); background:rgba(255,107,53,0.05); }

        /* ─── CTA FINAL ─── */
        .cta-final {
            padding:100px 64px;
            text-align:center;
            background:linear-gradient(135deg, var(--bg) 0%, var(--bg3) 100%);
            position:relative; overflow:hidden;
        }
        .cta-final::before {
            content:'';
            position:absolute; top:50%; left:50%;
            transform:translate(-50%,-50%);
            width:600px; height:300px;
            background:radial-gradient(ellipse, rgba(255,107,53,0.12) 0%, transparent 70%);
        }
        .cta-final h2 { font-size:52px; font-weight:800; margin-bottom:20px; position:relative; z-index:1; }
        .cta-final p { font-size:18px; color:var(--muted); margin-bottom:40px; position:relative; z-index:1; }
        .cta-btns { display:flex; gap:16px; justify-content:center; position:relative; z-index:1; }

        /* ─── FOOTER ─── */
        footer {
            padding:40px 64px;
            border-top:1px solid var(--border);
            display:flex; justify-content:space-between; align-items:center;
            background:var(--bg);
        }
        .footer-logo { font-size:20px; font-weight:700; color:var(--accent); }
        .footer-text { font-size:13px; color:var(--muted); }

        /* ─── PRICING STYLES ─── */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .price-card {
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 40px 30px;
            position: relative;
            transition: all 300ms ease;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .price-card.popular {
            border-color: var(--accent);
            box-shadow: 0 20px 40px rgba(255, 107, 53, 0.15);
            background: linear-gradient(135deg, var(--bg3) 0%, rgba(255, 107, 53, 0.05) 100%);
        }
        .price-badge {
            position: absolute;
            top: -15px;
            background: var(--accent);
            color: white;
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .price-card h4 { font-size: 20px; font-weight: 700; margin-bottom: 10px; color: var(--text); }
        .price-card .desc { font-size: 13px; color: var(--muted); margin-bottom: 30px; }
        .price-card .price { font-size: 48px; font-weight: 800; color: var(--text); margin-bottom: 30px; display: flex; align-items: baseline; gap: 4px; }
        .price-card .price span { font-size: 14px; color: var(--muted); font-weight: 500; }
        .price-features { list-style: none; width: 100%; margin-bottom: 40px; text-align: left; }
        .price-features li { display: flex; align-items: center; gap: 12px; font-size: 13px; color: var(--muted); padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.03); }
        .price-features li i { color: var(--accent); font-size: 14px; }
        .price-btn {
            width: 100%;
            padding: 14px;
            background: transparent;
            border: 2px solid var(--border);
            color: var(--text);
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 200ms ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .price-card.popular .price-btn {
            background: var(--accent);
            border-color: var(--accent);
            color: white;
            box-shadow: 0 6px 20px rgba(255,107,53,0.3);
        }
        .price-card:hover {
            transform: translateY(-8px);
            border-color: rgba(255, 107, 53, 0.4);
        }
        .price-card.popular:hover {
            border-color: var(--accent);
            box-shadow: 0 20px 50px rgba(255, 107, 53, 0.25);
        }

        /* ─── ANIMATIONS ─── */
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(24px); }
            to { opacity:1; transform:translateY(0); }
        }

        /* ─── SCROLL REVEAL ─── */
        .reveal { opacity:0; transform:translateY(30px); transition:opacity 600ms ease, transform 600ms ease; }
        .reveal.visible { opacity:1; transform:translateY(0); }

        /* ─── RESPONSIVE ─── */
        @media(max-width:1200px) {
            .features-grid { grid-template-columns:repeat(3,1fr); }
            .pricing-grid { grid-template-columns:repeat(3,1fr); gap:20px; }
        }
        @media(max-width:1024px) {
            nav { padding:18px 32px; }
            .nav-links { display:none; }
            .hero { grid-template-columns:1fr; padding:100px 32px 60px; }
            .hero-right { height:380px; }
            .platform, .results { grid-template-columns:1fr; padding:60px 32px; gap:40px; }
            .features, .clients, .cta-final { padding:60px 32px; }
            .strip { padding:24px 32px; }
            footer { padding:32px; flex-direction:column; gap:16px; text-align:center; }
            .pricing-grid { grid-template-columns:1fr; max-width:450px; margin:0 auto; gap:30px; }
        }
        @media(max-width:768px) {
            .hero h1 { font-size:38px; }
            .hero-right { display:none; }
            .hero { grid-template-columns:1fr; text-align:center; padding:120px 20px 40px; }
            .hero-badge { margin:0 auto 28px; }
            .hero p { margin:0 auto 40px; }
            .hero-btns { justify-content:center; }
            .hero-proof { flex-wrap:wrap; justify-content:center; gap:20px; }
            .features-grid { grid-template-columns:1fr 1fr; }
            .results-grid { grid-template-columns:1fr; }
            .logos-grid { grid-template-columns:1fr; }
            .cta-final h2 { font-size:32px; }
            .cta-btns { flex-direction:column; align-items:center; }
            .dash-stats { grid-template-columns:repeat(2,1fr); }
            .dash-body { grid-template-columns:1fr; }
            .dash-sidebar { display:none; }
        }
        @media(max-width:480px) {
            .features-grid { grid-template-columns:1fr; }
            .dash-stats { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
    <a href="/" class="nav-logo">
        <div class="nav-logo-icon">🚛</div>
        <span class="nav-logo-text">tms</span>
    </a>
    <ul class="nav-links">
        <li><a href="#features">Produto</a></li>
        <li><a href="#features">Soluções</a></li>
        <li><a href="#pricing">Preços</a></li>
        <li><a href="#clients">Sobre</a></li>
    </ul>
    <div class="nav-actions">
        <a href="/login"><button class="btn-text">Entrar</button></a>
        <a href="/register"><button class="btn-cta">Agendar demo</button></a>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-left">
        <div class="hero-badge">
            <span class="dot"></span>
            O TMS completo para transportadoras modernas
        </div>
        <h1>Mais controle.<br>Menos custos.<br><span class="line3">Entregas que geram valor.</span></h1>
        <p>Automatize processos, aumente a eficiência e tenha visibilidade total da sua operação em uma única plataforma.</p>
        <div class="hero-btns">
            <a href="/register" class="btn-hero-primary"><i class="fas fa-arrow-right"></i> Agendar uma demonstração</a>
            <a href="#features" class="btn-hero-secondary">
                <div class="play-icon"><i class="fas fa-play"></i></div>
                Ver como funciona
            </a>
        </div>
        <div class="hero-proof">
            <div class="proof-item">
                <div class="proof-icon"><i class="fas fa-tachometer-alt"></i></div>
                <div class="proof-text"><strong>+ Eficiência</strong>Reduz custos operacionais</div>
            </div>
            <div class="proof-item">
                <div class="proof-icon"><i class="fas fa-eye"></i></div>
                <div class="proof-text"><strong>+ Visibilidade</strong>Acompanhe tudo em tempo real</div>
            </div>
            <div class="proof-item">
                <div class="proof-icon"><i class="fas fa-chart-line"></i></div>
                <div class="proof-text"><strong>+ Resultados</strong>Clientes satisfeitos</div>
            </div>
        </div>
    </div>

    <div class="hero-right">
        <div class="truck-wrap">
            <div class="truck-scene">
                <div class="speed-line sl1"></div>
                <div class="speed-line sl2"></div>
                <div class="speed-line sl3"></div>
                <div class="truck-svg">🚛</div>
                <div class="road"><div class="road-line"></div></div>
            </div>
        </div>
        <!-- Metric card 1 -->
        <div class="metric-card mc1">
            <div class="mc-label">Entrega no prazo</div>
            <div class="mc-value">98,6%</div>
            <div class="mc-change up">▲ +12% vs mês anterior</div>
            <div class="mc-sparkline">
                <svg viewBox="0 0 120 30" fill="none">
                    <polyline points="0,25 20,18 40,22 60,10 80,15 100,5 120,8" stroke="#FF6B35" stroke-width="2.5" fill="none" stroke-linejoin="round"/>
                    <polyline points="0,25 20,18 40,22 60,10 80,15 100,5 120,8 120,30 0,30" fill="rgba(255,107,53,0.1)"/>
                </svg>
            </div>
        </div>
        <!-- Metric card 2 -->
        <div class="metric-card mc2">
            <div class="mc-label">Redução de custos</div>
            <div class="mc-value">-23%</div>
            <div class="mc-change up">▲ +8% vs mês anterior</div>
            <div class="mc-sparkline">
                <svg viewBox="0 0 120 30" fill="none">
                    <rect x="5" y="20" width="12" height="10" fill="rgba(255,107,53,0.3)"/>
                    <rect x="22" y="15" width="12" height="15" fill="rgba(255,107,53,0.4)"/>
                    <rect x="39" y="12" width="12" height="18" fill="rgba(255,107,53,0.5)"/>
                    <rect x="56" y="8" width="12" height="22" fill="rgba(255,107,53,0.6)"/>
                    <rect x="73" y="5" width="12" height="25" fill="rgba(255,107,53,0.7)"/>
                    <rect x="90" y="3" width="12" height="27" fill="rgba(255,107,53,0.85)"/>
                    <rect x="107" y="0" width="12" height="30" fill="var(--accent)"/>
                </svg>
            </div>
        </div>
    </div>
</section>

<!-- CLIENTS STRIP -->
<div class="strip">
    <span class="strip-label">BATTLE-TESTED POR QUEM VIVE A ESTRADA</span>
    <div class="strip-logos">
        <span class="strip-logo">THIGA LOGÍSTICA</span>
        <span class="strip-logo">OPERAÇÃO REAL</span>
        <span class="strip-logo">VALIDADO EM RODOVIAS</span>
        <span class="strip-logo">TECNOLOGIA NACIONAL</span>
        <span class="strip-logo">INTEGRADO COM WHATSAPP</span>
        <span class="strip-logo">CO-CRIADO NA PRÁTICA</span>
        <span class="strip-logo">THIGA LOGÍSTICA</span>
        <span class="strip-logo">OPERAÇÃO REAL</span>
        <span class="strip-logo">VALIDADO EM RODOVIAS</span>
        <span class="strip-logo">TECNOLOGIA NACIONAL</span>
        <span class="strip-logo">INTEGRADO COM WHATSAPP</span>
        <span class="strip-logo">CO-CRIADO NA PRÁTICA</span>
    </div>
</div>

<!-- FEATURES -->
<section class="features reveal" id="features">
    <div class="section-tag">Plataforma Completa</div>
    <div class="section-title">Tecnologia Inteligente para sua Operação</div>
    <p class="section-sub">Do orçamento instantâneo ao controle financeiro. Gerencie sua frota com automações que realmente funcionam.</p>
    <div class="features-grid">
        <div class="f-card reveal">
            <div class="f-icon"><i class="fab fa-whatsapp"></i></div>
            <h4>CRM & IA WhatsApp</h4>
            <p>Robô que faz cotações completas via chat, rastreia status, gera leads e registra custos de motoristas via áudio.</p>
        </div>
        <div class="f-card reveal">
            <div class="f-icon"><i class="fas fa-calculator"></i></div>
            <h4>Cálculo de Frete Pró</h4>
            <p>Tabelas de frete peso, Ad Valorem, GRIS e pedágios automatizados. Calculadora pública com autenticação OTP.</p>
        </div>
        <div class="f-card reveal">
            <div class="f-icon"><i class="fas fa-route"></i></div>
            <h4>Roteirização & Custos</h4>
            <p>Criação e bloqueio de rotas, alocação de frota/motorista e controle absoluto de despesas reais por viagem.</p>
        </div>
        <div class="f-card reveal">
            <div class="f-icon"><i class="fas fa-file-invoice-dollar"></i></div>
            <h4>Faturamento & Contas</h4>
            <p>Conciliação de receita versus despesas de rota, geração de faturas e acompanhamento de contas a receber.</p>
        </div>
        <div class="f-card reveal">
            <div class="f-icon"><i class="fas fa-bell"></i></div>
            <h4>Alertas de Documentação</h4>
            <p>Rotinas automatizadas de verificação (como vencimento de CNH de motoristas) com disparos diários de avisos.</p>
        </div>
    </div>
</section>

<!-- PLATFORM -->
<section class="platform reveal" id="platform">
    <div class="platform-left">
        <div class="section-tag">Plataforma completa</div>
        <h2>Tudo que sua transportadora precisa, <span style="background:linear-gradient(135deg,var(--accent),var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">em um só lugar.</span></h2>
        <p>Do planejamento à entrega, o Thiga conecta todas as etapas da operação para mais controle, eficiência e rentabilidade.</p>
        <ul class="check-list">
            <li><span class="chk"><i class="fas fa-check"></i></span> Implantação rápida e suporte próximo</li>
            <li><span class="chk"><i class="fas fa-check"></i></span> Interface moderna e fácil de usar</li>
            <li><span class="chk"><i class="fas fa-check"></i></span> Integração com ERPs e sistemas parceiros</li>
            <li><span class="chk"><i class="fas fa-check"></i></span> Escalável para transportadoras de todos os portes</li>
        </ul>
        <a href="/register" class="btn-hero-primary">Conhecer todas as funcionalidades →</a>
    </div>
    <div class="dash-mock">
        <div class="dash-topbar">
            <div class="dash-logo">🚛 tms</div>
            <div class="dash-user">
                <div class="dash-avatar">S</div>
                <span>Sretes</span>
                <i class="fas fa-search" style="color:var(--muted);font-size:13px;margin-left:10px;"></i>
            </div>
        </div>
        <div class="dash-body">
            <div class="dash-sidebar">
                <div class="dash-menu-item active"><i class="fas fa-home fa-fw"></i> Dashboard</div>
                <div class="dash-menu-item"><i class="fas fa-boxes fa-fw"></i> Fretes</div>
                <div class="dash-menu-item"><i class="fas fa-route fa-fw"></i> Viagens</div>
                <div class="dash-menu-item"><i class="fas fa-truck fa-fw"></i> Entregas</div>
                <div class="dash-menu-item"><i class="fas fa-user fa-fw"></i> Motoristas</div>
                <div class="dash-menu-item"><i class="fas fa-car fa-fw"></i> Veículos</div>
                <div class="dash-menu-item"><i class="fas fa-dollar-sign fa-fw"></i> Financeiro</div>
                <div class="dash-menu-item"><i class="fas fa-chart-bar fa-fw"></i> Relatórios</div>
            </div>
            <div class="dash-main">
                <div class="dash-title">Dashboard</div>
                <div class="dash-stats">
                    <div class="dash-stat">
                        <div class="ds-label">Fretes no mês</div>
                        <div class="ds-val">1.248</div>
                        <div class="ds-change">▲ +14%</div>
                    </div>
                    <div class="dash-stat">
                        <div class="ds-label">Entregas realizadas</div>
                        <div class="ds-val">982</div>
                        <div class="ds-change">▲ +11%</div>
                    </div>
                    <div class="dash-stat">
                        <div class="ds-label">Custo por entrega</div>
                        <div class="ds-val">R$312</div>
                        <div class="ds-change" style="color:#ef4444;">▼ -8%</div>
                    </div>
                    <div class="dash-stat">
                        <div class="ds-label">Entrega no prazo</div>
                        <div class="ds-val">98,6%</div>
                        <div class="ds-change">▲ +12%</div>
                    </div>
                </div>
                <div class="map-mock">
                    <div class="map-grid"></div>
                    <div class="map-dot md1"></div>
                    <div class="map-dot md2"></div>
                    <div class="map-dot md3"></div>
                    <div class="map-dot md4"></div>
                    <div class="map-line ml1"></div>
                    <div class="map-line ml2"></div>
                    <div class="map-line ml3"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- RESULTS -->
<section class="results reveal" id="results">
    <div class="results-left">
        <div class="section-tag">Resultados comprovados</div>
        <h2>Resultados reais para o <span style="background:linear-gradient(135deg,var(--accent),var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">seu negócio</span></h2>
        <p style="font-size:16px;color:var(--muted);margin-top:16px;">Operadores e transportadoras parceiras reportam ganhos expressivos de produtividade em menos de 90 dias.</p>
    </div>
    <div class="results-grid">
        <div class="r-card reveal">
            <div class="r-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="r-number">-23%</div>
            <div class="r-label">Redução média de custos</div>
        </div>
        <div class="r-card reveal">
            <div class="r-icon"><i class="fas fa-chart-line"></i></div>
            <div class="r-number">+35%</div>
            <div class="r-label">Aumento de eficiência operacional</div>
        </div>
        <div class="r-card reveal">
            <div class="r-icon"><i class="fas fa-box"></i></div>
            <div class="r-number">+28%</div>
            <div class="r-label">Crescimento médio de entregas</div>
        </div>
        <div class="r-card reveal">
            <div class="r-icon"><i class="fas fa-clock"></i></div>
            <div class="r-number">98,6%</div>
            <div class="r-label">De entregas no prazo</div>
        </div>
    </div>
</section>

<!-- CLIENTS & SOCIAL PROOF -->
<section class="clients reveal" id="clients">
    <div class="clients-intro">
        <div class="section-tag">Desenvolvimento em Parceria</div>
        <h2>Nascido na Estrada, Validado na Operação Real</h2>
        <p>Desenvolvemos a plataforma do Thiga diretamente de dentro da operação real da Thiga Logística. Cada automação foi exaustivamente testada em rotas nacionais e condições de estrada reais.</p>
    </div>
    <div class="clients-rating">
        <span class="stars">★★★★★</span>
        <span class="rating-val">100% de Confiabilidade Operacional</span>
    </div>
    
    <div class="case-showcase" style="background:var(--bg3); border:1px solid rgba(255,107,53,0.15); border-radius:20px; padding:40px; max-width:800px; margin:0 auto; text-align:left; box-shadow: 0 15px 35px rgba(0,0,0,0.3);">
        <div style="display:flex; align-items:center; gap:15px; margin-bottom:20px; flex-wrap:wrap;">
            <div style="font-size:32px;">🏢</div>
            <div>
                <h4 style="color:var(--text); font-size:20px; font-weight:700; margin:0;">Thiga Logística</h4>
                <p style="color:var(--accent); font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:1px; margin-top:2px;">Parceiro de Desenvolvimento & Operador Fundador</p>
            </div>
        </div>
        <p style="color:var(--muted); font-size:14px; line-height:1.7; margin-bottom:25px; font-style:italic;">
            "Antes de utilizarmos o Thiga, nossa equipe perdia metade do dia calculando valores de frete e ligando para motoristas para reportar status. Co-desenvolvemos a plataforma para automatizar as cotações via WhatsApp, unificar o controle de despesas de rota e gerar relatórios financeiros instantâneos. Hoje, toda nossa operação roda de forma autônoma."
        </p>
        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:15px; text-align:center;">
            <div style="background:var(--bg2); padding:15px; border-radius:10px; border:1px solid var(--border);">
                <div style="color:var(--accent); font-size:22px; font-weight:800;">100%</div>
                <div style="color:var(--muted); font-size:10px; margin-top:5px; text-transform:uppercase; letter-spacing:0.5px;">Cargas Roteirizadas</div>
            </div>
            <div style="background:var(--bg2); padding:15px; border-radius:10px; border:1px solid var(--border);">
                <div style="color:var(--accent); font-size:22px; font-weight:800;">-23%</div>
                <div style="color:var(--muted); font-size:10px; margin-top:5px; text-transform:uppercase; letter-spacing:0.5px;">Custos de Viagem</div>
            </div>
            <div style="background:var(--bg2); padding:15px; border-radius:10px; border:1px solid var(--border);">
                <div style="color:var(--accent); font-size:22px; font-weight:800;">98,6%</div>
                <div style="color:var(--muted); font-size:10px; margin-top:5px; text-transform:uppercase; letter-spacing:0.5px;">Entregas no Prazo</div>
            </div>
        </div>
    </div>
</section>

<!-- PRICING -->
<section class="pricing reveal" id="pricing" style="background: var(--bg2); padding: 100px 64px; text-align: center;">
    <div class="section-tag">Preços simples e transparentes</div>
    <h2 style="font-size: 40px; font-weight: 800; margin-bottom: 16px;">Planos que cabem no seu negócio</h2>
    <p class="section-sub" style="margin-left: auto; margin-right: auto;">Escolha o plano ideal para modernizar sua transportadora e reduzir seus custos operacionais hoje mesmo.</p>
    
    <div class="pricing-grid">
        @php
            $featureLabels = [
                'basic_tracking' => 'Rastreamento Básico',
                'email_support' => 'Suporte por E-mail',
                'basic_reports' => 'Relatórios Básicos',
                'user_management' => 'Gestão de Usuários',
                'advanced_tracking' => 'Rastreamento Avançado',
                'whatsapp_ai' => 'Assistente IA no WhatsApp',
                'fiscal_integration' => 'Integração Fiscal',
                'api_access' => 'Acesso via API',
                'advanced_reports' => 'Relatórios Avançados',
                'route_optimization' => 'Otimização de Rotas',
                'priority_support' => 'Suporte Prioritário',
                'all_features' => 'Todas as Funcionalidades',
                'custom_integrations' => 'Integrações Customizadas',
                'white_label' => 'White Label / Personalizado',
                'dedicated_support' => 'Gerente de Contas Dedicado',
                'custom_reports' => 'Relatórios Customizados',
                'advanced_analytics' => 'Analytics Avançado'
            ];
        @endphp

        @forelse(($plans ?? []) as $plan)
            <div class="price-card {{ $plan->is_popular ? 'popular' : '' }}">
                @if($plan->is_popular)
                    <span class="price-badge">Mais Popular</span>
                @endif
                <h4>{{ $plan->name }}</h4>
                <p class="desc">{{ $plan->description }}</p>
                <div class="price">
                    @if($plan->price > 0)
                        R$ {{ number_format($plan->price, 0, ',', '.') }}<span>/mês</span>
                    @else
                        Sob Consulta
                    @endif
                </div>
                <ul class="price-features">
                    @if($plan->features)
                        @foreach($plan->features as $feature)
                            <li>
                                <i class="fas fa-check"></i> 
                                {{ $featureLabels[$feature] ?? ucfirst(str_replace('_', ' ', $feature)) }}
                            </li>
                        @endforeach
                    @endif
                </ul>
                <a href="{{ route('register', ['plan' => $plan->id]) }}" class="price-btn">
                    {{ $plan->price > 0 ? 'Começar Agora' : 'Falar com Consultor' }}
                </a>
            </div>
        @empty
            <!-- Fallback se não houver planos no banco de dados -->
            <div class="price-card">
                <h4>Starter</h4>
                <p class="desc">Para transportadoras iniciando a automação</p>
                <div class="price">R$ 299<span>/mês</span></div>
                <ul class="price-features">
                    <li><i class="fas fa-check"></i> Até 300 Cargas/mês</li>
                    <li><i class="fas fa-check"></i> Controle de Frota & Motoristas</li>
                    <li><i class="fas fa-check"></i> Roteirização de Viagens</li>
                    <li><i class="fas fa-check"></i> Emissão de Propostas & Faturas</li>
                </ul>
                <a href="/register" class="price-btn">Começar Agora</a>
            </div>
            
            <div class="price-card popular">
                <span class="price-badge">Mais Popular</span>
                <h4>Pro</h4>
                <p class="desc">A automação operacional completa com IA</p>
                <div class="price">R$ 599<span>/mês</span></div>
                <ul class="price-features">
                    <li><i class="fas fa-check"></i> Cargas Ilimitadas</li>
                    <li><i class="fas fa-check"></i> Assistente IA no WhatsApp (WuzAPI)</li>
                    <li><i class="fas fa-check"></i> Cotações Automáticas via WhatsApp</li>
                    <li><i class="fas fa-check"></i> Lançamento de Custos via Áudio</li>
                    <li><i class="fas fa-check"></i> Painel Financeiro Completo</li>
                </ul>
                <a href="/register" class="price-btn">Começar Agora</a>
            </div>
        @endforelse
    </div>
</section>

<!-- CTA FINAL -->
<section class="cta-final reveal">
    <h2>Transforme sua operação hoje</h2>
    <p>Teste grátis por 30 dias. Sem cartão de crédito necessário.</p>
    <div class="cta-btns">
        <a href="/register" class="btn-hero-primary"><i class="fas fa-play"></i> Começar Teste Grátis</a>
        <a href="/login" class="btn-hero-secondary">Já tenho conta</a>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-logo">tms</div>
    <div class="footer-text">© {{ date('Y') }} TMS LOG. Todos os direitos reservados.</div>
</footer>

<script>
    // Scroll reveal
    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); } });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
</body>
</html>
