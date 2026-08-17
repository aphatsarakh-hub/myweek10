<?php
require_once __DIR__ . '/../../includes/boot.php';

// --- ตั้งค่าการเชื่อมต่อฐานข้อมูล ---
$db_host = 'localhost';
$db_name = 'bambam_cat_hotel';
$db_user = 'root';
$db_pass = '';

try {
    $local_conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $local_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage());
}

// ตรวจสอบรหัสพนักงาน
$staff_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';
$success = '';

if ($staff_id <= 0) {
    header("Location: staff_list.php");
    exit;
}

// ดึงข้อมูลพนักงานปัจจุบันมาก่อนเพื่อใช้ตรวจสอบรูปเดิม
try {
    $stmt = $local_conn->prepare("SELECT * FROM staff WHERE staff_id = :staff_id");
    $stmt->execute([':staff_id' => $staff_id]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$staff) {
        header("Location: staff_list.php");
        exit;
    }
} catch (Exception $e) {
    die("ดึงข้อมูลพนักงานล้มเหลว: " . $e->getMessage());
}

// ดึงบัญชีผู้ใช้ (account) ที่ผูกกับพนักงานคนนี้ ถ้ามี
// รูปโปรไฟล์พนักงานเก็บอยู่ที่ account.profile_image ไม่ใช่ staff.image อีกต่อไป
try {
    $stmt = $local_conn->prepare("SELECT account_id, profile_image FROM account WHERE staff_id = :staff_id LIMIT 1");
    $stmt->execute([':staff_id' => $staff_id]);
    $linkedAccount = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $linkedAccount = null;
}


// --- 1. ประมวลผลเมื่อผู้ใช้กดปุ่ม "บันทึกการเปลี่ยนแปลง" (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $position   = trim($_POST['position'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $status     = trim($_POST['status'] ?? 'ทำงาน');

    if (empty($first_name) || empty($position)) {
        $error = 'กรุณากรอกชื่อจริงและตำแหน่งงานพนักงาน';
    } else {

        // --- ระบบอัปโหลดรูปภาพ: เขียนเข้า account.profile_image ของบัญชีที่ผูกกับพนักงานคนนี้ ---
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            if (!$linkedAccount) {
                $error = 'พนักงานคนนี้ยังไม่มีบัญชีผู้ใช้งานในระบบ จึงยังอัปโหลดรูปโปรไฟล์ให้ไม่ได้';
            } else {
                $file_tmp  = $_FILES['image_file']['tmp_name'];
                $file_name = $_FILES['image_file']['name'];
                $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($file_ext, $allowed_exts)) {
                    // ใช้โฟลเดอร์เดียวกับที่ profile.php ใช้ (root/uploads/avatars/) เพื่อให้ path สอดคล้องกันทั้งระบบ
                    $upload_dir = __DIR__ . '/../../uploads/avatars/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    $new_file_name = 'avatar_staff_' . uniqid() . '_' . time() . '.' . $file_ext;
                    $target_file   = $upload_dir . $new_file_name;

                    if (move_uploaded_file($file_tmp, $target_file)) {
                        // ลบไฟล์รูปเก่าออก (ถ้ามี และไม่ใช่ลิงก์เว็บภายนอก)
                        if (!empty($linkedAccount['profile_image']) && strpos($linkedAccount['profile_image'], 'http') === false) {
                            $old_file_path = __DIR__ . '/../../' . ltrim($linkedAccount['profile_image'], '/');
                            if (file_exists($old_file_path)) {
                                @unlink($old_file_path);
                            }
                        }

                        $new_profile_image = '/uploads/avatars/' . $new_file_name;
                        $updImg = $local_conn->prepare("UPDATE account SET profile_image = :img WHERE account_id = :aid");
                        $updImg->execute([
                            ':img' => $new_profile_image,
                            ':aid' => $linkedAccount['account_id']
                        ]);
                        $linkedAccount['profile_image'] = $new_profile_image;
                    } else {
                        $error = 'เกิดข้อผิดพลาดในการย้ายไฟล์ไปยังโฟลเดอร์ปลายทาง';
                    }
                } else {
                    $error = 'รองรับเฉพาะไฟล์รูปภาพเท่านั้น (jpg, jpeg, png, gif, webp)';
                }
            }
        }

        // --- บันทึกลงฐานข้อมูลจริง หากไม่มี Error ค้างอยู่ ---
        // หมายเหตุ: ข้อมูลตัวหนังสือ (ชื่อ, ตำแหน่ง ฯลฯ) ยังคงบันทึกที่ตาราง staff เหมือนเดิม
        // มีแค่รูปโปรไฟล์เท่านั้นที่ย้ายไปเก็บที่ account.profile_image
        if (empty($error)) {
            try {
                $sql = "UPDATE staff SET 
                            first_name = :first_name, 
                            last_name = :last_name, 
                            position = :position, 
                            phone = :phone, 
                            email = :email, 
                            status = :status 
                        WHERE staff_id = :staff_id";
                
                $stmt = $local_conn->prepare($sql);
                $stmt->execute([
                    ':first_name' => $first_name,
                    ':last_name'  => $last_name,
                    ':position'   => $position,
                    ':phone'      => $phone,
                    ':email'      => $email,
                    ':status'     => $status,
                    ':staff_id'   => $staff_id
                ]);
                
                $success = 'อัปเดตข้อมูลพนักงานเรียบร้อยแล้ว!';
                
                // ดึงข้อมูลใหม่มาแสดงในฟอร์มทันทีหลังบันทึกเสร็จ
                $stmt = $local_conn->prepare("SELECT * FROM staff WHERE staff_id = :staff_id");
                $stmt->execute([':staff_id' => $staff_id]);
                $staff = $stmt->fetch(PDO::FETCH_ASSOC);
                
            } catch (Exception $e) {
                $error = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'แก้ไขข้อมูลพนักงาน (Admin)';
$showPrimaryAction = false;
include __DIR__ . '/../../includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body {
        background-color: #FDFBF7; 
        font-family: 'Noto Sans Thai', sans-serif;
        color: #3D2C1D;
    }

    .form-container {
        max-width: 750px;
        margin: 40px auto;
        background: #FFFFFF;
        border-radius: 24px;
        padding: 40px;
        border: 1px solid #F5EBE6;
        box-shadow: 0 10px 30px rgba(61, 44, 29, 0.04);
    }

    .form-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 32px;
        border-bottom: 1px dashed #EADBCE;
        padding-bottom: 20px;
    }

    .form-header h2 {
        font-size: 22px;
        font-weight: 700;
        color: #3D2C1D;
        margin: 0;
    }

    .btn-back-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #FDFBF7;
        border: 1px solid #EADBCE;
        color: #6B5B52;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-back-circle:hover {
        background-color: #FFF1E6;
        color: #E86A33;
        border-color: #FCE5D3;
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #5C4033;
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border-radius: 12px;
        border: 1px solid #EADBCE;
        background-color: #FDFBF7;
        font-size: 14px;
        font-family: inherit;
        color: #3D2C1D;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: #E86A33;
        background-color: #FFFFFF;
        box-shadow: 0 0 0 3px rgba(232, 106, 51, 0.1);
    }

    .alert {
        padding: 14px 16px;
        border-radius: 12px;
        font-size: 14px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }
    .alert-success { background-color: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9; }
    .alert-danger { background-color: #FEECEB; color: #C5221F; border: 1px solid #FAD2CF; }

    /* --- ส่วนอัปโหลดรูปภาพใหม่ --- */
    .image-preview-wrapper {
        display: flex;
        gap: 20px;
        align-items: center;
        background: #FDFBF7;
        padding: 20px;
        border-radius: 16px;
        border: 1px solid #F5EBE6;
    }

    .avatar-preview {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #FFFFFF;
        box-shadow: 0 4px 12px rgba(61, 44, 29, 0.08);
        background-color: #FFF1E6;
        flex-shrink: 0;
    }

    /* ตกแต่งปุ่มเลือกไฟล์ให้สวยงามกลืนกับธีม */
    .file-input-custom {
        display: block;
        width: 100%;
        font-size: 13px;
        color: #6B5B52;
    }
    .file-input-custom::file-selector-button {
        background: #FFF1E6;
        border: 1px solid #FCE5D3;
        color: #E86A33;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        margin-right: 12px;
        transition: all 0.2s;
    }
    .file-input-custom::file-selector-button:hover {
        background: #E86A33;
        color: #FFFFFF;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 32px;
        border-top: 1px dashed #EADBCE;
        padding-top: 24px;
    }

    .btn-cancel {
        color: #8C7A6B;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
        transition: color 0.2s;
    }
    .btn-cancel:hover { color: #E86A33; }

    .btn-submit {
        background: linear-gradient(135deg, #E86A33 0%, #D9531E 100%);
        color: #FFFFFF;
        padding: 14px 32px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 6px 16px rgba(232, 106, 51, 0.25);
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(232, 106, 51, 0.35);
        background: linear-gradient(135deg, #F07843 0%, #E86A33 100%);
    }

    @media (max-width: 600px) {
        .form-grid-2 { grid-template-columns: 1fr; gap: 0; }
        .form-container { padding: 24px; }
        .image-preview-wrapper { flex-direction: column; text-align: center; }
    }
</style>

<div class="form-container">
    
    <div class="form-header">
        <a href="staff_list.php" class="btn-back-circle" title="ย้อนกลับ">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h2>แก้ไขข้อมูลพนักงาน (ระบบอัปโหลดรูป)</h2>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        
        <div class="form-grid-2">
            <div class="form-group">
                <label for="first_name">ชื่อจริง <span style="color: #C5221F;">*</span></label>
                <input type="text" id="first_name" name="first_name" class="form-control" 
                       value="<?= htmlspecialchars($staff['first_name'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="last_name">นามสกุล</label>
                <input type="text" id="last_name" name="last_name" class="form-control" 
                       value="<?= htmlspecialchars($staff['last_name'] ?? '') ?>">
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="position">ตำแหน่งงาน <span style="color: #C5221F;">*</span></label>
                <input type="text" id="position" name="position" class="form-control" 
                       value="<?= htmlspecialchars($staff['position'] ?? '') ?>" placeholder="เช่น ผู้จัดการ, สัตวแพทย์" required>
            </div>
            
            <div class="form-group">
                <label for="status">สถานะการทำงาน</label>
                <select id="status" name="status" class="form-control">
                    <option value="ทำงาน" <?= ($staff['status'] ?? '') === 'ทำงาน' ? 'selected' : '' ?>>ปฏิบัติงาน (ทำงาน)</option>
                    <option value="พักงาน" <?= ($staff['status'] ?? '') === 'พักงาน' ? 'selected' : '' ?>>พักงาน / ไม่พร้อมปฏิบัติงาน</option>
                </select>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="phone">เบอร์โทรศัพท์ติดต่อ</label>
                <input type="text" id="phone" name="phone" class="form-control" 
                       value="<?= htmlspecialchars($staff['phone'] ?? '') ?>" placeholder="เช่น 089-xxxxxxx">
            </div>
            
            <div class="form-group">
                <label for="email">อีเมลพนักงาน</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?= htmlspecialchars($staff['email'] ?? '') ?>" placeholder="example@bambam.com">
            </div>
        </div>

        <div class="form-group">
            <label>รูปภาพโปรไฟล์พนักงาน</label>
            <div class="image-preview-wrapper">
                <?php
                $fallback = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&auto=format&fit=crop&q=80';
                // รูปโปรไฟล์อ่านจาก account.profile_image ของบัญชีที่ผูกกับพนักงานคนนี้
                $current_img = (!empty($linkedAccount['profile_image']))
                    ? '../../' . ltrim($linkedAccount['profile_image'], '/')
                    : $fallback;
                ?>

                <img src="<?= htmlspecialchars($current_img) ?>" class="avatar-preview" id="preview-img" onerror="this.src='<?= $fallback ?>';">

                <div style="flex: 1; width: 100%;">
                    <?php if ($linkedAccount): ?>
                        <input type="file" id="image_file" name="image_file" class="file-input-custom" accept="image/*">
                        <small style="color: #8C7A6B; font-size: 12px; display: block; margin-top: 6px; line-height: 1.5;">
                            <i class="fas fa-info-circle"></i> เลือกไฟล์รูปภาพจากเครื่องของคุณ (เช่น .jpg, .png)<br>
                            ระบบจะแสดงตัวอย่างรูปภาพให้เห็นด้านซ้ายทันทีเมื่อเลือกไฟล์เรียบร้อย
                        </small>
                    <?php else: ?>
                        <small style="color: #C5221F; font-size: 12px; display: block; line-height: 1.5;">
                            <i class="fas fa-exclamation-triangle"></i> พนักงานคนนี้ยังไม่มีบัญชีผู้ใช้งานในระบบ จึงยังอัปโหลดรูปโปรไฟล์ให้ไม่ได้<br>
                            ต้องสร้างบัญชี (account) ให้พนักงานคนนี้ก่อน จึงจะตั้งรูปได้
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="staff_list.php" class="btn-cancel">ยกเลิกการแก้ไข</a>
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> บันทึกการเปลี่ยนแปลง
            </button>
        </div>

    </form>

</div>

<script>
// ระบบ Live Preview เมื่อเลือกไฟล์ภาพจากเครื่อง (แสดงเฉพาะกรณีที่พนักงานมีบัญชีผู้ใช้งานแล้ว)
const imageFileInput = document.getElementById('image_file');
if (imageFileInput) {
    imageFileInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const previewSrc = URL.createObjectURL(file);
            document.getElementById('preview-img').src = previewSrc;
        }
    });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>