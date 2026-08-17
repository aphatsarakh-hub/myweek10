<?php
require_once __DIR__ . '/../../includes/boot.php';
$pageTitle = 'เพิ่มบริการเสริมใหม่';

// กำหนดตัวแปรไว้รองรับกรณีแสดงค่าเดิมในฟอร์มเมื่อเกิดข้อผิดพลาด (Old Input ค่าจะได้ไม่หาย)
$name  = '';
$price = '';
$desc  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name  = trim($_POST['service_name'] ?? '');
    $price = $_POST['price'] ?? '';
    $desc  = trim($_POST['service_detail'] ?? '');
    $image_name = '';
    $upload_ok = true;

    // อัปโหลดรูปภาพ
    if (!empty($_FILES['service_image']['name'])) {

        $upload_dir = '../../uploads/services/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // [แก้ไขความปลอดภัย] ตรวจสอบนามสกุลไฟล์ที่อนุญาต
        $file_extension = strtolower(pathinfo($_FILES['service_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($file_extension, $allowed_extensions)) {
            $error = "รูปแบบไฟล์ภาพไม่ถูกต้อง อนุญาตเฉพาะ JPG, JPEG, PNG, WEBP และ GIF เท่านั้น";
            $upload_ok = false;
        } else {
            // ตั้งชื่อไฟล์ใหม่ด้วยการสุ่มพ่วงเวลา เพื่อป้องกันชื่อไฟล์ซ้ำและอักขระแปลกปลอม
            $image_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_extension;

            if (!move_uploaded_file(
                $_FILES['service_image']['tmp_name'],
                $upload_dir . $image_name
            )) {
                $error = "ไม่สามารถอัปโหลดรูปภาพได้ กรุณาตรวจสอบสิทธิ์โฟลเดอร์ปลายทาง";
                $image_name = '';
                $upload_ok = false;
            }
        }
    }

    // บันทึกข้อมูล (ทำต่อเมื่อการอัปโหลดรูปภาพไม่มีปัญหา หรือไม่มีการอัปโหลดรูป)
    if ($upload_ok) {
        
        // ตรวจสอบตัวแปรการเชื่อมต่อ Database เผื่อใช้ชื่ออื่น
        if (!isset($mysqli)) {
            $error = "ไม่พบตัวแปรเชื่อมต่อฐานข้อมูล (\$mysqli) กรุณาตรวจสอบในไฟล์ boot.php";
        } else {
            $stmt = $mysqli->prepare("
                INSERT INTO service
                (service_name, service_detail, price, image_path, status)
                VALUES (?, ?, ?, ?, 'Active')
            ");

            if ($stmt) {
                // แปลงราคาเป็น float ก่อน bind
                $price_float = floatval($price);

                $stmt->bind_param(
                    "ssds",
                    $name,
                    $desc,
                    $price_float,
                    $image_name
                );

                if ($stmt->execute()) {
                    header("Location: service_list.php?added=1");
                    exit;
                } else {
                    $error = "บันทึกข้อมูลไม่สำเร็จ : " . $stmt->error;
                }

                $stmt->close();
            } else {
                $error = "Prepare Failed : " . $mysqli->error;
            }
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<style>
    .bg-warm-canvas { background-color: #F9F7F5; }
    .text-deep-brown { color: #5D4037; }
    .bg-rust-orange { background-color: #D35400; }
    .bg-rust-orange:hover { background-color: #A04000; }
    .input-border {
        border: 2px solid #E0D5CE;
        background: #FFFFFF;
    }
    .input-border:focus {
        border-color: #D35400;
        outline: none;
    }
</style>

<div class="max-w-3xl mx-auto py-10 bg-warm-canvas">

    <div class="mb-8 pl-4">
        <h1 class="text-3xl font-black text-deep-brown">
            สร้างบริการเสริมใหม่
        </h1>
        <p class="text-gray-600">
            เพิ่มรายการบริการ เพื่อยกระดับการดูแลน้องแมวให้ดียิ่งขึ้น
        </p>
    </div>

    <?php if (isset($error)) : ?>
        <div class="p-4 mb-6 text-red-700 bg-red-100 rounded-2xl font-bold">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post"
          enctype="multipart/form-data"
          class="bg-white p-10 rounded-[32px] border border-[#E0D5CE] shadow-lg shadow-gray-100">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <div class="space-y-6">

                <div>
                    <label class="block text-sm font-bold text-deep-brown mb-2">
                        ชื่อบริการ
                    </label>
                    <input
                        type="text"
                        name="service_name"
                        value="<?= htmlspecialchars($name) ?>"
                        class="w-full p-4 rounded-2xl input-border"
                        placeholder="เช่น อาบน้ำ ตัดเล็บ..."
                        required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-deep-brown mb-2">
                        ราคา (บาท)
                    </label>
                    <input
                        type="number"
                        name="price"
                        step="0.01"
                        value="<?= htmlspecialchars($price) ?>"
                        class="w-full p-4 rounded-2xl input-border"
                        placeholder="0.00"
                        required>
                </div>

            </div>

            <div class="space-y-6">

                <div>
                    <label class="block text-sm font-bold text-deep-brown mb-2">
                        รูปภาพบริการ
                    </label>
                    <input
                        type="file"
                        name="service_image"
                        accept="image/*"
                        class="w-full p-3 rounded-2xl border-2 border-dashed border-[#D35400]/30 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-bold text-deep-brown mb-2">
                        รายละเอียด
                    </label>
                    <textarea
                        name="service_detail"
                        rows="4"
                        class="w-full p-4 rounded-2xl input-border"
                        placeholder="อธิบายรายละเอียดของบริการ..."><?= htmlspecialchars($desc) ?></textarea>
                </div>

            </div>

        </div>

        <div class="mt-10 flex gap-4">

            <button
                type="submit"
                class="flex-1 bg-rust-orange text-white py-4 rounded-2xl font-black transition shadow-xl shadow-orange-100">
                บันทึกข้อมูล
            </button>

            <a href="service_list.php"
               class="px-8 py-4 bg-gray-100 text-gray-700 rounded-2xl font-bold hover:bg-gray-200">
                ยกเลิก
            </a>

        </div>

    </form>

</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>