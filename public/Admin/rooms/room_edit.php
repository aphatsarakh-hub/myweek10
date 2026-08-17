<?php
require_once __DIR__ . '/../../includes/boot.php';

$pageTitle = 'แก้ไขห้อง (Admin)';
$roomtype_id = intval($_GET['id'] ?? 0);

// ดึงข้อมูลห้อง
if (function_exists('fetch_roomtype_by_id')) {
    $room = fetch_roomtype_by_id($roomtype_id);
} else {
    // ข้อมูลจำลองกรณีไม่มีฟังก์ชัน
    $room = ['type_name' => '', 'description' => '', 'price_per_night' => 0, 'capacity' => 1, 'image_url' => ''];
}

if (!$room) {
    if (function_exists('flash')) flash('message', 'ไม่พบประเภทห้องพักที่ต้องการแก้ไข');
    header('Location: room_list.php');
    exit;
}

$error = '';

// ตรวจสอบเมื่อมีการกดปุ่มบันทึก
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_name = trim($_POST['type_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price_per_night = floatval($_POST['price_per_night'] ?? 0);
    $capacity = intval($_POST['capacity'] ?? 0);
    
    // จัดการรูปภาพ
    $image_url = $room['image_url'] ?? null;
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/room/'; 
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $fileExt = strtolower(pathinfo($_FILES['room_image']['name'], PATHINFO_EXTENSION));
        if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp'])) {
            $newFileName = uniqid('room_') . '.' . $fileExt;
            if (move_uploaded_file($_FILES['room_image']['tmp_name'], $uploadDir . $newFileName)) {
                $image_url = $newFileName;
            }
        }
    }

    if ($type_name === '' || $price_per_night <= 0 || $capacity <= 0) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วนและถูกต้อง';
    } else {
        // บันทึกข้อมูล
        if (function_exists('update_roomtype')) {
            // หากระบบคุณรองรับการบันทึกรูป ให้เพิ่ม $image_url เข้าไปในฟังก์ชัน update_roomtype ด้วย
            if (update_roomtype($roomtype_id, $type_name, $description, $price_per_night, $capacity)) {
                if (function_exists('flash')) flash('message', 'ปรับปรุงประเภทห้องพักเรียบร้อยแล้ว');
                header('Location: room_list.php');
                exit;
            } else {
                $error = 'ไม่สามารถบันทึกการแก้ไขได้';
            }
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap');
    
    .admin-wrapper {
        font-family: 'Prompt', sans-serif;
        background-color: #FAFAF8;
        color: #2C2523;
        padding: 2rem;
        min-height: 100vh;
        box-sizing: border-box;
    }
    
    .admin-wrapper * {
        box-sizing: border-box;
    }

    .page-header {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #EAE5E0;
    }

    .page-header h1 {
        font-size: 1.8rem;
        font-weight: 500;
        margin: 0 0 0.5rem 0;
        color: #2C2523;
    }

    .page-header p {
        margin: 0;
        color: #8A7363;
        font-size: 0.95rem;
    }

    .edit-card {
        background: #FFFFFF;
        max-width: 800px;
        margin: 0 auto;
        padding: 2.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        border: 1px solid #EAE5E0;
    }

    .alert-error {
        background-color: #FFF0ED;
        color: #D32F2F;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        border-left: 4px solid #D32F2F;
        font-size: 0.95rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: #4A3B32;
        font-size: 0.95rem;
    }

    .form-group input[type="text"],
    .form-group input[type="number"],
    .form-group textarea {
        padding: 0.8rem 1rem;
        border: 1px solid #DCD3C9;
        border-radius: 8px;
        font-family: 'Prompt', sans-serif;
        font-size: 0.95rem;
        color: #2C2523;
        background-color: #FAFAF8;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #C86428;
        box-shadow: 0 0 0 3px rgba(200, 100, 40, 0.1);
        background-color: #FFFFFF;
    }

    .image-upload-box {
        border: 2px dashed #DCD3C9;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        background-color: #FAFAF8;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .image-upload-box:hover {
        border-color: #C86428;
        background-color: #FFF9F5;
    }

    .image-upload-box input[type="file"] {
        display: none;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 2.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #EAE5E0;
    }

    .btn-group {
        display: flex;
        gap: 1rem;
    }

    .btn {
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        font-family: 'Prompt', sans-serif;
        font-weight: 500;
        font-size: 0.95rem;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-primary {
        background-color: #2C2523;
        color: white;
    }

    .btn-primary:hover {
        background-color: #C86428;
        box-shadow: 0 4px 12px rgba(200, 100, 40, 0.2);
    }

    .btn-secondary {
        background-color: white;
        color: #4A3B32;
        border: 1px solid #DCD3C9;
    }

    .btn-secondary:hover {
        background-color: #FAFAF8;
    }
    
    .btn-danger {
        color: #D32F2F;
        background-color: transparent;
    }
    
    .btn-danger:hover {
        background-color: #FFF0ED;
    }

    #preview-img {
        max-width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        margin-top: 1rem;
        border: 1px solid #DCD3C9;
        display: none;
    }
</style>

<div class="admin-wrapper">
    
    <div class="page-header">
        <h1>แก้ไขประเภทห้องพัก</h1>
        <p>ปรับปรุงข้อมูลรายละเอียดและราคาของห้องพักรหัส RM-<?php echo str_pad($roomtype_id, 2, '0', STR_PAD_LEFT); ?></p>
    </div>

    <div class="edit-card">
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <div class="form-grid">
                
                <div class="form-group full-width">
                    <label>ชื่อประเภทห้องพัก</label>
                    <input type="text" name="type_name" required 
                           value="<?php echo htmlspecialchars($_POST['type_name'] ?? $room['type_name']); ?>" 
                           placeholder="เช่น Deluxe Cat Suite">
                </div>

                <div class="form-group">
                    <label>ราคาต่อคืน (บาท)</label>
                    <input type="number" name="price_per_night" step="0.01" min="0" required 
                           value="<?php echo htmlspecialchars($_POST['price_per_night'] ?? $room['price_per_night']); ?>">
                </div>

                <div class="form-group">
                    <label>รองรับแมว (ตัว)</label>
                    <input type="number" name="capacity" min="1" required 
                           value="<?php echo htmlspecialchars($_POST['capacity'] ?? $room['capacity']); ?>">
                </div>

                <div class="form-group full-width">
                    <label>อัพโหลดรูปภาพใหม่ (ไม่บังคับ)</label>
                    <label class="image-upload-box">
                        <div style="color: #C86428; margin-bottom: 0.5rem; font-size: 1.5rem;">📷</div>
                        <div style="color: #4A3B32; font-weight: 500;">คลิกเพื่อเลือกไฟล์รูปภาพ</div>
                        <div style="color: #8A7363; font-size: 0.85rem; margin-top: 0.2rem;">รองรับ JPG, PNG, WEBP</div>
                        <input type="file" name="room_image" accept="image/jpeg, image/png, image/webp" onchange="previewImage(this)">
                    </label>
                    <img id="preview-img" src="" alt="Preview">
                </div>

                <div class="form-group full-width">
                    <label>รายละเอียดห้องพัก</label>
                    <textarea name="description" rows="5" 
                              placeholder="อธิบายสิ่งอำนวยความสะดวก..."><?php echo htmlspecialchars($_POST['description'] ?? $room['description']); ?></textarea>
                </div>

            </div>

            <div class="form-actions">
                <a href="room_delete.php?id=<?php echo $roomtype_id; ?>" 
                   onclick="return confirm('ยืนยันการลบห้องพักนี้?');" 
                   class="btn btn-danger">
                    ลบห้องนี้
                </a>
                
                <div class="btn-group">
                    <a href="room_list.php" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                </div>
            </div>

        </form>

    </div>
</div>

<script>
    function previewImage(input) {
        var preview = document.getElementById('preview-img');
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.style.display = 'none';
        }
    }
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>