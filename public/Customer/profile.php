<?php
require_once __DIR__ . '/../includes/boot.php';
$user = $_SESSION['user'] ?? [];
$pageTitle = 'โปรไฟล์ (Customer)';
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>โปรไฟล์</h1>
      <p>ดูและแก้ไขข้อมูลส่วนตัวของคุณ</p>
    </div>
  </div>

  <div class="card">
    <p><strong>ชื่อผู้ใช้:</strong> <?php echo htmlspecialchars($user['username'] ?? ''); ?></p>
    <p><strong>ชื่อ:</strong> <?php echo htmlspecialchars($user['name'] ?? ''); ?></p>
    <p><strong>บทบาท:</strong> <?php echo htmlspecialchars($user['role'] ?? ''); ?></p>
    <p><strong>อีเมล:</strong> <?php echo htmlspecialchars($user['email'] ?? '-'); ?></p>
    <?php if (!empty($user['customer_id'])): ?>
      <p><strong>รหัสลูกค้า:</strong> <?php echo htmlspecialchars($user['customer_id']); ?></p>
    <?php endif; ?>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
