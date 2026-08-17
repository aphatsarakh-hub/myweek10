<?php
require_once __DIR__ . '/../includes/boot.php';

require_login();
require_role('customer');

$pageTitle = 'ยืนยันการจอง - BamBam Cat Hotel';
$showPrimaryAction = false;

$customer_id = (int)($_SESSION['user']['customer_id'] ?? 0);
$bookingId = (int)($_GET['id'] ?? 0);

global $mysqli;

// ดึงข้อมูลการจอง พร้อมห้องพักและน้องแมว (prepared statement)
$stmt = $mysqli->prepare("
    SELECT b.*, r.type_name, r.room_number, r.price_per_night AS room_price,
           c.name AS cat_name, c.photo AS cat_photo, c.gender AS cat_gender, c.age AS cat_age
    FROM booking b
    JOIN roomtype r ON b.roomtype_id = r.roomtype_id
    LEFT JOIN cat c ON b.cat_id = c.cat_id
    WHERE b.booking_id = ? AND b.customer_id = ?
");
$stmt->bind_param('ii', $bookingId, $customer_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    flash('message', 'ไม่พบข้อมูลการจองที่ถูกต้อง กรุณาเริ่มทำการจองใหม่อีกครั้ง');
    header('Location: ' . url('Customer/booking.php'));
    exit;
}

$startDate = new DateTime($booking['start_date']);
$endDate = new DateTime($booking['end_date']);
$nights = max(1, $startDate->diff($endDate)->days);
$room_total_cost = $nights * (float)$booking['room_price'];

// ตาราง booking เก็บบริการเสริมในคอลัมน์ `services` รูปแบบ "id:qty,id:qty" (รองรับของเก่า "1,2,3" ด้วย)
function parse_services_string_bc(?string $str): array {
    $out = [];
    if (empty($str)) return $out;
    foreach (explode(',', $str) as $part) {
        $part = trim($part);
        if ($part === '') continue;
        if (strpos($part, ':') !== false) {
            [$sid, $qty] = explode(':', $part, 2);
            $sid = (int)$sid; $qty = (int)$qty;
        } else {
            $sid = (int)$part; $qty = 1;
        }
        if ($sid > 0 && $qty > 0) $out[$sid] = ($out[$sid] ?? 0) + $qty;
    }
    return $out;
}

$selectedServiceQty = parse_services_string_bc($booking['services'] ?? null);
$selectedServices = [];
$services_cost = 0.0;

if (!empty($selectedServiceQty)) {
    $ids = array_keys($selectedServiceQty);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $svc_stmt = $mysqli->prepare("SELECT * FROM service WHERE service_id IN ($placeholders)");
    $svc_stmt->bind_param($types, ...$ids);
    $svc_stmt->execute();
    $svc_result = $svc_stmt->get_result();
    while ($row = $svc_result->fetch_assoc()) {
        $sid = (int)$row['service_id'];
        $qty = $selectedServiceQty[$sid];
        $row['qty'] = $qty;
        $row['line_total'] = (float)$row['price'] * $qty;
        $services_cost += $row['line_total'];
        $selectedServices[] = $row;
    }
    $svc_stmt->close();
}

$grand_total = $room_total_cost + $services_cost;

// ยอดมัดจำ 60% ของยอดรวม ส่วนที่เหลือชำระวันเช็คอิน
$DEPOSIT_PERCENT = 0.6;
$deposit_amount = round($grand_total * $DEPOSIT_PERCENT);

// อัปเดตยอดรวมล่าสุดลงฐานข้อมูล (เผื่อราคาบริการเสริมมีการเปลี่ยนแปลง)
$upd = $mysqli->prepare("UPDATE booking SET total_price = ? WHERE booking_id = ? AND customer_id = ?");
$upd->bind_param('dii', $grand_total, $bookingId, $customer_id);
$upd->execute();
$upd->close();

function get_confirm_room_image($booking) {
    $luxuryInteriors = ['../uploads/room/1.jpg', '../uploads/room/2.jpg', '../uploads/room/3.jpg', '../uploads/room/4.avif'];
    $idx = (int)$booking['roomtype_id'] % count($luxuryInteriors);
    return $luxuryInteriors[$idx];
}
function get_confirm_cat_image($booking) {
    if (!empty($booking['cat_photo'])) return '../uploads/cat/' . $booking['cat_photo'];
    return '../assets/img/cat-placeholder.png';
}
function get_confirm_service_image($service) {
    if (!empty($service['image_path'])) return '../uploads/services/' . $service['image_path'];
    return 'https://images.unsplash.com/photo-1548767797-d8c844146e4c?auto=format&fit=crop&w=800&q=80';
}
function get_confirm_room_display_name($booking) {
    $name = $booking['type_name'] ?? '';
    if (!empty($booking['room_number'])) $name .= ' (' . $booking['room_number'] . ')';
    return $name;
}

$roomAmenities = [
    ['icon' => 'fa-door-closed', 'label' => 'ห้องแยกเป็นส่วนตัว'],
    ['icon' => 'fa-video',       'label' => 'CCTV 24 ชม.'],
    ['icon' => 'fa-snowflake',   'label' => 'แอร์ 24 ชม.'],
    ['icon' => 'fa-user-tie',    'label' => 'พนักงานดูแลตลอด 24 ชม.'],
];

$steps = [
    ['label' => 'เลือกห้องพัก'],
    ['label' => 'เลือกวันที่'],
    ['label' => 'บริการเสริม'],
    ['label' => 'ยืนยันการจอง'],
    ['label' => 'ชำระเงินมัดจำ'],
];

include __DIR__ . '/../includes/header.php';
$flashMsg = flash('message');
?>

<div class="min-h-screen bg-[#FAFAF8] p-4 md:p-8 font-sans text-stone-800">
    <div class="max-w-7xl mx-auto">

        <?php if ($flashMsg): ?>
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3 rounded-xl text-sm flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-600"></i>
            <?= htmlspecialchars($flashMsg) ?>
        </div>
        <?php endif; ?>

        <div class="mb-6 flex items-center flex-wrap gap-2 text-xs font-medium">
            <?php foreach ($steps as $i => $step): ?>
                <?php $active = $i === 3; $done = $i < 3; ?>
                <div class="flex items-center gap-2 rounded-xl px-4 py-2.5 shadow-sm <?= $active ? 'bg-[#C86428] text-white' : ($done ? 'bg-white border border-[#C86428]/30 text-[#C86428]' : 'bg-white border border-stone-200 text-stone-400') ?>">
                    <span class="flex h-5 w-5 items-center justify-center rounded-lg text-[10px] <?= $active ? 'bg-white/25' : ($done ? 'bg-[#C86428]/10' : 'bg-stone-100') ?>">
                        <?= $done ? '<i class="fas fa-check"></i>' : $i + 1 ?>
                    </span>
                    <?= htmlspecialchars($step['label']) ?>
                </div>
                <?php if ($i < count($steps) - 1): ?>
                    <i class="fas fa-chevron-right text-stone-300 text-[10px]"></i>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 items-start">

            <div class="space-y-6">

                <!-- รายละเอียดห้องพัก -->
                <div class="bg-white border border-stone-200/70 rounded-2xl shadow-sm p-6">
                    <h2 class="text-base font-semibold text-stone-900 mb-4">รายละเอียดการจอง</h2>
                    <div class="flex flex-col sm:flex-row gap-5">
                        <div class="w-full sm:w-48 h-32 rounded-xl overflow-hidden bg-stone-100 shrink-0">
                            <img src="<?= htmlspecialchars(get_confirm_room_image($booking)) ?>" class="w-full h-full object-cover" alt="">
                        </div>
                        <div class="flex-1">
                            <div class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">ประเภทห้อง</div>
                            <div class="text-base font-bold text-stone-900 mb-3"><?= htmlspecialchars(get_confirm_room_display_name($booking)) ?></div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <?php foreach ($roomAmenities as $amenity): ?>
                                <div class="flex flex-col items-center text-center gap-1">
                                    <i class="fas <?= htmlspecialchars($amenity['icon']) ?> text-stone-500 text-sm"></i>
                                    <span class="text-[9px] text-stone-400 leading-tight"><?= htmlspecialchars($amenity['label']) ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลการเข้าพัก -->
                <div class="bg-white border border-stone-200/70 rounded-2xl shadow-sm p-6">
                    <h2 class="text-base font-semibold text-stone-900 mb-4">ข้อมูลการเข้าพัก</h2>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                        <div class="flex items-center gap-3 shrink-0">
                            <img src="<?= htmlspecialchars(get_confirm_cat_image($booking)) ?>" class="w-14 h-14 rounded-full object-cover border border-stone-200" alt="">
                            <div>
                                <div class="text-[10px] text-stone-400 mb-0.5">น้องแมว</div>
                                <div class="text-sm font-bold text-stone-900"><?= htmlspecialchars($booking['cat_name'] ?? '—') ?></div>
                                <div class="text-[10px] text-stone-400">
                                    <?= !empty($booking['cat_gender']) ? 'เพศ' . htmlspecialchars($booking['cat_gender']) : '' ?>
                                    <?= !empty($booking['cat_age']) ? ' | อายุ ' . (int)$booking['cat_age'] . ' ปี' : '' ?>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3 flex-1 w-full">
                            <div class="rounded-xl bg-stone-50/70 border border-stone-100 px-3 py-2 text-center">
                                <div class="text-[9px] font-bold text-stone-400 uppercase tracking-wider mb-1">เช็คอิน</div>
                                <div class="text-xs font-bold text-stone-800"><?= htmlspecialchars(date('d/m/Y', strtotime($booking['start_date']))) ?></div>
                                <div class="text-[9px] text-stone-400">14:00 น.</div>
                            </div>
                            <div class="rounded-xl bg-stone-50/70 border border-stone-100 px-3 py-2 text-center">
                                <div class="text-[9px] font-bold text-stone-400 uppercase tracking-wider mb-1">เช็คเอาท์</div>
                                <div class="text-xs font-bold text-stone-800"><?= htmlspecialchars(date('d/m/Y', strtotime($booking['end_date']))) ?></div>
                                <div class="text-[9px] text-stone-400">12:00 น.</div>
                            </div>
                            <div class="rounded-xl bg-stone-50/70 border border-stone-100 px-3 py-2 text-center">
                                <div class="text-[9px] font-bold text-stone-400 uppercase tracking-wider mb-1">จำนวนคืน</div>
                                <div class="text-xs font-bold text-stone-800"><?= $nights ?> คืน</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- บริการเสริมที่เลือก -->
                <div class="bg-white border border-stone-200/70 rounded-2xl shadow-sm p-6">
                    <h2 class="text-base font-semibold text-stone-900 mb-4">บริการเสริมที่เลือก (<?= count($selectedServices) ?> รายการ)</h2>
                    <?php if (!empty($selectedServices)): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <?php foreach ($selectedServices as $service): ?>
                        <div class="border border-stone-200 rounded-xl overflow-hidden flex flex-col">
                            <div class="h-24 bg-stone-100 overflow-hidden">
                                <img src="<?= htmlspecialchars(get_confirm_service_image($service)) ?>" class="w-full h-full object-cover" alt="">
                            </div>
                            <div class="p-3">
                                <h3 class="text-xs font-bold text-stone-900 mb-1"><?= htmlspecialchars($service['service_name']) ?></h3>
                                <p class="text-[10px] text-stone-400 mb-2 line-clamp-2"><?= htmlspecialchars(trim($service['service_detail'] ?? '') ?: 'บริการดูแลน้องแมวโดยทีมงานผู้เชี่ยวชาญ') ?></p>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-[#C86428]">฿<?= number_format((float)$service['price'], 0) ?></span>
                                    <span class="text-[10px] font-semibold text-stone-500">x <?= (int)$service['qty'] ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-stone-400 text-xs py-4 text-center">ไม่ได้เลือกบริการเสริมเพิ่มเติม</p>
                    <?php endif; ?>
                </div>

                <!-- หมายเหตุ -->
                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
                    <h3 class="text-xs font-bold text-amber-800 mb-2 flex items-center gap-2">
                        <i class="fas fa-info-circle"></i> หมายเหตุ
                    </h3>
                    <ul class="text-[11px] text-amber-800/90 space-y-1 list-disc list-inside">
                        <li>กรุณามาถึงตามเวลาเช็คอินที่กำหนดเพื่อความสะดวกในการเข้าพักของน้องแมว</li>
                        <li>หากต้องการเลื่อนหรือยกเลิกการจอง กรุณาแจ้งล่วงหน้าอย่างน้อย 24 ชั่วโมง</li>
                    </ul>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="service.php?id=<?= $bookingId ?>" class="w-full sm:w-auto flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-xl border border-stone-200 text-stone-600 hover:bg-stone-50 py-3.5 px-6 text-xs font-semibold transition">
                        <i class="fas fa-arrow-left text-[10px]"></i> กลับไปแก้ไขข้อมูล
                    </a>
                    <a href="payment.php?id=<?= $bookingId ?>" class="w-full sm:flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-[#C86428] hover:bg-[#B0561F] text-white py-3.5 px-6 text-xs font-bold shadow-md transition">
                        ยืนยันการจองและดำเนินการชำระเงินมัดจำ <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <div class="bg-white border border-stone-200/70 p-6 rounded-2xl shadow-sm sticky top-6">
                <h3 class="text-sm font-bold text-stone-900 pb-3 border-b border-stone-100 mb-5">สรุปการจอง</h3>

                <div class="flex items-center gap-3 mb-5">
                    <img src="<?= htmlspecialchars(get_confirm_cat_image($booking)) ?>" class="w-10 h-10 rounded-full object-cover border border-stone-200" alt="">
                    <div>
                        <div class="text-xs font-bold text-stone-900"><?= htmlspecialchars($booking['cat_name'] ?? '—') ?></div>
                        <div class="text-[10px] text-stone-400">เจ้าของ: คุณ<?= htmlspecialchars(trim(($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['last_name'] ?? ''))) ?></div>
                    </div>
                </div>

                <div class="space-y-3 text-xs mb-5">
                    <div class="flex justify-between items-center text-stone-500">
                        <span>ประเภทห้อง</span>
                        <span class="font-semibold text-stone-800 text-right truncate max-w-[150px]"><?= htmlspecialchars(get_confirm_room_display_name($booking)) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500">
                        <span>เช็คอิน</span>
                        <span class="font-semibold text-stone-800"><?= htmlspecialchars(date('d/m/Y', strtotime($booking['start_date']))) ?> (14:00)</span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500">
                        <span>เช็คเอาท์</span>
                        <span class="font-semibold text-stone-800"><?= htmlspecialchars(date('d/m/Y', strtotime($booking['end_date']))) ?> (12:00)</span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500">
                        <span>จำนวนคืน</span>
                        <span class="font-semibold text-stone-800"><?= $nights ?> คืน</span>
                    </div>
                </div>

                <div class="my-4 border-t border-dashed border-stone-200"></div>

                <div class="mb-3">
                    <h4 class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2">บริการเสริมที่เลือก (<?= count($selectedServices) ?> รายการ)</h4>
                    <div class="space-y-2 text-xs">
                        <?php if (!empty($selectedServices)): ?>
                            <?php foreach ($selectedServices as $service): ?>
                            <div class="flex justify-between items-center text-stone-600">
                                <span class="truncate max-w-[160px]"><?= htmlspecialchars($service['service_name']) ?> <span class="text-stone-400">x<?= (int)$service['qty'] ?></span></span>
                                <span class="font-semibold text-stone-800">฿<?= number_format($service['line_total'], 0) ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-stone-400 italic">ยังไม่ได้เลือกบริการเสริม</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="space-y-3 text-xs mb-5 mt-4">
                    <div class="flex justify-between items-center text-stone-500">
                        <span>ค่าห้องพัก (฿<?= number_format((float)$booking['room_price'], 0) ?> x <?= $nights ?> คืน)</span>
                        <span class="font-semibold text-stone-800"><?= number_format($room_total_cost, 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500">
                        <span>บริการเสริม</span>
                        <span class="font-semibold text-stone-800"><?= number_format($services_cost, 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500">
                        <span>ส่วนลด</span>
                        <span class="font-semibold text-stone-800">0</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-stone-100 flex justify-between items-baseline mb-1">
                    <span class="text-xs font-semibold text-stone-700">ยอดรวมทั้งหมด</span>
                    <span class="text-xl font-bold text-[#C86428]">฿<?= number_format($grand_total, 0) ?></span>
                </div>
                <p class="text-[10px] text-stone-400 mb-5">ราคานี้รวมภาษีมูลค่าเพิ่ม 7% แล้ว</p>

                <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-4 text-center">
                    <div class="flex items-center justify-center gap-1.5 text-emerald-700 text-xs font-bold mb-1">
                        <i class="fas fa-shield-alt"></i> ต้องชำระเงินมัดจำ
                    </div>
                    <div class="text-2xl font-black text-emerald-600">฿<?= number_format($deposit_amount, 0) ?></div>
                    <div class="text-[10px] text-emerald-700/80 mt-1"><?= $DEPOSIT_PERCENT * 100 ?>% ของยอดรวมทั้งหมด</div>
                    <div class="text-[10px] text-stone-400 mt-1">ส่วนที่เหลือชำระในวันเช็คอิน</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>