<?php
// ตั้งค่า Session
session_set_cookie_params(['path' => '/']); 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. แก้ไขให้ตรงกับชื่อ Key ใน Session จริง (เปลี่ยนจาก account_id เป็น id)
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    die("กรุณาเข้าสู่ระบบก่อนใช้งานหน้านี้");
}

$account_id = $_SESSION['user']['id']; // ใช้ id จาก session
$base_back_path = "../../";

// ฟังก์ชัน URL
if (!function_exists('url')) {
    function url($path) { return "../../" . $path; }
}

// 2. เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "bambam_cat_hotel";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$alert_status = '';
$alert_message = '';
$active_tab = 'profile';

// ==========================================
// ส่วนหลังบ้านจัดการข้อมูล (BACKEND)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // --- 1. บันทึกข้อมูลส่วนตัว ---
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'update_profile') {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $phone = trim($_POST['phone']);

        if (empty($first_name) || empty($last_name) || empty($phone)) {
            $alert_status = 'danger';
            $alert_message = 'กรุณากรอกข้อมูลส่วนตัวให้ครบถ้วน';
        } else {
            $stmt = $conn->prepare("SELECT role, staff_id, customer_id FROM account WHERE account_id = ?");
            $stmt->bind_param("i", $account_id);
            $stmt->execute();
            $user_data = $stmt->get_result()->fetch_assoc();

            if ($user_data) {
                $conn->begin_transaction();
                try {
                    if ($user_data['role'] == 'customer' && !empty($user_data['customer_id'])) {
                        $upd = $conn->prepare("UPDATE customer SET first_name = ?, last_name = ?, phone = ? WHERE customer_id = ?");
                        $upd->bind_param("sssi", $first_name, $last_name, $phone, $user_data['customer_id']);
                        $upd->execute();
                    } else if ($user_data['role'] == 'admin') {
                        // admin แยกขาดจากตาราง staff แล้ว: แก้ข้อมูลของตัวเองที่ account โดยตรง
                        $upd = $conn->prepare("UPDATE account SET first_name = ?, last_name = ?, phone = ? WHERE account_id = ?");
                        $upd->bind_param("sssi", $first_name, $last_name, $phone, $account_id);
                        $upd->execute();
                    } else if (!empty($user_data['staff_id'])) {
                        $upd = $conn->prepare("UPDATE staff SET first_name = ?, last_name = ?, phone = ? WHERE staff_id = ?");
                        $upd->bind_param("sssi", $first_name, $last_name, $phone, $user_data['staff_id']);
                        $upd->execute();
                    }

                    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
                        $file_ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                            $new_filename = 'avatar_' . $user_data['role'] . '_' . time() . '.' . $file_ext;
                            $upload_dir = $base_back_path . "uploads/avatars/"; 
                            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir . $new_filename)) {
                                $path_for_db = "/uploads/avatars/" . $new_filename;
                                $stmt_img = $conn->prepare("UPDATE account SET profile_image = ? WHERE account_id = ?");
                                $stmt_img->bind_param("si", $path_for_db, $account_id);
                                $stmt_img->execute();
                            }
                        }
                    }
                    $conn->commit();
                    $alert_status = 'success';
                    $alert_message = 'อัปเดตข้อมูลเรียบร้อยแล้ว';
                } catch (Exception $e) {
                    $conn->rollback();
                    $alert_status = 'danger';
                    $alert_message = 'เกิดข้อผิดพลาดในการบันทึก';
                }
            }
        }
    }
    // --- 2. เปลี่ยนรหัสผ่าน ---
    elseif (isset($_POST['form_type']) && $_POST['form_type'] == 'change_password') {
        $active_tab = 'password';
        $old_pass = $_POST['old_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $conf_pass = $_POST['confirm_password'] ?? '';

        if ($new_pass !== $conf_pass) {
            $alert_status = 'danger';
            $alert_message = 'รหัสผ่านใหม่ไม่ตรงกัน';
        } else {
            $stmt = $conn->prepare("SELECT password FROM account WHERE account_id = ?");
            $stmt->bind_param("i", $account_id);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            
            if ($res && $old_pass === $res['password']) {
                $upd = $conn->prepare("UPDATE account SET password = ? WHERE account_id = ?");
                $upd->bind_param("si", $new_pass, $account_id);
                $upd->execute();
                $alert_status = 'success';
                $alert_message = 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว';
            } else {
                $alert_status = 'danger';
                $alert_message = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
            }
        }
    }
}

// 3. ดึงข้อมูล (เพิ่มการตรวจสอบ error กรณีไม่พบข้อมูล)
$stmt = $conn->prepare("SELECT a.*, s.first_name AS staff_first, s.last_name AS staff_last, s.phone AS staff_phone,
                               c.first_name AS cust_first, c.last_name AS cust_last, c.phone AS cust_phone
                        FROM account a
                        LEFT JOIN staff s ON a.staff_id = s.staff_id
                        LEFT JOIN customer c ON a.customer_id = c.customer_id
                        WHERE a.account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user_info = $stmt->get_result()->fetch_assoc();

// กำหนดค่าเริ่มต้นถ้า $user_info เป็น null เพื่อป้องกัน Error
if (!$user_info) { $user_info = ['role' => 'guest']; }

$first_name = ($user_info['role'] ?? '') == 'customer'
    ? ($user_info['cust_first'] ?? '')
    : (($user_info['role'] ?? '') == 'admin' ? ($user_info['first_name'] ?? '') : ($user_info['staff_first'] ?? ''));
$last_name = ($user_info['role'] ?? '') == 'customer'
    ? ($user_info['cust_last'] ?? '')
    : (($user_info['role'] ?? '') == 'admin' ? ($user_info['last_name'] ?? '') : ($user_info['staff_last'] ?? ''));
$phone = ($user_info['role'] ?? '') == 'customer'
    ? ($user_info['cust_phone'] ?? '')
    : (($user_info['role'] ?? '') == 'admin' ? ($user_info['phone'] ?? '') : ($user_info['staff_phone'] ?? ''));
$has_image = !empty($user_info['profile_image'] ?? '');
$image_src = $has_image ? $base_back_path . ltrim($user_info['profile_image'], '/') : '';

$pageTitle = 'ตั้งค่าบัญชีผู้ใช้งาน';
include($base_back_path . "includes/header.php"); 
?>

<style>
    .bambam-card { background: #FFFFFF; border: 1px solid #EDE7DF; border-radius: 12px; }
    .btn-bambam { background-color: #C05800; color: #FFFFFF; }
    .tab-link { color: #6B5B4B; cursor: pointer; }
    .tab-link.active { color: #C05800; background-color: #FBF3EC; border-left: 3px solid #C05800; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .input-bambam { border: 1px solid #EDE7DF; }
</style>

<div class="max-w-5xl mx-auto w-full p-4">
    <?php if (!empty($alert_status)): ?>
        <div class="mb-4 p-4 rounded-lg <?= $alert_status == 'success' ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800' ?>">
            <?= htmlspecialchars($alert_message) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="space-y-6">
            <div class="bambam-card p-6 text-center">
                <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-[#FBF3EC] flex items-center justify-center overflow-hidden text-[#C05800]">
                    <?php if($has_image): ?><img src="<?= $image_src ?>" class="w-full h-full object-cover"><?php else: ?><i class="fas fa-cat text-4xl"></i><?php endif; ?>
                </div>
                <h3 class="font-semibold"><?= htmlspecialchars($first_name . ' ' . $last_name) ?></h3>
                <span class="text-xs bg-[#FBF3EC] px-2 py-1 rounded-full"><?= htmlspecialchars($user_info['role'] ?? 'guest') ?></span>
            </div>
            <nav class="bambam-card flex flex-col p-2">
                <a onclick="switchProfileTab('profile')" id="nav-profile" class="tab-link p-3 <?= ($active_tab == 'profile') ? 'active' : '' ?>">ข้อมูลส่วนตัว</a>
                <a onclick="switchProfileTab('password')" id="nav-password" class="tab-link p-3 <?= ($active_tab == 'password') ? 'active' : '' ?>">เปลี่ยนรหัสผ่าน</a>
            </nav>
        </div>

        <div class="md:col-span-2">
            <div id="tab-profile" class="bambam-card p-8 tab-content <?= ($active_tab == 'profile') ? 'active' : '' ?>">
                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="form_type" value="update_profile">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <input type="text" name="first_name" class="p-2 border rounded input-bambam" value="<?= htmlspecialchars($first_name) ?>" placeholder="ชื่อ" required>
                        <input type="text" name="last_name" class="p-2 border rounded input-bambam" value="<?= htmlspecialchars($last_name) ?>" placeholder="นามสกุล" required>
                    </div>
                    <input type="text" name="phone" class="w-full p-2 border rounded input-bambam mb-4" value="<?= htmlspecialchars($phone) ?>" placeholder="เบอร์โทร" required>
                    <input type="file" name="profile_image" accept="image/*" class="mb-4">
                    <button type="submit" class="btn-bambam px-4 py-2 rounded">บันทึกข้อมูล</button>
                </form>
            </div>

            <div id="tab-password" class="bambam-card p-8 tab-content <?= ($active_tab == 'password') ? 'active' : '' ?>">
                <form action="profile.php" method="POST">
                    <input type="hidden" name="form_type" value="change_password">
                    <input type="password" name="old_password" class="w-full p-2 border rounded input-bambam mb-4" placeholder="รหัสผ่านปัจจุบัน" required>
                    <input type="password" name="new_password" class="w-full p-2 border rounded input-bambam mb-4" placeholder="รหัสผ่านใหม่" required>
                    <input type="password" name="confirm_password" class="w-full p-2 border rounded input-bambam mb-4" placeholder="ยืนยันรหัสผ่านใหม่" required>
                    <button type="submit" class="btn-bambam px-4 py-2 rounded">อัปเดตรหัสผ่าน</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function switchProfileTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-link').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + tabName).classList.add('active');
    document.getElementById('nav-' + tabName).classList.add('active');
}
</script>

<?php $conn->close(); include($base_back_path . "includes/footer.php"); ?>