<?php
require_once __DIR__ . '/../includes/boot.php';
$pageTitle = 'รายละเอียดแมว (Customer)';
$cat_id = intval($_GET['id'] ?? 0);
$customer_id = $_SESSION['user']['customer_id'] ?? null;
$cat = fetch_cat_by_id($cat_id);
if (!$cat || $cat['customer_id'] !== $customer_id) {
    flash('message', 'ไม่พบแมวของคุณ');
    header('Location: cat_list.php');
    exit;
}
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>รายละเอียดแมว</h1>
      <p>ข้อมูลแมวของคุณ</p>
    </div>
    <a href="cat_list.php" class="button button-secondary">กลับไปยังรายการแมว</a>
  </div>

  <div class="card">
    <p><strong>ชื่อแมว:</strong> <?php echo htmlspecialchars($cat['name']); ?></p>
    <p><strong>สายพันธุ์:</strong> <?php echo htmlspecialchars($cat['breed']); ?></p>
    <p><strong>อายุ:</strong> <?php echo htmlspecialchars($cat['age']); ?> ปี</p>
    <p><strong>เพศ:</strong> <?php echo htmlspecialchars($cat['gender']); ?></p>
    <p><strong>ประวัติการรักษา / หมายเหตุ:</strong><br><?php echo nl2br(htmlspecialchars($cat['medical_history'])); ?></p>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
