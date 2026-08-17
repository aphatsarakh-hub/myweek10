<?php
require_once __DIR__ . '/../includes/boot.php';
$pageTitle = 'ลบแมว (Customer)';
$cat_id = intval($_GET['id'] ?? 0);
$customer_id = $_SESSION['user']['customer_id'] ?? null;
$cat = fetch_cat_by_id($cat_id);
if (!$cat || $cat['customer_id'] !== $customer_id) {
    flash('message', 'ไม่พบแมวของคุณ');
    header('Location: cat_list.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (delete_cat($cat_id)) {
        flash('message', 'ลบแมวเรียบร้อยแล้ว');
        header('Location: cat_list.php');
        exit;
    }
    flash('message', 'ไม่สามารถลบแมวได้');
    header('Location: cat_list.php');
    exit;
}
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>ลบแมว</h1>
      <p>ยืนยันการลบแมวนี้</p>
    </div>
    <a href="cat_list.php" class="button button-secondary">กลับไปยังรายการแมว</a>
  </div>

  <div class="card">
    <p>คุณแน่ใจหรือไม่ว่าต้องการลบแมว <strong><?php echo htmlspecialchars($cat['name']); ?></strong>?</p>
    <form method="post">
      <button type="submit" class="button button-danger">ลบแมว</button>
    </form>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
