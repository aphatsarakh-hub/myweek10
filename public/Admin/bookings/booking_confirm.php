<?php
require_once __DIR__ . '/../../includes/boot.php';
$booking_id = intval($_GET['id'] ?? 0);
$booking = fetch_booking_by_id($booking_id);
if (!$booking) {
    flash('message', 'ไม่พบรายการการจอง');
    header('Location: booking_list.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (update_booking($booking_id, $booking['customer_id'], $booking['cat_id'], $booking['roomtype_id'], $booking['start_date'], $booking['end_date'], $booking['total_price'], 'Confirmed')) {
        flash('message', 'ยืนยันการจองเรียบร้อยแล้ว');
        header('Location: booking_list.php');
        exit;
    }
    flash('message', 'ไม่สามารถยืนยันการจองได้');
    header('Location: booking_list.php');
    exit;
}
$pageTitle = 'ยืนยันการจอง (Admin)';
include __DIR__ . '/../../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>ยืนยันการจอง</h1>
      <p>ยืนยันสถานะการจองนี้</p>
    </div>
    <a href="booking_list.php" class="button button-secondary">กลับไปยังรายการ</a>
  </div>

  <div class="card">
    <p>ยืนยันการจองของ <strong><?php echo htmlspecialchars(trim($booking['customer_first_name'] . ' ' . $booking['customer_last_name'])); ?></strong> สำหรับแมว <strong><?php echo htmlspecialchars($booking['cat_name']); ?></strong>?</p>
    <form method="post">
      <button type="submit" class="button button-primary">ยืนยันการจอง</button>
    </form>
  </div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
