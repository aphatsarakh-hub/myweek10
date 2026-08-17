@extends('layout')

@section('title', 'ประวัตินักศึกษา')

@section('content')

    <div class="container dossier-wrap py-5" style="margin-top: 110px; max-width: 760px;">

        <!-- =========================================
                 PAGE CATEGORY
            ========================================== -->

        <div class="d-flex align-items-center gap-3 mb-4">

            <span class="page-category">
                STUDENT RECORDS — PROFILE
            </span>

            <div class="category-line"></div>

        </div>


        <!-- =========================================
                 PAGE HEADER
            ========================================== -->

        <div class="mb-5">

            <span class="meta-label d-block mb-2">
                แฟ้มประวัติ
            </span>

            <h2 class="page-title">
                ข้อมูลนักศึกษา
            </h2>

            <p class="page-description">
                ระบบจัดการและแสดงข้อมูลประวัติส่วนตัวในรูปแบบดิจิทัลโปรไฟล์
            </p>

        </div>


        <!-- =========================================
                 PROFILE CARD
            ========================================== -->

        <div class="profile-card dossier-card p-4 p-md-5 pt-5">

            <!-- Orange Accent -->
            <div class="orange-accent"></div>

            <!-- File Tab -->

            <span class="file-tab">
                ID CARD
            </span>


            <div class="row g-4 align-items-center">


                <!-- =================================
                         PROFILE PHOTO
                    ================================== -->

                <div class="col-md-4 text-center text-md-start">

                    <div class="profile-avatar-wrapper">

                        <div class="profile-avatar">

                            <!-- ใส่ URL รูปภาพของคุณแทน # -->

                            <img src="#" alt="Student Photo"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                            <!-- ถ้าไม่มีรูป จะแสดง Icon นี้ -->

                            <div class="avatar-placeholder">

                                <i class="bi bi-person"></i>

                            </div>

                        </div>


                        <!-- =================================
                                 ACTIVE STUDENT BADGE
                            ================================== -->

                        <span class="student-status">

                            <span class="status-dot"></span>

                            Active Student

                        </span>

                    </div>

                </div>


                <!-- =================================
                         STUDENT NAME
                    ================================== -->

                <div class="col-md-8 text-center text-md-start ps-md-4 mt-5 mt-md-0">

                    <!-- Student ID -->

                    <div class="student-id">

                        STUDENT ID:
                        68152310243-6

                    </div>


                    <!-- Name -->

                    <h3 class="student-name">

                        นางสาว อภัสรา แคะมะดัน

                    </h3>


                    <!-- English Name -->

                    <p class="student-nickname">

                        Aphatsara

                    </p>

                </div>


                <!-- =================================
                         PERFORATION
                    ================================== -->

                <div class="perforation" style="margin: 32px -40px 0; padding: 0 40px;">

                    <span></span>

                </div>


                <div class="pt-4"></div>


                <!-- =================================
                         INFORMATION GRID
                    ================================== -->

                <div class="row g-4">


                    <!-- =============================
                             EDUCATION
                        ============================== -->

                    <div class="col-sm-6 col-md-4">

                        <div class="info-block">

                            <div class="info-icon">

                                <i class="bi bi-mortarboard"></i>

                            </div>

                            <div>

                                <div class="info-label">
                                    ระดับการศึกษา
                                </div>

                                <div class="info-value">
                                    ปริญญาตรี
                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- =============================
                             FACULTY
                        ============================== -->

                    <div class="col-sm-6 col-md-8">

                        <div class="info-block">

                            <div class="info-icon">

                                <i class="bi bi-building"></i>

                            </div>

                            <div>

                                <div class="info-label">
                                    คณะ / สำนัก
                                </div>

                                <div class="info-value">
                                    Faculty of Business Administration
                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- =============================
                             MAJOR
                        ============================== -->

                    <div class="col-12">

                        <div class="info-block">

                            <div class="info-icon">

                                <i class="bi bi-cpu"></i>

                            </div>

                            <div>

                                <div class="info-label">
                                    สาขาวิชา / เอกวิชา
                                </div>

                                <div class="info-value">
                                    สาขาระบบสารสนเทศ
                                    (Information Systems)
                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- =============================
                             UNIVERSITY
                        ============================== -->

                    <div class="col-12">

                        <div class="info-block university-block">

                            <div class="info-icon university-icon">

                                <i class="bi bi-shield-check"></i>

                            </div>

                            <div>

                                <div class="info-label">
                                    สถาบันการศึกษา
                                </div>

                                <div class="info-value university-name">

                                    มหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน

                                    <br>

                                    <span class="university-en">

                                        Rajamangala University
                                        of Technology Isan

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =========================================
                 PAGE STYLE
            ========================================== -->

        <style>
            /* =========================================
                   ORANGE THEME VARIABLES
                ========================================== */

            .dossier-wrap {

                --student-orange: #D96B27;
                --student-orange-dark: #A94E1D;
                --student-orange-light: #FCEBDD;
                --student-orange-rgb: 217, 107, 39;

                --student-ink: #2C2926;
                --student-gray: #5A544E;
                --student-muted: #77716B;

                --student-border: #E8E1D8;
                --student-paper: #FAF8F5;

            }


            /* =========================================
                   PAGE CATEGORY
                ========================================== */

            .page-category {

                font-family: var(--font-mono);

                font-size: 0.85rem;

                color: var(--student-orange);

                letter-spacing: 0.05em;

                font-weight: 700;

                text-transform: uppercase;

            }

            .category-line {

                height: 1px;

                background: var(--student-border);

                flex-grow: 1;

            }


            /* =========================================
                   PAGE HEADER
                ========================================== */

            .page-title {

                font-size: 2rem;

                font-weight: 800;

                color: var(--student-ink);

                margin-bottom: 6px;

                letter-spacing: -0.02em;

            }

            .page-description {

                color: var(--student-gray);

                font-size: 1rem;

                margin-bottom: 0;

            }

            .meta-label {

                font-family: var(--font-mono);

                font-size: 0.7rem;

                text-transform: uppercase;

                letter-spacing: 0.08em;

                color: var(--student-gray);

                font-weight: 700;

            }


            /* =========================================
                   DOSSIER CARD
                ========================================== */

            .dossier-card {

                position: relative;

                border: 1px solid var(--student-border);

                border-radius: 8px;

                background: #ffffff;

                box-shadow:
                    0 10px 30px rgba(0, 0, 0, 0.015);

                transition:
                    all 0.4s cubic-bezier(0.16,
                        1,
                        0.3,
                        1);

                overflow: hidden;

            }


            /* =========================================
                   ORANGE TOP ACCENT
                ========================================== */

            .orange-accent {

                position: absolute;

                top: 0;
                left: 0;

                width: 100%;

                height: 4px;

                background:
                    linear-gradient(90deg,
                        var(--student-orange),
                        #E99A68,
                        transparent);

            }


            /* =========================================
                   FILE TAB
                ========================================== */

            .file-tab {

                position: absolute;

                top: -1px;

                left: 24px;

                background: var(--student-orange);

                color: #ffffff;

                font-family: var(--font-mono);

                font-size: 0.68rem;

                letter-spacing: 0.08em;

                padding: 5px 14px;

                border-radius:
                    0 0 6px 6px;

                box-shadow:
                    0 5px 12px rgba(var(--student-orange-rgb),
                        0.18);

            }


            /* =========================================
                   PROFILE CARD HOVER
                ========================================== */

            .profile-card:hover {

                border-color:
                    rgba(var(--student-orange-rgb),
                        0.3) !important;

                transform:
                    translateY(-6px);

                box-shadow:
                    0 20px 40px -15px rgba(var(--student-orange-rgb),
                        0.13) !important;

            }


            /* =========================================
                   PROFILE AVATAR
                ========================================== */

            .profile-avatar-wrapper {

                position: relative;

                width: 150px;

                height: 185px;

                margin: 0 auto;

                border-radius: 10px;

                padding: 6px;

                background:
                    var(--student-orange-light);

                border:
                    1px dashed rgba(var(--student-orange-rgb),
                        0.45);

                box-shadow:
                    0 8px 20px -6px rgba(var(--student-orange-rgb),
                        0.12);

                transition:
                    all 0.3s ease;

            }

            .profile-card:hover .profile-avatar-wrapper {

                transform:
                    translateY(-3px);

                box-shadow:
                    0 12px 25px -8px rgba(var(--student-orange-rgb),
                        0.2);

            }


            /* =========================================
                   PHOTO
                ========================================== */

            .profile-avatar {

                width: 100%;

                height: 100%;

                border-radius: 6px;

                overflow: hidden;

                background:
                    #FFF8F3;

                display: flex;

                align-items: center;

                justify-content: center;

            }

            .profile-avatar img {

                width: 100%;

                height: 100%;

                object-fit: cover;

            }


            /* =========================================
                   PHOTO PLACEHOLDER
                ========================================== */

            .avatar-placeholder {

                display: none;

                flex-direction: column;

                align-items: center;

                justify-content: center;

                width: 100%;

                height: 100%;

                color: var(--student-gray);

            }

            .avatar-placeholder i {

                font-size: 3.5rem;

                color: var(--student-orange);

            }


            /* =========================================
                   ACTIVE STUDENT
                ========================================== */

            .student-status {

                position: absolute;

                bottom: -10px;

                left: 50%;

                transform:
                    translateX(-50%);

                white-space: nowrap;

                display: inline-flex;

                align-items: center;

                gap: 5px;

                padding: 5px 13px;

                font-size: 0.7rem;

                font-family: var(--font-mono);

                text-transform: uppercase;

                letter-spacing: 0.02em;

                font-weight: 700;

                color: var(--student-orange-dark);

                background-color:
                    var(--student-orange-light);

                border-radius: 20px;

                border:
                    1px solid rgba(var(--student-orange-rgb),
                        0.25);

                box-shadow:
                    0 3px 10px rgba(var(--student-orange-rgb),
                        0.1);

            }

            .status-dot {

                width: 6px;

                height: 6px;

                background-color:
                    var(--student-orange);

                border-radius: 50%;

                animation:
                    statusPulse 2s infinite;

            }

            @keyframes statusPulse {

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
                   STUDENT ID
                ========================================== */

            .student-id {

                display: inline-block;

                font-family: var(--font-mono);

                font-size: 0.85rem;

                font-weight: 700;

                color: var(--student-orange-dark);

                background:
                    var(--student-orange-light);

                padding: 6px 14px;

                border-radius: 999px;

                margin-bottom: 12px;

                border:
                    1px dashed rgba(var(--student-orange-rgb),
                        0.4);

            }


            /* =========================================
                   STUDENT NAME
                ========================================== */

            .student-name {

                font-size: 1.8rem;

                font-weight: 800;

                color: var(--student-ink);

                margin-bottom: 4px;

                letter-spacing: -0.02em;

            }

            .student-nickname {

                font-size: 1rem;

                font-weight: 600;

                color: var(--student-gray);

                margin-bottom: 0;

                text-transform: uppercase;

                letter-spacing: 0.02em;

            }


            /* =========================================
                   PERFORATION
                ========================================== */

            .perforation {

                display: flex;

                gap: 6px;

                overflow: hidden;

            }

            .perforation span {

                flex: 1;

                border-top:
                    2px dotted var(--student-border);

                height: 0;

            }


            /* =========================================
                   INFORMATION BLOCK
                ========================================== */

            .info-block {

                display: flex;

                align-items: center;

                gap: 16px;

                padding: 18px;

                border:
                    1px dashed var(--student-border);

                border-radius: 10px;

                height: 100%;

                background-color: #ffffff;

                transition:
                    all 0.3s cubic-bezier(0.16,
                        1,
                        0.3,
                        1);

            }


            /* =========================================
                   INFORMATION BLOCK HOVER
                ========================================== */

            .info-block:hover {

                border-color:
                    rgba(var(--student-orange-rgb),
                        0.4);

                border-style: solid;

                background-color:
                    var(--student-paper);

                transform:
                    translateY(-2px);

                box-shadow:
                    0 8px 18px rgba(var(--student-orange-rgb),
                        0.06);

            }


            /* =========================================
                   INFO ICON
                ========================================== */

            .info-icon {

                width: 44px;

                height: 44px;

                background:
                    var(--student-orange-light);

                color:
                    var(--student-orange);

                border-radius: 10px;

                display: flex;

                align-items: center;

                justify-content: center;

                font-size: 1.2rem;

                flex-shrink: 0;

                transition:
                    all 0.3s ease;

            }


            .info-block:hover .info-icon {

                background:
                    var(--student-orange);

                color: #ffffff;

                transform:
                    scale(1.05) rotate(3deg);

                box-shadow:
                    0 6px 15px rgba(var(--student-orange-rgb),
                        0.2);

            }


            /* =========================================
                   INFO LABEL
                ========================================== */

            .info-label {

                font-size: 0.75rem;

                font-family: var(--font-mono);

                font-weight: 700;

                color: var(--student-gray);

                text-transform: uppercase;

                letter-spacing: 0.06em;

                margin-bottom: 2px;

            }


            /* =========================================
                   INFO VALUE
                ========================================== */

            .info-value {

                font-size: 0.95rem;

                font-weight: 700;

                color: var(--student-ink);

            }


            /* =========================================
                   UNIVERSITY BLOCK
                ========================================== */

            .university-block {

                background:
                    var(--student-paper);

                border-color:
                    var(--student-border);

                border-style: solid;

            }

            .university-icon {

                background: #ffffff;

                color: var(--student-orange);

                border:
                    1px solid var(--student-border);

            }

            .university-name {

                font-size: 0.9rem;

                font-weight: 700;

                color: var(--student-ink);

                line-height: 1.6;

            }

            .university-en {

                font-size: 0.8rem;

                font-weight: 500;

                color: var(--student-gray);

            }


            /* =========================================
                   RESPONSIVE
                ========================================== */

            @media (max-width: 767px) {

                .page-title {
                    font-size: 1.7rem;
                }

                .student-name {
                    font-size: 1.5rem;
                }

                .profile-avatar-wrapper {
                    width: 135px;
                    height: 170px;
                }

                .perforation {
                    margin:
                        32px -24px 0 !important;

                    padding:
                        0 24px !important;
                }

                .info-block {
                    padding: 15px;
                }

                .info-icon {
                    width: 40px;
                    height: 40px;
                    font-size: 1rem;
                }

            }
        </style>

    </div>

@endsection
