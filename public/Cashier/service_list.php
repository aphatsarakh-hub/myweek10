<?php
require_once __DIR__ . '/../includes/boot.php';
$pageTitle = 'รายการบริการ (Cashier)';
$services = fetch_services();
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>รายการบริการ</h1>
      <p>บริการที่ลูกค้าสามารถเลือกใช้</p>
    </div>
  </div>

  <div class="card table-responsive">
    <?php if (empty($services)): ?>
      <p>ยังไม่มีบริการในระบบ</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>บริการ</th>
            <th>รายละเอียด</th>
            <th>ราคา</th>
            <th>สถานะ</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($services as $service): ?>
            <tr>
              <td><?php echo htmlspecialchars($service['service_id']); ?></td>
              <td><?php echo htmlspecialchars($service['service_name']); ?></td>
              <td><?php echo htmlspecialchars($service['service_detail']); ?></td>
              <td><?php echo htmlspecialchars(number_format($service['price'], 2)); ?></td>
              <td><?php echo htmlspecialchars($service['status']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
