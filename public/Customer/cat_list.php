<?php
require_once __DIR__ . '/../includes/boot.php';
$pageTitle = 'รายการแมว (Customer)';
$customer_id = $_SESSION['user']['customer_id'] ?? null;
$cats = fetch_cats_by_customer($customer_id);
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>รายการแมว</h1>
      <p>จัดการข้อมูลแมวของคุณ</p>
    </div>
    <a href="cat_add.php" class="button button-primary">เพิ่มแมว</a>
  </div>

  <div class="card table-responsive">
    <?php if (empty($cats)): ?>
      <p>ยังไม่มีแมวในระบบ</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>ชื่อ</th>
            <th>สายพันธุ์</th>
            <th>อายุ</th>
            <th>เพศ</th>
            <th>จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cats as $cat): ?>
            <tr>
              <td><?php echo htmlspecialchars($cat['cat_id']); ?></td>
              <td><?php echo htmlspecialchars($cat['name']); ?></td>
              <td><?php echo htmlspecialchars($cat['breed']); ?></td>
              <td><?php echo htmlspecialchars($cat['age']); ?></td>
              <td><?php echo htmlspecialchars($cat['gender']); ?></td>
              <td>
                <div class="button-group">
                  <a href="cat_detail.php?id=<?php echo $cat['cat_id']; ?>" class="button button-secondary">ดู</a>
                  <a href="cat_edit.php?id=<?php echo $cat['cat_id']; ?>" class="button button-secondary">แก้ไข</a>
                  <a href="cat_delete.php?id=<?php echo $cat['cat_id']; ?>" class="button button-danger">ลบ</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
