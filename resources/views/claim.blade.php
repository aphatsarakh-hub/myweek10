@extends('layout')

@section('title', 'แจ้งเคลมสินค้าชำรุด')

@section('content')

    <div class="container claim-wrap py-5" style="margin-top: 110px; max-width: 620px;">

        <!-- =========================================
             PAGE CATEGORY
        ========================================== -->

        <div class="d-flex align-items-center gap-3 mb-4">

            <span class="page-category">
                SUPPORT & SERVICES — CLAIM FORM
            </span>

            <div class="category-line"></div>

        </div>


        <!-- =========================================
             PAGE HEADER
        ========================================== -->

        <div class="mb-4">

            <span class="meta-label d-block mb-2">
                บันทึกคำร้อง
            </span>

            <h2 class="headline-lede mb-2">
                แจ้งเคลมสินค้าชำรุด
            </h2>

            <p class="page-description">
                กรอกข้อมูลรหัสสินค้าและอธิบายอาการชำรุด
                เพื่อส่งเรื่องให้เจ้าหน้าที่ตรวจสอบ
            </p>

        </div>


        <!-- =========================================
             SUCCESS MESSAGE
        ========================================== -->

        @if (session('success'))
            <div class="success-alert">

                <i class="bi bi-check-circle-fill"></i>

                <div>
                    {{ session('success') }}
                </div>

            </div>
        @endif


        <!-- =========================================
             CLAIM FORM CARD
        ========================================== -->

        <div class="dossier-card p-4 p-md-5 pt-5">

            <!-- Orange Accent -->

            <div class="orange-accent"></div>


            <!-- File Tab -->

            <span class="file-tab">
                FORM — CLAIM
            </span>


            <!-- =====================================
                 FORM TYPE
            ====================================== -->

            <div class="d-flex align-items-center gap-3 mb-4">

                <div class="monogram claim-icon">

                    <i class="bi bi-tools"></i>

                </div>

                <div>

                    <small class="meta-label d-block mb-1">
                        ประเภทคำร้อง
                    </small>

                    <span class="claim-type">
                        แจ้งเคลมสินค้าชำรุด
                    </span>

                </div>

            </div>


            <!-- Perforation -->

            <div class="perforation mb-4">
                <span></span>
            </div>


            <!-- =====================================
                 FORM
            ====================================== -->

            <form action="{{ route('claim.store') }}" method="POST">

                @csrf


                <!-- =================================
                     SERIAL NUMBER
                ================================== -->

                <div class="mb-4">

                    <label class="form-label meta-label d-block" style="margin-bottom: 8px;">

                        รหัสสินค้า (Serial Number)

                        <span class="text-danger">*</span>

                    </label>


                    <input type="text" name="serial_number"
                        class="form-control custom-input @error('serial_number') is-invalid @enderror"
                        value="{{ old('serial_number') }}" placeholder="เช่น SN12345678">


                    @error('serial_number')
                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>
                    @enderror

                </div>


                <!-- =================================
                     EMAIL
                ================================== -->

                <div class="mb-4">

                    <label class="form-label meta-label d-block" style="margin-bottom: 8px;">

                        อีเมลผู้ติดต่อ

                        <span class="text-danger">*</span>

                    </label>


                    <input type="email" name="email"
                        class="form-control custom-input @error('email') is-invalid @enderror" value="{{ old('email') }}"
                        placeholder="example@mail.com">


                    @error('email')
                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>
                    @enderror

                </div>


                <!-- =================================
                     ISSUE DESCRIPTION
                ================================== -->

                <div class="mb-4">

                    <label class="form-label meta-label d-block" style="margin-bottom: 8px;">

                        อาการชำรุด

                        <span class="text-danger">*</span>

                    </label>


                    <textarea name="issue_description" rows="4"
                        class="form-control custom-input @error('issue_description') is-invalid @enderror"
                        placeholder="ระบุรายละเอียดอาการชำรุดอย่างน้อย 10 ตัวอักษร...">{{ old('issue_description') }}</textarea>


                    @error('issue_description')
                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>
                    @enderror

                </div>


                <!-- =================================
                     URGENCY LEVEL
                ================================== -->

                <div class="mb-4">

                    <label class="form-label meta-label d-block" style="margin-bottom: 8px;">

                        ระดับความเร่งด่วน

                        <span class="text-danger">*</span>

                    </label>


                    <select name="urgency_level"
                        class="form-select custom-input @error('urgency_level') is-invalid @enderror">

                        <option value="" disabled {{ old('urgency_level') ? '' : 'selected' }}>

                            -- กรุณาเลือกระดับความเร่งด่วน --

                        </option>

                        <option value="low" {{ old('urgency_level') == 'low' ? 'selected' : '' }}>

                            ต่ำ

                        </option>

                        <option value="medium" {{ old('urgency_level') == 'medium' ? 'selected' : '' }}>

                            ปานกลาง

                        </option>

                        <option value="high" {{ old('urgency_level') == 'high' ? 'selected' : '' }}>

                            สูง

                        </option>

                    </select>


                    @error('urgency_level')
                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>
                    @enderror

                </div>


                <!-- =================================
                     PERFORATION
                ================================== -->

                <div class="perforation mb-4">
                    <span></span>
                </div>


                <!-- =================================
                     SUBMIT BUTTON
                ================================== -->

                <div class="pt-2">

                    <button type="submit" class="stamp-btn stamp-btn-block">

                        <i class="bi bi-send-check"></i>

                        ส่งข้อมูลแจ้งเคลมสินค้า

                    </button>

                </div>

            </form>

        </div>

    </div>


    <!-- =========================================
         ORANGE THEME STYLE
    ========================================== -->

    <style>
        /* =========================================
           ORANGE VARIABLES
        ========================================== */

        .claim-wrap {

            --claim-orange: #D96B27;

            --claim-orange-dark: #A94E1D;

            --claim-orange-light: #FCEBDD;

            --claim-orange-rgb: 217, 107, 39;

            --claim-ink: #2C2926;

            --claim-gray: #5A544E;

            --claim-border: #E8E1D8;

            --claim-paper: #FAF8F5;

        }


        /* =========================================
           PAGE CATEGORY
        ========================================== */

        .page-category {

            font-family: var(--font-mono);

            font-size: 0.85rem;

            color: var(--claim-orange);

            letter-spacing: 0.05em;

            font-weight: 700;

            text-transform: uppercase;

        }


        .category-line {

            height: 1px;

            background: var(--claim-border);

            flex-grow: 1;

        }


        /* =========================================
           PAGE DESCRIPTION
        ========================================== */

        .page-description {

            color: var(--claim-gray);

            font-size: 1rem;

            margin-bottom: 0;

            line-height: 1.7;

        }


        /* =========================================
           META LABEL
        ========================================== */

        .meta-label {

            font-family: var(--font-mono);

            font-size: 0.7rem;

            text-transform: uppercase;

            letter-spacing: 0.08em;

            color: var(--claim-gray);

            font-weight: 700;

        }


        /* =========================================
           HEADLINE
        ========================================== */

        .headline-lede {

            font-size: 2rem;

            font-weight: 800;

            color: var(--claim-ink);

            letter-spacing: -0.02em;

            line-height: 1.15;

        }


        /* =========================================
           SUCCESS ALERT
        ========================================== */

        .success-alert {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 14px 16px;

            margin-bottom: 20px;

            background:
                #FFF7ED;

            border:
                1px dashed rgba(217,
                    107,
                    39,
                    0.35);

            border-radius: 10px;

            color:
                var(--claim-orange-dark);

            font-size: 0.95rem;

            font-weight: 600;

        }


        .success-alert i {

            font-size: 1.25rem;

            color:
                var(--claim-orange);

        }


        /* =========================================
           DOSSIER CARD
        ========================================== */

        .dossier-card {

            position: relative;

            border:
                1px solid var(--claim-border);

            border-radius: 8px;

            background: #ffffff;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.015);

            transition:
                all 0.35s cubic-bezier(0.16,
                    1,
                    0.3,
                    1);

            overflow: hidden;

        }


        .dossier-card:hover {

            border-color:
                rgba(var(--claim-orange-rgb),
                    0.25);

            box-shadow:
                0 20px 40px -18px rgba(var(--claim-orange-rgb),
                    0.12);

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
                    var(--claim-orange),
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

            background:
                var(--claim-orange);

            color: #ffffff;

            font-family:
                var(--font-mono);

            font-size: 0.68rem;

            letter-spacing: 0.08em;

            padding: 5px 14px;

            border-radius:
                0 0 6px 6px;

            box-shadow:
                0 5px 12px rgba(var(--claim-orange-rgb),
                    0.18);

        }


        /* =========================================
           MONOGRAM / TOOL ICON
        ========================================== */

        .monogram {

            display: flex;

            align-items: center;

            justify-content: center;

        }


        .claim-icon {

            width: 44px;

            height: 44px;

            font-size: 1.1rem;

            border-radius: 12px;

            background:
                var(--claim-orange-light);

            color:
                var(--claim-orange);

            border:
                1px solid rgba(var(--claim-orange-rgb),
                    0.15);

            transition:
                all 0.3s ease;

        }


        .dossier-card:hover .claim-icon {

            background:
                var(--claim-orange);

            color: #ffffff;

            transform:
                scale(1.05) rotate(3deg);

            box-shadow:
                0 6px 15px rgba(var(--claim-orange-rgb),
                    0.2);

        }


        /* =========================================
           CLAIM TYPE
        ========================================== */

        .claim-type {

            color:
                var(--claim-orange);

            font-weight: 700;

            font-size: 0.9rem;

        }


        /* =========================================
           PERFORATION
        ========================================== */

        .perforation {

            display: flex;

            gap: 6px;

            margin:
                0 -40px;

            padding:
                0 40px;

            overflow: hidden;

        }


        .perforation span {

            flex: 1;

            border-top:
                2px dotted var(--claim-border);

            height: 0;

            margin-top: 6px;

        }


        /* =========================================
           FORM INPUT
        ========================================== */

        .custom-input {

            padding:
                14px 18px;

            border-radius:
                10px;

            font-size:
                0.95rem;

            font-family:
                var(--font-mono);

            color:
                var(--claim-ink);

            background:
                #ffffff;

            border:
                1px dashed var(--claim-border);

            outline: none;

            transition:
                all 0.3s cubic-bezier(0.16,
                    1,
                    0.3,
                    1);

        }


        /* =========================================
           INPUT HOVER
        ========================================== */

        .custom-input:hover {

            border-color:
                rgba(var(--claim-orange-rgb),
                    0.35);

            background:
                #FFFDFC;

        }


        /* =========================================
           INPUT FOCUS
        ========================================== */

        .custom-input:focus {

            border-style:
                solid !important;

            border-color:
                var(--claim-orange) !important;

            box-shadow:
                0 0 0 4px rgba(var(--claim-orange-rgb),
                    0.14) !important;

            background-color:
                #ffffff !important;

        }


        /* =========================================
           INVALID INPUT
        ========================================== */

        .custom-input.is-invalid {

            border-color:
                #dc2626 !important;

        }


        .custom-input.is-invalid:focus {

            border-style:
                solid !important;

            border-color:
                #dc2626 !important;

            box-shadow:
                0 0 0 4px rgba(220,
                    38,
                    38,
                    0.15) !important;

        }


        /* =========================================
           PLACEHOLDER
        ========================================== */

        .custom-input::placeholder {

            color:
                #A39B94;

            opacity: 1;

        }


        /* =========================================
           SELECT ARROW
        ========================================== */

        select.custom-input {

            cursor: pointer;

        }


        /* =========================================
           STAMP BUTTON
        ========================================== */

        .stamp-btn {

            position: relative;

            display: inline-flex;

            align-items: center;

            gap: 10px;

            padding:
                15px 24px;

            font-family:
                var(--font-mono);

            font-weight: 700;

            font-size:
                0.9rem;

            letter-spacing:
                0.04em;

            text-transform:
                uppercase;

            border:
                2px solid var(--claim-orange);

            border-radius:
                999px;

            color:
                var(--claim-orange);

            background:
                transparent;

            text-decoration:
                none;

            cursor:
                pointer;

            transition:
                all 0.25s ease;

        }


        .stamp-btn-block {

            width: 100%;

            justify-content:
                center;

        }


        /* =========================================
           BUTTON HOVER
        ========================================== */

        .stamp-btn:hover {

            background:
                var(--claim-orange);

            color:
                #ffffff;

            border-color:
                var(--claim-orange);

            transform:
                translateY(-2px) rotate(-1deg);

            box-shadow:
                0 10px 20px rgba(var(--claim-orange-rgb),
                    0.25);

        }


        .stamp-btn:active {

            transform:
                translateY(0) rotate(0);

        }


        /* =========================================
           REQUIRED FIELD
        ========================================== */

        .text-danger {

            color:
                #DC2626 !important;

        }


        /* =========================================
           RESPONSIVE
        ========================================== */

        @media (max-width: 767px) {

            .headline-lede {

                font-size:
                    1.6rem;

            }


            .perforation {

                margin:
                    0 -24px;

                padding:
                    0 24px;

            }


            .custom-input {

                font-size:
                    0.9rem;

            }

        }
    </style>

@endsection
