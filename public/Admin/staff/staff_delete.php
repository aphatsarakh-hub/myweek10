<?php
require_once __DIR__ . '/../../includes/boot.php';

// ตรวจสอบ ID
$staff_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($staff_id <= 0) {
    header("Location: staff_list.php");
    exit;
}

// ดึงชื่อพนักงานมาแสดง
$stmt = $conn->prepare("SELECT first_name, last_name FROM staff WHERE staff_id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$staff = $stmt->get_result()->fetch_assoc();

if (!$staff) {
    die("ไม่พบพนักงานคนนี้");
}

// สร้าง CSRF Token เพื่อความปลอดภัย
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'ลบพนักงาน';
include __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-2xl mx-auto mt-10">
    <div class="bambam-card p-8">
        <h1 class="text-2xl font-bold mb-4 text-red-600">ยืนยันการลบพนักงาน</h1>
        <p class="mb-4">คุณต้องการลบข้อมูลของ: <strong><?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?></strong> ใช่หรือไม่?</p>
        <div class="alert alert-warning mb-6">
            คำเตือน: การกระทำนี้ไม่สามารถย้อนกลับได้ ข้อมูลจะถูกลบถาวร
        </div>

        <form action="staff_delete_process.php" method="POST">
            <input type="hidden" name="id" value="<?= $staff_id ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="flex gap-4">
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">ยืนยันการลบ</button>
                <a href="staff_list.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>