<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Project</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sarabun:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap"
        rel="stylesheet">

    <style>
        :root {
            /* ==============================
               ORANGE PREMIUM COLOR THEME
            ============================== */

            --ink: #2C2926;
            --ink-light: #5A544E;
            --muted: #77716B;

            --paper: #FAF8F5;
            --line: #E8E1D8;

            /* Main Orange */
            --primary: #D96B27;
            --primary-rgb: 217, 107, 39;

            /* Dark Orange */
            --primary-dark: #A94E1D;

            /* Light Orange */
            --primary-light: #FCEBDD;
            --primary-light-rgb: 252, 235, 221;

            --font-main: 'Plus Jakarta Sans', 'Sarabun', system-ui, sans-serif;

            /* Shared aliases */
            --font-mono: 'JetBrains Mono', monospace;
            --slate-dark: var(--ink);
            --slate-gray: var(--ink-light);
            --border-cream: var(--line);
            --bg-cream: var(--paper);
        }

        /* ==============================
           GLOBAL
        ============================== */

        body {
            background-color: var(--paper);
            color: var(--ink);
            font-family: var(--font-main);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Space Grotesk', 'Sarabun', sans-serif;
            font-weight: 700;
        }

        a {
            text-decoration: none;
        }

        /* ==============================
           FLOATING ISLAND NAVBAR
        ============================== */

        .custom-navbar {
            background-color: rgba(250, 248, 245, 0.82) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);

            border: 1px solid rgba(232, 225, 216, 0.75) !important;
            border-radius: 16px;

            margin: 15px auto;
            max-width: 1200px;
            width: calc(100% - 30px);

            left: 50% !important;
            transform: translateX(-50%);

            padding: 12px 24px;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.025),
                0 1px 3px rgba(0, 0, 0, 0.015);

            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .custom-navbar:hover {
            box-shadow:
                0 15px 35px rgba(217, 107, 39, 0.08),
                0 3px 10px rgba(217, 107, 39, 0.03);

            border-color: rgba(217, 107, 39, 0.25) !important;
        }

        /* Brand */

        .navbar-brand-custom {
            font-weight: 800;
            font-size: 1.35rem;
            color: var(--ink);

            text-decoration: none;
            letter-spacing: -0.03em;

            display: flex;
            align-items: center;
            gap: 8px;

            font-family: var(--font-mono);

            transition: transform 0.2s ease;
        }

        .navbar-brand-custom:hover {
            transform: scale(1.02);
            color: var(--primary);
        }

        .navbar-brand-custom .brand-id {
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--muted);

            border: 1px dashed var(--line);
            border-radius: 999px;

            padding: 2px 8px;
        }

        /* Navigation */

        .custom-navbar .nav-link {
            color: var(--muted) !important;

            font-weight: 600;
            font-size: 0.95rem;

            padding: 8px 18px !important;
            border-radius: 10px;

            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .custom-navbar .nav-link:hover {
            color: var(--primary) !important;
            background-color: rgba(var(--primary-light-rgb), 0.65);
        }

        .custom-navbar .nav-link.active-green {
            color: var(--primary) !important;
            background-color: var(--primary-light);

            font-weight: 700;

            box-shadow:
                inset 0 0 0 1px rgba(var(--primary-rgb), 0.06);
        }

        /* ==============================
           HERO SECTION
        ============================== */

        .hero {
            position: relative;

            padding: 160px 20px 100px;

            background:
                linear-gradient(180deg,
                    #ffffff 0%,
                    var(--paper) 100%);

            border-bottom: 1px solid var(--line);

            text-align: center;

            overflow: hidden;
        }

        /* Grid Background */

        .hero::before {
            content: '';

            position: absolute;

            top: 0;
            left: 0;
            right: 0;
            bottom: 0;

            background-image:
                linear-gradient(to right,
                    rgba(217, 107, 39, 0.045) 1px,
                    transparent 1px),
                linear-gradient(to bottom,
                    rgba(217, 107, 39, 0.045) 1px,
                    transparent 1px);

            background-size: 40px 40px;

            z-index: 0;

            mask-image:
                linear-gradient(to bottom,
                    black 40%,
                    transparent 100%);

            -webkit-mask-image:
                linear-gradient(to bottom,
                    black 40%,
                    transparent 100%);
        }

        .hero .container {
            position: relative;
            z-index: 1;
        }

        /* ==============================
           EYEBROW
        ============================== */

        .eyebrow {
            font-family: var(--font-mono);

            font-size: 0.8rem;

            color: var(--primary-dark);

            background: var(--primary-light);

            border: 1px dashed rgba(217, 107, 39, 0.4);

            padding: 8px 16px;

            border-radius: 999px;

            display: inline-flex;
            align-items: center;

            gap: 8px;

            margin-bottom: 2rem;

            box-shadow:
                0 4px 15px rgba(217, 107, 39, 0.05);

            font-weight: 600;
        }

        .eyebrow i {
            color: var(--primary);
        }

        /* ==============================
           HERO TITLE
        ============================== */

        .hero h1 {
            font-size: clamp(2.8rem, 6vw, 4.5rem);

            letter-spacing: -0.04em;

            color: var(--ink);

            margin-bottom: 1.5rem;

            line-height: 1.1;

            font-weight: 800;
        }

        .hero h1 span {
            background:
                linear-gradient(135deg,
                    var(--primary),
                    #E99A68);

            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            color: var(--ink-light);

            font-size: 1.2rem;

            max-width: 650px;

            margin: 0 auto 3rem;

            line-height: 1.7;
        }

        /* ==============================
           BUTTONS
        ============================== */

        .btn-custom {
            padding: 12px 28px;

            font-weight: 700;

            border-radius: 999px;

            transition:
                all 0.3s cubic-bezier(0.16, 1, 0.3, 1);

            font-size: 0.9rem;

            font-family: var(--font-mono);

            letter-spacing: 0.02em;
        }

        /* Primary Button */

        .btn-primary-custom {
            background-color: var(--primary);

            color: white;

            border: 2px solid var(--primary);

            box-shadow:
                0 10px 25px -5px rgba(217, 107, 39, 0.3);
        }

        .btn-primary-custom:hover {
            background-color: var(--primary-dark);

            border-color: var(--primary-dark);

            color: white;

            transform:
                translateY(-3px) rotate(-1deg);

            box-shadow:
                0 15px 30px -5px rgba(217, 107, 39, 0.4);
        }

        /* Outline Button */

        .btn-outline-custom {
            background-color: white;

            color: var(--ink);

            border: 2px solid var(--line);

            box-shadow:
                0 2px 5px rgba(0, 0, 0, 0.015);
        }

        .btn-outline-custom:hover {
            border-color: var(--primary);

            background-color: var(--primary-light);

            color: var(--primary-dark);

            transform: translateY(-2px);
        }

        /* ==============================
           SECTION LABEL
        ============================== */

        .section-label {
            font-family: var(--font-mono);

            font-size: 0.85rem;

            color: var(--primary);

            letter-spacing: 0.08em;

            text-transform: uppercase;

            margin-bottom: 2.5rem;

            display: flex;
            align-items: center;

            gap: 16px;

            font-weight: 700;
        }

        .section-label::after {
            content: "";

            height: 1px;

            background: var(--line);

            flex-grow: 1;
        }

        /* ==============================
           FEATURE CARDS
        ============================== */

        .feature-card {
            position: relative;

            border: 1px solid var(--line);

            border-radius: 6px;

            background: #ffffff;

            padding: 40px 32px 32px;

            height: 100%;

            text-decoration: none;

            color: var(--ink);

            display: flex;
            flex-direction: column;

            transition:
                all 0.4s cubic-bezier(0.16, 1, 0.3, 1);

            overflow: hidden;

            box-shadow:
                0 4px 20px rgba(0, 0, 0, 0.012);
        }

        /* File Tab */

        .feature-card .file-tab {
            position: absolute;

            top: -1px;
            left: 24px;

            background: var(--ink);

            color: #fff;

            font-family: var(--font-mono);

            font-size: 0.66rem;

            letter-spacing: 0.08em;

            padding: 5px 12px;

            border-radius: 0 0 6px 6px;
        }

        /* Card Hover */

        .feature-card:hover {
            border-color: rgba(217, 107, 39, 0.35);

            transform: translateY(-6px);

            box-shadow:
                0 20px 40px -15px rgba(217, 107, 39, 0.15);

            color: var(--ink);
        }

        /* ==============================
           ICON
        ============================== */

        .icon-wrapper {
            width: 48px;
            height: 48px;

            background: var(--primary-light);

            color: var(--primary);

            border-radius: 12px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 1.4rem;

            margin-bottom: 22px;

            transition:
                all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .feature-card:hover .icon-wrapper {
            transform:
                scale(1.08) rotate(5deg);

            background: var(--primary);

            color: white;

            box-shadow:
                0 8px 20px rgba(217, 107, 39, 0.2);
        }

        /* ==============================
           CARD CONTENT
        ============================== */

        .feature-card h3 {
            font-size: 1.25rem;

            margin-bottom: 10px;

            letter-spacing: -0.015em;

            font-weight: 700;
        }

        .feature-card p {
            color: var(--ink-light);

            font-size: 0.92rem;

            margin-bottom: 20px;

            line-height: 1.6;

            flex-grow: 1;
        }

        /* ==============================
           PERFORATION
        ============================== */

        .feature-perforation {
            display: flex;

            margin: 0 -32px 16px;

            padding: 0 32px;
        }

        .feature-perforation span {
            flex: 1;

            border-top:
                2px dotted var(--line);
        }

        /* ==============================
           CARD ARROW
        ============================== */

        .feature-arrow {
            font-weight: 700;

            color: var(--primary);

            font-size: 0.85rem;

            font-family: var(--font-mono);

            text-transform: uppercase;

            letter-spacing: 0.02em;

            display: inline-flex;
            align-items: center;

            gap: 5px;

            margin-top: auto;

            transition: gap 0.25s ease;
        }

        .feature-card:hover .feature-arrow {
            gap: 10px;
        }

        .feature-arrow i {
            font-size: 1.1rem;
        }

        /* ==============================
           FOOTER
        ============================== */

        footer {
            background: #ffffff;

            border-top: 1px solid var(--line);

            padding: 48px 0;

            margin-top: 80px;
        }

        .footer-text {
            color: var(--muted);

            font-size: 0.9rem;

            line-height: 1.6;

            font-family: var(--font-mono);
        }

        footer a {
            transition: all 0.25s ease;
        }

        footer a:hover {
            color: var(--primary) !important;

            transform: translateY(-2px);
        }

        /* ==============================
           RESPONSIVE
        ============================== */

        @media (max-width: 991px) {
            .custom-navbar {
                padding: 10px 18px;
            }

            .custom-navbar .navbar-nav {
                margin-top: 15px;
            }

            .custom-navbar .nav-link {
                padding: 10px 14px !important;
            }
        }

        @media (max-width: 767px) {
            .hero {
                padding: 140px 16px 80px;
            }

            .hero h1 {
                font-size: 2.8rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .section-label {
                font-size: 0.72rem;
            }

            .feature-card {
                padding: 40px 28px 28px;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding-top: 125px;
            }

            .hero h1 {
                font-size: 2.35rem;
            }

            .hero .d-flex {
                flex-direction: column;
                align-items: stretch;
            }

            .hero .btn-custom {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <!-- ==============================
         FLOATING ISLAND NAVBAR
    ============================== -->

    <nav class="navbar navbar-expand-lg fixed-top custom-navbar">

        <div class="container">

            <a class="navbar-brand-custom" href="/">
                Aphatsara
                <span class="brand-id">DEV</span>
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav">

                <span class="navbar-toggler-icon"></span>

            </button>

            <div class="collapse navbar-collapse" id="navbarNav">

                <ul class="navbar-nav mx-auto gap-1">

                    <li class="nav-item">
                        <a class="nav-link active-green" href="/">
                            หน้าแรก
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/about">
                            เกี่ยวกับ
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/blog">
                            บทความ
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/from">
                            เขียนบทความ
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/student/1">
                            นักศึกษา
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/claim">
                            แจ้งเคลม
                        </a>
                    </li>

                </ul>

                <div class="d-flex gap-2 mt-3 mt-lg-0">

                    <a href="https://github.com" target="_blank" class="btn btn-outline-custom btn-custom">

                        <i class="bi bi-github me-2"></i>
                        GitHub

                    </a>

                </div>

            </div>

        </div>

    </nav>


    <!-- ==============================
         HERO SECTION
    ============================== -->

    <div class="hero">

        <div class="container">

            <div class="eyebrow">

                <i class="bi bi-braces"></i>

                01-406-093-203 | 3(2-2-5)

            </div>

            <h1>
                โปรเจกต์คอร์ส
                <span>Laravel</span>
            </h1>

            <p>
                รวมฟีเจอร์ทั้งหมดที่พัฒนาไว้ในคอร์สนี้
                ตั้งแต่ระบบจัดการบทความ ค้นหาประวัตินักศึกษา
                ไปจนถึงระบบฟอร์มแจ้งเคลมสินค้าที่ใช้งานได้จริง
            </p>

            <div class="d-flex justify-content-center gap-3">

                <a href="#features" class="btn btn-primary-custom btn-custom">

                    เริ่มต้นใช้งาน

                    <i class="bi bi-arrow-right ms-1"></i>

                </a>

                <a href="/about" class="btn btn-outline-custom btn-custom">

                    เกี่ยวกับผู้พัฒนา

                </a>

            </div>

        </div>

    </div>


    <!-- ==============================
         FEATURES SECTION
    ============================== -->

    <div class="container py-5" id="features">

        <div class="section-label">
            ARCHIVE — ALL FEATURES IN PROJECT
        </div>

        <div class="row g-4">


            <!-- Feature 1 -->

            <div class="col-md-6 col-lg-3">

                <a href="/about" class="feature-card">

                    <span class="file-tab">
                        NO. 001
                    </span>

                    <div class="icon-wrapper">

                        <i class="bi bi-info-circle"></i>

                    </div>

                    <h3>
                        เกี่ยวกับ
                    </h3>

                    <p>
                        แสดงข้อมูลทั่วไปเกี่ยวกับเว็บไซต์
                        วัตถุประสงค์ของการจัดทำ
                        และข้อมูลของผู้พัฒนาโปรเจกต์นี้
                    </p>

                    <div class="feature-perforation">
                        <span></span>
                    </div>

                    <span class="feature-arrow">

                        เข้าดูหน้าเว็บ

                        <i class="bi bi-arrow-right-short"></i>

                    </span>

                </a>

            </div>


            <!-- Feature 2 -->

            <div class="col-md-6 col-lg-3">

                <a href="/blog" class="feature-card">

                    <span class="file-tab">
                        NO. 002
                    </span>

                    <div class="icon-wrapper">

                        <i class="bi bi-journal-richtext"></i>

                    </div>

                    <h3>
                        ระบบบทความ
                    </h3>

                    <p>
                        ดึงข้อมูลบทความจำลอง 100 รายการ
                        จากฐานข้อมูล Database
                        พร้อมระบบแบ่งหน้า (Pagination)
                    </p>

                    <div class="feature-perforation">
                        <span></span>
                    </div>

                    <span class="feature-arrow">

                        เข้าดูหน้าเว็บ

                        <i class="bi bi-arrow-right-short"></i>

                    </span>

                </a>

            </div>


            <!-- Feature 3 -->

            <div class="col-md-6 col-lg-3">

                <a href="/student/1" class="feature-card">

                    <span class="file-tab">
                        NO. 003
                    </span>

                    <div class="icon-wrapper">

                        <i class="bi bi-person-vcard"></i>

                    </div>

                    <h3>
                        ประวัตินักศึกษา
                    </h3>

                    <p>
                        ระบบค้นหาและแสดงประวัตินักศึกษา
                        ตามรหัส ID ผ่านการใช้งาน
                        Dynamic Route ของ Laravel
                    </p>

                    <div class="feature-perforation">
                        <span></span>
                    </div>

                    <span class="feature-arrow">

                        เข้าดูหน้าเว็บ

                        <i class="bi bi-arrow-right-short"></i>

                    </span>

                </a>

            </div>


            <!-- Feature 4 -->

            <div class="col-md-6 col-lg-3">

                <a href="/claim" class="feature-card">

                    <span class="file-tab">
                        NO. 004
                    </span>

                    <div class="icon-wrapper">

                        <i class="bi bi-shield-check"></i>

                    </div>

                    <h3>
                        แจ้งเคลมสินค้า
                    </h3>

                    <p>
                        ระบบฟอร์มรับแจ้งปัญหาและเคลมสินค้าชำรุด
                        พร้อมระบบ Validation
                        ตรวจสอบความถูกต้องของข้อมูล
                    </p>

                    <div class="feature-perforation">
                        <span></span>
                    </div>

                    <span class="feature-arrow">

                        เข้าดูหน้าเว็บ

                        <i class="bi bi-arrow-right-short"></i>

                    </span>

                </a>

            </div>

        </div>

    </div>


    <!-- ==============================
         FOOTER
    ============================== -->

    <footer>

        <div class="container text-center">

            <div class="d-flex justify-content-center gap-3 mb-3">

                <a href="#" class="text-muted fs-5">

                    <i class="bi bi-facebook"></i>

                </a>

                <a href="#" class="text-muted fs-5">

                    <i class="bi bi-github"></i>

                </a>

                <a href="#" class="text-muted fs-5">

                    <i class="bi bi-envelope"></i>

                </a>

            </div>

            <p class="footer-text mb-0">

                © 2026 My Website — Laravel Course Project.
                <br>

                Built with Laravel & Bootstrap 5.

            </p>

        </div>

    </footer>


    <!-- ==============================
         BOOTSTRAP 5 JS
    ============================== -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
