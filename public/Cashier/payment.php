<?php
require_once __DIR__ . '/../includes/boot.php';

// รับค่า ID ถ้าไม่มีให้เป็น 0
$booking_id = intval($_GET['id'] ?? 0);
$booking = fetch_booking_by_id($booking_id);

// 🚨 จุดที่เพิ่มเข้ามา: ดักจับกรณีไม่มี ID หรือหาการจองไม่เจอ
if (!$booking) {
    flash('message', 'กรุณาเลือกรายการจองที่ต้องการชำระเงินจากตารางก่อนครับ');
    header('Location: booking_list.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount'] ?? 0);
    $payment_method = trim($_POST['payment_method'] ?? '');

    if ($amount <= 0 || $payment_method === '') {
        $error = 'กรุณาระบุจำนวนเงินและวิธีการชำระเงิน';
    } else {
        // ใช้ฟังก์ชันที่อยู่ใน functions.php
        $payment_id = insert_payment($booking_id, $amount, $payment_method);
        
        if ($payment_id) {
            flash('message', 'บันทึกการชำระเงินเรียบร้อยแล้ว');
            header('Location: booking_list.php'); 
            exit;
        }
        $error = 'ไม่สามารถบันทึกการชำระเงินได้ เกิดข้อผิดพลาดในระบบ';
    }
}
$pageTitle = 'ชำระเงิน (Cashier)';
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-title" style="margin-bottom: 20px;">
    <div>
      <h1 style="font-family: 'Noto Serif Thai', serif; font-weight: 700;">ชำระเงิน</h1>
      <p style="color: #6C7C80;">บันทึกการชำระเงินสำหรับการจองรหัส #<?php echo htmlspecialchars($booking['booking_id']); ?></p>
    </div>
    <a href="booking_list.php" class="button button-secondary">กลับไปหน้ารายการ</a>
  </div>

  <div class="card" style="max-width: 600px; margin: 0 auto; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
    <?php if ($error): ?>
      <div style="padding: 15px; border-left: 4px solid #C4453F; background: #FBEAE9; color: #C4453F; margin-bottom: 20px; border-radius: 4px;">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <div style="background: #F6F8F8; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin-bottom: 8px;"><strong>รหัสการจอง:</strong> <span style="font-family: monospace; font-size: 1.1em;">#<?php echo htmlspecialchars($booking['booking_id']); ?></span></p>
        <p style="margin-bottom: 8px;"><strong>ลูกค้า:</strong> <?php echo htmlspecialchars(trim($booking['customer_first_name'] . ' ' . $booking['customer_last_name'])); ?></p>
        <p style="margin-bottom: 0;"><strong>ยอดที่ต้องชำระ:</strong> <span style="color: #C8862A; font-size: 1.2em; font-weight: bold;">฿<?php echo htmlspecialchars(number_format($booking['total_price'], 2)); ?></span></p>
    </div>

    <form method="post" style="display: flex; flex-direction: column; gap: 15px;">
      <div>
        <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9em; color: #6C7C80;">จำนวนเงินที่ชำระ (บาท)</label>
        <input type="number" name="amount" step="0.01" min="0" required value="<?php echo htmlspecialchars($_POST['amount'] ?? $booking['total_price']); ?>" style="width: 100%; padding: 10px 15px; border: 1px solid #E2E8E9; border-radius: 8px;" />
      </div>
      
      <div>
        <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9em; color: #6C7C80;">วิธีการชำระเงิน</label>
        <select name="payment_method" required style="width: 100%; padding: 10px 15px; border: 1px solid #E2E8E9; border-radius: 8px; background-color: #fff;">
          <option value="">-- กรุณาเลือก --</option>
          <option value="เงินสด"<?php echo (($_POST['payment_method'] ?? '') === 'เงินสด') ? ' selected' : ''; ?>>💵 เงินสด</option>
          <option value="โอนเงิน"<?php echo (($_POST['payment_method'] ?? '') === 'โอนเงิน') ? ' selected' : ''; ?>>📱 โอนเงินผ่านธนาคาร</option>
          <option value="บัตรเครดิต"<?php echo (($_POST['payment_method'] ?? '') === 'บัตรเครดิต') ? ' selected' : ''; ?>>💳 บัตรเครดิต</option>
        </select>
      </div>
      
      <button type="submit" style="margin-top: 10px; width: 100%; padding: 12px; background: #2B6777; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s;">
        ยืนยันรับชำระเงิน
      </button>
    </form>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>