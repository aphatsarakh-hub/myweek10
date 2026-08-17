<?php
require_once __DIR__ . '/../../includes/boot.php';
$pageTitle = 'จัดการข้อมูลแมว';
$showPrimaryAction = false;

// --- ตั้งค่าการเชื่อมต่อฐานข้อมูล ---
try {
    $db_host = 'localhost';
    $db_name = 'bambam_cat_hotel';
    $db_user = 'root';
    $db_pass = '';

    $local_conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $local_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $local_conn->prepare("SELECT cat_id, name, breed, age, gender, medical_history, customer_id, photo FROM cat ORDER BY cat_id DESC");
    $stmt->execute();
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<div style='background:#FBEAE5; color:#9C3B1E; padding:15px; margin:10px; border-radius:8px; font-family:sans-serif;'>";
    echo "<strong>⚠️ เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
    $cats = [];
}

// --- โค้ดจำลองข้อมูลสำหรับทดสอบหน้าตา UI ---
if (empty($cats)) {
    $cats = [
        ['cat_id' => 1, 'name' => 'มะลิ', 'breed' => 'ไทย', 'age' => 2, 'gender' => 'ตัวเมีย', 'medical_history' => '', 'customer_id' => '', 'photo' => ''],
        ['cat_id' => 2, 'name' => 'ส้มโอ', 'breed' => 'วิเชียรมาศ', 'age' => 3, 'gender' => 'ตัวผู้', 'medical_history' => '', 'customer_id' => '', 'photo' => ''],
        ['cat_id' => 3, 'name' => 'ข้าวปั้น', 'breed' => 'ไทย', 'age' => 1, 'gender' => 'ตัวเมีย', 'medical_history' => 'มีประวัติ', 'customer_id' => '', 'photo' => ''],
        ['cat_id' => 4, 'name' => 'โอเลี้ยง', 'breed' => 'ไทย', 'age' => 4, 'gender' => 'ตัวผู้', 'medical_history' => '', 'customer_id' => '', 'photo' => '']
    ];
}
// -----------------------------------------------------------

// รูปสำรอง กรณีแมวตัวนั้นยังไม่มีการอัปโหลดรูปในระบบ (uploads/cats/ เก็บรูปจริงของแต่ละตัว)
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
    body {
        background-color: #FFFFFF;
        font-family: 'Prompt', sans-serif;
        color: #4A3B2E;
    }

    .hotel-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .action-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 28px;
    }

    .action-header p {
        font-size: 13px;
        color: #B08968;
        margin: 0;
    }

    .search-input {
        padding: 8px 16px 8px 40px;
        width: 240px;
        background: #FFFFFF;
        border: 1px solid #EDE7DF;
        border-radius: 8px;
        font-size: 13px;
        transition: border-color 0.15s;
    }
    .search-input:focus {
        outline: none;
        border-color: #C05800;
    }

    .btn-icon-add {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #C05800;
        color: #FFFFFF;
        text-decoration: none;
        font-size: 13px;
        transition: background 0.15s;
    }
    .btn-icon-add:hover { background: #9C4700; }

    /* --- การ์ดแมว: เรียบ ไม่มี gradient / shadow หนัก --- */
    .cat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }

    .cat-card {
        border: 1px solid #EDE7DF;
        border-radius: 12px;
        overflow: hidden;
        background: #FFFFFF;
        transition: border-color 0.15s;
    }
    .cat-card:hover {
        border-color: #E0B389;
    }

    .img-box {
        position: relative;
        width: 100%;
        height: 190px;
        background: #FAF8F5;
    }

    .img-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* ป้ายสถานะ: พาสเทลอ่อน โทนขาว-ส้ม-น้ำตาล */
    .status-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
        z-index: 5;
        backdrop-filter: blur(2px);
    }
    .status-ready   { background-color: rgba(255,255,255,0.9); color: #B26A00; border: 1px solid #F0D9B5; }
    .status-wait    { background-color: rgba(255,255,255,0.9); color: #9C4700; border: 1px solid #E8C4A0; }
    .status-adopted { background-color: rgba(255,255,255,0.9); color: #8A5A3B; border: 1px solid #E5D9CB; }

    .card-info-block {
        padding: 16px 18px 18px;
        border-top: 1px solid #F3EEE7;
    }

    .cat-name-title {
        font-size: 16px;
        font-weight: 600;
        color: #2E2115;
        margin: 0 0 3px 0;
    }

    .cat-sub-details {
        font-size: 12px;
        color: #B08968;
        margin: 0 0 14px 0;
    }

    .action-buttons-group {
        display: flex;
        gap: 8px;
        justify-content: flex-start;
    }

    .btn-action-circle {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        text-decoration: none;
        border: 1px solid transparent;
        transition: border-color 0.15s, background-color 0.15s;
    }

    .btn-style-edit { background: #FBF3EC; color: #C05800; }
    .btn-style-edit:hover { border-color: #E0B389; }

    .btn-style-add  { background: #FAF8F5; color: #8A5A3B; }
    .btn-style-add:hover { border-color: #E5D9CB; }

    .btn-style-del  { background: #FBEAE5; color: #9C3B1E; }
    .btn-style-del:hover { border-color: #E8B9A8; }

    .summary-note { display: none !important; }
</style>

<div class="hotel-container">

    <div class="action-header">
        <p>รายชื่อน้องแมวทั้งหมด (<?= count($cats) ?> ตัว)</p>
        <div class="flex items-center gap-3">
            <div class="relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-[#B08968] text-xs"></i>
                <input type="text" data-admin-search-input data-admin-search=".cat-card" placeholder="ค้นหาแมว..." class="search-input">
            </div>
            <a href="cat_add.php" class="btn-icon-add" title="เพิ่มแมว">
                <i class="fas fa-plus"></i>
            </a>
        </div>
    </div>

    <div class="cat-grid">
        <?php foreach ($cats as $i => $c):
            if (!empty($c['medical_history'])) {
                $statusClass = 'status-wait'; $statusText = 'รอดูแล';
            } elseif (!empty($c['customer_id'])) {
                $statusClass = 'status-adopted'; $statusText = 'ถูกรับเลี้ยงแล้ว';
            } else {
                $statusClass = 'status-ready'; $statusText = 'พร้อมรับเลี้ยง';
            }

            $genderText = '-';
            $g = strtolower($c['gender'] ?? '');
            if (in_array($g, ['male', 'm', 'ชาย', 'ตัวผู้'])) $genderText = 'เพศผู้';
            elseif (in_array($g, ['female', 'f', 'หญิง', 'ตัวเมีย'])) $genderText = 'เพศเมีย';
            else $genderText = htmlspecialchars($c['gender']);

            $breed = !empty($c['breed']) ? htmlspecialchars($c['breed']) : 'ไทย';
            $age = !empty($c['age']) ? htmlspecialchars($c['age']) . ' เดือน' : '-';

            // ใช้รูปจริงที่อัปโหลดไว้ในระบบก่อนเสมอ ถ้าไม่มีค่อย fallback ไปรูปตัวอย่างแบบวนลูป (ไม่สุ่มใหม่ทุกครั้งที่โหลด)
            $photoUrl = !empty($c['photo'])
                ? url('uploads/cats/' . $c['photo'])
                : $fallbackPhotos[$i % count($fallbackPhotos)];
        ?>

        <div class="cat-card admin-search-item" data-searchable="<?= htmlspecialchars(($c['name'] ?? '') . ' ' . ($c['breed'] ?? '') . ' ' . ($c['gender'] ?? '')) ?>">
            <div class="img-box">
                <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                <img src="<?= htmlspecialchars($photoUrl) ?>" alt="รูปน้อง <?= htmlspecialchars($c['name']) ?>" loading="lazy">
            </div>

            <div class="card-info-block">
                <h3 class="cat-name-title"><?= htmlspecialchars($c['name']) ?></h3>
                <p class="cat-sub-details"><?= $breed ?> • <?= $age ?> • <?= $genderText ?></p>

                <div class="action-buttons-group">
                    <a href="cat_edit.php?id=<?= urlencode($c['cat_id']) ?>" class="btn-action-circle btn-style-edit" title="แก้ไข">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                    <a href="cat_add.php?id=<?= urlencode($c['cat_id']) ?>" class="btn-action-circle btn-style-add" title="เพิ่มข้อมูลสุขภาพ">
                        <i class="fas fa-plus"></i>
                    </a>
                    <a href="cat_delete.php?id=<?= urlencode($c['cat_id']) ?>" class="btn-action-circle btn-style-del" title="ลบ" onclick="return confirm('ลบข้อมูลแมวตัวนี้ใช่ไหม?');">
                        <i class="far fa-trash-alt"></i>
                    </a>
                </div>
            </div>
        </div>

        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>