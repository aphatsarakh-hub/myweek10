<?php
require_once __DIR__ . '/../../includes/boot.php';

$pageTitle = 'เพิ่มประเภทห้องพัก';
$error = '';
$success = '';

// ตรวจสอบการส่งฟอร์ม (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_name = trim($_POST['type_name'] ?? '');
    $price_per_night = floatval($_POST['price_per_night'] ?? 0);
    $capacity = intval($_POST['capacity'] ?? 1);
    $description = trim($_POST['description'] ?? '');
    
    $image_url = null;

    // 1. จัดการอัพโหลดรูปภาพ
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === UPLOAD_ERR_OK) {
        // อ้างอิง Path ตามที่ใช้ในหน้า room_list.php
        $uploadDir = __DIR__ . '/../../uploads/room/'; 
        
        // สร้างโฟลเดอร์ถ้ายังไม่มี
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExt = strtolower(pathinfo($_FILES['room_image']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'avif'];

        if (in_array($fileExt, $allowedExts)) {
            // ตั้งชื่อไฟล์ใหม่กันการซ้ำ
            $newFileName = uniqid('room_') . '.' . $fileExt;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['room_image']['tmp_name'], $destPath)) {
                $image_url = $newFileName;
            } else {
                $error = 'ไม่สามารถบันทึกไฟล์รูปภาพไปยังโฟลเดอร์ปลายทางได้';
            }
        } else {
            $error = 'รองรับเฉพาะไฟล์รูปภาพ (JPG, JPEG, PNG, WEBP, AVIF) เท่านั้น';
        }
    }

    // 2. บันทึกข้อมูลลงฐานข้อมูล (ถ้าไม่มี Error)
    if (empty($error) && !empty($type_name) && $price_per_night >= 0) {
        
        /* * ==========================================
         * TODO: เพิ่มโค้ด SQL บันทึกลง Database ของคุณที่นี่
         * ==========================================
         * ตัวอย่างเช่น:
         * $stmt = $pdo->prepare("INSERT INTO room_types (type_name, price_per_night, capacity, description, image_url) VALUES (?, ?, ?, ?, ?)");
         * $stmt->execute([$type_name, $price_per_night, $capacity, $description, $image_url]);
         */

        // แจ้งเตือนสำเร็จและรีไดเรกต์ไปหน้า room_list.php
        if (function_exists('flash')) {
            flash('message', 'เพิ่มประเภทห้องพักเรียบร้อยแล้ว');
        }
        header('Location: room_list.php');
        exit;

    } elseif (empty($error)) {
        $error = 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน';
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen bg-[#FAFAF8] p-6 lg:p-10 font-sans text-stone-800">
    
    <div class="mb-10 pb-6 border-b border-stone-200/80">
        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-[#C86428] mb-2">
            <span>Property Management</span>
            <span>&bull;</span>
            <span>Add New Room</span>
        </div>
        <h1 class="text-3xl font-light text-stone-900 tracking-tight">เพิ่มประเภทห้องพัก</h1>
        <p class="text-stone-500 mt-2 text-sm">สร้างประเภทห้องพักใหม่ และอัพโหลดรูปภาพห้องพักเข้าสู่ระบบ</p>
    </div>

    <div class="max-w-4xl bg-white rounded-2xl border border-stone-200/60 shadow-[0_4px_20px_rgb(0,0,0,0.02)] p-8">
        
        <?php if ($error): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl text-sm flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-lg"></i>
                <span><?= htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-stone-700 mb-2">ชื่อประเภทห้องพัก <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="type_name" 
                        placeholder="เช่น Deluxe Cat Suite" 
                        required
                        class="w-full px-4 py-3 bg-[#FAFAF8] border border-stone-200 rounded-xl text-sm text-stone-800 placeholder-stone-400 focus:bg-white focus:ring-1 focus:ring-[#C86428] focus:border-[#C86428] outline-none transition"
                        value="<?= htmlspecialchars($_POST['type_name'] ?? ''); ?>">
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-2">ราคาต่อคืน (บาท) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-stone-400 font-medium">฿</span>
                        <input 
                            type="number" 
                            name="price_per_night" 
                            step="0.01" 
                            min="0" 
                            placeholder="0.00" 
                            required
                            class="w-full pl-9 pr-4 py-3 bg-[#FAFAF8] border border-stone-200 rounded-xl text-sm text-stone-800 placeholder-stone-400 focus:bg-white focus:ring-1 focus:ring-[#C86428] focus:border-[#C86428] outline-none transition"
                            value="<?= htmlspecialchars($_POST['price_per_night'] ?? ''); ?>">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-2">รองรับแมว (ตัว) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fas fa-cat absolute left-4 top-1/2 -translate-y-1/2 text-stone-400"></i>
                        <input 
                            type="number" 
                            name="capacity" 
                            min="1" 
                            placeholder="1" 
                            required
                            class="w-full pl-10 pr-4 py-3 bg-[#FAFAF8] border border-stone-200 rounded-xl text-sm text-stone-800 placeholder-stone-400 focus:bg-white focus:ring-1 focus:ring-[#C86428] focus:border-[#C86428] outline-none transition"
                            value="<?= htmlspecialchars($_POST['capacity'] ?? ''); ?>">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-stone-700 mb-2">รูปภาพห้องพัก</label>
                    <div class="relative flex flex-col items-center justify-center border-2 border-dashed border-stone-300 bg-[#FAFAF8] rounded-xl p-8 hover:bg-stone-50 hover:border-[#C86428] transition-colors cursor-pointer group" onclick="document.getElementById('room_image').click()">
                        
                        <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center shadow-sm mb-4 group-hover:scale-105 transition-transform">
                            <i class="fas fa-image text-[#C86428] text-xl"></i>
                        </div>
                        <p class="text-sm font-medium text-stone-700 mb-1">คลิกเพื่อเลือกไฟล์รูปภาพ</p>
                        <p class="text-xs text-stone-400">รองรับไฟล์ JPG, PNG, WEBP ขนาดไม่เกิน 2MB</p>
                        
                        <input 
                            type="file" 
                            name="room_image" 
                            id="room_image" 
                            accept="image/jpeg, image/png, image/webp" 
                            class="hidden"
                            onchange="previewImage(this)">
                    </div>

                    <div id="image_preview_container" class="mt-4 hidden">
                        <p class="text-xs font-medium text-stone-500 mb-2">รูปภาพที่เลือก:</p>
                        <img id="image_preview" src="" alt="Preview" class="h-40 w-auto object-cover rounded-xl border border-stone-200 shadow-sm">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-stone-700 mb-2">รายละเอียดห้องพัก</label>
                    <textarea 
                        name="description" 
                        rows="4" 
                        placeholder="อธิบายสิ่งอำนวยความสะดวก เช่น มีกล้อง CCTV, เครื่องปรับอากาศ, พื้นที่ปีนป่าย..."
                        class="w-full px-4 py-3 bg-[#FAFAF8] border border-stone-200 rounded-xl text-sm text-stone-800 placeholder-stone-400 focus:bg-white focus:ring-1 focus:ring-[#C86428] focus:border-[#C86428] outline-none transition resize-none"><?= htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-stone-100 mt-8">
                <a href="room_list.php" class="px-5 py-2.5 rounded-xl text-sm font-medium text-stone-600 bg-white border border-stone-200 hover:bg-stone-50 transition">
                    ยกเลิก
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-medium text-white bg-[#2C2523] hover:bg-[#C86428] shadow-sm hover:shadow transition duration-200 flex items-center gap-2">
                    <i class="fas fa-save text-xs"></i> บันทึกข้อมูล
                </button>
            </div>

        </form>
    </div>
</div>

<script>
function previewImage(input) {
    const previewContainer = document.getElementById('image_preview_container');
    const previewImage = document.getElementById('image_preview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.classList.add('hidden');
        previewImage.src = '';
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>