<?php
require_once __DIR__ . '/../includes/boot.php';

$booking_id = intval($_GET['id'] ?? 0);
$booking = fetch_booking_by_id($booking_id);
$customer_id = (int)($_SESSION['user']['customer_id'] ?? 0);

// ตรวจสอบความปลอดภัยและสิทธิ์
if (!$booking || (int)$booking['customer_id'] !== $customer_id) {
    flash('message', 'ไม่พบการจองของคุณ');
    header('Location: ' . url('Customer/booking_history.php'));
    exit;
}

// อนุญาตให้ยกเลิกได้เฉพาะรายการที่สถานะเป็น Pending เท่านั้น
if (($booking['status'] ?? '') !== 'Pending') {
    flash('message', 'ไม่สามารถยกเลิกรายการที่ผ่านการตรวจสอบไปแล้วได้');
    header('Location: ' . url('Customer/booking_detail.php?id=' . $booking_id));
    exit;
}

$error = '';
$reasonInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reasonInput = trim($_POST['reason'] ?? '');

    if ($reasonInput === '') {
        $error = 'กรุณาระบุเหตุผลในการยกเลิกการจอง';
    } else {
        // บันทึกคำขอยกเลิก (insert เข้า cancellations) + อัปเดตสถานะ booking
        // เป็น Cancelled ในทรานแซกชันเดียวกัน ให้หน้าแอดมิน cancel_list.php
        // ดึงไปแสดงผลได้ทันทีผ่าน fetch_cancellations()
        $refundAmount = 0.00; // ตามนโยบายไม่คืนเงิน (ดู cancel_list.php)
        $success = create_cancellation($booking_id, $customer_id, $reasonInput, $refundAmount);

        if ($success) {
            flash('message', 'ยกเลิกรายการจอง #' . $booking_id . ' เรียบร้อยแล้ว ระบบได้ปลดล็อกห้องพักคืนสู่ระบบแอดมินแล้วครับ');
            header('Location: ' . url('Customer/booking_history.php'));
            exit;
        } else {
            $error = 'ไม่สามารถดำเนินการยกเลิกได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง';
        }
    }
}

$pageTitle = 'ยืนยันการยกเลิกการจอง - BamBam Cat Hotel';
include __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-[#FDFBF7] py-16 px-4 font-sans flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-3xl border border-[#E8E1D5] shadow-2xl p-8 text-center space-y-6">
        
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-50 text-red-600 text-2xl border border-red-100">
            <i class="fas fa-exclamation-triangle"></i>
        </div>

        <div>
            <h1 class="text-xl font-bold text-[#3B2D20]">ยืนยันการยกเลิกคำขอจอง?</h1>
            <p class="text-xs text-[#7A6856] mt-2 leading-relaxed">
                ท่านกำลังขอยกเลิกรายการจองหมายเลข <strong class="font-mono text-[#3B2D20]">#<?= htmlspecialchars($booking_id) ?></strong> ของน้องแมวชื่อ <strong class="text-orange-600"><?= htmlspecialchars($booking['cat_name']) ?></strong> เมื่อยืนยันแล้วระบบจะคืนสิทธิ์ห้องพักนี้ให้ลูกค้าท่านอื่นทันที
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="rounded-xl bg-red-50 border border-red-200 p-3 text-xs text-red-800 text-left">
                <i class="fas fa-exclamation-circle mr-1"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-3 pt-2 text-left">
            <div>
                <label for="reason" class="block text-xs font-bold text-[#3B2D20] mb-1.5">
                    เหตุผลในการยกเลิก <span class="text-red-500">*</span>
                </label>
                <textarea
                    id="reason"
                    name="reason"
                    rows="3"
                    required
                    placeholder="เช่น เปลี่ยนแผนการเดินทาง, จองผิดวันที่ ฯลฯ"
                    class="w-full rounded-xl border border-[#D8CFC4] p-3 text-sm text-[#3B2D20] focus:ring-2 focus:ring-red-400 outline-none resize-none"
                ><?= htmlspecialchars($reasonInput) ?></textarea>
            </div>
            <button type="submit" class="w-full rounded-xl bg-red-600 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-500/25 hover:bg-red-700 transition active:scale-[0.99]">
                <i class="fas fa-trash-alt mr-1.5"></i> ยืนยันยกเลิกรายการจอง
            </button>
            <a href="booking_detail.php?id=<?= $booking_id ?>" class="block w-full rounded-xl border border-[#D8CFC4] bg-[#FDFCF9] py-3 text-sm font-bold text-[#3B2D20] hover:bg-gray-100 transition">
                เปลี่ยนใจ กลับไปหน้าเดิม
            </a>
        </form>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>