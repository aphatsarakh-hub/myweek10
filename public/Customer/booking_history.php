<?php
require_once __DIR__ . '/../includes/boot.php';

$pageTitle = 'ประวัติการจองทั้งหมด - BamBam Cat Hotel';
$customer_id = (int)($_SESSION['user']['customer_id'] ?? 0);
$bookings = fetch_bookings_by_customer($customer_id);

include __DIR__ . '/../includes/header.php';
$flashMsg = flash('message');
?>

<div class="min-h-screen bg-[#FDFBF7] py-10 px-4 sm:px-6 lg:px-8 font-sans">
    <div class="max-w-6xl mx-auto">
        
        <?php if ($flashMsg): ?>
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 text-sm text-emerald-800 flex items-center gap-3 shadow-sm">
            <i class="fas fa-check-circle text-lg text-emerald-600"></i>
            <span class="font-medium"><?= htmlspecialchars($flashMsg) ?></span>
        </div>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-[#3B2D20]">ประวัติการจองห้องพัก</h1>
                <p class="text-sm text-[#7A6856] mt-1">ตรวจสอบสถานะ ติดตามใบจอง และรายละเอียดการเข้าพักของน้องแมวทั้งหมด</p>
            </div>
            <a href="booking.php" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-amber-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-orange-500/25 hover:brightness-105 transition shrink-0">
                <i class="fas fa-plus"></i> จองห้องพักเพิ่ม
            </a>
        </div>

        <?php if (empty($bookings)): ?>
            <div class="bg-white rounded-3xl border border-[#EAE2D6] p-16 text-center shadow-xl shadow-[#E8E1D5]/40">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-[#FDF8F0] text-orange-500 text-2xl border border-orange-100">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h3 class="text-lg font-bold text-[#3B2D20] mb-1">ยังไม่มีประวัติการจอง</h3>
                <p class="text-[#7A6856] text-sm mb-6">คุณยังไม่เคยส่งคำขอจองห้องพักในระบบในขณะนี้</p>
                <a href="booking.php" class="inline-flex items-center gap-2 rounded-xl bg-[#3B2D20] px-6 py-3 text-sm font-bold text-white shadow hover:bg-opacity-90 transition">เริ่มการจองครั้งแรก</a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-3xl border border-[#E8E1D5] shadow-xl shadow-[#E8E1D5]/40 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="bg-[#FDF8F0] border-b border-[#EADCC8] text-[#8C6239] text-xs font-bold uppercase tracking-wider">
                                <th class="px-6 py-4">หมายเลข</th>
                                <th class="px-6 py-4">น้องแมว</th>
                                <th class="px-6 py-4">ประเภทห้อง</th>
                                <th class="px-6 py-4">วันที่เข้าพัก -> รับกลับ</th>
                                <th class="px-6 py-4">ยอดรวม</th>
                                <th class="px-6 py-4 text-center">สถานะฝั่งแอดมิน</th>
                                <th class="px-6 py-4 text-center">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F0EAE1]">
                            <?php foreach ($bookings as $booking): ?>
                                <?php
                                $status = $booking['status'] ?? 'Pending';
                                if ($status === 'Confirmed') {
                                    $badgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                    $statusTh = 'ยืนยันแล้ว';
                                } elseif ($status === 'Checked In') {
                                    $badgeClass = 'bg-blue-50 text-blue-700 border border-blue-200';
                                    $statusTh = 'กำลังเข้าพัก';
                                } elseif ($status === 'Pending') {
                                    $badgeClass = 'bg-amber-50 text-amber-700 border border-amber-200';
                                    $statusTh = 'รอแอดมินอนุมัติ';
                                } elseif ($status === 'Cancelled') {
                                    $badgeClass = 'bg-red-50 text-red-600 border border-red-200';
                                    $statusTh = 'ยกเลิกแล้ว';
                                } else {
                                    $badgeClass = 'bg-gray-50 text-gray-600 border border-gray-200';
                                    $statusTh = $status;
                                }
                                ?>
                                <tr class="hover:bg-[#FFFCF7] transition duration-150">
                                    <td class="px-6 py-4 font-mono font-bold text-[#7A6856]">#<?= htmlspecialchars($booking['booking_id']); ?></td>
                                    <td class="px-6 py-4 font-bold text-[#3B2D20]">🐱 <?= htmlspecialchars($booking['cat_name']); ?></td>
                                    <td class="px-6 py-4"><span class="bg-[#F0EAE1] text-[#3B2D20] px-2.5 py-1 rounded-lg text-xs font-bold"><?= htmlspecialchars($booking['roomtype_name']); ?></span></td>
                                    <td class="px-6 py-4 text-[#7A6856] whitespace-nowrap"><i class="far fa-calendar-alt text-orange-500 mr-1.5"></i><?= htmlspecialchars($booking['start_date'] . ' ถึง ' . $booking['end_date']); ?></td>
                                    <td class="px-6 py-4 font-extrabold text-orange-600">฿<?= number_format($booking['total_price'], 0); ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold <?= $badgeClass ?>">
                                            <?= $statusTh ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="booking_detail.php?id=<?= $booking['booking_id']; ?>" class="inline-flex items-center gap-1.5 rounded-xl border border-[#D8CFC4] bg-white px-3.5 py-1.5 text-xs font-bold text-[#3B2D20] hover:border-orange-500 hover:text-orange-600 transition shadow-sm">
                                            <i class="fas fa-eye text-[11px]"></i> รายละเอียด
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>