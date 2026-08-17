<?php
require_once __DIR__ . '/../includes/boot.php';
$pageTitle = 'แก้ไขแมว (Customer)';
$cat_id = intval($_GET['id'] ?? 0);
$customer_id = $_SESSION['user']['customer_id'] ?? null;
$cat = fetch_cat_by_id($cat_id);
if (!$cat || $cat['customer_id'] !== $customer_id) {
    flash('message', 'ไม่พบแมวของคุณ');
    header('Location: cat_list.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $breed = trim($_POST['breed'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $gender = trim($_POST['gender'] ?? '');
    $medical_history = trim($_POST['medical_history'] ?? '');

    if ($name === '' || $breed === '' || $age <= 0 || $gender === '') {
        $error = 'กรุณากรอกข้อมูลแมวให้ครบถ้วน';
    } else {
        if (update_cat($cat_id, $name, $breed, $age, $gender, $medical_history)) {
            flash('message', 'ปรับปรุงข้อมูลแมวเรียบร้อยแล้ว');
            header('Location: cat_list.php');
            exit;
        }
        $error = 'ไม่สามารถบันทึกการแก้ไขได้';
    }
}
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>แก้ไขแมว</h1>
      <p>อัปเดตข้อมูลแมวของคุณ</p>
    </div>
    <a href="cat_list.php" class="button button-secondary">กลับไปยังรายการแมว</a>
  </div>

  <div class="card">
    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
      <label>ชื่อแมว
        <input type="text" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? $cat['name']); ?>" />
      </label>
      <label>สายพันธุ์
        <input type="text" name="breed" required value="<?php echo htmlspecialchars($_POST['breed'] ?? $cat['breed']); ?>" />
      </label>
      <label>อายุ
        <input type="number" name="age" min="1" required value="<?php echo htmlspecialchars($_POST['age'] ?? $cat['age']); ?>" />
      </label>
      <label>เพศ
        <select name="gender" required>
          <option value="">เลือกเพศ</option>
          <option value="ชาย"<?php echo (($_POST['gender'] ?? $cat['gender']) === 'ชาย') ? ' selected' : ''; ?>>ชาย</option>
          <option value="หญิง"<?php echo (($_POST['gender'] ?? $cat['gender']) === 'หญิง') ? ' selected' : ''; ?>>หญิง</option>
        </select>
      </label>
      <label>ประวัติการรักษา / หมายเหตุ
        <textarea name="medical_history"><?php echo htmlspecialchars($_POST['medical_history'] ?? $cat['medical_history']); ?></textarea>
      </label>
      <button type="submit" class="button button-primary">บันทึกการแก้ไข</button>
      <a href="cat_list.php" class="button button-secondary">ยกเลิก</a>
    </form>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
