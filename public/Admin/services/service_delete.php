<?php
require_once __DIR__ . '/../../includes/boot.php';
$pageTitle = 'ลบบริการ (Admin)';
$service_id = intval($_GET['id'] ?? 0);
$service = fetch_service_by_id($service_id);
if (!$service) {
    flash('message', 'ไม่พบบริการที่ต้องการลบ');
    header('Location: service_list.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (delete_service($service_id)) {
        flash('message', 'ลบบริการเรียบร้อยแล้ว');
        header('Location: service_list.php');
        exit;
    }
    flash('message', 'ไม่สามารถลบบริการได้');
    header('Location: service_list.php');
    exit;
}
include __DIR__ . '/../../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>ลบบริการ</h1>
      <p>ยืนยันการลบบริการนี้</p>
    </div>
  </div>

  <div class="card">
    <p>คุณแน่ใจหรือไม่ว่าต้องการลบบริการ <strong><?php echo htmlspecialchars($service['service_name']); ?></strong>?</p>
    <form method="post">
      <button type="submit" class="button button-danger">ลบเลย</button>
      <a href="service_list.php" class="button button-secondary">ยกเลิก</a>
    </form>
  </div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
