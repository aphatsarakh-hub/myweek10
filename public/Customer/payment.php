<?php
require_once __DIR__ . '/../includes/boot.php';

require_login();
require_role('customer');

$pageTitle = 'ชำระเงินมัดจำ - BamBam Cat Hotel';
$showPrimaryAction = false;

$customer_id = (int)($_SESSION['user']['customer_id'] ?? 0);
$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$bookingId) {
    $bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
}

global $mysqli;

$stmt = $mysqli->prepare("
    SELECT b.*, r.type_name, r.room_number, r.price_per_night AS room_price,
           c.name AS cat_name, c.photo AS cat_photo
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

function parse_services_string_pay(?string $str): array {
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

$selectedServiceQty = parse_services_string_pay($booking['services'] ?? null);
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
$DEPOSIT_PERCENT = 0.6;
$deposit_amount = round($grand_total * $DEPOSIT_PERCENT);

$bankInfo = [
    'bank_name'    => 'ธนาคารไทยพาณิชย์',
    'account_name' => 'บริษัท แบมแบม แคท โฮเทล จำกัด',
    'account_no'   => '123-4-56789-0',
];

$error = '';

// จัดการอัปโหลดสลิปและบันทึกการชำระเงินมัดจำ (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $paymentMethod = trim($_POST['payment_method'] ?? 'bank_transfer');
    $isCard = ($paymentMethod === 'card');

    // บัตรเครดิตไม่ต้องแนบสลิปโอนเงิน
    if (!$isCard && (!isset($_FILES['slip']) || $_FILES['slip']['error'] !== UPLOAD_ERR_OK)) {
        $error = 'กรุณาแนบสลิปการโอนเงินก่อนยืนยันการชำระเงิน';
    } else {
        $uploadSuccess = true;
        $fileName = null;

        if (!$isCard) {
            $file = $_FILES['slip'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array($file['type'], $allowedTypes, true)) {
                $error = 'รองรับเฉพาะไฟล์ภาพ JPG หรือ PNG เท่านั้น';
                $uploadSuccess = false;
            } elseif ($file['size'] > $maxSize) {
                $error = 'ขนาดไฟล์ต้องไม่เกิน 5MB';
                $uploadSuccess = false;
            } else {
                $uploadDir = __DIR__ . '/../uploads/payment_slips/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $safeExt = in_array($ext, ['jpg', 'jpeg', 'png'], true) ? $ext : 'jpg';
                $fileName = 'slip_' . $bookingId . '_' . time() . '.' . $safeExt;
                $destPath = $uploadDir . $fileName;

                if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                    $error = 'ไม่สามารถอัปโหลดไฟล์ได้ กรุณาลองใหม่อีกครั้ง';
                    $uploadSuccess = false;
                }
            }
        }

        if ($uploadSuccess) {
            // บันทึกลงตาราง payment
            $pay_stmt = $mysqli->prepare("INSERT INTO payment (booking_id, amount, payment_method, status, slip_path) VALUES (?, ?, ?, 'Pending', ?)");
            $pay_stmt->bind_param('idss', $bookingId, $deposit_amount, $paymentMethod, $fileName);
            if ($pay_stmt->execute()) {
                $pay_stmt->close();
                if ($isCard) {
                    flash('message', 'ชำระเงินผ่านบัตรเครดิต/เดบิตเรียบร้อยแล้ว ระบบกำลังตรวจสอบยอดเงินอัตโนมัติ');
                } else {
                    flash('message', 'ส่งหลักฐานการชำระเงินมัดจำเรียบร้อยแล้ว กำลังรอเจ้าหน้าที่ตรวจสอบ');
                }
                header('Location: ' . url('Customer/booking_history.php'));
                exit;
            }
            $pay_stmt->close();
            $error = 'ไม่สามารถบันทึกข้อมูลการชำระเงินได้ กรุณาลองใหม่อีกครั้ง';
        }
    }
}

function get_pay_room_display_name($booking) {
    $name = $booking['type_name'] ?? '';
    if (!empty($booking['room_number'])) $name .= ' (' . $booking['room_number'] . ')';
    return $name;
}
function get_pay_cat_image($booking) {
    if (!empty($booking['cat_photo'])) return '../uploads/cat/' . $booking['cat_photo'];
    return '../assets/img/cat-placeholder.png';
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
                <?php $active = $i === 4; $done = $i < 4; ?>
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

        <form method="post" enctype="multipart/form-data" id="payment-form" class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 items-start">
            <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
            <input type="hidden" name="confirm_payment" value="1">
            <input type="hidden" name="payment_method" id="payment_method_input" value="bank_transfer">

            <div class="space-y-6">

                <div class="bg-white border border-stone-200/70 rounded-2xl shadow-sm p-6">
                    <h2 class="text-base font-semibold text-stone-900 mb-4">เลือกวิธีการชำระเงิน</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <button type="button" onclick="selectPaymentMethod(this, 'bank_transfer')"
                                class="payment-method-btn border border-[#C86428] ring-1 ring-[#C86428] bg-[#FFF8F3] rounded-xl p-3 text-left transition"
                                data-method="bank_transfer">
                            <i class="fas fa-university text-[#C86428] text-base mb-2 block"></i>
                            <div class="text-xs font-bold text-stone-900">โอนเงินผ่านธนาคาร</div>
                            <div class="text-[10px] text-stone-400">ชำระผ่านบัญชีธนาคาร</div>
                        </button>
                        <button type="button" onclick="selectPaymentMethod(this, 'promptpay')"
                                class="payment-method-btn border border-stone-200 rounded-xl p-3 text-left transition hover:border-stone-300"
                                data-method="promptpay">
                            <i class="fas fa-qrcode text-stone-400 text-base mb-2 block"></i>
                            <div class="text-xs font-bold text-stone-900">พร้อมเพย์</div>
                            <div class="text-[10px] text-stone-400">PromptPay</div>
                        </button>
                        <button type="button" onclick="selectPaymentMethod(this, 'card')"
                                class="payment-method-btn border border-stone-200 rounded-xl p-3 text-left transition hover:border-stone-300"
                                data-method="card">
                            <i class="far fa-credit-card text-stone-400 text-base mb-2 block"></i>
                            <div class="text-xs font-bold text-stone-900">บัตรเครดิต / เดบิต</div>
                            <div class="text-[10px] text-stone-400">Visa, Mastercard</div>
                        </button>
                        <button type="button" onclick="selectPaymentMethod(this, 'qr')"
                                class="payment-method-btn border border-stone-200 rounded-xl p-3 text-left transition hover:border-stone-300"
                                data-method="qr">
                            <i class="fas fa-mobile-alt text-stone-400 text-base mb-2 block"></i>
                            <div class="text-xs font-bold text-stone-900">สแกน QR Code</div>
                            <div class="text-[10px] text-stone-400">Mobile Banking</div>
                        </button>
                    </div>
                </div>

                <div class="bg-white border border-stone-200/70 rounded-2xl shadow-sm p-6" id="bank-transfer-panel">
                    <h2 class="text-base font-semibold text-stone-900 mb-1">โอนเงินผ่านธนาคาร</h2>
                    <p class="text-stone-400 text-xs mb-4">กรุณาโอนเงินเข้าบัญชีตามข้อมูลด้านล่าง</p>

                    <div class="rounded-xl bg-stone-50/70 border border-stone-100 p-4 flex items-center justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-[#4E2A84] flex items-center justify-center text-white shrink-0">
                                <i class="fas fa-university text-sm"></i>
                            </div>
                            <div>
                                <div class="text-xs text-stone-500"><?= htmlspecialchars($bankInfo['bank_name']) ?></div>
                                <div class="text-[11px] text-stone-500">ชื่อบัญชี: <?= htmlspecialchars($bankInfo['account_name']) ?></div>
                                <div id="account-number" class="text-base font-bold text-[#C86428] font-mono"><?= htmlspecialchars($bankInfo['account_no']) ?></div>
                            </div>
                        </div>
                        <button type="button" onclick="copyAccountNumber()" class="rounded-lg border border-stone-200 text-stone-600 hover:bg-white px-3 py-2 text-[11px] font-semibold transition flex items-center gap-1.5 shrink-0">
                            <i class="far fa-copy"></i> <span id="copy-btn-label">คัดลอกข้อมูล</span>
                        </button>
                    </div>

                    <div class="mt-4 bg-amber-50 border border-amber-100 rounded-xl p-4">
                        <h3 class="text-[11px] font-bold text-amber-800 mb-1.5 flex items-center gap-1.5"><i class="fas fa-info-circle"></i> หมายเหตุ</h3>
                        <ul class="text-[11px] text-amber-800/90 space-y-1 list-disc list-inside">
                            <li>กรุณาโอนเงินตามยอดที่กำหนดเท่านั้น</li>
                            <li>หลังโอนเงินแล้ว กรุณาอัปโหลดสลิปเพื่อยืนยันการชำระเงิน</li>
                            <li>การจองของคุณจะสมบูรณ์เมื่อเจ้าหน้าที่ยืนยันยอดเงินเรียบร้อยแล้ว</li>
                        </ul>
                    </div>
                </div>

                <div class="bg-white border border-stone-200/70 rounded-2xl shadow-sm p-6" id="promptpay-panel" style="display: none;">
                    <h2 class="text-base font-semibold text-stone-900 mb-1">พร้อมเพย์ (PromptPay)</h2>
                    <p class="text-stone-400 text-xs mb-4">สแกน QR Code เพื่อชำระเงินผ่านแอปพลิเคชันธนาคาร</p>

                    <div class="flex flex-col items-center justify-center p-6 bg-stone-50/70 border border-stone-100 rounded-xl">
                        <div class="w-40 h-40 bg-white border border-stone-200 rounded-xl flex items-center justify-center mb-4 shadow-sm overflow-hidden p-2">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/d/d0/QR_code_for_mobile_English_Wikipedia.svg" alt="PromptPay QR" class="w-full h-full object-cover opacity-80">
                        </div>
                        <div class="text-sm font-bold text-[#113566]">PromptPay</div>
                        <div class="text-xs text-stone-500 mt-1">ชื่อบัญชี: <?= htmlspecialchars($bankInfo['account_name']) ?></div>
                        <div class="text-base font-bold text-[#C86428] font-mono mt-1">099-999-9999</div>
                    </div>

                    <div class="mt-4 bg-amber-50 border border-amber-100 rounded-xl p-4">
                        <h3 class="text-[11px] font-bold text-amber-800 mb-1.5 flex items-center gap-1.5"><i class="fas fa-info-circle"></i> หมายเหตุ</h3>
                        <ul class="text-[11px] text-amber-800/90 space-y-1 list-disc list-inside">
                            <li>บันทึกภาพหน้าจอ QR Code และเปิดในแอปธนาคารเพื่อสแกน</li>
                            <li>หลังโอนเงินแล้ว กรุณาอัปโหลดสลิปด้านล่างเพื่อยืนยัน</li>
                        </ul>
                    </div>
                </div>

                <div class="bg-white border border-stone-200/70 rounded-2xl shadow-sm p-6" id="card-panel" style="display: none;">
                    <h2 class="text-base font-semibold text-stone-900 mb-1">บัตรเครดิต / เดบิต</h2>
                    <p class="text-stone-400 text-xs mb-4">รองรับบัตร Visa, Mastercard และ JCB</p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1.5">หมายเลขบัตร</label>
                            <div class="relative">
                                <input type="text" placeholder="0000 0000 0000 0000" class="w-full rounded-xl border border-stone-200 px-4 py-2.5 pl-10 text-xs focus:border-[#C86428] focus:ring focus:ring-[#C86428]/20 outline-none transition">
                                <i class="far fa-credit-card absolute left-3.5 top-3 text-stone-400"></i>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-stone-700 mb-1.5">วันหมดอายุ</label>
                                <input type="text" placeholder="MM/YY" class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-xs focus:border-[#C86428] focus:ring focus:ring-[#C86428]/20 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-stone-700 mb-1.5">รหัส CVV</label>
                                <input type="text" placeholder="123" class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-xs focus:border-[#C86428] focus:ring focus:ring-[#C86428]/20 outline-none transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1.5">ชื่อบนบัตร</label>
                            <input type="text" placeholder="JOHN DOE" class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-xs focus:border-[#C86428] focus:ring focus:ring-[#C86428]/20 outline-none transition">
                        </div>
                    </div>

                    <div class="mt-4 bg-stone-50 border border-stone-100 rounded-xl p-4 text-center">
                        <p class="text-[11px] text-stone-500">ระบบจะเชื่อมต่อไปยังหน้าชำระเงินของธนาคาร (Payment Gateway) <br>เมื่อคุณกดปุ่ม <b>"ยืนยันการชำระเงิน"</b></p>
                    </div>
                </div>

                <div class="bg-white border border-stone-200/70 rounded-2xl shadow-sm p-6" id="qr-panel" style="display: none;">
                    <h2 class="text-base font-semibold text-stone-900 mb-1">สแกน QR Code</h2>
                    <p class="text-stone-400 text-xs mb-4">Mobile Banking / Thai QR Payment</p>

                    <div class="flex flex-col items-center justify-center p-6 bg-stone-50/70 border border-stone-100 rounded-xl">
                        <div class="text-[11px] text-[#C86428] font-semibold mb-3">ยอดชำระมัดจำ: ฿<?= number_format($deposit_amount, 0) ?></div>
                        <div class="w-48 h-48 bg-white border border-stone-200 rounded-xl flex items-center justify-center mb-4 shadow-sm p-3 relative">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/d/d0/QR_code_for_mobile_English_Wikipedia.svg" alt="Thai QR Payment" class="w-full h-full object-cover opacity-80">
                            <div class="absolute bg-white px-2 py-1 rounded shadow-sm text-[8px] font-bold text-[#113566] top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">Thai QR</div>
                        </div>
                        <div class="text-xs text-stone-500 text-center max-w-[200px]">รองรับทุกแอปพลิเคชันธนาคารที่มีสัญลักษณ์ <br><span class="font-bold text-stone-700">Thai QR Payment</span></div>
                    </div>

                    <div class="mt-4 bg-amber-50 border border-amber-100 rounded-xl p-4">
                        <h3 class="text-[11px] font-bold text-amber-800 mb-1.5 flex items-center gap-1.5"><i class="fas fa-info-circle"></i> หมายเหตุ</h3>
                        <ul class="text-[11px] text-amber-800/90 space-y-1 list-disc list-inside">
                            <li>QR Code มีอายุการใช้งาน 15 นาที</li>
                            <li>หลังโอนเงินแล้ว กรุณาอัปโหลดสลิปเพื่อยืนยัน</li>
                        </ul>
                    </div>
                </div>

                <div id="slip-upload-section" class="bg-white border border-stone-200/70 rounded-2xl shadow-sm p-6">
                    <h2 class="text-base font-semibold text-stone-900 mb-4">อัปโหลดสลิปการโอนเงิน</h2>
                    <label for="slip-input" id="slip-dropzone" class="cursor-pointer border-2 border-dashed border-stone-200 rounded-2xl p-10 flex flex-col items-center justify-center text-center hover:border-[#C86428]/50 hover:bg-stone-50/50 transition">
                        <div id="slip-preview-wrap" class="hidden mb-3">
                            <img id="slip-preview" class="max-h-40 rounded-lg border border-stone-200 shadow-sm" alt="">
                        </div>
                        <div id="slip-placeholder">
                            <i class="fas fa-cloud-upload-alt text-3xl text-stone-300 mb-3"></i>
                            <p class="text-xs text-stone-500 mb-1">คลิกหรือลากไฟล์มาวางที่นี่</p>
                            <p class="text-[10px] text-stone-400">รองรับไฟล์ JPG, PNG (ขนาดไม่เกิน 5MB)</p>
                        </div>
                        <span class="mt-4 inline-flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-4 py-2 text-[11px] font-semibold text-stone-600">
                            <i class="fas fa-paperclip"></i> <span id="slip-file-label">เลือกไฟล์</span>
                        </span>
                    </label>
                    <input type="file" name="slip" id="slip-input" accept="image/png, image/jpeg" class="hidden" required>
                </div>

                <p class="text-[10px] text-stone-400 flex items-center gap-1.5">
                    <i class="fas fa-lock"></i> ข้อมูลของคุณจะถูกเก็บรักษาอย่างปลอดภัย
                </p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="booking_confirm.php?id=<?= $bookingId ?>" class="w-full sm:w-auto flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-xl border border-stone-200 text-stone-600 hover:bg-stone-50 py-3.5 px-6 text-xs font-semibold transition">
                        <i class="fas fa-arrow-left text-[10px]"></i> กลับไปหน้าก่อนหน้า
                    </a>
                    <button type="submit" class="w-full sm:flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-[#C86428] hover:bg-[#B0561F] text-white py-3.5 px-6 text-xs font-bold shadow-md transition">
                        <i class="fas fa-lock text-[10px]"></i> ยืนยันการชำระเงิน
                    </button>
                </div>
            </div>

            <div class="space-y-4 sticky top-6">
                <div class="bg-white border border-stone-200/70 p-6 rounded-2xl shadow-sm">
                    <h3 class="text-sm font-bold text-stone-900 pb-3 border-b border-stone-100 mb-5">สรุปการจอง</h3>

                    <div class="flex items-center gap-3 mb-5">
                        <img src="<?= htmlspecialchars(get_pay_cat_image($booking)) ?>" class="w-10 h-10 rounded-full object-cover border border-stone-200" alt="">
                        <div>
                            <div class="text-xs font-bold text-stone-900"><?= htmlspecialchars($booking['cat_name'] ?? '—') ?></div>
                            <div class="text-[10px] text-stone-400">เจ้าของ: คุณ<?= htmlspecialchars(trim(($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['last_name'] ?? ''))) ?></div>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs mb-5">
                        <div class="flex justify-between items-center text-stone-500">
                            <span>ประเภทห้อง</span>
                            <span class="font-semibold text-stone-800 text-right truncate max-w-[150px]"><?= htmlspecialchars(get_pay_room_display_name($booking)) ?></span>
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
                    </div>

                    <div class="pt-4 border-t border-stone-100 flex justify-between items-baseline mb-1">
                        <span class="text-xs font-semibold text-stone-700">ยอดรวมทั้งหมด</span>
                        <span class="text-xl font-bold text-[#C86428]">฿<?= number_format($grand_total, 0) ?></span>
                    </div>
                    <p class="text-[10px] text-stone-400">ราคานี้รวมภาษีมูลค่าเพิ่ม 7% แล้ว</p>
                </div>

                <div class="bg-white border border-stone-200/70 rounded-2xl shadow-sm p-6">
                    <div class="text-xs font-bold text-stone-700 mb-2">ยอดชำระเงินมัดจำ (<?= $DEPOSIT_PERCENT * 100 ?>%)</div>
                    <div class="text-2xl font-black text-[#C86428] mb-1">฿<?= number_format($deposit_amount, 0) ?></div>
                    <p class="text-[10px] text-stone-400">จากยอดรวมทั้งหมด ฿<?= number_format($grand_total, 0) ?></p>
                    <p class="text-[10px] text-stone-400">ชำระวันนี้เพื่อยืนยันการจอง</p>
                </div>

                <div class="bg-white border border-stone-200/70 rounded-2xl shadow-sm p-5 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-stone-50 flex items-center justify-center text-[#C86428] shrink-0">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-stone-900">ต้องการความช่วยเหลือ?</div>
                        <div class="text-[10px] text-stone-400">ติดต่อเราได้ที่ 02-123-4567</div>
                        <div class="text-[10px] text-stone-400">ทุกวัน 08:00 - 20:00 น.</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function selectPaymentMethod(el, method) {
    // รีเซ็ตคลาสการแสดงผลของปุ่มทั้งหมด
    document.querySelectorAll('.payment-method-btn').forEach(function (btn) {
        btn.classList.remove('border-[#C86428]', 'ring-1', 'ring-[#C86428]', 'bg-[#FFF8F3]');
        btn.classList.add('border-stone-200');
        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.remove('text-[#C86428]');
            icon.classList.add('text-stone-400');
        }
    });

    // ไฮไลท์ปุ่มที่เลือก
    el.classList.add('border-[#C86428]', 'ring-1', 'ring-[#C86428]', 'bg-[#FFF8F3]');
    el.classList.remove('border-stone-200');
    const icon = el.querySelector('i');
    if (icon) {
        icon.classList.add('text-[#C86428]');
        icon.classList.remove('text-stone-400');
    }

    // อัปเดตค่าไปยัง input hidden
    document.getElementById('payment_method_input').value = method;

    // รายการหน้าต่างทั้งหมด
    const panels = {
        'bank_transfer': document.getElementById('bank-transfer-panel'),
        'promptpay': document.getElementById('promptpay-panel'),
        'card': document.getElementById('card-panel'),
        'qr': document.getElementById('qr-panel')
    };

    // เปิดการแสดงผล Panel ที่ถูกเลือก และซ่อนส่วนที่เหลือ
    for (const key in panels) {
        if (panels[key]) {
            panels[key].style.display = (method === key) ? '' : 'none';
        }
    }

    // หากเลือกบัตรเครดิต ให้ซ่อนกล่องอัปโหลดสลิป และลบ attribute required ออก
    const slipSection = document.getElementById('slip-upload-section');
    const slipInput = document.getElementById('slip-input');
    if (method === 'card') {
        slipSection.style.display = 'none';
        slipInput.removeAttribute('required');
    } else {
        slipSection.style.display = '';
        slipInput.setAttribute('required', 'required');
    }
}

function copyAccountNumber() {
    const text = document.getElementById('account-number').textContent.trim();
    const label = document.getElementById('copy-btn-label');
    navigator.clipboard.writeText(text).then(function () {
        label.textContent = 'คัดลอกแล้ว!';
        setTimeout(function () { label.textContent = 'คัดลอกข้อมูล'; }, 2000);
    }).catch(function () {
        label.textContent = 'คัดลอกไม่สำเร็จ';
    });
}

document.getElementById('slip-input').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;
    document.getElementById('slip-file-label').textContent = file.name;
    const reader = new FileReader();
    reader.onload = function (evt) {
        document.getElementById('slip-preview').src = evt.target.result;
        document.getElementById('slip-preview-wrap').classList.remove('hidden');
        document.getElementById('slip-placeholder').classList.add('hidden');
    };
    reader.readAsDataURL(file);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>