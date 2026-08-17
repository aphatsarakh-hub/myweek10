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

$error = '';

// --- ประมวลผลเมื่อผู้ใช้กดปุ่ม "บันทึกข้อมูลพนักงาน" (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $gender     = $_POST['gender'] ?? '';
    $birth_date = !empty($_POST['birth_date']) ? $_POST['birth_date'] : null;
    $position   = trim($_POST['position'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $hire_date  = !empty($_POST['hire_date']) ? $_POST['hire_date'] : date('Y-m-d'); // ถ้าไม่ระบุให้เป็นวันนี้
    $salary     = !empty($_POST['salary']) ? floatval($_POST['salary']) : 0.00;
    $status     = $_POST['status'] ?? 'ทำงาน';

    // ตรวจสอบฟิลด์ที่จำเป็น
    if (empty($first_name) || empty($position)) {
        $error = 'กรุณากรอกชื่อจริงและตำแหน่งงานพนักงาน';
    } else {

        // --- เพิ่มข้อมูลลงฐานข้อมูลจริง หากไม่มี Error ---
        // หมายเหตุ: เอาระบบอัปโหลดรูปภาพออกแล้ว รูปโปรไฟล์พนักงานตอนนี้ใช้
        // account.profile_image ที่พนักงานอัปโหลดเองผ่านหน้า "โปรไฟล์" (profile.php) แทน
        if (empty($error)) {
            try {
                $sql = "INSERT INTO staff (first_name, last_name, gender, birth_date, position, phone, email, address, hire_date, salary, status) 
                        VALUES (:first_name, :last_name, :gender, :birth_date, :position, :phone, :email, :address, :hire_date, :salary, :status)";
                
                $stmt = $local_conn->prepare($sql);
                $stmt->execute([
                    ':first_name' => $first_name,
                    ':last_name'  => $last_name,
                    ':gender'     => $gender,
                    ':birth_date' => $birth_date,
                    ':position'   => $position,
                    ':phone'      => $phone,
                    ':email'      => $email,
                    ':address'    => $address,
                    ':hire_date'  => $hire_date,
                    ':salary'     => $salary,
                    ':status'     => $status
                ]);
                
                // ✨ บันทึกเสร็จเรียบร้อยแล้ว เด้งกลับไปหน้า staff_list.php ทันที
                header("Location: staff_list.php");
                exit;
                
            } catch (Exception $e) {
                $error = 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'เพิ่มพนักงานใหม่ (Admin)';
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
        max-width: 800px;
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

    .form-grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
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
    .alert-danger { background-color: #FEECEB; color: #C5221F; border: 1px solid #FAD2CF; }

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
        .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; gap: 0; }
        .form-container { padding: 24px; }
        .image-preview-wrapper { flex-direction: column; text-align: center; }
    }
</style>

<div class="form-container">
    
    <div class="form-header">
        <a href="staff_list.php" class="btn-back-circle" title="ย้อนกลับ">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h2>เพิ่มพนักงานใหม่เข้าสู่ระบบ</h2>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        
        <div class="form-grid-2">
            <div class="form-group">
                <label for="first_name">ชื่อจริง <span style="color: #C5221F;">*</span></label>
                <input type="text" id="first_name" name="first_name" class="form-control" placeholder="กรอกชื่อจริงพนักงาน" required>
            </div>
            
            <div class="form-group">
                <label for="last_name">นามสกุล</label>
                <input type="text" id="last_name" name="last_name" class="form-control" placeholder="กรอกนามสกุล">
            </div>
        </div>

        <div class="form-grid-3">
            <div class="form-group">
                <label for="gender">เพศ</label>
                <select id="gender" name="gender" class="form-control">
                    <option value="">-- เลือกเพศ --</option>
                    <option value="ชาย">ชาย</option>
                    <option value="หญิง">หญิง</option>
                    <option value="อื่นๆ">อื่นๆ</option>
                </select>
            </div>

            <div class="form-group">
                <label for="birth_date">วันเกิด</label>
                <input type="date" id="birth_date" name="birth_date" class="form-control">
            </div>

            <div class="form-group">
                <label for="position">ตำแหน่งงาน <span style="color: #C5221F;">*</span></label>
                <input type="text" id="position" name="position" class="form-control" placeholder="เช่น พนักงานเก็บเงิน, Caretaker" required>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="phone">เบอร์โทรศัพท์</label>
                <input type="text" id="phone" name="phone" class="form-control" placeholder="เช่น 087XXXXXXX">
            </div>
            
            <div class="form-group">
                <label for="email">อีเมล</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="example@bambam.com">
            </div>
        </div>

        <div class="form-grid-3">
            <div class="form-group">
                <label for="salary">เงินเดือน (บาท)</label>
                <input type="number" id="salary" name="salary" step="0.01" class="form-control" placeholder="0.00">
            </div>

            <div class="form-group">
                <label for="hire_date">วันเริ่มงาน</label>
                <input type="date" id="hire_date" name="hire_date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label for="status">สถานะปัจจุบัน</label>
                <select id="status" name="status" class="form-control">
                    <option value="ทำงาน">ทำงาน</option>
                    <option value="พักงาน">พักงาน</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="address">ที่อยู่พนักงาน</label>
            <textarea id="address" name="address" class="form-control" rows="3" placeholder="กรอกบ้านเลขที่, ตำบล, อำเภอ, จังหวัด"></textarea>
        </div>

        <div class="form-actions">
            <a href="staff_list.php" class="btn-cancel">ยกเลิกและย้อนกลับ</a>
            <button type="submit" class="btn-submit">
                <i class="fas fa-plus"></i> เพิ่มพนักงานใหม่
            </button>
        </div>

    </form>

</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>