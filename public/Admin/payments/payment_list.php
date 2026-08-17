<?php
// 1. เรียกไฟล์ตั้งค่าหลัก
require_once __DIR__ . '/../../includes/boot.php';

// 2. ตั้งค่าตัวแปรสำหรับหน้าเว็บ
$pageTitle = 'ตรวจสอบการชำระเงิน';

// 3. เชื่อมต่อฐานข้อมูล (ใช้การเชื่อมต่อในหน้าเพื่อความชัวร์)
try {
    $db_host = 'localhost';
    $db_name = 'bambam_cat_hotel';
    $db_user = 'root';
    $db_pass = '';
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ดึงข้อมูลจริงจากตาราง payment
    $stmt = $pdo->prepare("SELECT payment_id, booking_id, payment_date, amount, payment_method, status FROM payment ORDER BY payment_id DESC");
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $payments = [];
    error_log("Database Error: " . $e->getMessage());
}

// 4. คำนวณสรุปยอดจากข้อมูลจริงที่ดึงมาได้
$totalPending = 0;
$totalIncome = 0;
foreach ($payments as $p) {
    $status = strtolower(trim($p['status'] ?? ''));
    if (in_array($status, ['pending', 'รอตรวจสอบ', 'รอชำระเงิน'])) {
        $totalPending++;
    } elseif (in_array($status, ['completed', 'สำเร็จ', 'ชำระแล้ว'])) {
        $totalIncome += (float)$p['amount'];
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <p class="text-sm text-gray-500">จัดการสลิปโอนเงิน ยอดชำระ และยืนยันสถานะการจอง</p>
        <div class="relative">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" id="paymentSearchInput" placeholder="ค้นหารหัสการจอง..." class="pl-11 pr-4 py-2.5 w-64 md:w-80 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-orange-400 outline-none shadow-sm">
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <div class="bg-gradient-to-br from-amber-500 to-orange-400 p-6 rounded-[24px] shadow-sm text-white h-32 flex flex-col justify-between">
            <p class="text-xs font-bold opacity-80 uppercase">รอตรวจสอบสลิป</p>
            <h3 class="text-4xl font-black"><?= number_format($totalPending) ?> <span class="text-sm font-normal">รายการ</span></h3>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-emerald-400 p-6 rounded-[24px] shadow-sm text-white h-32 flex flex-col justify-between">
            <p class="text-xs font-bold opacity-80 uppercase">ยอดรวมสำเร็จ</p>
            <h3 class="text-3xl font-black">฿<?= number_format($totalIncome, 0) ?></h3>
        </div>
        <div class="bg-white p-6 rounded-[24px] shadow-sm border border-gray-100 h-32 flex flex-col justify-between">
            <p class="text-xs font-bold text-gray-400 uppercase">รายการทั้งหมด</p>
            <h3 class="text-4xl font-black text-gray-800"><?= number_format(count($payments)) ?> <span class="text-sm font-normal text-gray-500">รายการ</span></h3>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5" id="paymentGrid">
        <?php if (empty($payments)): ?>
            <div class="col-span-full py-16 text-center text-gray-500">ยังไม่มีข้อมูลการชำระเงินในระบบ</div>
        <?php else: foreach ($payments as $p): ?>
            <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 p-5">
                <h4 class="text-sm font-bold text-gray-800">PM-<?= str_pad($p['payment_id'], 4, '0', STR_PAD_LEFT) ?></h4>
                <p class="text-xs text-gray-500 my-2">ยอด: ฿<?= number_format($p['amount'], 2) ?></p>
                <a href="payment_detail.php?id=<?= $p['payment_id'] ?>" class="block text-center bg-gray-900 text-white py-2 rounded-lg text-xs font-bold hover:bg-orange-600 transition">ตรวจสอบ</a>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>