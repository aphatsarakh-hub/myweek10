<?php
require_once __DIR__ . '/../includes/boot.php';

require_login();
require_role('customer');

$pageTitle = 'จองห้องพักระบบอัตโนมัติ - BamBam Cat Hotel';
$showPrimaryAction = false;

$customer_id = (int)($_SESSION['user']['customer_id'] ?? 0);
$roomtypes = fetch_roomtypes();
$cats = fetch_cats_by_customer($customer_id);

$error = '';
$form = [
    'cat_id' => '',
    'roomtype_id' => '',
    'start_date' => '',
    'end_date' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['cat_id'] = trim($_POST['cat_id'] ?? '');
    $form['roomtype_id'] = trim($_POST['roomtype_id'] ?? '');
    $form['start_date'] = trim($_POST['start_date'] ?? '');
    $form['end_date'] = trim($_POST['end_date'] ?? '');

    $cat_id = (int)$form['cat_id'];
    $roomtype_id = (int)$form['roomtype_id'];
    $cat = $cat_id ? fetch_cat_by_id($cat_id) : null;
    $room = $roomtype_id ? fetch_roomtype_by_id($roomtype_id) : null;
    $today = date('Y-m-d');

    if (empty($cats)) {
        $error = 'กรุณาเพิ่มข้อมูลแมวก่อนทำการจอง';
    } elseif (!$cat || (int)$cat['customer_id'] !== $customer_id) {
        $error = 'กรุณาเลือกแมวของคุณให้ถูกต้อง';
    } elseif (!$room) {
        $error = 'กรุณาเลือกประเภทห้องพักที่ต้องการ';
    } elseif (!$form['start_date'] || !$form['end_date']) {
        $error = 'กรุณาระบุวันที่เข้าพักและวันที่รับกลับ';
    } elseif ($form['start_date'] < $today) {
        $error = 'วันที่เข้าพักต้องไม่เป็นวันในอดีต';
    } elseif ($form['end_date'] <= $form['start_date']) {
        $error = 'วันที่รับกลับต้องหลังวันที่เข้าพักอย่างน้อย 1 วัน';
    } else {
        $total_price = calculate_booking_total($form['start_date'], $form['end_date'], $room['price_per_night']);

        // 🏨 ระบบจองอัตโนมัติ: บันทึกลงตารางฐานข้อมูลทันที
        global $mysqli;
        $stmt = $mysqli->prepare("INSERT INTO booking (customer_id, cat_id, roomtype_id, start_date, end_date, total_price, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
        if ($stmt) {
            $stmt->bind_param('iiissd', $customer_id, $cat_id, $roomtype_id, $form['start_date'], $form['end_date'], $total_price);
            if ($stmt->execute()) {
                $booking_id = $mysqli->insert_id;
                $stmt->close();

                // ✅ แก้ไขแล้ว: นำทางลูกค้าไปยังขั้นตอนถัดไป พร้อมส่ง $booking_id ที่ถูกต้องไปให้
                flash('message', 'บันทึกห้องพักเรียบร้อยแล้ว กำลังพาท่านไปเลือกบริการเสริม...');
                header('Location: service.php?id=' . $booking_id);
                exit;
            }
            $stmt->close();
        }
        $error = 'ไม่สามารถบันทึกการจองได้เนื่องจากระบบฐานข้อมูลขัดข้อง';
    }
}

// ไอคอน/ป้ายสิ่งอำนวยความสะดวกมาตรฐานของทุกห้อง (ปรับตามข้อมูลจริงจากฐานข้อมูลได้ในอนาคต)
$roomAmenities = [
    ['icon' => 'fa-door-closed', 'label' => 'ห้องแยกเป็นส่วนตัว'],
    ['icon' => 'fa-video',       'label' => 'CCTV 24 ชม.'],
    ['icon' => 'fa-snowflake',   'label' => 'แอร์ 24 ชม.'],
    ['icon' => 'fa-user-tie',    'label' => 'มีพนักงานดูแล'],
];

function get_customer_room_image($room, $index) {
    if (!empty($room['image_url'])) {
        return '../uploads/room/' . $room['image_url'];
    }
    $luxuryInteriors = ['../uploads/room/1.jpg', '../uploads/room/2.jpg', '../uploads/room/3.jpg', '../uploads/room/4.avif'];
    return $luxuryInteriors[$index % count($luxuryInteriors)];
}

// ตาราง cat เก็บชื่อไว้ในคอลัมน์ `name` และรูปในคอลัมน์ `photo`
function get_customer_cat_name($cat) {
    return $cat['name'] ?? '';
}

function get_customer_cat_image($cat) {
    if (!empty($cat['photo'])) {
        return '../uploads/cat/' . $cat['photo'];
    }
    return '../assets/img/cat-placeholder.png';
}

// ตาราง roomtype แยก type_name กับ room_number คนละคอลัมน์ เช่น "VIP รายเดือน" + "RM-04"
function get_customer_room_display_name($room) {
    $name = $room['type_name'] ?? '';
    if (!empty($room['room_number'])) {
        $name .= ' (' . $room['room_number'] . ')';
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

        <?php if ($error): ?>
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-5 py-3 rounded-xl text-sm flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-red-600"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <div class="mb-6 flex items-center flex-wrap gap-2 text-xs font-medium">
            <?php foreach ($steps as $i => $step): ?>
                <?php $active = $i === 0; ?>
                <div class="flex items-center gap-2 rounded-xl px-4 py-2.5 shadow-sm <?= $active ? 'bg-[#C86428] text-white' : 'bg-white border border-stone-200 text-stone-400' ?>">
                    <span class="flex h-5 w-5 items-center justify-center rounded-lg text-[10px] <?= $active ? 'bg-white/25' : 'bg-stone-100' ?>">
                        <?= $i + 1 ?>
                    </span>
                    <?= htmlspecialchars($step['label']) ?>
                </div>
                <?php if ($i < count($steps) - 1): ?>
                    <i class="fas fa-chevron-right text-stone-300 text-[10px]"></i>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php if (empty($cats)): ?>
        <div class="bg-white rounded-2xl p-16 text-center border border-stone-200/60 shadow-sm max-w-md mx-auto my-12">
            <div class="w-16 h-16 rounded-2xl bg-stone-50 border border-stone-100 flex items-center justify-center mx-auto mb-5 text-[#C86428] text-2xl shadow-inner">
                <i class="fas fa-cat"></i>
            </div>
            <div class="text-sm font-medium text-stone-900">ไม่พบข้อมูลน้องแมวในระบบ</div>
            <p class="text-stone-400 text-xs mt-1 mb-6">กรุณาลงทะเบียนข้อมูลน้องแมวก่อนทำการจองห้องพักครับ</p>
            <a href="<?= htmlspecialchars(url('Customer/cat_add.php')) ?>" class="inline-flex w-full items-center justify-center gap-2 bg-[#2C2523] hover:bg-[#C86428] text-white px-5 py-3 rounded-xl text-sm font-medium transition shadow-sm">
                <i class="fas fa-plus text-xs"></i> เพิ่มข้อมูลน้องแมวตัวแรก
            </a>
        </div>

        <?php else: ?>
        <form method="post" id="booking-form" class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 items-start">

            <input type="hidden" name="roomtype_id" id="roomtype_id_input" value="" required>

            <div>
                <div class="bg-white border border-stone-200/70 rounded-2xl shadow-sm p-4 mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label for="cat_id" class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2">น้องแมวที่จะเข้าพัก</label>
                        <div class="flex items-center gap-2">
                            <img id="cat-avatar-preview" src="<?= htmlspecialchars(get_customer_cat_image($cats[0])) ?>" class="w-8 h-8 rounded-full object-cover border border-stone-200 shrink-0" alt="">
                            <select name="cat_id" id="cat_id" required class="w-full rounded-xl border border-stone-200 bg-stone-50/50 px-2 py-2 text-xs text-stone-800 focus:ring-1 focus:ring-[#C86428] focus:border-[#C86428] outline-none">
                                <?php foreach ($cats as $c): ?>
                                    <option value="<?= (int)$c['cat_id'] ?>" data-photo="<?= htmlspecialchars(get_customer_cat_image($c)) ?>">
                                        <?= htmlspecialchars(get_customer_cat_name($c)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="start_date" class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2">เช็คอิน</label>
                        <input type="date" name="start_date" id="start_date" required value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" class="w-full rounded-xl border border-stone-200 bg-stone-50/50 px-3 py-2 text-xs focus:ring-1 focus:ring-[#C86428] focus:border-[#C86428] outline-none">
                    </div>
                    <div>
                        <label for="end_date" class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2">เช็คเอาท์</label>
                        <input type="date" name="end_date" id="end_date" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" class="w-full rounded-xl border border-stone-200 bg-stone-50/50 px-3 py-2 text-xs focus:ring-1 focus:ring-[#C86428] focus:border-[#C86428] outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2">จำนวนคืน</label>
                        <div id="nights-display" class="w-full rounded-xl border border-stone-200 bg-stone-100 px-3 py-2 text-xs text-stone-600 font-semibold">1 คืน</div>
                    </div>
                </div>

                <h2 class="text-base font-semibold text-stone-900 mb-1">เลือกประเภทห้องพัก</h2>
                <p class="text-stone-400 text-xs mb-6">คลิกที่ห้องเพื่อดูรายละเอียด</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="room-grid">
                    <?php foreach ($roomtypes as $idx => $room): ?>
                        <?php
                        $rid = (int)$room['roomtype_id'];
                        $price = (float)$room['price_per_night'];
                        $imgSrc = get_customer_room_image($room, $idx);
                        $isRecommended = !empty($room['is_recommended']) || $idx === 0;
                        $displayName = get_customer_room_display_name($room);
                        $rawDesc = trim($room['description'] ?? '');
                        // ข้อมูลบางแถวใน description ดันเก็บตัวเลขล้วน (ข้อมูลตกค้าง) ให้ fallback เป็นข้อความทั่วไปแทน
                        $roomDesc = ($rawDesc !== '' && !ctype_digit($rawDesc))
                            ? $rawDesc
                            : 'ห้องพักส่วนตัว ระบบปรับอุณหภูมิอัจฉริยะ ดูแลตลอด 24 ชั่วโมง';
                        ?>
                        <div class="room-selection-card group relative bg-white rounded-2xl overflow-hidden border border-stone-200/70 shadow-sm hover:shadow-md transition duration-200 flex flex-col"
                             data-room-id="<?= $rid ?>"
                             data-price="<?= $price ?>"
                             data-name="<?= htmlspecialchars($displayName) ?>">

                            <div class="relative h-40 bg-stone-100 overflow-hidden">
                                <img src="<?= htmlspecialchars($imgSrc) ?>" alt="ห้องพัก" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <?php if ($isRecommended): ?>
                                <div class="absolute top-3 left-3 px-2.5 py-1 rounded-md text-[10px] font-bold tracking-wider bg-white/95 text-amber-500 shadow-sm flex items-center gap-1">
                                    <i class="fas fa-star"></i> แนะนำ
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="p-5 flex-1 flex flex-col">
                                <div class="flex justify-between items-start gap-2 mb-3">
                                    <h3 class="text-sm font-bold text-stone-900 group-hover:text-[#C86428] transition-colors">
                                        <?= htmlspecialchars($displayName) ?>
                                    </h3>
                                    <div class="text-right shrink-0">
                                        <span class="text-base font-bold text-stone-900">฿<?= number_format($price, 0) ?></span>
                                        <span class="text-[10px] text-stone-400"> / คืน</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-4 gap-2 mb-4 pb-4 border-b border-stone-100">
                                    <?php foreach ($roomAmenities as $amenity): ?>
                                    <div class="flex flex-col items-center text-center gap-1">
                                        <i class="fas <?= htmlspecialchars($amenity['icon']) ?> text-stone-500 text-sm"></i>
                                        <span class="text-[9px] text-stone-400 leading-tight"><?= htmlspecialchars($amenity['label']) ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="flex items-center gap-2 mt-auto">
                                    <button type="button" onclick="toggleRoomDetails(<?= $rid ?>)" class="flex-1 rounded-xl border border-stone-200 text-stone-600 py-2 text-[11px] font-semibold hover:bg-stone-50 transition">
                                        รายละเอียด
                                    </button>
                                    <button type="button"
                                            onclick="selectRoomCard(this, <?= $rid ?>, '<?= htmlspecialchars($displayName, ENT_QUOTES) ?>', <?= $price ?>)"
                                            class="select-room-btn flex-1 rounded-xl border border-stone-200 text-stone-600 py-2 text-[11px] font-semibold transition flex items-center justify-center gap-1.5">
                                        <i class="far fa-circle text-[10px]"></i> เลือกห้องนี้
                                    </button>
                                </div>

                                <p id="room-detail-<?= $rid ?>" class="hidden text-stone-400 text-[11px] leading-relaxed mt-3 pt-3 border-t border-dashed border-stone-200">
                                    <?= htmlspecialchars($roomDesc) ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white border border-stone-200/70 p-6 rounded-2xl shadow-sm sticky top-6">
                <h3 class="text-sm font-bold text-stone-900 pb-3 border-b border-stone-100 mb-5">
                    สรุปการจอง
                </h3>

                <div class="flex items-center gap-3 mb-5">
                    <img id="summary-cat-avatar" src="<?= htmlspecialchars(get_customer_cat_image($cats[0])) ?>" class="w-10 h-10 rounded-full object-cover border border-stone-200" alt="">
                    <div>
                        <div id="summary-cat-name" class="text-xs font-bold text-stone-900"><?= htmlspecialchars(get_customer_cat_name($cats[0])) ?></div>
                        <div class="text-[10px] text-stone-400">เจ้าของ: คุณ<?= htmlspecialchars(trim(($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['last_name'] ?? ''))) ?></div>
                    </div>
                </div>

                <div class="space-y-3 text-xs mb-5">
                    <div class="flex justify-between items-center text-stone-500">
                        <span>ประเภทห้อง</span>
                        <span id="sum-room" class="font-semibold text-stone-800 text-right truncate max-w-[150px]">— ยังไม่ได้เลือก —</span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500">
                        <span>เช็คอิน</span>
                        <span id="sum-checkin" class="font-semibold text-stone-800">—</span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500">
                        <span>เช็คเอาท์</span>
                        <span id="sum-checkout" class="font-semibold text-stone-800">—</span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500">
                        <span>จำนวนคืน</span>
                        <span id="sum-nights" class="font-semibold text-stone-800">—</span>
                    </div>
                </div>

                <div class="my-4 border-t border-dashed border-stone-200"></div>

                <div class="space-y-3 text-xs mb-5">
                    <div class="flex justify-between items-center text-stone-500">
                        <span id="sum-room-line-label">ค่าห้องพัก</span>
                        <span id="sum-room-total" class="font-semibold text-stone-800">0</span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500">
                        <span>บริการเสริม</span>
                        <span>0</span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500">
                        <span>ส่วนลด</span>
                        <span>0</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-stone-100 flex justify-between items-baseline mb-1">
                    <span class="text-xs font-semibold text-stone-700">ยอดรวมทั้งหมด</span>
                    <span id="sum-total" class="text-xl font-bold text-[#C86428]">฿0</span>
                </div>
                <p class="text-[10px] text-stone-400 mb-5">ราคานี้รวมภาษีมูลค่าเพิ่ม 7% แล้ว</p>

                <button type="submit" class="w-full rounded-xl bg-[#C86428] hover:bg-[#B0561F] text-white py-3.5 text-xs font-bold shadow-md transition duration-200 flex items-center justify-center gap-2 mb-3">
                    ไปขั้นตอนบริการเสริม <i class="fas fa-arrow-right text-[10px]"></i>
                </button>
                <button type="button" class="w-full rounded-xl border border-stone-200 text-stone-600 hover:bg-stone-50 py-3 text-xs font-semibold transition duration-200 flex items-center justify-center gap-2">
                    <i class="far fa-heart"></i> บันทึกรายการนี้ไว้
                </button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
// ข้อมูลกลางสำหรับการคำนวณราคาแบบเรียลไทม์
let selectedRoomId = null;
let selectedRoomPrice = 0;
let selectedRoomName = '';

const thaiMonths = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

function formatThaiDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    const buddhistYear = d.getFullYear() + 543;
    return d.getDate() + ' ' + thaiMonths[d.getMonth()] + ' ' + buddhistYear;
}

function toggleRoomDetails(roomId) {
    const el = document.getElementById('room-detail-' + roomId);
    if (el) el.classList.toggle('hidden');
}

function selectRoomCard(element, roomId, roomName, price) {
    document.getElementById('roomtype_id_input').value = roomId;
    selectedRoomId = roomId;
    selectedRoomPrice = price;
    selectedRoomName = roomName;

    document.querySelectorAll('.room-selection-card').forEach(card => {
        card.classList.remove('border-[#C86428]', 'ring-1', 'ring-[#C86428]');
        const btn = card.querySelector('.select-room-btn');
        if (btn) {
            btn.className = 'select-room-btn flex-1 rounded-xl border border-stone-200 text-stone-600 py-2 text-[11px] font-semibold transition flex items-center justify-center gap-1.5';
            btn.innerHTML = '<i class="far fa-circle text-[10px]"></i> เลือกห้องนี้';
        }
    });

    element.closest('.room-selection-card').classList.add('border-[#C86428]', 'ring-1', 'ring-[#C86428]');
    element.className = 'select-room-btn flex-1 rounded-xl bg-[#C86428] text-white py-2 text-[11px] font-bold transition flex items-center justify-center gap-1.5';
    element.innerHTML = '<i class="fas fa-check"></i> เลือกห้องนี้';

    updateStaySummary();
}

function updateStaySummary() {
    const startEl = document.getElementById('start_date');
    const endEl = document.getElementById('end_date');
    const sumRoom = document.getElementById('sum-room');
    const sumCheckin = document.getElementById('sum-checkin');
    const sumCheckout = document.getElementById('sum-checkout');
    const sumNights = document.getElementById('sum-nights');
    const sumRoomTotal = document.getElementById('sum-room-total');
    const sumRoomLineLabel = document.getElementById('sum-room-line-label');
    const sumTotal = document.getElementById('sum-total');
    const nightsDisplay = document.getElementById('nights-display');

    if (!sumRoom) return;

    sumRoom.textContent = selectedRoomName || '— ยังไม่ได้เลือก —';

    let nights = 0;
    if (startEl.value && endEl.value) {
        const d1 = new Date(startEl.value);
        const d2 = new Date(endEl.value);
        nights = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
        if (nights < 0) nights = 0;

        sumCheckin.textContent = formatThaiDate(startEl.value) + ' (14:00)';
        sumCheckout.textContent = formatThaiDate(endEl.value) + ' (12:00)';
        if (nightsDisplay) nightsDisplay.textContent = nights > 0 ? nights + ' คืน' : '—';
    } else {
        sumCheckin.textContent = '—';
        sumCheckout.textContent = '—';
        if (nightsDisplay) nightsDisplay.textContent = '—';
    }

    sumNights.textContent = nights > 0 ? nights + ' คืน' : '—';

    if (nights > 0 && selectedRoomPrice > 0) {
        const roomTotal = nights * selectedRoomPrice;
        sumRoomLineLabel.textContent = 'ค่าห้องพัก (' + selectedRoomPrice.toLocaleString() + ' x ' + nights + ' คืน)';
        sumRoomTotal.textContent = roomTotal.toLocaleString();
        sumTotal.textContent = '฿' + roomTotal.toLocaleString();
    } else {
        sumRoomLineLabel.textContent = 'ค่าห้องพัก';
        sumRoomTotal.textContent = '0';
        sumTotal.textContent = '฿0';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const startEl = document.getElementById('start_date');
    const endEl = document.getElementById('end_date');
    const catSelect = document.getElementById('cat_id');

    if (startEl && endEl) {
        startEl.addEventListener('change', function () {
            if (startEl.value) {
                const nextDay = new Date(startEl.value);
                nextDay.setDate(nextDay.getDate() + 1);
                endEl.min = nextDay.toISOString().slice(0, 10);
                if (endEl.value <= startEl.value) {
                    endEl.value = endEl.min;
                }
            }
            updateStaySummary();
        });
        endEl.addEventListener('change', updateStaySummary);
    }

    if (catSelect) {
        catSelect.addEventListener('change', function () {
            const opt = catSelect.options[catSelect.selectedIndex];
            const photo = opt.getAttribute('data-photo');
            const name = opt.textContent.trim();
            document.getElementById('cat-avatar-preview').src = photo;
            document.getElementById('summary-cat-avatar').src = photo;
            document.getElementById('summary-cat-name').textContent = name;
        });
    }

    // 🪄 เลือกห้องพักลำดับแรกให้ลูกค้าโดยอัตโนมัติเมื่อเปิดหน้า
    const firstBtn = document.querySelector('.select-room-btn');
    if (firstBtn) {
        firstBtn.click();
    }

    updateStaySummary();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>