<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ยินดีต้อนรับ</title>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Sarabun:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary: #3E4E3C;
            --primary-dark: #2A3628;
            --primary-light: #ECEFE6;
            --bg-cream: #FAF8F5;
            --border-cream: #E6E2D8;
            --slate-dark: #2C362B;
            --slate-gray: #6B7280;
            --font-main: 'Plus Jakarta Sans', 'Sarabun', system-ui, sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background-color: var(--bg-cream);
            background-image: linear-gradient(to right, rgba(62, 78, 60, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(62, 78, 60, 0.04) 1px, transparent 1px);
            background-size: 40px 40px;
            color: var(--slate-dark);
            font-family: var(--font-main);
            -webkit-font-smoothing: antialiased;
        }

        .welcome-card {
            position: relative;
            width: 100%;
            max-width: 560px;
            background: #ffffff;
            border: 1px solid var(--border-cream);
            border-radius: 6px;
            padding: 48px 40px 40px;
            box-shadow: 0 20px 45px -20px rgba(0, 0, 0, 0.1);
        }

        .file-tab {
            position: absolute;
            top: -1px;
            left: 24px;
            background: var(--slate-dark);
            color: #fff;
            font-family: var(--font-mono);
            font-size: 0.68rem;
            letter-spacing: 0.08em;
            padding: 5px 14px;
            border-radius: 0 0 6px 6px;
        }

        .eyebrow {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            color: var(--primary);
            letter-spacing: 0.08em;
            font-weight: 700;
            text-transform: uppercase;
            margin: 0 0 18px;
        }

        h2 {
            font-size: 1.9rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 0 0 14px;
            color: var(--slate-dark);
        }

        p {
            font-size: 0.98rem;
            line-height: 1.75;
            color: var(--slate-gray);
            margin: 0 0 28px;
        }

        .perforation {
            display: flex;
            margin: 0 -40px 28px;
            padding: 0 40px;
        }

        .perforation span {
            flex: 1;
            border-top: 2px dotted var(--border-cream);
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .stamp-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            font-family: var(--font-mono);
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            border-radius: 999px;
            text-decoration: none;
            transition: all 0.25s ease;
        }

        .stamp-link.primary {
            background: var(--primary);
            color: #fff;
            border: 2px solid var(--primary);
            box-shadow: 0 8px 18px -6px rgba(62, 78, 60, 0.35);
        }

        .stamp-link.primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px) rotate(-1deg);
        }

        .stamp-link.outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--border-cream);
        }

        .stamp-link.outline:hover {
            border-color: var(--primary);
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        @media (max-width: 480px) {
            .welcome-card {
                padding: 40px 24px 32px;
            }

            .perforation {
                margin: 0 -24px 24px;
                padding: 0 24px;
            }
        }
    </style>
</head>

<body>
    <div class="welcome-card">
        <span class="file-tab">NO. 000</span>

        <p class="eyebrow">Aphatsara — Welcome</p>
        <h2>ยินดีต้อนรับ</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Minus facilis quas nisi a ut ipsa officia rem
            officiis possimus ex est et, expedita ad reprehenderit mollitia unde. Accusamus, minus perferendis.</p>

        <div class="perforation"><span></span></div>

        <div class="actions">
            <a href="about" class="stamp-link primary"><i class="bi bi-person-badge"></i> About</a>
            <a href="blog" class="stamp-link outline"><i class="bi bi-journal-text"></i> Blog</a>
        </div>
    </div>
</body>

</html>
