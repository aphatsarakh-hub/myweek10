<?php
require_once __DIR__ . '/../includes/boot.php';
require_role(['cashier','admin']);
$pageTitle = 'เช็คเอาท์ด่วน (Cashier)';
$bookings = fetch_bookings();
// Filter bookings that are eligible for checkout (Confirmed or maybe already checked in)
$ready = array_filter($bookings, function($b) {
    return isset($b['status']) && ($b['status'] === 'Confirmed' || $b['status'] === 'Pending');
});
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>เช็คเอาท์ด่วน</h1>
      <p>เลือกการจองเพื่อเข้าสู่กระบวนการเช็คเอาท์</p>
    </div>
  </div>

  <div class="card table-responsive">
    <?php if (empty($ready)): ?>
      <p>ยังไม่มีรายการที่พร้อมเช็คเอาท์</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>ลูกค้า</th>
            <th>ชื่อแมว</th>
            <th>ห้องพัก</th>
            <th>วันที่</th>
            <th>สถานะ</th>
            <th>จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ready as $b): ?>
            <tr>
              <td><?php echo htmlspecialchars($b['booking_id']); ?></td>
              <td><?php echo htmlspecialchars(trim($b['customer_first_name'] . ' ' . $b['customer_last_name'])); ?></td>
              <td><?php echo htmlspecialchars($b['cat_name']); ?></td>
              <td><?php echo htmlspecialchars($b['roomtype_name']); ?></td>
              <td><?php echo htmlspecialchars($b['start_date'] . ' ถึง ' . $b['end_date']); ?></td>
              <td><?php echo htmlspecialchars($b['status']); ?></td>
              <td>
                <div class="button-group">
                  <a href="booking_detail.php?id=<?php echo $b['booking_id']; ?>" class="button button-secondary">ดู</a>
                  <a href="checkout.php?id=<?php echo $b['booking_id']; ?>" class="button button-primary">เช็คเอาท์</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
