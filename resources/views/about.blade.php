@extends('layout')

@section('title')
    เกี่ยวกับเรา
@endsection

@section('content')
    <style>
        /* =========================================
               ABOUT PAGE — ORANGE PREMIUM THEME
            ========================================= */

        .dossier-wrap {
            --stamp-rotate: -8deg;

            --about-orange: #D96B27;
            --about-orange-dark: #A94E1D;
            --about-orange-light: #FCEBDD;
            --about-orange-rgb: 217, 107, 39;

            --about-ink: #2C2926;
            --about-gray: #5A544E;
            --about-muted: #77716B;
            --about-border: #E8E1D8;
            --about-paper: #FAF8F5;
        }

        /* =========================================
               DOSSIER CARD
            ========================================= */

        .dossier-card {
            position: relative;

            border: 1px solid var(--about-border);
            border-radius: 8px;

            background: #ffffff;

            box-shadow:
                0 20px 40px -20px rgba(0, 0, 0, 0.08);

            transition:
                transform 0.3s ease,
                box-shadow 0.3s ease,
                border-color 0.3s ease;
        }

        .dossier-card:hover {
            border-color: rgba(var(--about-orange-rgb), 0.35);

            box-shadow:
                0 25px 50px -20px rgba(var(--about-orange-rgb), 0.14);

            transform: translateY(-3px);
        }

        /* =========================================
               FILE TAB
            ========================================= */

        .file-tab {
            position: absolute;

            top: -1px;
            left: 24px;

            background: var(--about-orange);

            color: #ffffff;

            font-family: var(--font-mono);

            font-size: 0.68rem;

            letter-spacing: 0.08em;

            padding: 6px 14px;

            border-radius: 0 0 6px 6px;

            box-shadow:
                0 5px 12px rgba(var(--about-orange-rgb), 0.18);
        }

        /* =========================================
               MONOGRAM
            ========================================= */

        .monogram {
            width: 84px;
            height: 84px;

            border-radius: 50%;

            border: 2px solid var(--about-orange);

            display: flex;
            align-items: center;
            justify-content: center;

            font-family: var(--font-mono);

            font-size: 1.6rem;

            font-weight: 700;

            color: var(--about-orange);

            background: var(--about-orange-light);

            flex-shrink: 0;

            box-shadow:
                0 8px 20px rgba(var(--about-orange-rgb), 0.08);

            transition:
                transform 0.3s ease,
                background 0.3s ease;
        }

        .dossier-card:hover .monogram {
            transform: scale(1.05) rotate(3deg);

            background: #F9E0CC;
        }

        /* =========================================
               META ROW
            ========================================= */

        .meta-row {
            display: flex;

            justify-content: space-between;

            align-items: baseline;

            padding: 14px 0;

            border-bottom:
                1px dashed var(--about-border);
        }

        .meta-row:last-child {
            border-bottom: none;
        }

        .meta-label {
            font-family: var(--font-mono);

            font-size: 0.7rem;

            text-transform: uppercase;

            letter-spacing: 0.08em;

            color: var(--about-muted);
        }

        .meta-value {
            font-weight: 700;

            color: var(--about-ink);

            font-size: 0.98rem;

            text-align: right;
        }

        /* =========================================
               STATUS
            ========================================= */

        .verified-status {
            color: var(--about-orange);

            font-weight: 700;

            font-size: 0.9rem;

            display: inline-flex;

            align-items: center;

            gap: 5px;
        }

        .verified-status .status-dot {
            font-size: 0.75rem;

            animation:
                orangePulse 2s infinite;
        }

        @keyframes orangePulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.45;
            }

            100% {
                opacity: 1;
            }
        }

        /* =========================================
               PERFORATION
            ========================================= */

        .perforation {
            display: flex;

            gap: 6px;

            margin: 0 -40px;

            padding: 0 40px;

            overflow: hidden;
        }

        .perforation span {
            flex: 1;

            border-top:
                2px dotted var(--about-border);

            height: 0;

            margin-top: 6px;
        }

        /* =========================================
               EYEBROW
            ========================================= */

        .about-eyebrow {
            font-family: var(--font-mono);

            font-size: 0.85rem;

            color: var(--about-orange);

            letter-spacing: 0.05em;

            font-weight: 700;

            text-transform: uppercase;
        }

        .about-eyebrow-line {
            height: 1px;

            background: var(--about-border);

            flex-grow: 1;
        }

        /* =========================================
               HEADLINE
            ========================================= */

        .headline-lede {
            font-size: 2.1rem;

            font-weight: 800;

            color: var(--about-ink);

            letter-spacing: -0.02em;

            line-height: 1.15;

            position: relative;

            display: inline-block;
        }

        .headline-lede::after {
            content: "";

            display: block;

            width: 48px;

            height: 4px;

            background: var(--about-orange);

            border-radius: 999px;

            margin-top: 10px;
        }

        /* =========================================
               DESCRIPTION
            ========================================= */

        .about-description {
            color: var(--about-gray);

            font-size: 1rem;

            line-height: 1.8;

            margin-bottom: 0;
        }

        /* =========================================
               STAMP BUTTON
            ========================================= */

        .stamp-btn {
            position: relative;

            display: inline-flex;

            align-items: center;

            gap: 10px;

            padding: 11px 24px;

            font-family: var(--font-mono);

            font-weight: 700;

            font-size: 0.85rem;

            letter-spacing: 0.04em;

            text-transform: uppercase;

            border:
                2px solid var(--about-orange);

            border-radius: 999px;

            color: var(--about-orange);

            background: transparent;

            text-decoration: none;

            transition:
                all 0.25s ease;
        }

        .stamp-btn:hover {
            background: var(--about-orange);

            border-color: var(--about-orange);

            color: #ffffff;

            transform:
                translateY(-2px) rotate(var(--stamp-rotate));

            box-shadow:
                0 10px 22px rgba(var(--about-orange-rgb), 0.22);
        }

        .stamp-btn i {
            transition:
                transform 0.25s ease;
        }

        .stamp-btn:hover i {
            transform: translateX(-3px);
        }

        /* =========================================
               CARD TOP DECORATION
            ========================================= */

        .orange-accent {
            width: 100%;

            height: 3px;

            background:
                linear-gradient(90deg,
                    var(--about-orange),
                    #E99A68,
                    transparent);

            position: absolute;

            top: 0;
            left: 0;

            border-radius:
                8px 8px 0 0;
        }

        /* =========================================
               MOBILE
            ========================================= */

        @media (max-width: 767px) {

            .headline-lede {
                font-size: 1.6rem;
            }

            .dossier-wrap {
                padding-left: 16px;
                padding-right: 16px;
            }

            .meta-row {
                gap: 15px;
            }

            .meta-value {
                font-size: 0.9rem;
            }

            .monogram {
                width: 70px;
                height: 70px;

                font-size: 1.35rem;
            }

            .perforation {
                margin-left: -24px;
                margin-right: -24px;

                padding-left: 24px;
                padding-right: 24px;
            }

            .stamp-btn {
                width: 100%;

                justify-content: center;
            }
        }
    </style>


    <div class="container dossier-wrap py-5" style="margin-top: 110px; max-width: 1000px;">

        <!-- =====================================
                 EYEBROW
            ====================================== -->

        <div class="d-flex align-items-center gap-3 mb-4">

            <span class="about-eyebrow">
                FILE — ABOUT THE DEVELOPER
            </span>

            <div class="about-eyebrow-line"></div>

        </div>


        <!-- =====================================
                 CONTENT
            ====================================== -->

        <div class="row g-4">


            <!-- =================================
                     LEFT — DEVELOPER CARD
                ================================== -->

            <div class="col-md-5 col-lg-4">

                <div class="dossier-card p-4 pt-5">

                    <!-- Orange Accent -->
                    <div class="orange-accent"></div>

                    <!-- File Tab -->
                    <span class="file-tab">
                        NO. 001
                    </span>


                    <!-- Developer Header -->

                    <div class="d-flex align-items-center gap-3 mb-4">

                        <div class="monogram">
                            {{ mb_substr($name, 0, 1) }}
                        </div>

                        <div>

                            <small class="meta-label d-block mb-1">
                                สถานะ
                            </small>

                            <span class="verified-status">

                                <span class="status-dot">
                                    ●
                                </span>

                                ยืนยันตัวตนแล้ว

                            </span>

                        </div>

                    </div>


                    <!-- Perforation -->

                    <div class="perforation mb-3">
                        <span></span>
                    </div>


                    <!-- Developer Name -->

                    <div class="meta-row">

                        <span class="meta-label">
                            ผู้พัฒนาระบบ
                        </span>

                        <span class="meta-value">
                            {{ $name }}
                        </span>

                    </div>


                    <!-- Birthday -->

                    <div class="meta-row">

                        <span class="meta-label">
                            วันเกิด
                        </span>

                        <span class="meta-value">
                            {{ $date }}
                        </span>

                    </div>

                </div>

            </div>


            <!-- =================================
                     RIGHT — ABOUT CONTENT
                ================================== -->

            <div class="col-md-7 col-lg-8">

                <div class="dossier-card p-4 p-md-5 h-100 d-flex flex-column justify-content-between">

                    <!-- Orange Accent -->
                    <div class="orange-accent"></div>


                    <div>

                        <!-- Label -->

                        <span class="meta-label d-block mb-2">
                            บันทึกประจำตัว
                        </span>


                        <!-- Heading -->

                        <h2 class="headline-lede mb-4">
                            เกี่ยวกับเรา
                        </h2>


                        <!-- Description -->

                        <p class="about-description">

                            Lorem ipsum, dolor sit amet consectetur adipisicing elit.
                            Voluptatem eveniet, quis odit architecto illum dicta
                            earum totam aliquam id, corrupti consectetur delectus
                            corporis sapiente minus. Amet optio inventore ipsa ut!

                        </p>

                    </div>


                    <!-- =================================
                             NAVIGATION
                        ================================== -->

                    <div class="pt-4 mt-4" style="border-top: 1px dashed var(--about-border);">

                        <a href="/" class="stamp-btn">

                            <i class="bi bi-arrow-left"></i>

                            กลับหน้าแรก

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>
@endsection
