<?php
require_once __DIR__ . '/../../includes/boot.php';

// --- จุดแก้ไข: สร้างการเชื่อมต่อฐานข้อมูลที่นี่ ถ้าใน boot.php ไม่มี ---
if (!isset($conn)) {
    $servername = "localhost";
    $username_db = "root";
    $password_db = "";
    $dbname = "bambam_cat_hotel";
    $conn = new mysqli($servername, $username_db, $password_db, $dbname);
    if ($conn->connect_error) {
        die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
}
// -------------------------------------------------------------

$pageTitle = 'ลบพนักงาน (Admin)';
include __DIR__ . '/../../includes/header.php';

// ตรวจสอบ ID
$staff_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ดึงชื่อพนักงานมาแสดง
$stmt = $conn->prepare("SELECT first_name, last_name FROM staff WHERE staff_id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$staff = $stmt->get_result()->fetch_assoc();
?>

<div class="max-w-2xl mx-auto mt-10">
    <div class="bg-white p-8 rounded-lg shadow-md border border-gray-200">
        <h1 class="text-2xl font-bold mb-4 text-red-600">ยืนยันการลบพนักงาน</h1>
        
        <?php if ($staff): ?>
            <p class="mb-4">คุณต้องการลบข้อมูลของ: <strong><?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?></strong> ใช่หรือไม่?</p>
            <div class="bg-yellow-50 p-4 rounded-lg mb-6 text-yellow-800 border border-yellow-200">
                คำเตือน: การกระทำนี้ไม่สามารถย้อนกลับได้ ข้อมูลจะถูกลบถาวร
            </div>

            <form action="staff_delete_process.php" method="POST">
                <input type="hidden" name="id" value="<?= $staff_id ?>">
                <div class="flex gap-4">
                    <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">ยืนยันการลบ</button>
                    <a href="staff_list.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">ยกเลิก</a>
                </div>
            </form>
        <?php else: ?>
            <p>ไม่พบข้อมูลพนักงาน</p>
            <a href="staff_list.php" class="text-blue-600 hover:underline">กลับไปยังรายการพนักงาน</a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>