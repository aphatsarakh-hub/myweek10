@extends('layout')

@section('title', 'บทความทั้งหมด')

@section('content')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Serif+Thai:wght@600;700&family=IBM+Plex+Sans+Thai:wght@400;500;600&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --ink-900: #1f2937;
            --ink-600: #6b7280;
            --ink-300: #d1d5db;
            --paper: #fff7ed;
            --paper-card: #ffffff;

            /* สีหลักส้ม */
            --orange: #f97316;
            --orange-dark: #ea580c;
            --orange-soft: #ffedd5;

            /* เผยแพร่ */
            --green: #16a34a;
            --green-soft: #dcfce7;

            /* ไม่เผยแพร่ / ลบ */
            --red: #dc2626;
            --red-soft: #fee2e2;

            /* แก้ไข */
            --yellow: #eab308;
            --yellow-dark: #ca8a04;
            --yellow-soft: #fef9c3;
        }

        .blog-page {
            background: var(--paper);
            padding: 56px 15px 80px;
        }

        .blog-container {
            max-width: 1020px;
            margin: 0 auto;
        }

        .blog-eyebrow {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-family: 'IBM Plex Sans Thai', sans-serif;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--orange-dark);
            margin-bottom: 10px;
        }

        .blog-eyebrow::before,
        .blog-eyebrow::after {
            content: "";
            width: 26px;
            height: 2px;
            background: var(--orange);
        }

        .blog-title {
            font-family: 'Noto Serif Thai', serif;
            text-align: center;
            font-size: 34px;
            font-weight: 700;
            color: var(--orange-dark);
            margin-bottom: 6px;
        }

        .blog-subtitle {
            text-align: center;
            font-family: 'IBM Plex Sans Thai', sans-serif;
            font-size: 14px;
            color: var(--ink-600);
            margin-bottom: 36px;
        }

        /* ตาราง */
        .blog-table-wrapper {
            background: var(--paper-card);
            border-radius: 8px;
            border: 1px solid #fed7aa;
            border-top: 4px solid var(--orange);
            box-shadow: 0 20px 40px -24px rgba(249, 115, 22, 0.35);
            overflow: hidden;
        }

        .blog-table {
            width: 100%;
            margin-bottom: 0;
            font-family: 'IBM Plex Sans Thai', sans-serif;
            font-size: 14.5px;
            vertical-align: middle;
            border-collapse: collapse;
        }

        .blog-table thead th {
            background: var(--orange-soft);
            border-bottom: 2px solid var(--orange);
            padding: 16px 14px;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            white-space: nowrap;
            color: var(--orange-dark);
        }

        .blog-table tbody tr {
            border-left: 4px solid transparent;
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }

        .blog-table tbody tr.row-published:hover {
            border-left-color: var(--green);
            background-color: #f0fdf4;
        }

        .blog-table tbody tr.row-draft:hover {
            border-left-color: var(--red);
            background-color: #fef2f2;
        }

        .blog-table tbody td {
            padding: 14px;
            border-bottom: 1px solid #fed7aa;
        }

        .blog-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ขนาดคอลัมน์ */
        .title-column {
            width: 48%;
            text-align: left;
            padding-left: 22px !important;
            font-weight: 500;
            color: var(--ink-900);
        }

        .status-column {
            width: 20%;
        }

        .edit-column,
        .control-column {
            width: 16%;
        }

        /* =====================================================
           STATUS SWITCH
        ===================================================== */

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 4px 12px 4px 4px;
            border-radius: 999px;
            font-family: 'IBM Plex Sans Thai', sans-serif;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid transparent;
            background: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .status-badge:hover {
            transform: translateY(-1px);
        }

        .status-badge:disabled,
        .status-badge.is-loading {
            opacity: 0.45;
            cursor: not-allowed;
            pointer-events: none;
        }

        .switch {
            position: relative;
            width: 34px;
            height: 19px;
            border-radius: 999px;
            background: var(--red);
            flex-shrink: 0;
            transition: background-color 0.2s ease;
        }

        .dot {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
            transition: transform 0.2s ease;
        }

        /* เผยแพร่ = สีเขียว */
        .status-published .switch {
            background: var(--green);
        }

        .status-published .dot {
            transform: translateX(15px);
        }

        .status-published .status-text {
            color: var(--green);
            font-weight: 700;
        }

        /* ไม่เผยแพร่ = สีแดง */
        .status-unpublished .switch {
            background: var(--red);
        }

        .status-unpublished .dot {
            transform: translateX(0);
        }

        .status-unpublished .status-text {
            color: var(--red);
            font-weight: 600;
        }

        .blog-btn {
            display: inline-block;
            min-width: 76px;
            padding: 7px 14px;
            font-family: 'IBM Plex Sans Thai', sans-serif;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-edit {
            color: #854d0e;
            border: 1px solid #facc15;
            background: #fef08a;
        }

        .btn-edit:hover {
            background: var(--yellow);
            border-color: var(--yellow-dark);
            color: #422006;
            transform: translateY(-1px);
        }

        
        .btn-delete {
            color: #991b1b;
            border: 1px solid #fca5a5;
            background: var(--red-soft);
        }

        .btn-delete:hover {
            background: var(--red);
            border-color: var(--red);
            color: #ffffff;
            transform: translateY(-1px);
        }

    
        .blog-pagination-wrap {
            margin-top: 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            font-family: 'IBM Plex Sans Thai', sans-serif;
        }

        .blog-pagination-wrap .pagination {
            margin-bottom: 0;
        }

        .blog-pagination-wrap .page-link {
            border: 1px solid #fed7aa;
            color: var(--orange-dark);
            padding: 7px 13px;
            font-size: 13px;
            margin-left: 4px;
            border-radius: 5px !important;
            line-height: 1.2;
        }

        .blog-pagination-wrap .page-link svg {
            width: 16px;
            height: 16px;
            vertical-align: middle;
        }

        .blog-pagination-wrap .page-item.active .page-link {
            background-color: var(--orange);
            border-color: var(--orange);
            color: #fff;
        }

        .blog-pagination-wrap .page-item.disabled .page-link {
            color: #d1d5db;
            background: transparent;
        }

        .blog-pagination-wrap .page-link:hover {
            background-color: var(--orange-soft);
            border-color: var(--orange);
            color: var(--orange-dark);
        }

        .blog-pagination-meta {
            font-size: 12.5px;
            color: var(--ink-600);
        }

        @media (max-width: 768px) {

            .blog-page {
                padding: 30px 12px 50px;
            }

            .blog-title {
                font-size: 26px;
            }

            .blog-table {
                font-size: 13px;
            }

            .blog-table thead th,
            .blog-table tbody td {
                padding: 10px 6px;
            }

            .blog-btn {
                min-width: unset;
                padding: 5px 8px;
                font-size: 12px;
            }

            .status-badge .status-text {
                display: none;
            }
        }
    </style>

    <div class="blog-page">
        <div class="blog-container">

            <p class="blog-eyebrow">โต๊ะบรรณาธิการ</p>
            <h2 class="blog-title">บทความทั้งหมด</h2>
            <p class="blog-subtitle">จัดการการเผยแพร่ แก้ไข และลบบทความในระบบ</p>

            <div class="blog-table-wrapper table-responsive">
                <table class="table blog-table text-center mb-0">
                    <thead>
                        <tr>
                            <th scope="col" class="title-column">Title</th>
                            <th scope="col" class="status-column">Status</th>
                            <th scope="col" class="edit-column">Edit</th>
                            <th scope="col" class="control-column">Control</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($blogs as $item)
                            <tr class="{{ $item->status === 'published' ? 'row-published' : 'row-draft' }}">

                                <td class="title-column">
                                    {{ $item->title }}
                                </td>

                                <td class="status-column">
                                    <button type="button"
                                        class="status-badge {{ $item->status === 'published' ? 'status-published' : 'status-unpublished' }}"
                                        data-id="{{ $item->id }}"
                                        data-status="{{ $item->status === 'published' ? 'published' : 'draft' }}"
                                        data-url="{{ url('/change/' . $item->id) }}" title="คลิกเพื่อเปลี่ยนสถานะ">
                                        <span class="switch"><span class="dot"></span></span>
                                        <span
                                            class="status-text">{{ $item->status === 'published' ? 'เผยแพร่' : 'ไม่เผยแพร่' }}</span>
                                    </button>
                                </td>

                                <td class="edit-column">
                                    <a href="{{ Route('edit', $item->id) }}" class="blog-btn btn-edit">
                                        แก้ไข
                                    </a>
                                </td>

                                <td class="control-column">
                                    <a href="{{ Route('delete', $item->id) }}" class="blog-btn btn-delete"
                                        onclick="return confirm('คุณต้องการลบบทความนี้ {{ $item->title }} จริงหรือไม่?')">
                                        ลบ
                                    </a>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
            @if (method_exists($blogs, 'links'))
                <div class="blog-pagination-wrap">
                    {{ $blogs->onEachSide(1)->links('pagination::bootstrap-5') }}
                    <p class="blog-pagination-meta">
                        แสดง {{ $blogs->firstItem() }}–{{ $blogs->lastItem() }} จากทั้งหมด {{ $blogs->total() }} บทความ
                    </p>
                </div>
            @endif

        </div>
    </div>

    <script>
        document.querySelectorAll('.status-badge').forEach(function(badge) {
            badge.addEventListener('click', function() {
                if (badge.classList.contains('is-loading')) return;

                const url = badge.dataset.url;
                const currentStatus = badge.dataset.status; 
                const row = badge.closest('tr');

                badge.classList.add('is-loading');

                fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    })
                    .then(function(res) {
                        if (!res.ok) throw new Error('Request failed');
                        return res.json().catch(function() {
                            return null;
                        });
                    })
                    .then(function(data) {
                        const newStatus = (data && data.status) ?
                            data.status :
                            (currentStatus === 'published' ? 'draft' : 'published');

                        const text = badge.querySelector('.status-text');
                        badge.dataset.status = newStatus;

                        if (newStatus === 'published') {
                            badge.classList.remove('status-unpublished');
                            badge.classList.add('status-published');
                            text.textContent = 'เผยแพร่';
                            if (row) {
                                row.classList.remove('row-draft');
                                row.classList.add('row-published');
                            }
                        } else {
                            badge.classList.remove('status-published');
                            badge.classList.add('status-unpublished');
                            text.textContent = 'ไม่เผยแพร่';
                            if (row) {
                                row.classList.remove('row-published');
                                row.classList.add('row-draft');
                            }
                        }
                    })
                    .catch(function() {
                        alert('เปลี่ยนสถานะไม่สำเร็จ กรุณาลองใหม่อีกครั้ง');
                    })
                    .finally(function() {
                        badge.classList.remove('is-loading');
                    });
            });
        });
    </script>

@endsection
