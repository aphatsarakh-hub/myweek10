<?php
require_once __DIR__ . '/../includes/boot.php';

$booking_id = intval($_GET['id'] ?? 0);
$booking = fetch_booking_by_id($booking_id);
$customer_id = $_SESSION['user']['customer_id'] ?? null;

if (!$booking || (int)$booking['customer_id'] !== (int)$customer_id) {
    flash('message', 'ไม่พบการจองของคุณ');
    header('Location: ' . url('Customer/booking_history.php'));
    exit;
}

$pageTitle = 'ใบจองห้องพัก #' . $booking['booking_id'] . ' - BamBam Cat Hotel';
include __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-[#FDFBF7] py-10 px-4 font-sans">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <a href="booking_history.php" class="inline-flex items-center text-xs font-bold uppercase tracking-wider text-[#7A6856] hover:text-orange-600 transition gap-2">
                <i class="fas fa-arrow-left"></i> กลับสู่ประวัติการจอง
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#3B2D20] bg-white px-3 py-1.5 rounded-lg border border-[#D8CFC4] shadow-sm hover:bg-gray-50 transition">
                <i class="fas fa-print"></i> พิมพ์ใบจอง
            </button>
        </div>

        <div class="bg-white rounded-3xl border border-[#E8E1D5] shadow-xl shadow-[#E8E1D5]/40 overflow-hidden">
            <div class="px-8 py-6 bg-[#3B2D20] text-white flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="text-orange-400 text-[10px] uppercase font-bold tracking-widest block">Official Booking Voucher</span>
                    <h1 class="text-xl md:text-2xl font-extrabold font-mono">BOOKING ID: #<?= htmlspecialchars($booking['booking_id']); ?></h1>
                </div>
                <div class="text-right">
                    <span class="block text-[11px] text-[#D8CFC4] mb-1">สถานะระบบแอดมิน</span>
                    <?php
                    $status = $booking['status'] ?? 'Pending';
                    if ($status === 'Confirmed') {
                        echo '<span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 rounded-full px-3 py-1 text-xs font-bold inline-flex items-center"><i class="fas fa-check-circle mr-1.5"></i> ยืนยันห้องพักแล้ว</span>';
                    } elseif ($status === 'Pending') {
                        echo '<span class="bg-amber-500/20 text-amber-300 border border-amber-500/40 rounded-full px-3 py-1 text-xs font-bold inline-flex items-center"><i class="fas fa-clock mr-1.5"></i> รอแอดมินจัดสรรห้อง</span>';
                    } elseif ($status === 'Cancelled') {
                        echo '<span class="bg-red-500/20 text-red-300 border border-red-500/40 rounded-full px-3 py-1 text-xs font-bold inline-flex items-center"><i class="fas fa-times-circle mr-1.5"></i> ยกเลิกรายการแล้ว</span>';
                    } else {
                        echo '<span class="bg-blue-500/20 text-blue-300 border border-blue-500/40 rounded-full px-3 py-1 text-xs font-bold inline-flex items-center">' . htmlspecialchars($status) . '</span>';
                    }
                    ?>
                </div>
            </div>

            <div class="p-8 space-y-6 text-sm">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pb-6 border-b border-[#F0EAE1]">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#8C6239] block mb-2">ข้อมูลผู้จอง (Customer)</span>
                        <p class="font-bold text-[#3B2D20] text-base"><?= htmlspecialchars(($booking['customer_first_name'] ?? '') . ' ' . ($booking['customer_last_name'] ?? '')); ?></p>
                        <p class="text-xs text-[#7A6856] mt-0.5"><i class="fas fa-envelope text-orange-400 mr-1.5"></i> <?= htmlspecialchars($booking['customer_email'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#8C6239] block mb-2">แขกผู้เข้าพัก (Guest Cat)</span>
                        <p class="font-bold text-[#3B2D20] text-base">🐱 <?= htmlspecialchars($booking['cat_name']); ?></p>
                        <p class="text-xs text-[#7A6856] mt-0.5">สายพันธุ์: <?= htmlspecialchars($booking['cat_breed'] ?? 'ทั่วไป'); ?></p>
                    </div>
                </div>

                <div class="space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#8C6239] block">รายละเอียดห้องพักและช่วงเวลา</span>
                    <div class="rounded-2xl bg-[#FDF8F0] border border-[#F0E2D0] p-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <span class="text-xs text-[#7A6856] block">ประเภทห้องพัก</span>
                            <span class="font-extrabold text-[#3B2D20] text-base"><?= htmlspecialchars($booking['roomtype_name']); ?></span>
                        </div>
                        <div>
                            <span class="text-xs text-[#7A6856] block">วันที่เช็คอิน</span>
                            <span class="font-bold text-orange-600"><?= htmlspecialchars($booking['start_date']); ?></span>
                        </div>
                        <div>
                            <span class="text-xs text-[#7A6856] block">วันที่เช็คเอาท์</span>
                            <span class="font-bold text-orange-600"><?= htmlspecialchars($booking['end_date']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 space-y-2 border-t border-[#F0EAE1]">
                    <div class="flex justify-between items-center text-[#7A6856]">
                        <span>ค่าบริการห้องพักและดูแล 24 ชม.</span>
                        <span class="font-semibold text-[#3B2D20]">฿<?= number_format($booking['total_price'], 2); ?></span>
                    </div>
                    <div class="flex justify-between items-center text-[#7A6856]">
                        <span>ภาษีมูลค่าเพิ่ม & Service Charge</span>
                        <span class="font-semibold text-emerald-600">ฟรี (รวมในแพ็กเกจแล้ว)</span>
                    </div>
                    <div class="h-px bg-[#EADCC8] my-3"></div>
                    <div class="flex justify-between items-baseline">
                        <span class="text-base font-extrabold text-[#3B2D20]">ยอดชำระสุทธิ (Net Total)</span>
                        <span class="text-3xl font-black text-orange-600">฿<?= number_format($booking['total_price'], 2); ?> <span class="text-xs font-medium text-[#7A6856]">THB</span></span>
                    </div>
                </div>

                <?php if (($booking['status'] ?? '') === 'Pending'): ?>
                <div class="mt-8 pt-6 border-t border-[#F0EAE1] flex items-center justify-between bg-red-50/50 p-4 rounded-2xl border border-red-100">
                    <div>
                        <span class="text-xs font-bold text-red-800 block"><i class="fas fa-exclamation-triangle mr-1"></i> ต้องการยกเลิกรายการจองนี้?</span>
                        <span class="text-[11px] text-red-600">สามารถยกเลิกได้ฟรีในขณะที่สถานะยังเป็น "รอแอดมินจัดสรรห้อง"</span>
                    </div>
                    <a href="cancel_booking.php?id=<?= $booking['booking_id']; ?>" class="inline-flex items-center gap-1.5 rounded-xl bg-red-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-red-700 transition">
                        <i class="fas fa-ban"></i> ขอยกเลิกการจอง
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>