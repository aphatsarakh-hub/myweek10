<?php
require_once __DIR__ . '/../includes/boot.php';

require_login();
require_role('customer');

$pageTitle = 'บริการเสริมพิเศษ - BamBam Cat Hotel';
$showPrimaryAction = false;

$customer_id = (int)($_SESSION['user']['customer_id'] ?? 0);

// รับค่า booking_id จาก URL (GET) หรือจากฟอร์ม (POST) เพื่อความต่อเนื่องของระบบจอง
$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$bookingId) {
    $bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
}

global $mysqli;

// 1. ดึงข้อมูลการจองปัจจุบัน พร้อมข้อมูลห้องพักและน้องแมว (ใช้ prepared statement กันการฉีด SQL)
$booking_stmt = $mysqli->prepare("
    SELECT b.*, r.type_name, r.room_number, r.price_per_night AS room_price,
           c.name AS cat_name, c.photo AS cat_photo
    FROM booking b
    JOIN roomtype r ON b.roomtype_id = r.roomtype_id
    LEFT JOIN cat c ON b.cat_id = c.cat_id
    WHERE b.booking_id = ? AND b.customer_id = ?
");
$booking_stmt->bind_param('ii', $bookingId, $customer_id);
$booking_stmt->execute();
$booking = $booking_stmt->get_result()->fetch_assoc();
$booking_stmt->close();

if (!$booking) {
    flash('message', 'ไม่พบข้อมูลการจองห้องพักที่ถูกต้อง กรุณาเริ่มทำการจองใหม่อีกครั้ง');
    header('Location: ' . url('Customer/booking.php'));
    exit;
}

// คำนวณจำนวนคืนและราคารวมค่าห้องพัก
$startDate = new DateTime($booking['start_date']);
$endDate = new DateTime($booking['end_date']);
$nights = max(1, $startDate->diff($endDate)->days);
$room_total_cost = $nights * (float)$booking['room_price'];

// ตาราง booking เก็บบริการเสริมที่เลือกไว้ในคอลัมน์ `services`
// รูปแบบใหม่: "service_id:qty,service_id:qty" เพื่อรองรับจำนวน (กรณีมีน้องแมวหลายตัว/สั่งหลายครั้ง)
// ยังรองรับข้อมูลเก่ารูปแบบ "1,2,3" (ไม่มี qty) โดยถือว่า qty = 1
function parse_services_string(?string $str): array {
    $out = [];
    if (empty($str)) return $out;
    foreach (explode(',', $str) as $part) {
        $part = trim($part);
        if ($part === '') continue;
        if (strpos($part, ':') !== false) {
            [$sid, $qty] = explode(':', $part, 2);
            $sid = (int)$sid;
            $qty = (int)$qty;
        } else {
            $sid = (int)$part;
            $qty = 1;
        }
        if ($sid > 0 && $qty > 0) {
            $out[$sid] = ($out[$sid] ?? 0) + $qty;
        }
    }
    return $out;
}

function format_services_string(array $quantities): string {
    $parts = [];
    foreach ($quantities as $sid => $qty) {
        if ($qty > 0) $parts[] = $sid . ':' . $qty;
    }
    return implode(',', $parts);
}

$currentServices = parse_services_string($booking['services'] ?? null); // [service_id => qty]

// 2. ดึงรายการบริการเสริมที่เปิดใช้งานทั้งหมด (มาจากฐานเดียวกับ Admin/services/service_list.php)
$available_services = [];
$services_result = $mysqli->query("SELECT * FROM service WHERE status = 'Active' ORDER BY service_id ASC");
if ($services_result) {
    while ($row = $services_result->fetch_assoc()) {
        $available_services[(int)$row['service_id']] = $row;
    }
}

// 3. จัดการ Action: เพิ่ม/ลด/ลบ จำนวนบริการเสริม (POST) ด้วย prepared statement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['service_id'])) {
    $action = $_POST['action'];
    $serviceId = (int)$_POST['service_id'];
    $isAjax = !empty($_POST['ajax']);

    if (isset($available_services[$serviceId])) {
        $qty = $currentServices[$serviceId] ?? 0;
        if ($action === 'inc') {
            $qty += 1;
        } elseif ($action === 'dec') {
            $qty = max(0, $qty - 1);
        } elseif ($action === 'remove') {
            $qty = 0;
        }
        if ($qty > 0) {
            $currentServices[$serviceId] = $qty;
        } else {
            unset($currentServices[$serviceId]);
        }
    }

    $serviceIdsStr = format_services_string($currentServices);

    $services_total_cost = 0.0;
    foreach ($currentServices as $sId => $qty) {
        if (isset($available_services[$sId])) {
            $services_total_cost += (float)$available_services[$sId]['price'] * $qty;
        }
    }
    $grand_total = $room_total_cost + $services_total_cost;

    $update_stmt = $mysqli->prepare("UPDATE booking SET services = ?, total_price = ? WHERE booking_id = ? AND customer_id = ?");
    $update_stmt->bind_param('sdii', $serviceIdsStr, $grand_total, $bookingId, $customer_id);
    $update_stmt->execute();
    $update_stmt->close();

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'ok' => true,
            'quantities' => $currentServices,
            'room_total_cost' => $room_total_cost,
            'services_total_cost' => $services_total_cost,
            'grand_total' => $grand_total,
        ]);
        exit;
    }

    header('Location: service.php?id=' . $bookingId);
    exit;
}

// ราคารวมของบริการเสริมที่เลือกไว้ ณ ปัจจุบัน สำหรับแสดงผล
$current_services_cost = 0.0;
foreach ($currentServices as $sId => $qty) {
    if (isset($available_services[$sId])) {
        $current_services_cost += (float)$available_services[$sId]['price'] * $qty;
    }
}
$display_grand_total = $room_total_cost + $current_services_cost;

// เส้นทางรูปภาพต้องตรงกับฝั่ง Admin (Admin/services/service_list.php ใช้ ../../uploads/services/)
function get_customer_service_image($service) {
    if (!empty($service['image_path'])) {
        return '../uploads/services/' . $service['image_path'];
    }
    return 'https://images.unsplash.com/photo-1548767797-d8c844146e4c?auto=format&fit=crop&w=800&q=80';
}

function get_customer_cat_image_svc($booking) {
    if (!empty($booking['cat_photo'])) {
        return '../uploads/cat/' . $booking['cat_photo'];
    }
    return '../assets/img/cat-placeholder.png';
}

function get_customer_room_display_name_svc($booking) {
    $name = $booking['type_name'] ?? '';
    if (!empty($booking['room_number'])) {
        $name .= ' (' . $booking['room_number'] . ')';
    }
    return $name;
}

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
                <?php $active = $i === 2; $done = $i < 2; ?>
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

            <div>
                <div class="bg-white border border-stone-200/70 rounded-2xl shadow-sm p-6 mb-6">
                    <h2 class="text-base font-semibold text-stone-900 mb-1">บริการเสริม</h2>
                    <p class="text-stone-400 text-xs mb-6">เลือกบริการเพิ่มเติม เพื่อความสุขและความสะดวกสบายของน้องแมว</p>

                    <?php if (!empty($available_services)): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4" id="service-grid">
                        <?php foreach ($available_services as $service):
                            $sid = (int)$service['service_id'];
                            $qty = $currentServices[$sid] ?? 0;
                            $is_selected = $qty > 0;
                            $detail = trim($service['service_detail'] ?? '');
                        ?>
                        <div class="service-card border <?= $is_selected ? 'border-[#C86428] ring-1 ring-[#C86428]' : 'border-stone-200' ?> bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition flex flex-col" data-service-id="<?= $sid ?>">
                            <div class="relative h-28 bg-stone-100 overflow-hidden">
                                <img src="<?= htmlspecialchars(get_customer_service_image($service)) ?>" alt="<?= htmlspecialchars($service['service_name']) ?>" class="w-full h-full object-cover">
                                <div class="svc-badge absolute top-2 right-2 w-6 h-6 rounded-full flex items-center justify-center text-[11px] shadow-sm transition <?= $is_selected ? 'bg-[#C86428] text-white' : 'bg-white/90 text-stone-300 border border-stone-200' ?>">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                            <div class="p-4 flex-1 flex flex-col">
                                <h3 class="text-xs font-bold text-stone-900 mb-1"><?= htmlspecialchars($service['service_name']) ?></h3>
                                <p class="text-[10px] text-stone-400 leading-relaxed mb-3 flex-1">
                                    <?= $detail !== '' ? htmlspecialchars($detail) : 'บริการดูแลน้องแมวโดยทีมงานผู้เชี่ยวชาญ' ?>
                                </p>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-bold text-[#C86428]">฿<?= number_format((float)$service['price'], 0) ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="changeQty(<?= $sid ?>, 'remove')" class="w-9 h-9 shrink-0 rounded-xl border border-stone-200 text-stone-400 hover:text-red-500 hover:border-red-200 hover:bg-red-50 transition flex items-center justify-center">
                                        <i class="far fa-trash-alt text-xs"></i>
                                    </button>
                                    <div class="flex-1 flex items-center justify-between rounded-xl border border-stone-200 bg-stone-50/60 px-1">
                                        <button type="button" onclick="changeQty(<?= $sid ?>, 'dec')" class="w-8 h-8 rounded-lg text-stone-500 hover:bg-white hover:text-[#C86428] transition flex items-center justify-center">
                                            <i class="fas fa-minus text-[10px]"></i>
                                        </button>
                                        <span class="svc-qty text-xs font-bold text-stone-800 w-6 text-center"><?= $qty ?></span>
                                        <button type="button" onclick="changeQty(<?= $sid ?>, 'inc')" class="w-8 h-8 rounded-lg text-stone-500 hover:bg-white hover:text-[#C86428] transition flex items-center justify-center">
                                            <i class="fas fa-plus text-[10px]"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-stone-400 text-xs py-6 text-center">ไม่มีข้อมูลบริการเสริมในขณะนี้</p>
                    <?php endif; ?>
                </div>

                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-amber-500 shrink-0 shadow-sm">
                        <i class="fas fa-lightbulb text-sm"></i>
                    </div>
                    <p class="text-xs text-amber-800">
                        <span class="font-semibold">แนะนำสำหรับน้องแมว:</span> คุณสามารถเลือกได้มากกว่า 1 บริการ ระบบจะคำนวณยอดรวมให้อัตโนมัติที่แถบสรุปด้านขวา
                    </p>
                </div>
            </div>

            <div class="bg-white border border-stone-200/70 p-6 rounded-2xl shadow-sm sticky top-6">
                <h3 class="text-sm font-bold text-stone-900 pb-3 border-b border-stone-100 mb-5">
                    สรุปการจอง
                </h3>

                <div class="flex items-center gap-3 mb-5">
                    <img src="<?= htmlspecialchars(get_customer_cat_image_svc($booking)) ?>" class="w-10 h-10 rounded-full object-cover border border-stone-200" alt="">
                    <div>
                        <div class="text-xs font-bold text-stone-900"><?= htmlspecialchars($booking['cat_name'] ?? '—') ?></div>
                        <div class="text-[10px] text-stone-400">เจ้าของ: คุณ<?= htmlspecialchars(trim(($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['last_name'] ?? ''))) ?></div>
                    </div>
                </div>

                <div class="space-y-3 text-xs mb-5">
                    <div class="flex justify-between items-center text-stone-500">
                        <span>ห้องพักที่เลือก</span>
                        <span class="font-semibold text-stone-800 text-right truncate max-w-[150px]"><?= htmlspecialchars(get_customer_room_display_name_svc($booking)) ?></span>
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
                    <h4 class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2">
                        บริการเสริมที่เลือก (<span id="svc-count"><?= count($currentServices) ?></span> รายการ)
                    </h4>
                    <div class="space-y-2 text-xs" id="svc-selected-list">
                        <?php if (!empty($currentServices)): ?>
                            <?php foreach ($currentServices as $sId => $qty): if (!isset($available_services[$sId])) continue; ?>
                            <div class="flex justify-between items-center text-stone-600" data-svc-line="<?= $sId ?>">
                                <span class="truncate max-w-[160px]"><?= htmlspecialchars($available_services[$sId]['service_name']) ?> <span class="text-stone-400">x<?= $qty ?></span></span>
                                <span class="font-semibold text-stone-800">฿<?= number_format((float)$available_services[$sId]['price'] * $qty, 0) ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-stone-400 italic" id="svc-empty-msg">ยังไม่ได้เลือกบริการเสริม</p>
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
                        <span class="font-semibold text-stone-800" id="svc-total-cost"><?= number_format($current_services_cost, 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500">
                        <span>ส่วนลด</span>
                        <span class="font-semibold text-stone-800">0</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-stone-100 flex justify-between items-baseline mb-1">
                    <span class="text-xs font-semibold text-stone-700">ยอดรวมทั้งหมด</span>
                    <span class="text-xl font-bold text-[#C86428]" id="grand-total">฿<?= number_format($display_grand_total, 0) ?></span>
                </div>
                <p class="text-[10px] text-stone-400 mb-5">ราคานี้รวมภาษีมูลค่าเพิ่ม 7% แล้ว</p>

                <a href="booking_confirm.php?id=<?= $bookingId ?>" class="w-full rounded-xl bg-[#C86428] hover:bg-[#B0561F] text-white py-3.5 text-xs font-bold shadow-md transition duration-200 flex items-center justify-center gap-2 mb-3">
                    ไปขั้นตอน: ยืนยันการจอง <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
                <a href="<?= htmlspecialchars(url('Customer/booking.php')) ?>" class="w-full rounded-xl border border-stone-200 text-stone-600 hover:bg-stone-50 py-3 text-xs font-semibold transition duration-200 flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-left text-[10px]"></i> ย้อนกลับไปหน้าเลือกห้องพัก
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// ข้อมูลบริการเสริมทั้งหมด (สำหรับ re-render ฝั่ง client โดยไม่รีเฟรชหน้า)
const SERVICES = <?= json_encode(array_map(function ($s) {
    return [
        'id' => (int)$s['service_id'],
        'name' => $s['service_name'],
        'price' => (float)$s['price'],
    ];
}, array_values($available_services)), JSON_UNESCAPED_UNICODE) ?>;
const BOOKING_ID = <?= (int)$bookingId ?>;
const ROOM_TOTAL_COST = <?= json_encode($room_total_cost) ?>;

function renderSidebar(quantities) {
    const listEl = document.getElementById('svc-selected-list');
    const countEl = document.getElementById('svc-count');
    const svcTotalEl = document.getElementById('svc-total-cost');
    const grandTotalEl = document.getElementById('grand-total');

    let count = 0;
    let servicesTotal = 0;
    let html = '';

    SERVICES.forEach(function (svc) {
        const qty = quantities[svc.id] || 0;
        if (qty > 0) {
            count++;
            const lineTotal = svc.price * qty;
            servicesTotal += lineTotal;
            html += '<div class="flex justify-between items-center text-stone-600" data-svc-line="' + svc.id + '">' +
                '<span class="truncate max-w-[160px]">' + svc.name + ' <span class="text-stone-400">x' + qty + '</span></span>' +
                '<span class="font-semibold text-stone-800">฿' + Math.round(lineTotal).toLocaleString() + '</span>' +
                '</div>';
        }
    });

    if (count === 0) {
        html = '<p class="text-stone-400 italic" id="svc-empty-msg">ยังไม่ได้เลือกบริการเสริม</p>';
    }

    listEl.innerHTML = html;
    countEl.textContent = count;
    svcTotalEl.textContent = Math.round(servicesTotal).toLocaleString();
    grandTotalEl.textContent = '฿' + Math.round(ROOM_TOTAL_COST + servicesTotal).toLocaleString();
}

function renderCard(serviceId, qty) {
    const card = document.querySelector('.service-card[data-service-id="' + serviceId + '"]');
    if (!card) return;
    card.querySelector('.svc-qty').textContent = qty;

    const badge = card.querySelector('.svc-badge');
    if (qty > 0) {
        card.classList.add('border-[#C86428]', 'ring-1', 'ring-[#C86428]');
        card.classList.remove('border-stone-200');
        badge.classList.add('bg-[#C86428]', 'text-white');
        badge.classList.remove('bg-white/90', 'text-stone-300', 'border', 'border-stone-200');
    } else {
        card.classList.remove('border-[#C86428]', 'ring-1', 'ring-[#C86428]');
        card.classList.add('border-stone-200');
        badge.classList.remove('bg-[#C86428]', 'text-white');
        badge.classList.add('bg-white/90', 'text-stone-300', 'border', 'border-stone-200');
    }
}

let isSubmitting = false;
function changeQty(serviceId, action) {
    if (isSubmitting) return;
    isSubmitting = true;

    const body = new URLSearchParams({
        booking_id: BOOKING_ID,
        service_id: serviceId,
        action: action,
        ajax: 1
    });

    fetch('service.php', { method: 'POST', body: body })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data || !data.ok) return;
            const qty = data.quantities[serviceId] || 0;
            renderCard(serviceId, qty);
            renderSidebar(data.quantities);
        })
        .catch(function () {
            // หากเชื่อมต่อล้มเหลว ให้รีเฟรชหน้าเพื่อความถูกต้องของข้อมูล
            window.location.href = 'service.php?id=' + BOOKING_ID;
        })
        .finally(function () {
            isSubmitting = false;
        });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>