@extends('layout')

@section('title')
    บทความ
@endsection

@section('content')
    <div class="container dossier-wrap py-5" style="margin-top: 110px; max-width: 1200px;">

        <!-- เส้นป้ายบอกหมวดหมู่สไตล์โมเดิร์น -->
        <div class="d-flex align-items-center gap-3 mb-4">
            <span
                style="font-family: var(--font-mono); font-size: 0.85rem; color: var(--primary); letter-spacing: 0.05em; font-weight: 700; text-transform: uppercase;">
                ARCHIVE — EXPLORE OUR ARTICLES
            </span>
            <div style="height: 1px; background: var(--border-cream); flex-grow: 1;"></div>
        </div>

        <!-- แผงควบคุมด้านบน: หัวข้อ + ช่องค้นหาโมเดิร์น -->
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-5">
            <div>
                <span class="meta-label d-block mb-2">บันทึกประจำแฟ้ม</span>
                <h2 class="headline-lede mb-2">บทความทั้งหมด</h2>
                <p style="color: var(--slate-gray); font-size: 1rem; margin-bottom: 0;">
                    อ่านสาระความรู้และอัปเดตบทความใหม่ล่าสุดจากเรา</p>
            </div>

            <!-- ช่องค้นหาสไตล์แฟ้มค้นหา -->
            <form action="{{ url('/blog') }}" method="GET" style="width: 100%; max-width: 360px;">
                <div style="position: relative; width: 100%;">
                    <i class="bi bi-search"
                        style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--slate-gray); font-size: 1rem;"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="ค้นหาชื่อบทความ หรือเนื้อหา..."
                        style="width: 100%; padding: 14px 18px 14px 48px; font-size: 0.95rem; font-family: var(--font-mono); border: 1px dashed var(--border-cream); border-radius: 999px; color: var(--slate-dark); background-color: #ffffff; outline: none; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 4px 12px rgba(0,0,0,0.01);"
                        onfocus="this.style.borderColor='var(--primary)'; this.style.borderStyle='solid'; this.style.boxShadow='0 0 0 4px rgba(62, 78, 60, 0.15)';"
                        onblur="this.style.borderColor='var(--border-cream)'; this.style.borderStyle='dashed'; this.style.boxShadow='none';">
                </div>
            </form>
        </div>

        <!-- การแสดงผลแบบ Grid Layout สไตล์แฟ้มเอกสาร -->
        <div class="row g-4">
            @forelse ($blogs as $item)
                <div class="col-md-6 col-lg-4">
                    <div class="blog-card dossier-card p-4 pt-5">

                        <!-- เลขแฟ้มมุมบน -->
                        <span class="file-tab">NO.
                            {{ str_pad($blogs->firstItem() + $loop->index, 3, '0', STR_PAD_LEFT) }}</span>

                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div class="monogram"
                                style="width: 44px; height: 44px; font-size: 1.1rem; border-radius: 12px;">
                                <i class="bi bi-journal-text"></i>
                            </div>

                            <!-- สถานะ Pill Badge -->
                            @if ($item->status == 'published')
                                <span
                                    style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 13px; font-size: 0.72rem; font-family: var(--font-mono); text-transform: uppercase; letter-spacing: 0.04em; font-weight: 700; color: #16a34a; background-color: #f0fdf4; border-radius: 20px; border: 1px solid rgba(22, 163, 74, 0.2);">
                                    <span
                                        style="width: 6px; height: 6px; background-color: #16a34a; border-radius: 50%;"></span>
                                    เผยแพร่
                                </span>
                            @else
                                <span
                                    style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 13px; font-size: 0.72rem; font-family: var(--font-mono); text-transform: uppercase; letter-spacing: 0.04em; font-weight: 700; color: #dc2626; background-color: #fef2f2; border-radius: 20px; border: 1px solid rgba(220, 38, 38, 0.2);">
                                    <span
                                        style="width: 6px; height: 6px; background-color: #dc2626; border-radius: 50%;"></span>
                                    ไม่เผยแพร่
                                </span>
                            @endif
                        </div>

                        <!-- ชื่อบทความ -->
                        <h3
                            style="font-size: 1.2rem; font-weight: 700; color: var(--slate-dark); margin-bottom: 12px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 3.0em;">
                            {{ $item->title }}
                        </h3>

                        <!-- เนื้อหาบทความย่อ -->
                        <p
                            style="color: var(--slate-gray); font-size: 0.95rem; line-height: 1.7; margin-bottom: 20px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; height: 5.1em;">
                            {{ $item->content }}
                        </p>

                        <div class="perforation mb-3"><span></span></div>

                        <!-- ปุ่มลิงก์ท้ายการ์ด -->
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="meta-label">อ่านต่อ</span>
                            <span class="read-more-arrow"
                                style="font-size: 0.9rem; font-weight: 700; color: var(--primary); display: inline-flex; align-items: center; gap: 6px; transition: gap 0.25s;">
                                รายละเอียด <i class="bi bi-arrow-right-short" style="font-size: 1.25rem;"></i>
                            </span>
                        </div>

                    </div>
                </div>
            @empty
                <!-- กรณีไม่พบข้อมูลบทความจากการค้นหา -->
                <div class="col-12 text-center py-5">
                    <div class="dossier-card d-inline-block p-5">
                        <div
                            style="width: 64px; height: 64px; background: var(--primary-light); color: var(--primary); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.6rem; margin-bottom: 20px;">
                            <i class="bi bi-search"></i>
                        </div>
                        <h4 style="font-size: 1.2rem; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">
                            ไม่พบข้อมูลบทความ</h4>
                        <p style="color: var(--slate-gray); font-size: 0.95rem; margin-bottom: 0;">
                            ลองเปลี่ยนคำค้นหาหรือล้างช่องค้นหาเพื่อดูข้อมูลทั้งหมด</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- ส่วนควบคุมการแบ่งหน้า (Laravel Pagination) -->
        <div class="d-flex justify-content-center mt-5 green-pagination">
            {{ $blogs->links('pagination::bootstrap-5') }}
        </div>

    </div>

    <!-- สไตล์สำหรับคอนเซปต์แฟ้มเอกสาร (Dossier) -->
    <style>
        .dossier-card {
            position: relative;
            border: 1px solid var(--border-cream);
            border-radius: 6px;
            background: #ffffff;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.015);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
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

        .monogram {
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .meta-label {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--slate-gray);
        }

        .headline-lede {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--slate-dark);
            letter-spacing: -0.02em;
            line-height: 1.15;
        }

        .perforation {
            display: flex;
            gap: 6px;
            margin: 0 -32px;
            padding: 0 32px;
            overflow: hidden;
        }

        .perforation span {
            flex: 1;
            border-top: 2px dotted var(--border-cream);
            height: 0;
            margin-top: 6px;
        }

        .blog-card:hover {
            border-color: rgba(62, 78, 60, 0.3) !important;
            transform: translateY(-6px);
            box-shadow: 0 20px 40px -15px rgba(62, 78, 60, 0.08) !important;
            cursor: pointer;
        }

        .blog-card:hover .read-more-arrow i {
            padding-left: 6px;
        }

        @media (max-width: 767px) {
            .headline-lede {
                font-size: 1.7rem;
            }
        }

        /* ย้อมสีระบบแบ่งหน้าของ Bootstrap 5 ให้เป็นสีเขียวเข้าธีม */
        .green-pagination .pagination .page-link {
            color: var(--primary) !important;
            border-color: var(--border-cream) !important;
            padding: 10px 18px;
            font-weight: 600;
            font-family: var(--font-mono);
            border-radius: 999px;
            margin: 0 4px;
            box-shadow: none !important;
            transition: all 0.25s ease;
        }

        .green-pagination .pagination .page-item.active .page-link {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
            color: white !important;
        }

        .green-pagination .pagination .page-link:hover {
            background-color: var(--primary-light) !important;
            border-color: var(--primary) !important;
        }

        .green-pagination .pagination .page-item.disabled .page-link {
            color: var(--slate-gray) !important;
            background-color: var(--bg-cream) !important;
        }
    </style>
@endsection
