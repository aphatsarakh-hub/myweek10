<?php
require_once __DIR__ . '/../../includes/boot.php';

// --- เพิ่มส่วนนี้เพื่อเชื่อมต่อฐานข้อมูลใหม่ถ้าใน boot.php ไม่มี $pdo ---
$db_host = 'localhost';
$db_name = 'bambam_cat_hotel';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage());
}
// -------------------------------------------------------------

$pageTitle = 'จัดการข้อมูลแมว';
// ... ต่อด้วยโค้ดเดิมของคุณที่ใช้ $pdo->prepare(...) ได้เลย

// --- เชื่อมต่อฐานข้อมูล ---
try {
    // สมมติว่าใน boot.php มีการประกาศตัวแปร $pdo ไว้แล้ว
    // หากไม่มี ให้ใช้การเชื่อมต่อในนี้ได้เลย (ปรับเปลี่ยนค่าตาม config ของคุณ)
    $stmt = $pdo->prepare("SELECT cat_id, name, breed, age, gender, medical_history, customer_id, photo FROM cat ORDER BY cat_id DESC");
    $stmt->execute();
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage());
    $cats = [];
    $errorMessage = "เกิดข้อผิดพลาดในการโหลดข้อมูล";
}

// รูปสำรองกรณีไม่มีรูปในระบบ
$fallbackPhotos = [
    url('assets/cats/cat_1.png'),
    url('assets/cats/cat_2.png'),
    url('assets/cats/cat_3.png'),
];

include __DIR__ . '/../../includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body { background-color: #FFFFFF; font-family: 'Prompt', sans-serif; color: #4A3B2E; }
    .hotel-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
    .action-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; }
    .search-input { padding: 8px 16px 8px 40px; width: 240px; border: 1px solid #EDE7DF; border-radius: 8px; font-size: 13px; }
    .btn-icon-add { width: 34px; height: 34px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; background: #C05800; color: #FFFFFF; text-decoration: none; }
    .cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
    .cat-card { border: 1px solid #EDE7DF; border-radius: 12px; overflow: hidden; background: #FFFFFF; }
    .img-box { position: relative; width: 100%; height: 190px; background: #FAF8F5; }
    .img-box img { width: 100%; height: 100%; object-fit: cover; }
    .status-badge { position: absolute; top: 12px; left: 12px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 500; backdrop-filter: blur(2px); }
    .status-ready { background-color: rgba(255,255,255,0.9); color: #B26A00; border: 1px solid #F0D9B5; }
    .status-wait { background-color: rgba(255,255,255,0.9); color: #9C4700; border: 1px solid #E8C4A0; }
    .status-adopted { background-color: rgba(255,255,255,0.9); color: #8A5A3B; border: 1px solid #E5D9CB; }
    .card-info-block { padding: 16px; border-top: 1px solid #F3EEE7; }
    .action-buttons-group { display: flex; gap: 8px; margin-top: 12px; }
    .btn-action-circle { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; text-decoration: none; border: 1px solid transparent; }
    .btn-style-edit { background: #FBF3EC; color: #C05800; }
    .btn-style-del { background: #FBEAE5; color: #9C3B1E; }
    .success-banner { background: #F1F8EE; border: 1px solid #CFE6C4; color: #3F7A2E; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; }
</style>

<div class="hotel-container">
    <?php if (isset($_GET['added'])) echo '<div class="success-banner"><i class="fas fa-check-circle"></i> เพิ่มข้อมูลสำเร็จ</div>'; ?>
    <?php if (isset($errorMessage)) echo '<div class="error-banner">'.$errorMessage.'</div>'; ?>

    <div class="action-header">
        <p>รายชื่อน้องแมวทั้งหมด (<?= count($cats) ?> ตัว)</p>
        <div class="flex items-center gap-3">
            <input type="text" placeholder="ค้นหาแมว..." class="search-input" onkeyup="filterCats(this.value)">
            <a href="cat_add.php" class="btn-icon-add"><i class="fas fa-plus"></i></a>
        </div>
    </div>

    <div class="cat-grid">
        <?php foreach ($cats as $i => $c): 
            // กำหนดสถานะ
            if (!empty($c['medical_history'])) { $statusClass = 'status-wait'; $statusText = 'รอดูแล'; }
            elseif (!empty($c['customer_id'])) { $statusClass = 'status-adopted'; $statusText = 'ถูกรับเลี้ยงแล้ว'; }
            else { $statusClass = 'status-ready'; $statusText = 'พร้อมรับเลี้ยง'; }

            $photoUrl = !empty($c['photo']) ? url('uploads/cats/' . $c['photo']) : $fallbackPhotos[$i % count($fallbackPhotos)];
        ?>
        <div class="cat-card">
            <div class="img-box">
                <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                <img src="<?= htmlspecialchars($photoUrl) ?>" alt="<?= htmlspecialchars($c['name']) ?>">
            </div>
            <div class="card-info-block">
                <h3 style="margin:0; font-size:16px;"><?= htmlspecialchars($c['name']) ?></h3>
                <p style="font-size:12px; color:#B08968; margin:5px 0;">
                    <?= htmlspecialchars($c['breed']) ?> • <?= htmlspecialchars($c['age']) ?> เดือน • <?= htmlspecialchars($c['gender']) ?>
                </p>
                <div class="action-buttons-group">
                    <a href="cat_edit.php?id=<?= $c['cat_id'] ?>" class="btn-action-circle btn-style-edit"><i class="fas fa-pencil-alt"></i></a>
                    <a href="cat_delete.php?id=<?= $c['cat_id'] ?>" class="btn-action-circle btn-style-del" onclick="return confirm('ยืนยันการลบ?');"><i class="far fa-trash-alt"></i></a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function filterCats(val) {
    const cards = document.querySelectorAll('.cat-card');
    cards.forEach(card => {
        card.style.display = card.innerText.toLowerCase().includes(val.toLowerCase()) ? '' : 'none';
    });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>