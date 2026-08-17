<?php
require_once __DIR__ . '/../includes/boot.php';
$pageTitle = 'เลือกบริการ (Customer)';

$customer_id = current_customer_id();
if (!$customer_id) {
    header('Location: ../customer_portal.php');
    exit;
}

$bookingId = (int) ($_GET['booking_id'] ?? 0);
$booking   = $bookingId ? fetch_booking_by_id($bookingId) : null;

// ต้องเป็น booking ของลูกค้าคนนี้เท่านั้น
if (!$booking || (int)$booking['customer_id'] !== $customer_id) {
    $booking = null;
}

// Action: จองบริการเข้ากับ booking นี้
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_service']) && $booking) {
    $templateId = (int) $_POST['service_id'];
    book_service($templateId, $booking['booking_id']);
    flash('message', 'เพิ่มบริการสำเร็จ');
    header('Location: select_service.php?booking_id=' . $booking['booking_id']);
    exit;
}

$services = fetch_active_services();
$myServicesForBooking = $booking ? fetch_services_by_booking($booking['booking_id']) : [];

include __DIR__ . '/../includes/header.php';
?>

  <?php if ($msg = flash('message')): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($msg); ?></div>
  <?php endif; ?>

  <h1>เลือกบริการ</h1>
  <p>เลือกรายการบริการเพิ่มเติมสำหรับการจอง</p>

  <?php if (!$booking): ?>
    <div class="card">
      <p>ไม่พบการจองที่ระบุ หรือคุณไม่มีสิทธิ์เข้าถึงการจองนี้</p>
      <p><a href="service.php">กลับไปหน้าบริการทั้งหมด</a></p>
    </div>
  <?php else: ?>

    <div class="card" style="margin-bottom:1.5rem;">
      <p>กำลังเพิ่มบริการให้กับการจอง <strong>#<?php echo (int)$booking['booking_id']; ?></strong>
         (<?php echo htmlspecialchars($booking['start_date']); ?> - <?php echo htmlspecialchars($booking['end_date']); ?>)</p>
    </div>

    <div class="card table-responsive">
      <?php if (empty($services)): ?>
        <p>ยังไม่มีบริการในระบบ</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>ชื่อบริการ</th>
              <th>รายละเอียด</th>
              <th>ราคา</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($services as $service): ?>
              <tr>
                <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                <td><?php echo htmlspecialchars($service['service_detail']); ?></td>
                <td><?php echo htmlspecialchars(number_format($service['price'], 2)); ?></td>
                <td>
                  <form method="post">
                    <input type="hidden" name="service_id" value="<?php echo (int)$service['service_id']; ?>">
                    <button type="submit" name="book_service" class="btn btn-sm">เพิ่ม</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="page-title" style="margin-top:2rem;">
      <div><h2>บริการที่เพิ่มแล้วในการจองนี้</h2></div>
    </div>

    <div class="card table-responsive">
      <?php if (empty($myServicesForBooking)): ?>
        <p>ยังไม่มีบริการเพิ่มเติมสำหรับการจองนี้</p>
      <?php else: ?>
        <table>
          <thead>
            <tr><th>ชื่อบริการ</th><th>ราคา</th><th>สถานะ</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($myServicesForBooking as $s): ?>
              <tr>
                <td><?php echo htmlspecialchars($s['service_name']); ?></td>
                <td><?php echo htmlspecialchars(number_format($s['price'], 2)); ?></td>
                <td><?php echo htmlspecialchars($s['status']); ?></td>
                <td>
                  <?php if ($s['status'] === 'Booked'): ?>
                    <a href="service.php?cancel=<?php echo (int)$s['service_id']; ?>"
                       onclick="return confirm('ยืนยันยกเลิกบริการนี้?');">ยกเลิก</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

  <?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>