<?php
require_once __DIR__ . '/../../includes/boot.php';
$pageTitle = 'ลบแมว (Admin)';

$catId = (int)($_GET['id'] ?? 0);
$errorMessage = '';

// --- ตั้งค่าการเชื่อมต่อฐานข้อมูล (รูปแบบเดียวกับ cat_list.php / cat_add.php / cat_edit.php) ---
$db_host = 'localhost';
$db_name = 'bambam_cat_hotel';
$db_user = 'root';
$db_pass = '';

$uploadDir = __DIR__ . '/../../uploads/cats/';

if ($catId <= 0) {
    header('Location: cat_list.php');
    exit;
}

try {
    $local_conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $local_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- ดึงข้อมูลแมวก่อน เพื่อเอาชื่อไฟล์รูปมาลบด้วย ---
    $stmt = $local_conn->prepare("SELECT photo FROM cat WHERE cat_id = :id");
    $stmt->execute([':id' => $catId]);
    $cat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cat) {
        // ไม่พบแมวตัวนี้ในระบบ (อาจถูกลบไปแล้ว) กลับไปหน้ารายการเฉยๆ
        header('Location: cat_list.php');
        exit;
    }

    // --- ลบข้อมูลในฐานข้อมูล ---
    $deleteStmt = $local_conn->prepare("DELETE FROM cat WHERE cat_id = :id");
    $deleteStmt->execute([':id' => $catId]);

    // --- ลบไฟล์รูปที่เกี่ยวข้องออกจากเซิร์ฟเวอร์ (ถ้ามี) ---
    if (!empty($cat['photo']) && file_exists($uploadDir . $cat['photo'])) {
        @unlink($uploadDir . $cat['photo']);
    }

    header('Location: cat_list.php?deleted=1');
    exit;

} catch (Exception $e) {
    $errorMessage = $e->getMessage();
}

include __DIR__ . '/../../includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body { background-color: #FFFFFF; font-family: 'Prompt', sans-serif; color: #4A3B2E; }

    .error-container { max-width: 480px; margin: 60px auto 0; text-align: center; }

    .error-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #FBEAE5;
        color: #9C3B1E;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin: 0 auto 18px;
    }

    .error-title { font-size: 17px; font-weight: 600; color: #2E2115; margin-bottom: 8px; }

    .error-detail {
        font-size: 13px;
        color: #9C3B1E;
        background: #FBEAE5;
        border: 1px solid #E8B9A8;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 20px;
        text-align: left;
    }

    .btn-back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #C05800;
        color: #FFFFFF;
        padding: 9px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        transition: background 0.15s;
    }
    .btn-back-link:hover { background: #9C4700; }
</style>

<div class="error-container">
    <div class="error-icon"><i class="fas fa-exclamation"></i></div>
    <p class="error-title">ลบข้อมูลแมวไม่สำเร็จ</p>
    <p class="error-detail"><?= htmlspecialchars($errorMessage) ?></p>
    <a href="cat_list.php" class="btn-back-link">
        <i class="fas fa-arrow-left text-xs"></i> กลับไปยังรายการแมว
    </a>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>