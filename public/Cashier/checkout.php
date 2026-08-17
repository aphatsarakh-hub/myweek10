<?php
require_once __DIR__ . '/../includes/boot.php';
$booking_id = intval($_GET['id'] ?? 0);
$booking = fetch_booking_by_id($booking_id);
$staff_id = $_SESSION['user']['staff_id'] ?? null;
if (!$booking) {
    flash('message', 'ไม่พบรายการการจอง');
    header('Location: booking_list.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $additional_charges = floatval($_POST['additional_charges'] ?? 0);
    if ($additional_charges < 0) {
        $error = 'จำนวนเงินเพิ่มเติมต้องไม่ติดลบ';
    } else {
        $checkout_id = insert_checkout($booking_id, $staff_id, $additional_charges);
        if ($checkout_id) {
            flash('message', 'เช็คเอาท์สำเร็จแล้ว');
            header('Location: booking_list.php');
            exit;
        }
        $error = 'ไม่สามารถบันทึกการเช็คเอาท์ได้';
    }
}
$pageTitle = 'เช็คเอาท์ (Cashier)';
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>เช็คเอาท์</h1>
      <p>บันทึกการเช็คเอาท์สำหรับการจองนี้</p>
    </div>
    <a href="booking_detail.php?id=<?php echo $booking['booking_id']; ?>" class="button button-secondary">กลับไปยังการจอง</a>
  </div>

  <div class="card">
    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <p><strong>รหัสการจอง:</strong> <?php echo htmlspecialchars($booking['booking_id']); ?></p>
    <p><strong>ชื่อแมว:</strong> <?php echo htmlspecialchars($booking['cat_name']); ?></p>
    <p><strong>ลูกค้า:</strong> <?php echo htmlspecialchars(trim($booking['customer_first_name'] . ' ' . $booking['customer_last_name'])); ?></p>
    <form method="post">
      <label>ค่าธรรมเนียมเพิ่มเติม
        <input type="number" name="additional_charges" step="0.01" min="0" required value="<?php echo htmlspecialchars($_POST['additional_charges'] ?? '0.00'); ?>" />
      </label>
      <button type="submit" class="button button-primary">บันทึกเช็คเอาท์</button>
    </form>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
