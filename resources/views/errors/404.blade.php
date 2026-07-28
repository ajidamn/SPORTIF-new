<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | SPORTIF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800;900&family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --tech-blue: #00d4ff;
            --emerald: #00ff88;
            --dark-bg: #030712;
            --dark-surface: #0a0f1e;
            --text-primary: #e2e8f0;
            --text-secondary: rgba(226, 232, 240, 0.6);
            --font-body: 'Inter', sans-serif;
            --font-display: 'Outfit', sans-serif;
            --font-tech: 'Orbitron', sans-serif;
            --glass-border: rgba(255, 255, 255, 0.06);
        }

        body {
            background: var(--dark-bg);
            color: var(--text-primary);
            font-family: var(--font-body);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background:
                radial-gradient(ellipse 80% 50% at 50% 0%, rgba(0,212,255,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 20% 80%, rgba(0,255,136,0.05) 0%, transparent 50%),
                radial-gradient(ellipse 60% 40% at 80% 80%, rgba(255,215,0,0.04) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .error-container {
            text-align: center;
            background: rgba(10, 15, 30, 0.6);
            backdrop-filter: blur(20px) saturate(1.5);
            -webkit-backdrop-filter: blur(20px) saturate(1.5);
            border: 1px solid var(--glass-border);
            padding: 4rem 3rem;
            border-radius: 24px;
            box-shadow: 0 0 40px rgba(0, 212, 255, 0.05), inset 0 0 20px rgba(255, 255, 255, 0.02);
            position: relative;
            max-width: 650px;
            width: 90%;
            z-index: 10;
        }

        .error-code {
            font-family: var(--font-tech);
            font-size: 8rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--tech-blue), var(--emerald));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
            line-height: 1;
            margin-bottom: 0.5rem;
            text-shadow: 0 0 40px rgba(0, 212, 255, 0.3);
        }

        .error-icon {
            font-size: 4rem;
            background: linear-gradient(135deg, var(--tech-blue), var(--emerald));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
            margin-bottom: 1rem;
            display: inline-block;
        }

        .error-title {
            font-family: var(--font-display);
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-top: 1rem;
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
        }

        .error-message {
            color: var(--text-secondary);
            font-size: 1.15rem;
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }

        .btn-action-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-cyber {
            font-family: var(--font-display);
            font-weight: 600;
            padding: 12px 32px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 1rem;
        }

        .btn-cyber-primary {
            background: linear-gradient(135deg, var(--tech-blue), var(--emerald));
            color: var(--dark-bg);
            border: none;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.2);
        }

        .btn-cyber-primary:hover {
            box-shadow: 0 0 30px rgba(0, 212, 255, 0.4);
            transform: translateY(-3px);
            color: var(--dark-bg);
        }

        .btn-cyber-outline {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
        }

        .btn-cyber-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
            color: var(--text-primary);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        .glitch-wrapper {
            position: relative;
        }

        /* Simple floating animation for the icon */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .floating-icon {
            animation: float 4s ease-in-out infinite;
        }

        /* ── GLASS PEDESTAL — LOGOS ─────────── */
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
            margin-bottom: 2rem;
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

        /* ── RESPONSIVE ─────────── */
        @media (max-width: 768px) {
            .error-container {
                padding: 2.5rem 1.5rem;
                border-radius: 16px;
            }
            .error-code {
                font-size: 5rem;
            }
            .error-icon {
                font-size: 3rem;
                margin-bottom: 0.5rem;
            }
            .error-title {
                font-size: 1.6rem;
                margin-top: 0.5rem;
            }
            .error-message {
                font-size: 1rem;
                margin-bottom: 2rem;
            }
            .logo-pedestal {
                padding: 8px 16px;
                gap: 4px;
                margin-bottom: 1.5rem;
            }
            .logo-item {
                padding: 4px;
            }
            .logo-icon img {
                height: 28px !important;
            }
            .btn-action-group {
                flex-direction: column;
                gap: 0.8rem;
            }
            .btn-cyber {
                width: 100%;
                font-size: 0.95rem;
                padding: 10px 24px;
            }
            .btn-cyber-outline {
                margin-bottom: 0;
            }
        }

    </style>
</head>
<body>

    <div class="error-container">
        <!-- LOGOS -->
        <div class="logo-pedestal">
            <div class="logo-item">
                <div class="logo-icon dispora"><img src="{{ asset('logo/1_gerbang-baru.png') }}" alt="Gerbang Baru Jatim" style="height:40px;width:auto;filter:drop-shadow(0 0 10px rgba(0,212,255,0.25));"></div>
            </div>
            <div class="logo-item">
                <div class="logo-icon koni"><img src="{{ asset('logo/2_jatim.png') }}" alt="Jawa Timur" style="height:40px;width:auto;filter:drop-shadow(0 0 10px rgba(255,215,0,0.25));"></div>
            </div>
            <div class="logo-item">
                <div class="logo-icon pramuka"><img src="{{ asset('logo/3_dispora.png') }}" alt="Dispora Jatim" style="height:40px;width:auto;filter:drop-shadow(0 0 10px rgba(239,68,68,0.25));"></div>
            </div>
            <div class="logo-item">
                <div class="logo-icon pemuda"><img src="{{ asset('logo/4_sportif.png') }}" alt="SPORTIF" style="height:40px;width:auto;filter:drop-shadow(0 0 10px rgba(245,158,11,0.25));"></div>
            </div>
        </div>

        <div class="floating-icon">
            <i class="bi bi-radar error-icon"></i>
        </div>
        <div class="glitch-wrapper">
            <h1 class="error-code">404</h1>
        </div>
        <h3 class="error-title">Sinyal Hilang!</h3>
        <p class="error-message">Sistem tidak dapat menemukan rute ke halaman yang Anda tuju. Halaman mungkin telah dihapus, dipindahkan, atau Anda berada di luar jangkauan radar.</p>
        
        <div class="btn-action-group">
            <a href="javascript:history.back()" class="btn-cyber btn-cyber-outline">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ url('/') }}" class="btn-cyber btn-cyber-primary">
                <i class="bi bi-house-door"></i> Ke Beranda Pusat
            </a>
        </div>
    </div>

</body>
</html>
