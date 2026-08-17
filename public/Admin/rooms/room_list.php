<?php
require_once __DIR__ . '/../../includes/boot.php';

/*
 |--------------------------------------------------------------------------
 | 1) Action: เปลี่ยนสถานะเมื่อทำความสะอาดเรียบร้อย
 |--------------------------------------------------------------------------
*/
if (isset($_GET['mark_clean'])) {
    $roomId = (int) $_GET['mark_clean'];
    if (function_exists('update_room_status')) {
        update_room_status($roomId, 'Available');
    }
    header('Location: ' . basename($_SERVER['PHP_SELF']) . (isset($_GET['type']) ? '?type=' . urlencode($_GET['type']) : ''));
    exit;
}

/*
 |--------------------------------------------------------------------------
 | 1b) Action: ยกเลิกการจอง -> ห้องกลับมาว่างอัตโนมัติ ไม่คืนเงิน
 |--------------------------------------------------------------------------
*/
if (isset($_GET['cancel_booking'])) {
    $bookingId = (int) $_GET['cancel_booking'];
    if (function_exists('cancel_booking')) {
        cancel_booking($bookingId, 'ยกเลิกโดยแอดมินผ่านหน้าจัดการห้องพัก');
        flash('message', 'ยกเลิกการจองสำเร็จ ห้องกลับมาว่างแล้ว');
    }
    header('Location: ' . basename($_SERVER['PHP_SELF']) . (isset($_GET['type']) ? '?type=' . urlencode($_GET['type']) : ''));
    exit;
}

$pageTitle = 'จัดการห้องพักแมว';
$showPrimaryAction = false;

/*
 |--------------------------------------------------------------------------
 | 2) รับค่า Filter จาก URL
 |--------------------------------------------------------------------------
*/
$typeFilter   = $_GET['type'] ?? 'All';
$searchTerm   = trim($_GET['q'] ?? '');

/*
 |--------------------------------------------------------------------------
 | 3) ดึงข้อมูลห้องพัก
 |--------------------------------------------------------------------------
*/
if (function_exists('fetch_rooms')) {
    $allRooms = fetch_rooms();
} else {
    if (function_exists('fetch_roomtypes')) {
        $roomtypes = fetch_roomtypes();
    } else {
        $roomtypes = [
            ['roomtype_id' => 1, 'type_name' => 'standard', 'price_per_night' => 350],
            ['roomtype_id' => 2, 'type_name' => 'VIP', 'price_per_night' => 650],
            ['roomtype_id' => 3, 'type_name' => 'standard รายเดือน', 'price_per_night' => 7500],
            ['roomtype_id' => 4, 'type_name' => 'VIP รายเดือน', 'price_per_night' => 12000],
        ];
    }

    $allRooms = array_map(function ($rt) {
        return [
            'room_id'          => $rt['roomtype_id'],
            'room_number'      => 'RM-' . str_pad($rt['roomtype_id'], 2, '0', STR_PAD_LEFT),
            'roomtype_id'      => $rt['roomtype_id'],
            'type_name'        => $rt['type_name'],
            'price_per_night'  => $rt['price_per_night'],
            'status'           => 'Available',
            'image_url'        => $rt['image_url'] ?? null,
            'temperature'      => '24.0°C',
        ];
    }, $roomtypes);
}

$typeNames = [];
foreach ($allRooms as $r) {
    $t = $r['type_name'] ?? '';
    if ($t !== '' && !in_array($t, $typeNames, true)) {
        $typeNames[] = $t;
    }
}
sort($typeNames);

$rooms = array_filter($allRooms, function ($r) use ($typeFilter, $searchTerm) {
    $matchesType = ($typeFilter === 'All') || (($r['type_name'] ?? '') === $typeFilter);
    if (!$matchesType) return false;
    if ($searchTerm === '') return true;
    $haystack = mb_strtolower(($r['room_number'] ?? '') . ' ' . ($r['type_name'] ?? ''));
    return mb_strpos($haystack, mb_strtolower($searchTerm)) !== false;
});

/*
 |--------------------------------------------------------------------------
 | 4) คำนวณสถิติภาพรวม (Dashboard Metrics)
 |--------------------------------------------------------------------------
*/
$totalRooms       = count($allRooms);
$availableCount   = count(array_filter($allRooms, fn($r) => ucfirst(strtolower(trim($r['status'] ?? ''))) === 'Available'));
$reservedCount    = count(array_filter($allRooms, fn($r) => ucfirst(strtolower(trim($r['status'] ?? ''))) === 'Reserved'));
$occupiedCount    = count(array_filter($allRooms, fn($r) => ucfirst(strtolower(trim($r['status'] ?? ''))) === 'Occupied'));
$maintenanceCount = count(array_filter($allRooms, fn($r) => ucfirst(strtolower(trim($r['status'] ?? ''))) === 'Maintenance'));

/*
 |--------------------------------------------------------------------------
 | 5) ดึงรูปภาพภายในโรงแรมแมวสไตล์ Luxury
 |--------------------------------------------------------------------------
*/
function get_luxury_cat_hotel_image($room, $index)
{
    if (!empty($room['image_url'])) {
        return '../../uploads/room/' . $room['image_url'];
    }
    $luxuryInteriors = [
        '../../uploads/room/1.jpg',
        '../../uploads/room/2.jpg',
        '../../uploads/room/3.jpg',
        '../../uploads/room/4.avif',
    ];
    return $luxuryInteriors[$index % count($luxuryInteriors)];
}

$statusConfig = [
    'Available' => [
        'label' => 'Available',
        'sub'   => 'ว่างพร้อมให้บริการ',
        'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200/80',
        'dot'   => 'bg-emerald-500',
    ],
    'Reserved' => [
        'label' => 'Reserved',
        'sub'   => 'ติดจองแล้ว รอเข้าพัก',
        'badge' => 'bg-purple-50 text-purple-800 border-purple-200/80',
        'dot'   => 'bg-purple-500',
    ],
    'Occupied' => [
        'label' => 'Occupied',
        'sub'   => 'กำลังเข้าพัก',
        'badge' => 'bg-amber-50 text-amber-900 border-amber-200/80',
        'dot'   => 'bg-[#C86428]',
    ],
    'Cleaning' => [
        'label' => 'Cleaning',
        'sub'   => 'รอทำความสะอาด',
        'badge' => 'bg-blue-50 text-blue-900 border-blue-200/80',
        'dot'   => 'bg-blue-600',
    ],
    'Maintenance' => [
        'label' => 'Maintenance',
        'sub'   => 'ปิดบำรุงรักษา',
        'badge' => 'bg-stone-100 text-stone-700 border-stone-300/80',
        'dot'   => 'bg-stone-400',
    ],
];

include __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen bg-[#FAFAF8] p-6 lg:p-10 font-sans text-stone-800">

    <?php if ($msg = flash('message')): ?>
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3 rounded-xl text-sm">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 pb-6 border-b border-stone-200/80 gap-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-[#C86428] mb-2">
                <span>Property Management</span>
                <span>&bull;</span>
                <span>Suites & Units</span>
            </div>
            <h1 class="text-3xl font-light text-stone-900 tracking-tight"></h1>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:w-72">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-stone-400 text-xs"></i>
                <input type="text"
                       data-admin-search-input
                       data-admin-search=".room-card"
                       placeholder="ค้นหาเลขห้อง, ประเภทห้องพัก..."
                       class="w-full pl-10 pr-4 py-2.5 bg-white border border-stone-200 rounded-xl text-sm text-stone-800 placeholder-stone-400 focus:ring-1 focus:ring-[#C86428] focus:border-[#C86428] outline-none shadow-sm transition">
            </div>
            <a href="../cancellations/cancel_list.php" class="bg-white border border-stone-200 hover:bg-stone-50 text-stone-600 px-5 py-2.5 rounded-xl text-sm font-medium shadow-sm transition duration-200 flex items-center justify-center gap-2 shrink-0">
                <i class="fas fa-clock-rotate-left text-xs"></i> ประวัติการยกเลิก
            </a>
            <a href="room_add.php" class="bg-[#2C2523] hover:bg-[#C86428] text-white px-5 py-2.5 rounded-xl text-sm font-medium shadow-sm hover:shadow transition duration-200 flex items-center justify-center gap-2 shrink-0">
                <i class="fas fa-plus text-xs"></i> เพิ่มห้องพัก
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-10">
        <div class="bg-white p-6 rounded-2xl border border-stone-200/60 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Total Units</span>
                <span class="w-2 h-2 rounded-full bg-stone-300"></span>
            </div>
            <div>
                <div class="text-3xl font-light text-stone-900"><?= number_format($totalRooms) ?> <span class="text-sm font-normal text-stone-400">ห้อง</span></div>
                <div class="text-xs text-stone-500 mt-1">จำนวนห้องพักทั้งหมดในโครงการ</div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-stone-200/60 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Available</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            </div>
            <div>
                <div class="text-3xl font-light text-stone-900"><?= number_format($availableCount) ?> <span class="text-sm font-normal text-stone-400">ห้อง</span></div>
                <div class="text-xs text-emerald-700 mt-1 font-medium">พร้อมเปิดรับเช็คอิน</div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-stone-200/60 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Reserved</span>
                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
            </div>
            <div>
                <div class="text-3xl font-light text-stone-900"><?= number_format($reservedCount) ?> <span class="text-sm font-normal text-stone-400">ห้อง</span></div>
                <div class="text-xs text-purple-700 mt-1 font-medium">ติดจองแล้ว รอเข้าพัก</div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-stone-200/60 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Occupied</span>
                <span class="w-2 h-2 rounded-full bg-[#C86428]"></span>
            </div>
            <div>
                <div class="text-3xl font-light text-stone-900"><?= number_format($occupiedCount) ?> <span class="text-sm font-normal text-stone-400">ห้อง</span></div>
                <div class="text-xs text-[#C86428] mt-1 font-medium">อยู่ระหว่างการเข้าพัก</div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-stone-200/60 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Maintenance</span>
                <span class="w-2 h-2 rounded-full bg-stone-400"></span>
            </div>
            <div>
                <div class="text-3xl font-light text-stone-900"><?= number_format($maintenanceCount) ?> <span class="text-sm font-normal text-stone-400">ห้อง</span></div>
                <div class="text-xs text-stone-500 mt-1">ปิดตรวจสอบและบำรุงรักษา</div>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between border-b border-stone-200/80 pb-4 mb-8">
        <div class="flex items-center gap-2 overflow-x-auto w-full no-scrollbar">
            <a href="?<?= htmlspecialchars(http_build_query(array_filter(['q' => $searchTerm ?: null]))) ?>"
               class="<?= $typeFilter === 'All' ? 'bg-[#2C2523] text-white font-medium shadow-sm' : 'bg-white text-stone-600 hover:bg-stone-100/80 border border-stone-200/60' ?> px-5 py-2 rounded-xl text-xs tracking-wide transition duration-150 whitespace-nowrap">
                ทั้งหมด
            </a>
            <?php foreach ($typeNames as $t): ?>
                <a href="?<?= htmlspecialchars(http_build_query(array_filter(['type' => $t, 'q' => $searchTerm ?: null]))) ?>"
                   class="<?= $typeFilter === $t ? 'bg-[#2C2523] text-white font-medium shadow-sm' : 'bg-white text-stone-600 hover:bg-stone-100/80 border border-stone-200/60' ?> px-5 py-2 rounded-xl text-xs tracking-wide transition duration-150 whitespace-nowrap">
                    <?= htmlspecialchars($t) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (empty($rooms)): ?>
        <div class="bg-white rounded-2xl p-16 text-center border border-stone-200/60 shadow-sm max-w-md mx-auto my-12">
            <div class="w-12 h-12 rounded-full bg-stone-100 flex items-center justify-center mx-auto mb-4 text-stone-400">
                <i class="fas fa-folder-open text-lg"></i>
            </div>
            <div class="font-medium text-stone-800">ไม่พบห้องพักที่ระบุ</div>
            <p class="text-stone-400 text-xs mt-1">กรุณาตรวจสอบคำค้นหาหรือตัวกรองประเภทห้องอีกครั้ง</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php
            $cardIndex = 0;
            foreach ($rooms as $room):
                $rawStatus = $room['status'] ?? 'Available';
                $status = ucfirst(strtolower(trim($rawStatus)));

                if (isset($statusConfig[$status])) {
                    $cfg = $statusConfig[$status];
                } else {
                    $cfg = [
                        'label' => $status ?: 'Unknown',
                        'sub'   => 'สถานะไม่ระบุ',
                        'badge' => 'bg-stone-100 text-stone-700 border-stone-300/80',
                        'dot'   => 'bg-stone-400',
                    ];
                }

                $imgSrc = get_luxury_cat_hotel_image($room, $cardIndex++);

                $searchText = implode(' ', [
                    $room['room_number'] ?? '',
                    $room['type_name'] ?? '',
                    $status,
                    $cfg['label'] ?? '',
                ]);
            ?>
                <div class="room-card admin-search-item bg-white rounded-2xl overflow-hidden border border-stone-200/70 shadow-[0_4px_20px_rgb(0,0,0,0.02)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] hover:border-[#C86428]/40 transition duration-300 flex flex-col group" data-searchable="<?= htmlspecialchars($searchText) ?>">

                    <div class="relative h-52 bg-stone-100 overflow-hidden">
                        <img src="<?= htmlspecialchars($imgSrc) ?>"
                             alt="<?= htmlspecialchars($room['type_name']) ?>"
                             loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out <?= $status === 'Maintenance' ? 'grayscale opacity-75' : '' ?>">

                        <div class="absolute top-3 right-3 px-3 py-1 rounded-full text-[11px] font-medium border <?= $cfg['badge'] ?> flex items-center gap-1.5 backdrop-blur-md shadow-sm">
                            <span class="w-1.5 h-1.5 rounded-full <?= $cfg['dot'] ?>"></span>
                            <?= htmlspecialchars($cfg['label']) ?>
                        </div>

                        <div class="absolute bottom-3 left-3 bg-[#2C2523]/90 backdrop-blur-md text-white px-3 py-1 rounded-lg text-xs font-medium tracking-wider shadow-sm">
                            <?= htmlspecialchars($room['room_number'] ?? ('RM-' . $room['room_id'])) ?>
                        </div>
                    </div>

                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start gap-2 mb-3">
                                <div>
                                    <h3 class="text-base font-semibold text-stone-900 group-hover:text-[#C86428] transition-colors leading-snug">
                                        <?= htmlspecialchars($room['type_name']) ?>
                                    </h3>
                                    <div class="text-[11px] text-stone-400 mt-0.5"><?= htmlspecialchars($cfg['sub']) ?></div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-lg font-bold text-stone-900"><?= number_format($room['price_per_night'], 0) ?></span>
                                    <span class="text-[11px] text-stone-400 font-normal block -mt-1">/ คืน</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 pt-3 border-t border-stone-100 text-xs text-stone-500 mb-4">
                                <span class="inline-flex items-center gap-1.5 bg-stone-50 px-2.5 py-1 rounded-md border border-stone-200/50">
                                    <i class="far fa-snowflake text-stone-400"></i>
                                    <span><?= htmlspecialchars($room['temperature'] ?? '24.0°C') ?></span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 bg-stone-50 px-2.5 py-1 rounded-md border border-stone-200/50">
                                    <i class="far fa-eye text-stone-400"></i>
                                    <span>CCTV 24/7</span>
                                </span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-stone-100 flex items-center justify-between">

                            <div class="flex items-center">
                                <?php if ($status === 'Available'): ?>
                                    <span class="text-[11px] text-emerald-700 font-medium flex items-center gap-1">
                                        <i class="far fa-check-circle"></i> ตรวจสอบแล้ว
                                    </span>
                                <?php elseif ($status === 'Reserved'): ?>
                                    <div class="text-[11px] text-purple-700 truncate max-w-[140px]">
                                        จองโดย: <span class="font-medium text-stone-900"><?= htmlspecialchars($room['current_customer_name'] ?? 'ไม่ระบุ') ?></span>
                                    </div>
                                <?php elseif ($status === 'Occupied'): ?>
                                    <div class="text-[11px] text-stone-600 truncate max-w-[140px]">
                                        แขก: <span class="font-medium text-stone-900"><?= htmlspecialchars($room['current_customer_name'] ?? 'ไม่ระบุ') ?></span>
                                    </div>
                                <?php elseif ($status === 'Cleaning'): ?>
                                    <span class="text-[11px] text-blue-700 font-medium">รอทำความสะอาด</span>
                                <?php elseif ($status === 'Maintenance'): ?>
                                    <span class="text-[11px] text-stone-400">ปิดปรับปรุงชั่วคราว</span>
                                <?php else: ?>
                                    <span class="text-[11px] text-stone-400 font-medium truncate max-w-[140px]">
                                        สถานะ: <?= htmlspecialchars($status) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center gap-3">
                                <?php if ($status === 'Available'): ?>
                                    <a href="room_edit.php?id=<?= urlencode($room['room_id']) ?>" class="text-xs font-medium text-stone-600 hover:text-[#C86428] transition flex items-center gap-1">
                                        <span>ตั้งค่า</span>
                                    </a>
                                <?php elseif ($status === 'Reserved'): ?>
                                    <a href="../bookings/booking_detail.php?id=<?= urlencode($room['booking_id']) ?>" class="text-xs font-medium text-stone-600 hover:text-[#C86428] transition">
                                        ดูการจอง
                                    </a>
                                    <span class="w-px h-3 bg-stone-200"></span>
                                    <a href="?cancel_booking=<?= urlencode($room['booking_id']) ?><?= $typeFilter !== 'All' ? '&type=' . urlencode($typeFilter) : '' ?>"
                                       onclick="return confirm('ยืนยันยกเลิกการจองนี้? ระบบจะไม่คืนเงินตามนโยบายโรงแรม');"
                                       class="text-xs font-medium text-red-500 hover:text-red-700 transition">
                                        ยกเลิกจอง
                                    </a>
                                <?php elseif ($status === 'Occupied'): ?>
                                    <a href="room_edit.php?id=<?= urlencode($room['room_id']) ?>" class="text-xs font-medium text-[#C86428] hover:underline">
                                        ดูข้อมูล
                                    </a>
                                <?php elseif ($status === 'Cleaning'): ?>
                                    <a href="?mark_clean=<?= urlencode($room['room_id']) ?><?= $typeFilter !== 'All' ? '&type=' . urlencode($typeFilter) : '' ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg text-xs font-medium transition shadow-sm">
                                        เสร็จสิ้น
                                    </a>
                                <?php else: ?>
                                    <a href="room_edit.php?id=<?= urlencode($room['room_id']) ?>" class="text-xs font-medium text-stone-600 hover:text-[#C86428] transition">
                                        จัดการ
                                    </a>
                                <?php endif; ?>

                                <?php if (!in_array($status, ['Reserved'], true)): ?>
                                    <span class="w-px h-3 bg-stone-200"></span>
                                    <a href="room_delete.php?id=<?= urlencode($room['room_id']) ?>"
                                       onclick="return confirm('ยืนยันการลบห้องพัก <?= htmlspecialchars($room['room_number'] ?? ('RM-'.$room['room_id'])) ?> ใช่หรือไม่? ข้อมูลจะไม่สามารถกู้คืนได้');"
                                       class="text-stone-300 hover:text-red-500 transition duration-200" title="ลบห้องพัก">
                                        <i class="far fa-trash-alt text-sm"></i>
                                    </a>
                                <?php endif; ?>
                            </div>

                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>