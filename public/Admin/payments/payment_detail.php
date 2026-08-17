<?php
require_once __DIR__ . '/../../includes/boot.php';

// 1. ตรวจสอบว่ามีการส่ง ID มาหรือไม่
$payment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$payment = null;
if ($payment_id > 0) {
    try {
        // เชื่อมต่อฐานข้อมูล
        $db_host = 'localhost';
        $db_name = 'bambam_cat_hotel';
        $db_user = 'root';
        $db_pass = '';
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // ดึงข้อมูลการชำระเงินและข้อมูลการจองที่เกี่ยวข้อง
        // *หมายเหตุ: เพิ่มการดึงคอลัมน์ slip_image (หากใน DB ใช้ชื่ออื่น ให้เปลี่ยนชื่อคอลัมน์ตรงนี้ด้วยครับ)
        $stmt = $pdo->prepare("SELECT p.*, b.booking_id 
                               FROM payment p 
                               LEFT JOIN booking b ON p.booking_id = b.booking_id 
                               WHERE p.payment_id = :id");
        $stmt->execute(['id' => $payment_id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Database Error: " . $e->getMessage());
    }
}

$pageTitle = 'รายละเอียดการชำระเงิน';
include __DIR__ . '/../../includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <a href="payment_list.php" class="text-orange-600 hover:text-orange-800 font-bold mb-4 inline-block">
        <i class="fas fa-arrow-left"></i> กลับไปยังรายการชำระเงิน
    </a>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-4 text-center font-bold">
            อัปเดตสถานะการชำระเงินเรียบร้อยแล้ว!
        </div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4 text-center font-bold">
            เกิดข้อผิดพลาด: ไม่สามารถบันทึกข้อมูลได้
        </div>
    <?php endif; ?>

    <?php if ($payment): ?>
        <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-8">
            <h1 class="text-2xl font-black text-gray-800 mb-6">รายละเอียดการชำระเงิน #<?= str_pad($payment['payment_id'], 4, '0', STR_PAD_LEFT) ?></h1>
            
            <div class="space-y-4">
                <div class="flex justify-between border-b pb-3">
                    <span class="text-gray-500">รหัสการจอง:</span>
                    <span class="font-bold text-gray-800">#BK-<?= str_pad($payment['booking_id'], 4, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="flex justify-between border-b pb-3">
                    <span class="text-gray-500">วันที่ชำระ:</span>
                    <span class="font-bold text-gray-800"><?= $payment['payment_date'] ?></span>
                </div>
                <div class="flex justify-between border-b pb-3">
                    <span class="text-gray-500">ช่องทาง:</span>
                    <span class="font-bold text-gray-800"><?= htmlspecialchars($payment['payment_method']) ?></span>
                </div>
                <div class="flex justify-between border-b pb-3">
                    <span class="text-gray-500">สถานะปัจจุบัน:</span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 uppercase">
                        <?= htmlspecialchars($payment['status']) ?>
                    </span>
                </div>
                <div class="flex justify-between pt-4 border-b pb-4">
                    <span class="text-lg text-gray-500">ยอดรวมสุทธิ:</span>
                    <span class="text-2xl font-black text-orange-500">฿<?= number_format($payment['amount'], 2) ?></span>
                </div>

                <div class="pt-4">
                    <span class="text-gray-500 block mb-3 text-left font-bold">หลักฐานการโอนเงิน (สลิป):</span>
                    <?php 
                    // ตรวจสอบว่ามีชื่อไฟล์รูปในฐานข้อมูลหรือไม่ (แก้ชื่อคอลัมน์ 'slip_image' ให้ตรงกับ DB ของคุณ)
                    if (!empty($payment['slip_image'])): 
                    ?>
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <img src="../../uploads/slips/<?= htmlspecialchars($payment['slip_image']) ?>" 
                                 alt="สลิปโอนเงิน" 
                                 class="max-w-full h-auto rounded-lg mx-auto border border-gray-200 shadow-sm"
                                 onerror="this.onerror=null; this.src='https://placehold.co/400x500?text=Image+Not+Found';">
                        </div>
                    <?php else: ?>
                        <div class="bg-gray-50 p-8 rounded-xl border border-dashed border-gray-300 text-center text-gray-400">
                            <i class="fas fa-file-invoice-dollar text-4xl mb-2 block"></i>
                            ไม่พบรูปภาพสลิปแนบมาในรายการนี้
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($payment['status'] === 'pending' || $payment['status'] === 'รอตรวจสอบ'): // ปุ่มจะขึ้นเฉพาะตอนที่ยังไม่ได้ตรวจสอบ ?>
                <div class="mt-8">
                    <form action="process_payment.php" method="POST" class="flex gap-3 w-full" onsubmit="return confirm('คุณแน่ใจใช่หรือไม่ที่จะดำเนินการนี้?');">
                        <input type="hidden" name="payment_id" value="<?= $payment['payment_id'] ?>">
                        
                        <button type="submit" name="action" value="confirm" class="flex-1 bg-green-600 text-white py-3 rounded-xl font-bold hover:bg-green-700 transition shadow-sm">
                            <i class="fas fa-check-circle mr-1"></i> ยืนยันการชำระเงิน
                        </button>
                        <button type="submit" name="action" value="reject" class="flex-1 bg-red-50 text-red-600 py-3 rounded-xl font-bold hover:bg-red-100 transition">
                            <i class="fas fa-times-circle mr-1"></i> ปฏิเสธสลิป
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="mt-8 p-4 bg-gray-50 rounded-xl text-center text-sm text-gray-500 font-medium">
                    รายการนี้ได้รับการตรวจสอบแล้วเรียบร้อย
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-10 bg-white rounded-[24px]">
            <p class="text-gray-500">ไม่พบข้อมูลการชำระเงินที่ระบุ</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>