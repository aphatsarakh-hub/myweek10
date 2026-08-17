<?php
require_once __DIR__ . '/../includes/boot.php';
$pageTitle = 'ประวัติการชำระเงิน (Cashier)';
$payments = fetch_payments();
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>ประวัติการชำระเงิน</h1>
      <p>ดูการชำระเงินทั้งหมดที่บันทึกในระบบ</p>
    </div>
  </div>

  <div class="card table-responsive">
    <?php if (empty($payments)): ?>
      <p>ยังไม่มีการชำระเงินในระบบ</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>การจอง</th>
            <th>ลูกค้า</th>
            <th>ชื่อแมว</th>
            <th>ยอดชำระ</th>
            <th>วิธีชำระ</th>
            <th>สถานะ</th>
            <th>วันที่</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $payment): ?>
            <tr>
              <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
              <td><?php echo htmlspecialchars($payment['booking_id']); ?></td>
              <td><?php echo htmlspecialchars(trim($payment['customer_first_name'] . ' ' . $payment['customer_last_name'])); ?></td>
              <td><?php echo htmlspecialchars($payment['cat_name']); ?></td>
              <td><?php echo htmlspecialchars(number_format($payment['amount'], 2)); ?></td>
              <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
              <td><?php echo htmlspecialchars($payment['status']); ?></td>
              <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
