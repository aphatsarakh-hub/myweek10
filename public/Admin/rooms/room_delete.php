<?php
require_once __DIR__ . '/../../includes/boot.php';
$pageTitle = 'ลบห้อง (Admin)';
$roomtype_id = intval($_GET['id'] ?? 0);
$room = fetch_roomtype_by_id($roomtype_id);
if (!$room) {
    flash('message', 'ไม่พบประเภทห้องพักที่ต้องการลบ');
    header('Location: room_list.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (delete_roomtype($roomtype_id)) {
        flash('message', 'ลบประเภทห้องพักเรียบร้อยแล้ว');
        header('Location: room_list.php');
        exit;
    }
    flash('message', 'ไม่สามารถลบประเภทห้องพักได้');
    header('Location: room_list.php');
    exit;
}
include __DIR__ . '/../../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>ลบประเภทห้องพัก</h1>
      <p>ยืนยันการลบประเภทห้องพักนี้</p>
    </div>
  </div>

  <div class="card">
    <p>คุณแน่ใจหรือไม่ว่าต้องการลบประเภทห้องพัก <strong><?php echo htmlspecialchars($room['type_name']); ?></strong>?</p>
    <form method="post">
      <button type="submit" class="button button-danger">ลบเลย</button>
      <a href="room_list.php" class="button button-secondary">ยกเลิก</a>
    </form>
  </div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
