<?php
require_once __DIR__ . '/../includes/boot.php';
$pageTitle = 'เพิ่มแมว (Customer)';
$customer_id = $_SESSION['user']['customer_id'] ?? null;
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
        $cat_id = insert_cat($name, $breed, $age, $gender, $medical_history, $customer_id);
        if ($cat_id) {
            flash('message', 'เพิ่มแมวเรียบร้อยแล้ว');
            header('Location: cat_list.php');
            exit;
        }
        $error = 'ไม่สามารถเพิ่มแมวได้';
    }
}
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>เพิ่มแมว</h1>
      <p>กรอกข้อมูลแมวของคุณเพื่อลงทะเบียน</p>
    </div>
  </div>

  <div class="card">
    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
      <label>ชื่อแมว
        <input type="text" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" />
      </label>
      <label>สายพันธุ์
        <input type="text" name="breed" required value="<?php echo htmlspecialchars($_POST['breed'] ?? ''); ?>" />
      </label>
      <label>อายุ
        <input type="number" name="age" min="1" required value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>" />
      </label>
      <label>เพศ
        <select name="gender" required>
          <option value="">เลือกเพศ</option>
          <option value="ชาย"<?php echo (($_POST['gender'] ?? '') === 'ชาย') ? ' selected' : ''; ?>>ชาย</option>
          <option value="หญิง"<?php echo (($_POST['gender'] ?? '') === 'หญิง') ? ' selected' : ''; ?>>หญิง</option>
        </select>
      </label>
      <label>ประวัติการรักษา / หมายเหตุ
        <textarea name="medical_history"><?php echo htmlspecialchars($_POST['medical_history'] ?? ''); ?></textarea>
      </label>
      <button type="submit" class="button button-primary">เพิ่มแมว</button>
      <a href="cat_list.php" class="button button-secondary">ยกเลิก</a>
    </form>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
