<?php
require_once __DIR__ . '/../../includes/boot.php';
$pageTitle = 'เพิ่มแมว';
$showPrimaryAction = false;

$errors = [];
$old = [
    'name' => '', 'breed' => '', 'age' => '', 'gender' => '', 'medical_history' => '', 'customer_id' => '',
];

// --- ตั้งค่าการเชื่อมต่อฐานข้อมูล (รูปแบบเดียวกับ cat_list.php) ---
$db_host = 'localhost';
$db_name = 'bambam_cat_hotel';
$db_user = 'root';
$db_pass = '';

try {
    $local_conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $local_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    $local_conn = null;
    $errors[] = 'เชื่อมต่อฐานข้อมูลไม่สำเร็จ: ' . $e->getMessage();
}

// โฟลเดอร์เก็บรูปแมว (โครงสร้างเดียวกับ uploads/avatars/ ที่ใช้อยู่แล้ว)
$uploadDir = __DIR__ . '/../../uploads/cats/';
$allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
$maxFileSize = 4 * 1024 * 1024; // 4MB

// --- ดึงรายการสายพันธุ์ / เพศ ที่มีอยู่จริงในฐานข้อมูล (ไม่ hardcode) ---
$breedOptions = [];
$genderOptions = [];
if ($local_conn) {
    try {
        $breedStmt = $local_conn->query(
            "SELECT DISTINCT breed FROM cat WHERE breed IS NOT NULL AND breed <> '' ORDER BY breed ASC"
        );
        $breedOptions = $breedStmt->fetchAll(PDO::FETCH_COLUMN);

        $genderStmt = $local_conn->query(
            "SELECT DISTINCT gender FROM cat WHERE gender IS NOT NULL AND gender <> '' ORDER BY gender ASC"
        );
        $genderOptions = $genderStmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        // ถ้าดึงไม่สำเร็จ ปล่อยเป็น array ว่าง ฟอร์มจะยังใช้งานได้ปกติ (แค่ไม่มีตัวเลือกให้เลือก)
    }
}
// ถ้าฐานข้อมูลยังไม่มีข้อมูลแมวเลย (array ว่าง) ใส่ค่าเริ่มต้นขั้นต่ำไว้ให้เลือกได้
if (empty($genderOptions)) {
    $genderOptions = ['ตัวผู้', 'ตัวเมีย'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $local_conn) {
    $old['name'] = trim($_POST['name'] ?? '');
    $old['breed'] = trim($_POST['breed'] ?? '');
    $old['age'] = trim($_POST['age'] ?? '');
    $old['gender'] = trim($_POST['gender'] ?? '');
    $old['medical_history'] = trim($_POST['medical_history'] ?? '');
    $old['customer_id'] = trim($_POST['customer_id'] ?? '');

    // --- ตรวจสอบข้อมูล ---
    if ($old['name'] === '') {
        $errors[] = 'กรุณากรอกชื่อแมว';
    }
    if ($old['age'] !== '' && !is_numeric($old['age'])) {
        $errors[] = 'อายุต้องเป็นตัวเลข';
    }

    // --- ตรวจสอบไฟล์รูป (ถ้ามีการอัปโหลด) ---
    $photoFilename = null;
    if (!empty($_FILES['photo']['name'])) {
        $file = $_FILES['photo'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'อัปโหลดรูปไม่สำเร็จ (error code ' . $file['error'] . ')';
        } elseif ($file['size'] > $maxFileSize) {
            $errors[] = 'ไฟล์รูปต้องมีขนาดไม่เกิน 4MB';
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) {
                $errors[] = 'รองรับเฉพาะไฟล์ JPG, JPEG, PNG หรือ WEBP เท่านั้น';
            }
        }
    }

    // --- ถ้าไม่มี error: บันทึกข้อมูลลงฐานข้อมูลก่อน แล้วค่อยจัดการไฟล์รูป ---
    if (empty($errors)) {
        try {
            $stmt = $local_conn->prepare(
                "INSERT INTO cat (name, breed, age, gender, medical_history, customer_id, photo)
                 VALUES (:name, :breed, :age, :gender, :medical_history, :customer_id, NULL)"
            );
            $stmt->execute([
                ':name' => $old['name'],
                ':breed' => $old['breed'] !== '' ? $old['breed'] : null,
                ':age' => $old['age'] !== '' ? $old['age'] : null,
                ':gender' => $old['gender'] !== '' ? $old['gender'] : null,
                ':medical_history' => $old['medical_history'] !== '' ? $old['medical_history'] : null,
                ':customer_id' => $old['customer_id'] !== '' ? $old['customer_id'] : null,
            ]);
            $newCatId = (int)$local_conn->lastInsertId();

            // --- ย้ายไฟล์รูปด้วยชื่อที่อ้างอิง cat_id (แบบเดียวกับ avatar_admin_{timestamp}.ext) ---
            if (!empty($_FILES['photo']['name']) && !empty($ext)) {
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $newFilename = 'cat_' . $newCatId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $newFilename)) {
                    $updateStmt = $local_conn->prepare("UPDATE cat SET photo = :photo WHERE cat_id = :id");
                    $updateStmt->execute([':photo' => $newFilename, ':id' => $newCatId]);
                } else {
                    $errors[] = 'บันทึกข้อมูลแมวสำเร็จ แต่ย้ายไฟล์รูปไม่สำเร็จ กรุณาแก้ไขและอัปโหลดรูปใหม่อีกครั้ง';
                }
            }

            if (empty($errors)) {
                header('Location: cat_list.php?added=1');
                exit;
            }
        } catch (Exception $e) {
            $errors[] = 'บันทึกข้อมูลไม่สำเร็จ: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body { background-color: #FFFFFF; font-family: 'Prompt', sans-serif; color: #4A3B2E; }

    .form-container { max-width: 640px; margin: 0 auto; }

    .form-card {
        border: 1px solid #EDE7DF;
        border-radius: 12px;
        padding: 28px;
        background: #FFFFFF;
    }

    .form-group { margin-bottom: 18px; }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #6B5B4B;
        margin-bottom: 6px;
    }

    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 9px 14px;
        border: 1px solid #EDE7DF;
        border-radius: 8px;
        font-size: 14px;
        font-family: 'Prompt', sans-serif;
        color: #2E2115;
        background: #FFFFFF;
        transition: border-color 0.15s;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: #C05800;
    }

    .form-textarea { resize: vertical; min-height: 80px; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    .photo-upload-box {
        display: flex;
        align-items: center;
        gap: 16px;
        border: 1px dashed #E0B389;
        border-radius: 10px;
        padding: 16px;
        background: #FBF3EC;
    }

    .photo-upload-box .preview-circle {
        width: 56px;
        height: 56px;
        border-radius: 10px;
        background: #FFFFFF;
        border: 1px solid #EDE0D2;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #E0B389;
        font-size: 20px;
        flex-shrink: 0;
        overflow: hidden;
    }
    .photo-upload-box .preview-circle img { width: 100%; height: 100%; object-fit: cover; }

    .photo-upload-box .upload-text { font-size: 12px; color: #B08968; }

    .btn-primary-submit {
        background: #C05800;
        color: #FFFFFF;
        padding: 10px 22px;
        border-radius: 8px;
        border: none;
        font-size: 14px;
        font-weight: 500;
        font-family: 'Prompt', sans-serif;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-primary-submit:hover { background: #9C4700; }

    .btn-cancel-link {
        color: #6B5B4B;
        font-size: 14px;
        text-decoration: none;
        padding: 10px 18px;
    }
    .btn-cancel-link:hover { color: #2E2115; }

    .error-box {
        background: #FBEAE5;
        border: 1px solid #E8B9A8;
        color: #9C3B1E;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 13px;
        margin-bottom: 18px;
    }
    .error-box ul { margin: 4px 0 0 18px; padding: 0; }
</style>

<div class="form-container">
    <div class="mb-6">
        <a href="cat_list.php" class="text-sm text-[#B08968] hover:text-[#C05800] transition-colors">
            <i class="fas fa-arrow-left mr-1.5 text-xs"></i>กลับไปยังรายการแมว
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <strong>กรุณาตรวจสอบข้อมูล:</strong>
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data" novalidate>

            <div class="form-group">
                <label class="form-label">รูปแมว</label>
                <div class="photo-upload-box">
                    <div class="preview-circle" id="photoPreview">
                        <i class="fas fa-paw"></i>
                    </div>
                    <div>
                        <input type="file" name="photo" id="photoInput" accept=".jpg,.jpeg,.png,.webp"
                               onchange="previewPhoto(event)" class="text-xs">
                        <p class="upload-text mt-1">รองรับ JPG, PNG, WEBP ขนาดไม่เกิน 4MB</p>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">ชื่อแมว <span class="text-[#C05800]">*</span></label>
                <input type="text" name="name" class="form-input" required
                       value="<?= htmlspecialchars($old['name']) ?>" placeholder="เช่น มะลิ">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">สายพันธุ์</label>
                    <input type="text" name="breed" class="form-input" list="breedList"
                           value="<?= htmlspecialchars($old['breed']) ?>" placeholder="เลือกหรือพิมพ์สายพันธุ์ใหม่">
                    <datalist id="breedList">
                        <?php foreach ($breedOptions as $b): ?>
                            <option value="<?= htmlspecialchars($b) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group">
                    <label class="form-label">อายุ (เดือน)</label>
                    <input type="number" name="age" class="form-input" min="0"
                           value="<?= htmlspecialchars($old['age']) ?>" placeholder="เช่น 6">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">เพศ</label>
                <select name="gender" class="form-select">
                    <option value="">-- เลือกเพศ --</option>
                    <?php foreach ($genderOptions as $g): ?>
                        <option value="<?= htmlspecialchars($g) ?>" <?= $old['gender'] === $g ? 'selected' : '' ?>><?= htmlspecialchars($g) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">ประวัติทางการแพทย์</label>
                <textarea name="medical_history" class="form-textarea"
                          placeholder="ระบุถ้ามี เช่น แพ้ยา, เคยผ่าตัด"><?= htmlspecialchars($old['medical_history']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">รหัสลูกค้า (ถ้าถูกรับเลี้ยงแล้ว)</label>
                <input type="text" name="customer_id" class="form-input"
                       value="<?= htmlspecialchars($old['customer_id']) ?>" placeholder="เว้นว่างไว้หากยังไม่มีเจ้าของ">
            </div>

            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-[#F3EEE7]">
                <button type="submit" class="btn-primary-submit">
                    <i class="fas fa-check mr-1.5 text-xs"></i>บันทึกข้อมูลแมว
                </button>
                <a href="cat_list.php" class="btn-cancel-link">ยกเลิก</a>
            </div>

        </form>
    </div>
</div>

<script>
function previewPhoto(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('photoPreview').innerHTML =
            '<img src="' + e.target.result + '" alt="preview">';
    };
    reader.readAsDataURL(file);
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>