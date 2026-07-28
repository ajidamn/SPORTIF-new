<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SPORTIF — Sistem Data Keolahragaan, Kepemudaan & Kepramukaan Jawa Timur</title>
    <meta name="description" content="Sistem Informasi Data Keolahragaan, Kepemudaan & Kepramukaan Provinsi Jawa Timur — Immersive Data Universe">
    <link rel="icon" type="image/png" href="{{ asset('logo/4_sportif.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800;900&family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
/* ═══════════════════════════════════════════════════════════════
   IMMERSIVE DARK CINEMATIC LANDING PAGE — SPORTIF DISPORA JATIM
   ═══════════════════════════════════════════════════════════════ */

:root {
    --tech-blue: #00d4ff;
    --tech-blue-dim: #0088aa;
    --victory-gold: #ffd700;
    --victory-gold-dim: #b8960a;
    --emerald: #00ff88;
    --emerald-dim: #00aa5c;
    --neon-purple: #a855f7;
    --dark-bg: #030712;
    --dark-surface: #0a0f1e;
    --dark-card: rgba(10, 15, 35, 0.7);
    --glass-border: rgba(255, 255, 255, 0.06);
    --glass-bg: rgba(255, 255, 255, 0.03);
    --text-primary: #e2e8f0;
    --text-secondary: rgba(226, 232, 240, 0.6);
    --font-body: 'Inter', sans-serif;
    --font-display: 'Outfit', sans-serif;
    --font-tech: 'Orbitron', sans-serif;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html {
    scroll-behavior: smooth;
    scrollbar-width: thin;
    scrollbar-color: var(--tech-blue-dim) var(--dark-bg);
}

body {
    font-family: var(--font-body);
    background: var(--dark-bg);
    color: var(--text-primary);
    overflow-x: hidden;
    line-height: 1.6;
}

::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: var(--dark-bg); }
::-webkit-scrollbar-thumb { background: var(--tech-blue-dim); border-radius: 3px; }

/* ── CANVAS BACKGROUNDS ─────────────────────────────── */
#particle-canvas {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    z-index: 0;
    pointer-events: none;
}

.page-wrapper {
    position: relative;
    z-index: 1;
}

/* ── FLOATING NAVBAR ─────────────────────────────────── */
.cyber-nav {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    background: rgba(3, 7, 18, 0.6);
    backdrop-filter: blur(20px) saturate(1.5);
    -webkit-backdrop-filter: blur(20px) saturate(1.5);
    border: 1px solid var(--glass-border);
    border-radius: 60px;
    padding: 10px 32px;
    display: flex;
    align-items: center;
    gap: 32px;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 0 40px rgba(0, 212, 255, 0.05);
}

.cyber-nav.scrolled {
    background: rgba(3, 7, 18, 0.85);
    box-shadow: 0 8px 40px rgba(0, 0, 0, 0.5), 0 0 60px rgba(0, 212, 255, 0.08);
    top: 12px;
}

.nav-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: var(--text-primary);
    flex-shrink: 0;
}

.nav-brand-icon {
    width: 36px; height: 36px;
    background: linear-gradient(135deg, var(--tech-blue), var(--emerald));
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    color: var(--dark-bg);
    font-weight: 700;
}

.nav-brand-text {
    font-family: var(--font-tech);
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 2px;
    background: linear-gradient(135deg, var(--tech-blue), var(--emerald));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 6px;
    list-style: none;
}

.nav-links a {
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 0.8rem;
    font-weight: 500;
    padding: 8px 16px;
    border-radius: 30px;
    transition: all 0.3s ease;
    white-space: nowrap;
    position: relative;
}

.nav-links a:hover,
.nav-links a.active {
    color: var(--tech-blue);
    background: rgba(0, 212, 255, 0.08);
}

.nav-cta {
    background: linear-gradient(135deg, var(--tech-blue), var(--emerald)) !important;
    color: var(--dark-bg) !important;
    font-weight: 600 !important;
    padding: 8px 20px !important;
    -webkit-text-fill-color: var(--dark-bg) !important;
}

.nav-cta:hover {
    box-shadow: 0 0 20px rgba(0, 212, 255, 0.4) !important;
    transform: scale(1.05);
}

.nav-toggle {
    display: none;
    background: none;
    border: 1px solid var(--glass-border);
    color: var(--text-primary);
    font-size: 1.4rem;
    padding: 4px 8px;
    border-radius: 8px;
    cursor: pointer;
}

/* ── HERO SECTION ────────────────────────────────────── */
.hero-section {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    padding: 120px 24px 80px;
    text-align: center;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background:
        radial-gradient(ellipse 80% 50% at 50% 0%, rgba(0,212,255,0.08) 0%, transparent 60%),
        radial-gradient(ellipse 60% 40% at 20% 80%, rgba(0,255,136,0.05) 0%, transparent 50%),
        radial-gradient(ellipse 60% 40% at 80% 80%, rgba(255,215,0,0.04) 0%, transparent 50%);
    pointer-events: none;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    border-radius: 50px;
    border: 1px solid rgba(0, 212, 255, 0.2);
    background: rgba(0, 212, 255, 0.06);
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--tech-blue);
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 28px;
    animation: fadeInDown 1s ease-out 0.2s both;
}

.hero-badge .dot {
    width: 6px; height: 6px;
    background: var(--emerald);
    border-radius: 50%;
    animation: pulse-dot 2s ease-in-out infinite;
}

@keyframes pulse-dot {
    0%, 100% { opacity: 1; box-shadow: 0 0 4px var(--emerald); }
    50% { opacity: 0.4; box-shadow: 0 0 12px var(--emerald); }
}

.hero-title {
    font-family: var(--font-display);
    font-size: clamp(2.8rem, 7vw, 5.5rem);
    font-weight: 900;
    line-height: 1.05;
    margin-bottom: 24px;
    animation: fadeInUp 1s ease-out 0.4s both;
}

.hero-title .line1 { color: var(--text-primary); }
.hero-title .gradient {
    background: linear-gradient(135deg, var(--tech-blue), var(--emerald), var(--victory-gold));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    background-size: 200% 200%;
    animation: gradientShift 6s ease-in-out infinite;
}
.hero-title .line3 { color: var(--text-primary); }

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.hero-subtitle {
    font-size: clamp(1rem, 2vw, 1.2rem);
    color: var(--text-secondary);
    max-width: 680px;
    margin: 0 auto 40px;
    line-height: 1.8;
    animation: fadeInUp 1s ease-out 0.6s both;
}

/* ── GLASS PEDESTAL — LOGOS (capsule at top) ─────────── */
.logo-pedestal {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 28px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 100px;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    margin-bottom: 28px;
    animation: fadeInDown 1s ease-out 0.2s both;
    position: relative;
    overflow: hidden;
}

.logo-pedestal::before {
    content: '';
    position: absolute;
    top: -1px; left: -1px;
    right: -1px; bottom: -1px;
    border-radius: 100px;
    background: linear-gradient(135deg, rgba(0,212,255,0.12), transparent 40%, transparent 60%, rgba(245,158,11,0.12));
    z-index: -1;
}

.logo-item {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 6px;
    border-radius: 50%;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: default;
    position: relative;
}

.logo-item:hover {
    transform: translateY(-2px) scale(1.1);
}

.logo-icon {
    width: auto; height: auto;
    border-radius: 0;
    display: flex; align-items: center; justify-content: center;
    position: relative;
    transition: all 0.4s ease;
}

.logo-item:hover .logo-icon {
    filter: brightness(1.3);
}

.logo-icon.dispora,
.logo-icon.koni,
.logo-icon.pramuka,
.logo-icon.pemuda {
    background: transparent;
    border: none;
    color: inherit;
}

.logo-label {
    display: none;
}

.logo-item:hover .logo-label { display: none; }

/* ── SECTION SHARED ──────────────────────────────────── */
.section-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
}

.section-label {
    font-family: var(--font-tech);
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: var(--tech-blue);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-label::before {
    content: '';
    width: 24px; height: 1px;
    background: var(--tech-blue);
}

.section-heading {
    font-family: var(--font-display);
    font-size: clamp(2rem, 4vw, 3.2rem);
    font-weight: 800;
    margin-bottom: 16px;
    line-height: 1.15;
}

.section-desc {
    color: var(--text-secondary);
    font-size: 1.05rem;
    max-width: 640px;
    line-height: 1.8;
}

/* ── DYNAMIC PORTRAITS ───────────────────────────────── */
.portraits-section {
    padding: 120px 0;
    position: relative;
    overflow: hidden;
}

.portraits-section::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background:
        radial-gradient(ellipse 50% 50% at 30% 50%, rgba(0,255,136,0.04) 0%, transparent 70%),
        radial-gradient(ellipse 50% 50% at 70% 50%, rgba(0,212,255,0.04) 0%, transparent 70%);
    pointer-events: none;
}

.portraits-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
    margin-top: 60px;
}

.portrait-card {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    background: var(--dark-card);
    border: 1px solid var(--glass-border);
    backdrop-filter: blur(10px);
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: default;
    transform-style: preserve-3d;
    perspective: 1000px;
}

.portrait-card:hover {
    transform: translateY(-12px) rotateX(2deg);
    border-color: rgba(0, 212, 255, 0.2);
    box-shadow:
        0 24px 60px rgba(0, 0, 0, 0.4),
        0 0 60px rgba(0, 212, 255, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.portrait-visual {
    height: 280px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: flex-end;
    justify-content: center;
}

.portrait-glow {
    position: absolute;
    bottom: -20%;
    left: 50%;
    transform: translateX(-50%);
    width: 80%;
    height: 60%;
    border-radius: 50%;
    filter: blur(40px);
    opacity: 0.4;
    transition: opacity 0.5s;
}

.portrait-card:hover .portrait-glow { opacity: 0.7; }

.portrait-figure {
    position: relative;
    z-index: 2;
    font-size: 6rem;
    line-height: 1;
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    filter: drop-shadow(0 8px 24px rgba(0,0,0,0.5));
    display: flex;
    justify-content: center;
    align-items: flex-end;
    height: 100%;
    width: 100%;
}

.portrait-figure img {
    height: 260px;
    width: auto;
    object-fit: contain;
    object-position: bottom;
    filter: drop-shadow(0 10px 20px rgba(0,0,0,0.5));
}

.portrait-card:hover .portrait-figure {
    transform: scale(1.08) translateY(-8px);
}

.portrait-particles {
    position: absolute;
    bottom: 0; left: 0;
    width: 100%; height: 100%;
    pointer-events: none;
}

.portrait-info {
    padding: 24px;
    position: relative;
}

.portrait-info::before {
    content: '';
    position: absolute;
    top: 0; left: 24px; right: 24px;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--glass-border), transparent);
}

.portrait-name {
    font-family: var(--font-display);
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 4px;
}

.portrait-role {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-bottom: 12px;
}

.portrait-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
}

.badge-athlete {
    background: rgba(0, 212, 255, 0.1);
    color: var(--tech-blue);
    border: 1px solid rgba(0, 212, 255, 0.2);
}

.badge-scout {
    background: rgba(0, 255, 136, 0.1);
    color: var(--emerald);
    border: 1px solid rgba(0, 255, 136, 0.2);
}

.badge-youth {
    background: rgba(255, 215, 0, 0.1);
    color: var(--victory-gold);
    border: 1px solid rgba(255, 215, 0, 0.2);
}

/* ── DATA DASHBOARD ──────────────────────────────────── */
.dashboard-section {
    padding: 120px 0;
    position: relative;
    overflow: hidden;
}

.dashboard-section::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background:
        radial-gradient(ellipse 70% 50% at 50% 50%, rgba(0,212,255,0.06) 0%, transparent 70%);
    pointer-events: none;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    margin-top: 60px;
}

.stat-card {
    position: relative;
    padding: 40px 28px;
    border-radius: 24px;
    background: var(--dark-card);
    border: 1px solid var(--glass-border);
    backdrop-filter: blur(10px);
    text-align: center;
    overflow: hidden;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card:hover {
    transform: translateY(-8px);
    border-color: rgba(0, 212, 255, 0.15);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 0 0 40px rgba(0, 212, 255, 0.06);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 3px;
    border-radius: 3px 3px 0 0;
}

.stat-card:nth-child(1)::before { background: linear-gradient(90deg, var(--tech-blue), transparent); }
.stat-card:nth-child(2)::before { background: linear-gradient(90deg, var(--victory-gold), transparent); }
.stat-card:nth-child(3)::before { background: linear-gradient(90deg, var(--emerald), transparent); }
.stat-card:nth-child(4)::before { background: linear-gradient(90deg, var(--neon-purple), transparent); }

.stat-icon {
    width: 56px; height: 56px;
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 20px;
    position: relative;
}

.stat-icon::after {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 20px;
    opacity: 0.15;
    filter: blur(8px);
}

.stat-number {
    font-family: var(--font-tech);
    font-size: clamp(2.5rem, 4vw, 3.5rem);
    font-weight: 800;
    line-height: 1;
    margin-bottom: 8px;
    position: relative;
}

.stat-label {
    font-size: 0.85rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.stat-sub {
    font-size: 0.72rem;
    color: var(--emerald);
    margin-top: 8px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

/* ── VENUE GALLERY ───────────────────────────────────── */
.venue-section {
    padding: 120px 0;
    position: relative;
    overflow: hidden;
}

.venue-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-top: 60px;
}

.venue-card {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    height: 320px;
    cursor: pointer;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid var(--glass-border);
}

.venue-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4), 0 0 40px rgba(0, 212, 255, 0.1);
    border-color: rgba(0, 212, 255, 0.2);
}

.venue-bg {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.8s ease, filter 0.5s ease;
}

.venue-card:hover .venue-bg {
    transform: scale(1.1);
    filter: brightness(0.7);
}

.venue-blueprint {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    opacity: 0;
    transition: opacity 0.5s ease;
    background: linear-gradient(135deg, rgba(0,212,255,0.15), rgba(0,255,136,0.1));
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.venue-card:hover .venue-blueprint {
    opacity: 1;
}

.venue-blueprint-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(0,212,255,0.06) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,212,255,0.06) 1px, transparent 1px);
    background-size: 30px 30px;
    animation: gridScroll 8s linear infinite;
}

@keyframes gridScroll {
    0% { transform: translate(0, 0); }
    100% { transform: translate(30px, 30px); }
}

.venue-overlay {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    padding: 24px;
    background: linear-gradient(to top, rgba(3, 7, 18, 0.95) 0%, rgba(3, 7, 18, 0.6) 50%, transparent 100%);
    transform: translateY(20px);
    opacity: 0;
    transition: all 0.5s ease;
}

.venue-card:hover .venue-overlay {
    transform: translateY(0);
    opacity: 1;
}

.venue-name {
    font-family: var(--font-display);
    font-size: 1.15rem;
    font-weight: 700;
    margin-bottom: 6px;
}

.venue-location {
    font-size: 0.8rem;
    color: var(--tech-blue);
    display: flex;
    align-items: center;
    gap: 6px;
}

.venue-tag {
    position: absolute;
    top: 16px; right: 16px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    background: rgba(3, 7, 18, 0.6);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    color: var(--tech-blue);
    z-index: 2;
}

/* ── SYSTEM EXPLANATION ──────────────────────────────── */
.system-section {
    padding: 120px 0;
    position: relative;
    overflow: hidden;
}

.system-section::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background:
        radial-gradient(ellipse 60% 40% at 30% 50%, rgba(0,212,255,0.05) 0%, transparent 60%),
        radial-gradient(ellipse 60% 40% at 70% 60%, rgba(0,255,136,0.04) 0%, transparent 60%);
    pointer-events: none;
}

.system-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 80px;
    align-items: center;
    margin-top: 60px;
}

.system-visual {
    position: relative;
    height: 500px;
}

.topo-map {
    position: absolute;
    inset: 0;
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid var(--glass-border);
}

.topo-map canvas {
    width: 100%;
    height: 100%;
}

.system-content {
    position: relative;
}

.mission-block {
    padding: 32px;
    border-radius: 20px;
    background: var(--dark-card);
    border: 1px solid var(--glass-border);
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    transition: all 0.4s ease;
}

.mission-block:hover {
    border-color: rgba(0, 212, 255, 0.15);
    transform: translateX(8px);
}

.mission-block::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 3px; height: 100%;
}

.mission-block:nth-child(1)::before { background: var(--tech-blue); }
.mission-block:nth-child(2)::before { background: var(--victory-gold); }
.mission-block:nth-child(3)::before { background: var(--emerald); }

.mission-number {
    font-family: var(--font-tech);
    font-size: 0.65rem;
    font-weight: 700;
    color: var(--tech-blue);
    letter-spacing: 3px;
    margin-bottom: 12px;
}

.mission-title {
    font-family: var(--font-display);
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 8px;
}

.mission-desc {
    font-size: 0.9rem;
    color: var(--text-secondary);
    line-height: 1.7;
}

/* ── CTA FOOTER ──────────────────────────────────────── */
.cta-section {
    padding: 100px 0;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.cta-section::before {
    content: '';
    position: absolute;
    bottom: 0; left: 50%;
    transform: translateX(-50%);
    width: 80%;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--glass-border), transparent);
}

.cta-glow {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 600px; height: 300px;
    background: radial-gradient(ellipse, rgba(0,212,255,0.08) 0%, transparent 70%);
    pointer-events: none;
}

.cta-title {
    font-family: var(--font-display);
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 800;
    margin-bottom: 16px;
    position: relative;
}

.cta-desc {
    color: var(--text-secondary);
    font-size: 1.05rem;
    max-width: 600px;
    margin: 0 auto 40px;
    line-height: 1.8;
}

.cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 16px 40px;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.cta-btn-primary {
    background: linear-gradient(135deg, var(--tech-blue), var(--emerald));
    color: var(--dark-bg);
    border: none;
}

.cta-btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 40px rgba(0, 212, 255, 0.3), 0 0 60px rgba(0, 212, 255, 0.1);
    color: var(--dark-bg);
}

.cta-btn-outline {
    background: transparent;
    color: var(--text-primary);
    border: 1px solid var(--glass-border);
    margin-left: 16px;
}

.cta-btn-outline:hover {
    border-color: var(--tech-blue);
    color: var(--tech-blue);
    box-shadow: 0 0 30px rgba(0, 212, 255, 0.1);
}

/* ── FOOTER ──────────────────────────────────────────── */
.cyber-footer {
    padding: 60px 0 30px;
    border-top: 1px solid var(--glass-border);
}

.footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1.5fr;
    gap: 40px;
    margin-bottom: 40px;
}

.footer-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.footer-desc {
    color: var(--text-secondary);
    font-size: 0.85rem;
    line-height: 1.7;
    max-width: 320px;
}

.footer-heading {
    font-family: var(--font-tech);
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--text-primary);
    margin-bottom: 20px;
}

.footer-links {
    list-style: none;
}

.footer-links li { margin-bottom: 10px; }

.footer-links a {
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 0.85rem;
    transition: color 0.3s;
}

.footer-links a:hover { color: var(--tech-blue); }

.footer-contact {
    list-style: none;
}

.footer-contact li {
    color: var(--text-secondary);
    font-size: 0.85rem;
    margin-bottom: 10px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.footer-contact i { color: var(--tech-blue); margin-top: 3px; }

.footer-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 24px;
    border-top: 1px solid var(--glass-border);
    font-size: 0.8rem;
    color: var(--text-secondary);
}

/* ── ANIMATIONS ──────────────────────────────────────── */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.reveal {
    opacity: 0;
    transform: translateY(50px);
    transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.reveal.active {
    opacity: 1;
    transform: translateY(0);
}

.reveal-delay-1 { transition-delay: 0.1s; }
.reveal-delay-2 { transition-delay: 0.2s; }
.reveal-delay-3 { transition-delay: 0.3s; }
.reveal-delay-4 { transition-delay: 0.4s; }

/* ── RESPONSIVE ──────────────────────────────────────── */
@media (max-width: 1024px) {
    .portraits-grid { grid-template-columns: repeat(2, 1fr); }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .venue-grid { grid-template-columns: repeat(2, 1fr); }
    .system-grid { grid-template-columns: 1fr; gap: 40px; }
    .system-visual { height: 350px; }
    .footer-grid { grid-template-columns: 1fr 1fr; }
}

@media (max-width: 768px) {
    .cyber-nav {
        left: 16px; right: 16px;
        transform: none;
        padding: 10px 20px;
        flex-wrap: wrap;
    }
    .nav-toggle { display: block; }
    .nav-links {
        display: none;
        width: 100%;
        flex-direction: column;
        padding: 12px 0;
        gap: 4px;
    }
    .nav-links.show { display: flex; }
    .nav-links a { width: 100%; text-align: center; }

    .portraits-grid { grid-template-columns: 1fr; max-width: 400px; margin-left: auto; margin-right: auto; }
    .stats-grid { grid-template-columns: 1fr; max-width: 400px; margin-left: auto; margin-right: auto; }
    .venue-grid { grid-template-columns: 1fr; }
    .system-visual { height: 280px; }
    .footer-grid { grid-template-columns: 1fr; }
    .footer-bottom { flex-direction: column; gap: 8px; text-align: center; }
    .logo-pedestal { padding: 20px; gap: 16px; }
    .logo-item { padding: 10px 16px; }
    .logo-icon { width: 48px; height: 48px; font-size: 1.3rem; }
}

/* ── FLOATING SCAN LINE ──────────────────────────────── */
.scan-line {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 2px;
    background: linear-gradient(90deg, transparent, var(--tech-blue), transparent);
    opacity: 0.15;
    z-index: 9999;
    pointer-events: none;
    animation: scanDown 8s linear infinite;
}

@keyframes scanDown {
    0% { top: -2px; }
    100% { top: 100vh; }
}

/* ── CUSTOM CURSOR GLOW ──────────────────────────────── */
.cursor-glow {
    position: fixed;
    width: 300px; height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,212,255,0.06) 0%, transparent 70%);
    pointer-events: none;
    z-index: 0;
    transform: translate(-50%, -50%);
    transition: opacity 0.3s;
}

/* ── Venue placeholder backgrounds ───────────────────── */
.venue-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    font-size: 3rem;
    position: relative;
}

.venue-placeholder i {
    opacity: 0.3;
    filter: drop-shadow(0 0 20px currentColor);
}

.venue-placeholder span {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    opacity: 0.3;
}

/* ── CUSTOM FIXES & ADDITIONS ───────────────────────── */
.logo-icon img {
    height: 40px;
    width: auto;
    transition: height 0.3s ease;
}

.hero-leader-wrapper {
    position: absolute;
    bottom: 0;
    z-index: 0;
    pointer-events: none;
    text-align: center;
    opacity: 0.9;
}

.hero-leader-wrapper.left {
    left: 2%;
    animation: slideInLeft 2.5s cubic-bezier(0.16, 1, 0.3, 1) forwards, floatLeaderLeft 8s ease-in-out infinite 2.5s;
    transform: translateX(-100px);
    opacity: 0;
}

.hero-leader-wrapper.right {
    right: 2%;
    animation: slideInRight 2.5s cubic-bezier(0.16, 1, 0.3, 1) forwards, floatLeaderRight 8s ease-in-out infinite 2.5s;
    transform: translateX(100px);
    opacity: 0;
}

.hero-leader-img {
    width: 380px;
    filter: drop-shadow(0 0 30px rgba(0, 212, 255, 0.2));
    display: block;
    margin: 0 auto;
    mask-image: linear-gradient(to top, transparent 0%, black 15%);
    -webkit-mask-image: linear-gradient(to top, transparent 0%, black 15%);
}

.hero-leader-info {
    margin-top: -30px;
    background: rgba(10, 15, 30, 0.7);
    border: 1px solid rgba(0, 212, 255, 0.2);
    border-radius: 12px;
    padding: 10px 15px;
    backdrop-filter: blur(10px);
    display: inline-block;
    position: relative;
    z-index: 2;
    box-shadow: 0 4px 15px rgba(0,0,0,0.5);
}

.hero-leader-info h4 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 2px 0;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.hero-leader-info p {
    font-size: 0.75rem;
    color: var(--tech-blue);
    margin: 0;
}

@keyframes slideInLeft {
    to { transform: translateX(0); opacity: 0.9; }
}
@keyframes slideInRight {
    to { transform: translateX(0); opacity: 0.9; }
}

@keyframes floatLeaderLeft {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

@keyframes floatLeaderRight {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

@media (max-width: 1400px) {
    .hero-leader-img { width: 300px; }
    .hero-leader-wrapper.left { left: 0; }
    .hero-leader-wrapper.right { right: 0; }
}

@media (max-width: 1024px) {
    .hero-leader-img { width: 280px; }
    .hero-leader-wrapper.left { left: -40px; opacity: 0.5 !important; }
    .hero-leader-wrapper.right { right: -40px; opacity: 0.5 !important; }
    .hero-leader-info { display: none; } /* Hide text tags on mobile to avoid clutter */
}

@media (max-width: 768px) {
    .hero-leader-img { width: 220px; }
    .hero-leader-wrapper.left { left: -60px; opacity: 0.25 !important; }
    .hero-leader-wrapper.right { right: -60px; opacity: 0.25 !important; }
}

@media (max-width: 768px) {
    .logo-icon img { height: 28px !important; }
}

/* ── ORG & INFO SECTION ───────────────────────── */
.org-card {
    min-width: 280px;
    background: var(--dark-surface);
    border: 1px solid var(--glass-border);
    border-radius: 16px;
    padding: 32px 24px;
    text-align: center;
    transition: transform 0.3s;
    scroll-snap-align: start;
    flex-shrink: 0;
}
.org-card:hover {
    transform: translateY(-5px);
    border-color: rgba(0, 255, 136, 0.4);
}
.org-card img {
    width: 90px;
    height: 90px;
    object-fit: contain;
    margin-bottom: 20px;
}
.org-card h4 {
    font-size: 1.15rem;
    color: var(--text-primary);
    margin-bottom: 8px;
    font-weight: 600;
}
.org-card p {
    font-size: 0.9rem;
    color: var(--text-secondary);
    margin: 0;
}
.info-card:hover {
    transform: translateY(-8px);
    border-color: rgba(255, 215, 0, 0.4) !important;
}
.org-list-wrapper::-webkit-scrollbar { height: 8px; }
.org-list-wrapper::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 4px; }
.org-list-wrapper::-webkit-scrollbar-thumb { background: rgba(0,255,136,0.3); border-radius: 4px; }

/* ── EVENT CALENDAR SECTION ───────────────────────── */
.event-section {
    padding: 100px 0;
    position: relative;
    background: linear-gradient(180deg, var(--dark-bg) 0%, rgba(0,212,255,0.02) 100%);
}
.event-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-top: 40px;
}
.calendar-wrapper {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--glass-border);
    border-radius: 24px;
    padding: 24px;
    backdrop-filter: blur(10px);
}
.event-list-wrapper {
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 420px;
    overflow-y: auto;
    padding-right: 10px;
}
.event-list-wrapper::-webkit-scrollbar { width: 6px; }
.event-list-wrapper::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 4px; }
.event-list-wrapper::-webkit-scrollbar-thumb { background: rgba(0,212,255,0.3); border-radius: 4px; }
.event-card {
    background: rgba(10, 15, 30, 0.6);
    border: 1px solid rgba(0, 212, 255, 0.1);
    border-radius: 16px;
    padding: 20px;
    transition: all 0.3s ease;
    display: flex;
    gap: 16px;
    align-items: center;
    cursor: pointer;
}
.event-card:hover {
    transform: translateX(5px);
    border-color: var(--tech-blue);
    background: rgba(0, 212, 255, 0.05);
}
.event-date-box {
    background: linear-gradient(135deg, rgba(0,212,255,0.1), transparent);
    border: 1px solid rgba(0,212,255,0.2);
    border-radius: 12px;
    min-width: 70px;
    text-align: center;
    padding: 10px 0;
    color: var(--tech-blue);
}
.event-date-box .day { font-size: 1.5rem; font-weight: 800; line-height: 1; }
.event-date-box .month { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; }
.event-details h4 { font-family: var(--font-display); font-size: 1.1rem; margin-bottom: 4px; color: var(--text-primary); }
.event-details p { font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0; }

/* ── ORGANISASI SECTION ───────────────────────────── */
.organisasi-section {
    padding: 100px 0;
    background: linear-gradient(180deg, transparent, rgba(0,255,136,0.02) 50%, transparent);
}
.org-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 24px;
    margin-top: 40px;
}
.org-card {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    padding: 30px 20px;
    text-align: center;
    transition: all 0.4s ease;
}
.org-card:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.05);
    border-color: var(--emerald);
    box-shadow: 0 10px 30px rgba(0, 255, 136, 0.1);
}
.org-card img {
    height: 60px;
    margin-bottom: 16px;
    filter: drop-shadow(0 0 10px rgba(255,255,255,0.1));
}
.org-card h4 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 4px;
}
.org-card p {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .event-grid { grid-template-columns: 1fr; }
}
    </style>
    @stack('styles')
</head>
<body>

<!-- Scan Line Effect -->
<div class="scan-line"></div>

<!-- Cursor Glow -->
<div class="cursor-glow" id="cursorGlow"></div>

<!-- Particle Background -->
<canvas id="particle-canvas"></canvas>

<div class="page-wrapper">

    <!-- ══════════════════════════════════════════════════════
         NAVIGATION
         ══════════════════════════════════════════════════════ -->
    <nav class="cyber-nav" id="cyberNav">
        <a href="{{ route('landing') }}" class="nav-brand">
            <img src="{{ asset('logo/4_sportif.png') }}" alt="SPORTIF" style="height:34px;width:auto;filter:drop-shadow(0 0 8px rgba(245,158,11,0.3));">
        </a>
        <button class="nav-toggle" onclick="document.querySelector('.nav-links').classList.toggle('show')">
            <i class="bi bi-list"></i>
        </button>
        <ul class="nav-links">
            <li><a href="{{ route('landing') }}">Beranda</a></li>
            <li><a href="{{ route('orang.public') }}" class="{{ request()->routeIs('orang.public') ? 'active' : '' }}">Data SDM</a></li>
            <li><a href="{{ route('prasarana.public') }}" class="{{ request()->routeIs('prasarana.public') ? 'active' : '' }}">Prasarana</a></li>
            <li><a href="{{ route('organisasi.public') }}" class="{{ request()->routeIs('organisasi.public') ? 'active' : '' }}">Organisasi</a></li>
            <li><a href="{{ route('informasi.index') }}" class="{{ request()->routeIs('informasi.*') ? 'active' : '' }}">Informasi</a></li>
            <li><a href="{{ route('admin.login') }}" class="nav-cta"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
        </ul>
    </nav>
    
    <main>@yield('content')</main>
    <!-- ══════════════════════════════════════════════════════
         FOOTER
         ══════════════════════════════════════════════════════ -->
    <footer class="cyber-footer">
        <div class="section-container">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand">
                        <img src="{{ asset('logo/4_sportif.png') }}" alt="SPORTIF" style="height:38px;width:auto;filter:drop-shadow(0 0 8px rgba(245,158,11,0.3));">
                    </div>
                    <p class="footer-desc">
                        Sistem Informasi Pengelolaan Data Keolahragaan, Kepemudaan & Kepramukaan
                        Provinsi Jawa Timur — Dinas Kepemudaan dan Olahraga.
                    </p>
                </div>
                <div>
                    <div class="footer-heading">Menu</div>
                    <ul class="footer-links">
                        <li><a href="{{ route('landing') }}">Beranda</a></li>
                        <li><a href="{{ route('orang.public') }}">Data SDM</a></li>
                        <li><a href="{{ route('prasarana.public') }}">Prasarana</a></li>
                        <li><a href="{{ route('informasi.index') }}">Informasi</a></li>
                    </ul>
                </div>
                <div>
                    <div class="footer-heading">Layanan</div>
                    <ul class="footer-links">
                        <li><a href="#">Olahraga Prestasi</a></li>
                        <li><a href="#">Olahraga Masyarakat</a></li>
                        <li><a href="#">Kepemudaan</a></li>
                        <li><a href="#">Kepramukaan</a></li>
                    </ul>
                </div>
                <div>
                    <div class="footer-heading">Kontak</div>
                    <ul class="footer-contact">
                        <li><i class="bi bi-geo-alt"></i> Jl. Kayoon No.56, Surabaya, Jawa Timur</li>
                        <li><i class="bi bi-telephone"></i> (031) 5344927</li>
                        <li><i class="bi bi-envelope"></i> dispora@jatimprov.go.id</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; {{ date('Y') }} Dinas Kepemudaan dan Olahraga Provinsi Jawa Timur</span>
                <span>SPORTIF v2.0 — Immersive Edition</span>
            </div>
        </div>
    </footer>
</div>

<!-- ══════════════════════════════════════════════════════
     SCRIPTS
     ══════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', () => {

    // ── PARTICLE BACKGROUND SYSTEM ──────────────────────
    const canvas = document.getElementById('particle-canvas');
    const ctx = canvas.getContext('2d');
    let particles = [];
    let mouse = { x: -1000, y: -1000 };
    let animFrame;

    function resizeCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    class Particle {
        constructor() {
            this.reset();
        }
        reset() {
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * canvas.height;
            this.size = Math.random() * 2 + 0.5;
            this.speedX = (Math.random() - 0.5) * 0.3;
            this.speedY = (Math.random() - 0.5) * 0.3;
            this.opacity = Math.random() * 0.5 + 0.1;
            // Random neon color
            const colors = [
                '0, 212, 255',   // tech-blue
                '0, 255, 136',   // emerald
                '255, 215, 0',   // victory-gold
                '168, 85, 247',  // neon-purple
            ];
            this.color = colors[Math.floor(Math.random() * colors.length)];
        }
        update() {
            this.x += this.speedX;
            this.y += this.speedY;

            // Mouse interaction
            const dx = mouse.x - this.x;
            const dy = mouse.y - this.y;
            const dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < 150) {
                this.x -= dx * 0.01;
                this.y -= dy * 0.01;
                this.opacity = Math.min(0.8, this.opacity + 0.02);
            }

            // Wrap around
            if (this.x < 0) this.x = canvas.width;
            if (this.x > canvas.width) this.x = 0;
            if (this.y < 0) this.y = canvas.height;
            if (this.y > canvas.height) this.y = 0;
        }
        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(${this.color}, ${this.opacity})`;
            ctx.fill();
        }
    }

    // Create particles
    const particleCount = Math.min(120, Math.floor((canvas.width * canvas.height) / 12000));
    for (let i = 0; i < particleCount; i++) {
        particles.push(new Particle());
    }

    function connectParticles() {
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 120) {
                    const opacity = (1 - dist / 120) * 0.15;
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = `rgba(0, 212, 255, ${opacity})`;
                    ctx.lineWidth = 0.5;
                    ctx.stroke();
                }
            }
        }
    }

    function animateParticles() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        particles.forEach(p => {
            p.update();
            p.draw();
        });
        connectParticles();
        animFrame = requestAnimationFrame(animateParticles);
    }
    animateParticles();

    // Mouse tracking
    document.addEventListener('mousemove', e => {
        mouse.x = e.clientX;
        mouse.y = e.clientY;

        // Update cursor glow
        const glow = document.getElementById('cursorGlow');
        if (glow) {
            glow.style.left = e.clientX + 'px';
            glow.style.top = e.clientY + 'px';
        }
    });

    // ── PORTRAIT CARD PARTICLES ─────────────────────────
    document.querySelectorAll('.portrait-particles').forEach(cvs => {
        const pCtx = cvs.getContext('2d');
        const color = cvs.dataset.color || '0, 212, 255';
        let w, h;

        function resize() {
            const rect = cvs.parentElement.getBoundingClientRect();
            w = cvs.width = rect.width;
            h = cvs.height = rect.height;
        }
        resize();
        window.addEventListener('resize', resize);

        const dots = [];
        for (let i = 0; i < 25; i++) {
            dots.push({
                x: Math.random() * w,
                y: h - Math.random() * h * 0.6,
                size: Math.random() * 2 + 0.5,
                speedY: -(Math.random() * 0.2 + 0.1),
                speedX: (Math.random() - 0.5) * 0.1,
                opacity: Math.random() * 0.6 + 0.1,
            });
        }

        let isPortraitVisible = false;
        function animate() {
            if(!isPortraitVisible) return;
            pCtx.clearRect(0, 0, w, h);
            dots.forEach(d => {
                d.y += d.speedY;
                d.x += d.speedX;
                d.opacity -= 0.001;
                if (d.y < 0 || d.opacity <= 0) {
                    d.y = h;
                    d.x = Math.random() * w;
                    d.opacity = Math.random() * 0.6 + 0.1;
                }
                pCtx.beginPath();
                pCtx.arc(d.x, d.y, d.size, 0, Math.PI * 2);
                pCtx.fillStyle = `rgba(${color}, ${d.opacity})`;
                pCtx.fill();
            });
            requestAnimationFrame(animate);
        }
        
        const portraitObserver = new IntersectionObserver((entries) => {
            if(entries[0].isIntersecting) {
                if(!isPortraitVisible) {
                    isPortraitVisible = true;
                    animate();
                }
            } else {
                isPortraitVisible = false;
            }
        });
        portraitObserver.observe(cvs);
    });

    // ── TOPOGRAPHIC MAP CANVAS ──────────────────────────
    const topoCanvas = document.getElementById('topoCanvas');
    if (topoCanvas) {
        const tCtx = topoCanvas.getContext('2d');
        let tw, th;

        function resizeTopo() {
            const rect = topoCanvas.parentElement.getBoundingClientRect();
            tw = topoCanvas.width = rect.width;
            th = topoCanvas.height = rect.height;
        }
        resizeTopo();
        window.addEventListener('resize', resizeTopo);

        // Generate topo nodes (cities of East Java)
        const cities = [
            { name: 'Surabaya', x: 0.58, y: 0.35, size: 8, color: '0, 212, 255' },
            { name: 'Malang', x: 0.52, y: 0.55, size: 6, color: '0, 255, 136' },
            { name: 'Sidoarjo', x: 0.6, y: 0.42, size: 5, color: '255, 215, 0' },
            { name: 'Kediri', x: 0.35, y: 0.5, size: 5, color: '168, 85, 247' },
            { name: 'Jember', x: 0.78, y: 0.6, size: 5, color: '0, 212, 255' },
            { name: 'Madiun', x: 0.2, y: 0.35, size: 4, color: '0, 255, 136' },
            { name: 'Blitar', x: 0.42, y: 0.6, size: 4, color: '255, 215, 0' },
            { name: 'Batu', x: 0.48, y: 0.52, size: 4, color: '168, 85, 247' },
            { name: 'Mojokerto', x: 0.48, y: 0.4, size: 4, color: '0, 212, 255' },
            { name: 'Pasuruan', x: 0.6, y: 0.5, size: 4, color: '0, 255, 136' },
            { name: 'Probolinggo', x: 0.68, y: 0.48, size: 4, color: '255, 215, 0' },
            { name: 'Banyuwangi', x: 0.9, y: 0.55, size: 5, color: '0, 212, 255' },
            { name: 'Tuban', x: 0.35, y: 0.2, size: 4, color: '168, 85, 247' },
            { name: 'Lamongan', x: 0.42, y: 0.25, size: 4, color: '0, 255, 136' },
            { name: 'Gresik', x: 0.52, y: 0.28, size: 4, color: '255, 215, 0' },
            { name: 'Bojonegoro', x: 0.28, y: 0.25, size: 4, color: '0, 212, 255' },
            { name: 'Nganjuk', x: 0.3, y: 0.42, size: 3, color: '168, 85, 247' },
            { name: 'Tulungagung', x: 0.38, y: 0.58, size: 3, color: '0, 255, 136' },
            { name: 'Lumajang', x: 0.7, y: 0.58, size: 3, color: '255, 215, 0' },
            { name: 'Situbondo', x: 0.82, y: 0.42, size: 3, color: '0, 212, 255' },
            { name: 'Bondowoso', x: 0.8, y: 0.5, size: 3, color: '0, 255, 136' },
            { name: 'Sampang', x: 0.65, y: 0.2, size: 3, color: '255, 215, 0' },
            { name: 'Sumenep', x: 0.82, y: 0.18, size: 3, color: '168, 85, 247' },
            { name: 'Bangkalan', x: 0.55, y: 0.22, size: 3, color: '0, 212, 255' },
            { name: 'Pamekasan', x: 0.75, y: 0.2, size: 3, color: '0, 255, 136' },
            { name: 'Pacitan', x: 0.15, y: 0.65, size: 3, color: '168, 85, 247' },
            { name: 'Ponorogo', x: 0.22, y: 0.5, size: 3, color: '255, 215, 0' },
            { name: 'Trenggalek', x: 0.32, y: 0.65, size: 3, color: '0, 212, 255' },
            { name: 'Magetan', x: 0.17, y: 0.4, size: 3, color: '0, 255, 136' },
            { name: 'Ngawi', x: 0.18, y: 0.28, size: 3, color: '255, 215, 0' },
        ];

        // Define connections
        const connections = [];
        for (let i = 0; i < cities.length; i++) {
            for (let j = i + 1; j < cities.length; j++) {
                const dx = cities[i].x - cities[j].x;
                const dy = cities[i].y - cities[j].y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 0.18) {
                    connections.push([i, j]);
                }
            }
        }

        let time = 0;
        let pulsePhase = 0;

        function drawTopo() {
            tCtx.clearRect(0, 0, tw, th);
            time += 0.005;
            pulsePhase += 0.02;

            // Draw topo contour lines
            tCtx.strokeStyle = 'rgba(0, 212, 255, 0.03)';
            tCtx.lineWidth = 1;
            for (let i = 0; i < 12; i++) {
                tCtx.beginPath();
                const cx = tw * 0.5 + Math.sin(time + i) * 40;
                const cy = th * 0.5 + Math.cos(time + i * 0.7) * 30;
                const rx = 60 + i * 35;
                const ry = 40 + i * 25;
                tCtx.ellipse(cx, cy, rx, ry, Math.sin(time * 0.3) * 0.2, 0, Math.PI * 2);
                tCtx.stroke();
            }

            // Draw connections
            connections.forEach(([a, b]) => {
                const c1 = cities[a];
                const c2 = cities[b];
                const x1 = c1.x * tw, y1 = c1.y * th;
                const x2 = c2.x * tw, y2 = c2.y * th;

                // Animated pulse along line
                const progress = (Math.sin(time * 2 + a + b) + 1) / 2;

                tCtx.beginPath();
                tCtx.moveTo(x1, y1);
                tCtx.lineTo(x2, y2);
                tCtx.strokeStyle = `rgba(0, 212, 255, 0.08)`;
                tCtx.lineWidth = 0.5;
                tCtx.stroke();

                // Pulse dot
                const px = x1 + (x2 - x1) * progress;
                const py = y1 + (y2 - y1) * progress;
                tCtx.beginPath();
                tCtx.arc(px, py, 1.5, 0, Math.PI * 2);
                tCtx.fillStyle = `rgba(0, 212, 255, 0.4)`;
                tCtx.fill();
            });

            // Draw cities
            cities.forEach((city, idx) => {
                const x = city.x * tw;
                const y = city.y * th;
                const pulse = Math.sin(pulsePhase + idx * 0.5) * 0.3 + 0.7;

                // Glow
                const gradient = tCtx.createRadialGradient(x, y, 0, x, y, city.size * 4);
                gradient.addColorStop(0, `rgba(${city.color}, ${0.2 * pulse})`);
                gradient.addColorStop(1, `rgba(${city.color}, 0)`);
                tCtx.beginPath();
                tCtx.arc(x, y, city.size * 4, 0, Math.PI * 2);
                tCtx.fillStyle = gradient;
                tCtx.fill();

                // Core dot
                tCtx.beginPath();
                tCtx.arc(x, y, city.size * 0.5, 0, Math.PI * 2);
                tCtx.fillStyle = `rgba(${city.color}, ${0.7 * pulse + 0.3})`;
                tCtx.fill();

                // Label
                tCtx.font = '9px Inter';
                tCtx.fillStyle = `rgba(${city.color}, ${0.5 * pulse + 0.2})`;
                tCtx.textAlign = 'center';
                tCtx.fillText(city.name, x, y - city.size - 4);
            });

            if (isTopoVisible) {
                requestAnimationFrame(drawTopo);
            }
        }
        
        let isTopoVisible = false;
        const topoObserver = new IntersectionObserver((entries) => {
            if(entries[0].isIntersecting) {
                if(!isTopoVisible) {
                    isTopoVisible = true;
                    drawTopo();
                }
            } else {
                isTopoVisible = false;
            }
        });
        topoObserver.observe(topoCanvas);
    }

    // ── SCROLL REVEAL ───────────────────────────────────
    const revealElements = document.querySelectorAll('.reveal');
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    revealElements.forEach(el => revealObserver.observe(el));

    // ── COUNTER ANIMATION ───────────────────────────────
    const counters = document.querySelectorAll('.counter');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.dataset.target);
                if (!target || el.dataset.animated) return;
                el.dataset.animated = 'true';

                const duration = 2500;
                const startTime = performance.now();
                const easeOutQuart = t => 1 - Math.pow(1 - t, 4);

                function updateCounter(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const eased = easeOutQuart(progress);
                    const current = Math.floor(eased * target);
                    el.textContent = current.toLocaleString('id-ID');
                    if (progress < 1) {
                        requestAnimationFrame(updateCounter);
                    } else {
                        el.textContent = target.toLocaleString('id-ID');
                    }
                }
                requestAnimationFrame(updateCounter);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(c => counterObserver.observe(c));

    // ── NAVBAR SCROLL ───────────────────────────────────
    window.addEventListener('scroll', () => {
        document.getElementById('cyberNav').classList.toggle('scrolled', window.scrollY > 80);
    });

    // ── MOBILE MENU CLOSE ───────────────────────────────
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            document.querySelector('.nav-links').classList.remove('show');
        });
    });

    // ── PARALLAX TILT ON PORTRAIT CARDS ─────────────────
    document.querySelectorAll('[data-tilt]').forEach(card => {
        card.addEventListener('mousemove', e => {
            const rect = card.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;
            card.style.transform = `perspective(1000px) rotateY(${x * 8}deg) rotateX(${-y * 8}deg) translateY(-12px)`;
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });

}); // end DOMContentLoaded
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
