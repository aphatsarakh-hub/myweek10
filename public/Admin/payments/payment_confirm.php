<?php
require_once __DIR__ . '/../../includes/boot.php';

$payment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

// เชื่อมต่อฐานข้อมูล
try {
    $db_host = 'localhost'; $db_name = 'bambam_cat_hotel'; $db_user = 'root'; $db_pass = '';
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. จัดการการอัปเดตสถานะเมื่อกดปุ่มยืนยัน
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
        $stmt = $pdo->prepare("UPDATE payment SET status = 'completed' WHERE payment_id = :id");
        $stmt->execute(['id' => $payment_id]);
        $message = "ยืนยันการชำระเงินเรียบร้อยแล้ว!";
    }

    // 2. ดึงข้อมูลการชำระเงิน
    $stmt = $pdo->prepare("SELECT * FROM payment WHERE payment_id = :id");
    $stmt->execute(['id' => $payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $payment = null;
}

$pageTitle = 'ยืนยันการชำระเงิน';
include __DIR__ . '/../../includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-lg">
    <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-8">
        <h1 class="text-2xl font-black text-gray-800 mb-6">ยืนยันการชำระเงิน</h1>

        <?php if ($message): ?>
            <div class="bg-green-50 text-green-600 p-4 rounded-xl font-bold text-center mb-6">
                <i class="fas fa-check-circle"></i> <?= $message ?>
            </div>
            <a href="payment_list.php" class="block text-center bg-gray-900 text-white py-3 rounded-xl font-bold">กลับหน้ารายการ</a>
        <?php elseif ($payment): ?>
            <div class="text-center mb-6">
                <p class="text-gray-500">ตรวจสอบความถูกต้องของสลิป</p>
                <h2 class="text-3xl font-black text-gray-900">฿<?= number_format($payment['amount'], 2) ?></h2>
                <p class="text-sm text-gray-400 mt-2">รหัสการชำระเงิน: PM-<?= str_pad($payment['payment_id'], 4, '0', STR_PAD_LEFT) ?></p>
            </div>

            <form method="POST" class="flex gap-3">
                <button name="confirm_payment" class="flex-1 bg-green-600 text-white py-3 rounded-xl font-bold hover:bg-green-700">ยืนยันว่าได้รับเงินแล้ว</button>
                <a href="payment_list.php" class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl font-bold text-center">ยกเลิก</a>
            </form>
        <?php else: ?>
            <p class="text-center text-red-500 font-bold">ไม่พบข้อมูลการชำระเงิน</p>
            <a href="payment_list.php" class="block text-center mt-4 text-orange-600 font-bold">กลับหน้ารายการ</a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>