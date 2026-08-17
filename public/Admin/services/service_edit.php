<?php
require_once __DIR__ . '/../../includes/boot.php';
$pageTitle = 'แก้ไขบริการ (Admin)';
$service_id = intval($_GET['id'] ?? 0);
$service = fetch_service_by_id($service_id);
if (!$service) {
    flash('message', 'ไม่พบบริการที่ต้องการแก้ไข');
    header('Location: service_list.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_name = trim($_POST['service_name'] ?? '');
    $service_detail = trim($_POST['service_detail'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $status = trim($_POST['status'] ?? 'Active');

    if ($service_name === '' || $price <= 0) {
        $error = 'กรุณากรอกชื่อบริการและราคาที่ถูกต้อง';
    } else {
        if (update_service($service_id, $service_name, $service_detail, $price, $status)) {
            flash('message', 'ปรับปรุงข้อมูลบริการเรียบร้อยแล้ว');
            header('Location: service_list.php');
            exit;
        }
        $error = 'ไม่สามารถบันทึกการแก้ไขได้';
    }
}
include __DIR__ . '/../../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>แก้ไขบริการ</h1>
      <p>ปรับข้อมูลบริการเสริม</p>
    </div>
  </div>

  <div class="card">
    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
      <label>ชื่อบริการ
        <input type="text" name="service_name" required value="<?php echo htmlspecialchars($_POST['service_name'] ?? $service['service_name']); ?>" />
      </label>
      <label>รายละเอียด
        <textarea name="service_detail"><?php echo htmlspecialchars($_POST['service_detail'] ?? $service['service_detail']); ?></textarea>
      </label>
      <label>ราคา
        <input type="number" name="price" step="0.01" min="0" required value="<?php echo htmlspecialchars($_POST['price'] ?? $service['price']); ?>" />
      </label>
      <label>สถานะ
        <select name="status">
          <option value="Active"<?php echo (($_POST['status'] ?? $service['status']) === 'Active') ? ' selected' : ''; ?>>Active</option>
          <option value="Inactive"<?php echo (($_POST['status'] ?? $service['status']) === 'Inactive') ? ' selected' : ''; ?>>Inactive</option>
        </select>
      </label>
      <button type="submit" class="button button-primary">บันทึกการเปลี่ยนแปลง</button>
      <a href="service_list.php" class="button button-secondary">ยกเลิก</a>
    </form>
  </div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
