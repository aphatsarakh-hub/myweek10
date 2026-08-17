<?php
require_once __DIR__ . '/../includes/boot.php';
$booking_id = intval($_GET['id'] ?? 0);
$booking = fetch_booking_by_id($booking_id);
if (!$booking) {
    flash('message', 'ไม่พบรายการการจอง');
    header('Location: booking_list.php');
    exit;
}
$pageTitle = 'รายละเอียดการจอง (Cashier)';
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>รายละเอียดการจอง</h1>
      <p>ข้อมูลฉบับเต็มสำหรับการประมวลผล</p>
    </div>
    <a href="booking_list.php" class="button button-secondary">กลับไปยังรายการ</a>
  </div>

  <div class="card">
    <p><strong>ลูกค้า:</strong> <?php echo htmlspecialchars(trim($booking['customer_first_name'] . ' ' . $booking['customer_last_name'])); ?></p>
    <p><strong>อีเมล:</strong> <?php echo htmlspecialchars($booking['customer_email']); ?></p>
    <p><strong>ชื่อแมว:</strong> <?php echo htmlspecialchars($booking['cat_name']); ?></p>
    <p><strong>ประเภทห้องพัก:</strong> <?php echo htmlspecialchars($booking['roomtype_name']); ?></p>
    <p><strong>วันที่เริ่มต้น:</strong> <?php echo htmlspecialchars($booking['start_date']); ?></p>
    <p><strong>วันที่สิ้นสุด:</strong> <?php echo htmlspecialchars($booking['end_date']); ?></p>
    <p><strong>ราคารวม:</strong> <?php echo htmlspecialchars(number_format($booking['total_price'], 2)); ?> บาท</p>
    <p><strong>สถานะ:</strong> <?php echo htmlspecialchars($booking['status']); ?></p>
    <div class="button-group" style="margin-top:16px;">
      <a href="payment.php?id=<?php echo $booking['booking_id']; ?>" class="button button-primary">ชำระเงิน</a>
      <a href="checkin.php?id=<?php echo $booking['booking_id']; ?>" class="button button-secondary">เช็คอิน</a>
      <a href="checkout.php?id=<?php echo $booking['booking_id']; ?>" class="button button-secondary">เช็คเอาท์</a>
    </div>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
