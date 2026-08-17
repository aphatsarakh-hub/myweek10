<?php
require_once __DIR__ . '/../includes/boot.php';
$user = $_SESSION['user'] ?? [];
$pageTitle = 'โปรไฟล์พนักงาน (Cashier)';
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>โปรไฟล์พนักงาน</h1>
      <p>ดูและแก้ไขข้อมูลส่วนตัวของคุณ</p>
    </div>
  </div>

  <div class="card">
    <p><strong>ชื่อผู้ใช้:</strong> <?php echo htmlspecialchars($user['username'] ?? ''); ?></p>
    <p><strong>ชื่อ:</strong> <?php echo htmlspecialchars($user['name'] ?? ''); ?></p>
    <p><strong>บทบาท:</strong> <?php echo htmlspecialchars($user['role'] ?? ''); ?></p>
    <?php if (!empty($user['staff_id'])): ?>
      <p><strong>รหัสพนักงาน:</strong> <?php echo htmlspecialchars($user['staff_id']); ?></p>
    <?php endif; ?>
    <p><strong>อีเมล:</strong> <?php echo htmlspecialchars($user['email'] ?? '-'); ?></p>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
